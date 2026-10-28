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

use Craft;
use craft\base\Component;
use craft\web\View;
use yii\helpers\IpHelper;
use yii\validators\IpValidator;
use yii\web\HttpException;

/**
 * Restrict Service
 *
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
class RestrictService extends Component
{
    /**
     * Determine if control panel request should be restricted, redirect or render template
     */
    public function restrictControlPanel(): void
    {
        $allowed = true;
        $userIp = Craft::$app->getRequest()->getUserIP();

        if(IpRestrictor::$plugin->getSettings()->getEnabledControlPanel()) {
            $allowed = false;

            $allowed = self::checkIp(IpRestrictor::$plugin->getSettings()->ipWhitelistControlPanel, $userIp);
        }

        if(!$allowed) {
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodControlPanel() == SettingsModel::METHOD_REDIRECT) {
                $redirect = IpRestrictor::$plugin->getSettings()->getRedirectControlPanel();
                if(!empty($redirect)) {
                    IpRestrictor::info($userIp .' does not match whitelist for control panel, redirecting to '.$redirect);
                    Craft::$app->response->redirect($redirect);
                    Craft::$app->end();
                } else {
                    IpRestrictor::info($userIp .' does not match whitelist for control panel but no redirect found, redirecting to front-end of primary site');
                    Craft::$app->response->redirect(Craft::$app->getSites()->getPrimarySite()->getBaseUrl());
                    Craft::$app->end();
                }
            } else
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodControlPanel() == SettingsModel::METHOD_TEMPLATE) {
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
                $template = IpRestrictor::$plugin->getSettings()->getTemplateControlPanel();
                if(!empty($template)) {
                    try {
                        echo Craft::$app->view->renderTemplate($template);
                        IpRestrictor::info($userIp .' does not match whitelist for control panel, rendering template '.$template);
                    } catch (\Throwable $th) {
                        IpRestrictor::error($userIp .' does not match whitelist for control panel but error rendering template '.$template.', throwing exception');
                        throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                    }
                    Craft::$app->end();
                } else {
                    IpRestrictor::error($userIp .' does not match whitelist for control panel but no template found, throwing exception');
                    throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                }
            } else {
                IpRestrictor::error($userIp .' does not match whitelist for control panel and no restriction method found, throwing exception');
                throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
            }
        }
    }

    /**
     * Determine if front-end request should be restricted, redirect or render template
     */
    public function restrictFrontEnd(): void
    {
        $allowed = true;
        $userIp = Craft::$app->getRequest()->getUserIP();

        if(IpRestrictor::$plugin->getSettings()->getEnabledFrontEnd()) {
            $allowed = false;

            $allowed = self::checkIp(IpRestrictor::$plugin->getSettings()->ipWhitelistFrontEnd, $userIp);
        }

        if(!$allowed) {
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodFrontEnd() == SettingsModel::METHOD_REDIRECT) {
                $redirect = IpRestrictor::$plugin->getSettings()->getRedirectFrontEnd();
                if(!empty($redirect)) {
                    IpRestrictor::info($userIp .' does not match whitelist for front-end, redirecting to '.$redirect);
                    Craft::$app->response->redirect($redirect);
                    Craft::$app->end();
                } else {
                    IpRestrictor::error($userIp .' does not match whitelist for front-end but no redirect found, throwing exception');
                    throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                    Craft::$app->end();
                }
            }else
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodFrontEnd() == SettingsModel::METHOD_TEMPLATE) {
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);
                $template = IpRestrictor::$plugin->getSettings()->getTemplateFrontEnd();
                if(!empty($template)) {
                    try {
                        echo Craft::$app->view->renderTemplate($template);
                        IpRestrictor::info($userIp .' does not match whitelist for front-end, rendering template '.$template);
                    } catch (\Throwable $th) {
                        IpRestrictor::error($userIp .' does not match whitelist for front-end but error rendering template '.$template.', throwing exception');
                        throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                    }
                    Craft::$app->end();
                } else {
                    IpRestrictor::error($userIp .' does not match whitelist for front-end but no template found, throwing exception');
                    throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                }
            } else {
                IpRestrictor::error($userIp .' does not match whitelist for front-end and no restriction method found, throwing exception');
                throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
            }
        }
    }

    /**
     * @var bool
     */
    public static function checkIp($whitelist, $userIp): bool
    {
        // IpHelper::inRange() performs no validation of its own, and the whitelist
        // can come straight from config, so vet both sides first
        if (!is_string($userIp) || !filter_var($userIp, FILTER_VALIDATE_IP)) {
            return false;
        }

        $validator = new IpValidator(['subnet' => null]);

        foreach($whitelist as $ipCidr) {
            $entry = $ipCidr[0] ?? null;

            if (!$validator->validate($entry)) {
                // Invalid format, skip this entry
                IpRestrictor::error('Invalid IP/CIDR whitelist entry: ' . (is_scalar($entry) ? $entry : gettype($entry)));
                continue;
            }

            // Handles both single IPs (exact match) and CIDR ranges
            if (IpHelper::inRange($userIp, $entry)) {
                return true;
            }
        }

        return false;
    }
}