<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

namespace jrrdnx\iprestrictor\services;

use jrrdnx\iprestrictor\IpRestrictor;
use jrrdnx\iprestrictor\models\SettingsModel;
use CraftCms\Cms\Support\Facades\I18N;
use CraftCms\Cms\Twig\TemplateRenderer;
use Illuminate\Http\Exceptions\HttpResponseException;
use IPTools\IP;
use IPTools\Network;

/**
 * Restrict Service
 *
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
class RestrictService
{
    /**
     * Determine if control panel request should be restricted, then redirect or render template.
     */
    public function restrictControlPanel(): void
    {
        /** @var IpRestrictor $plugin */
        $plugin = IpRestrictor::getInstance();
        $settings = $plugin->getSettings();

        if (!$settings->getEnabledControlPanel()) {
            return;
        }

        $userIp = request()->ip();
        if (self::checkIp($settings->ipWhitelistControlPanel, $userIp)) {
            return;
        }

        if ($settings->getRestrictionMethodControlPanel() === SettingsModel::METHOD_REDIRECT) {
            $redirect = $settings->getRedirectControlPanel();
            if (!empty($redirect)) {
                IpRestrictor::info($userIp . ' does not match whitelist for control panel, redirecting to ' . $redirect);
            } else {
                $redirect = url('/');
                IpRestrictor::info($userIp . ' does not match whitelist for control panel but no redirect found, redirecting to site root');
            }
            throw new HttpResponseException(redirect($redirect));
        }

        if ($settings->getRestrictionMethodControlPanel() === SettingsModel::METHOD_TEMPLATE) {
            $template = $settings->getTemplateControlPanel();
            if (!empty($template)) {
                try {
                    $html = app(TemplateRenderer::class)->renderTemplate($template);
                    IpRestrictor::info($userIp . ' does not match whitelist for control panel, rendering template ' . $template);
                    throw new HttpResponseException(response($html, 403));
                } catch (HttpResponseException $e) {
                    throw $e;
                } catch (\Throwable $th) {
                    IpRestrictor::error($userIp . ' does not match whitelist for control panel but error rendering template ' . $template . ', throwing 403');
                }
            }
        }

        IpRestrictor::error($userIp . ' does not match whitelist for control panel, throwing 403');
        abort(403, I18N::translate('accessDenied', [], 'ip-restrictor'));
    }

    /**
     * Determine if front-end request should be restricted, then redirect or render template.
     */
    public function restrictFrontEnd(): void
    {
        /** @var IpRestrictor $plugin */
        $plugin = IpRestrictor::getInstance();
        $settings = $plugin->getSettings();

        if (!$settings->getEnabledFrontEnd()) {
            return;
        }

        $userIp = request()->ip();
        if (self::checkIp($settings->ipWhitelistFrontEnd, $userIp)) {
            return;
        }

        if ($settings->getRestrictionMethodFrontEnd() === SettingsModel::METHOD_REDIRECT) {
            $redirect = $settings->getRedirectFrontEnd();
            if (!empty($redirect)) {
                IpRestrictor::info($userIp . ' does not match whitelist for front-end, redirecting to ' . $redirect);
                throw new HttpResponseException(redirect($redirect));
            }
            IpRestrictor::error($userIp . ' does not match whitelist for front-end but no redirect found, throwing 403');
            abort(403, I18N::translate('accessDenied', [], 'ip-restrictor'));
        }

        if ($settings->getRestrictionMethodFrontEnd() === SettingsModel::METHOD_TEMPLATE) {
            $template = $settings->getTemplateFrontEnd();
            if (!empty($template)) {
                try {
                    $html = app(TemplateRenderer::class)->renderTemplate($template);
                    IpRestrictor::info($userIp . ' does not match whitelist for front-end, rendering template ' . $template);
                    throw new HttpResponseException(response($html, 403));
                } catch (HttpResponseException $e) {
                    throw $e;
                } catch (\Throwable $th) {
                    IpRestrictor::error($userIp . ' does not match whitelist for front-end but error rendering template ' . $template . ', throwing 403');
                }
            }
        }

        IpRestrictor::error($userIp . ' does not match whitelist for front-end, throwing 403');
        abort(403, I18N::translate('accessDenied', [], 'ip-restrictor'));
    }

    /**
     * Returns true if $userIp matches any entry in $whitelist.
     */
    public static function checkIp(array $whitelist, ?string $userIp): bool
    {
        if ($userIp === null) {
            return false;
        }

        foreach ($whitelist as $ipCidr) {
            $entry = $ipCidr[0];
            // Handle entries that were flagged as errors during validation
            if (is_array($entry)) {
                $entry = $entry['value'] ?? '';
            }

            try {
                $userIpObj = IP::parse($userIp);

                if (!str_contains($entry, '/')) {
                    $entryIp = IP::parse($entry);
                    if ($entryIp->inAddr() === $userIpObj->inAddr()) {
                        return true;
                    }
                } else {
                    $network = Network::parse($entry);
                    $firstIp = $network->getFirstIP();
                    $lastIp = $network->getLastIP();
                    if (
                        strcmp($userIpObj->inAddr(), $firstIp->inAddr()) >= 0 &&
                        strcmp($userIpObj->inAddr(), $lastIp->inAddr()) <= 0
                    ) {
                        return true;
                    }
                }
            } catch (\Exception $e) {
                IpRestrictor::error($e->getMessage());
                continue;
            }
        }

        return false;
    }
}
