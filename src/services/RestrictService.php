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
use Dxw\CIDR\IP;
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

        if(IpRestrictor::$plugin->getSettings()->getEnabledControlPanel()) {
            $allowed = false;
            $userIp = Craft::$app->getRequest()->getRemoteIP();

            $allowed = self::checkIp(IpRestrictor::$plugin->getSettings()->ipWhitelistControlPanel, $userIp);
        }

        if(!$allowed) {
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodControlPanel() == SettingsModel::METHOD_REDIRECT) {
                $redirect = IpRestrictor::$plugin->getSettings()->getRedirectControlPanel();
                if(!empty($redirect)) {
                    IpRestrictor::$plugin->log('No IP match for control panel, redirecting to '.$redirect);
                    Craft::$app->response->redirect($redirect);
                    Craft::$app->end();
                } else {
                    IpRestrictor::$plugin->log('No IP match for control panel but no redirect found, redirecting to front-end of primary site');
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
                        IpRestrictor::$plugin->log('No IP match for control panel, rendering template '.$template);
                    } catch (\Throwable $th) {
                        IpRestrictor::$plugin->log('No IP match for control panel but error rendering template '.$template.', throwing exception');
                        throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                    }
                    Craft::$app->end();
                } else {
                    IpRestrictor::$plugin->log('No IP match for control panel but no template found, throwing exception');
                    throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                }
            } else {
                IpRestrictor::$plugin->log('No IP match for control panel and no restriction method found, throwing exception');
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

        if(IpRestrictor::$plugin->getSettings()->getEnabledFrontEnd()) {
            $allowed = false;
            $userIp = Craft::$app->getRequest()->getRemoteIP();

            $allowed = self::checkIp(IpRestrictor::$plugin->getSettings()->ipWhitelistFrontEnd, $userIp);
        }

        if(!$allowed) {
            if(IpRestrictor::$plugin->getSettings()->getRestrictionMethodFrontEnd() == SettingsModel::METHOD_REDIRECT) {
                $redirect = IpRestrictor::$plugin->getSettings()->getRedirectFrontEnd();
                if(!empty($redirect)) {
                    IpRestrictor::$plugin->log('No IP match for front-end, redirecting to '.$redirect);
                    Craft::$app->response->redirect($redirect);
                    Craft::$app->end();
                } else {
                    IpRestrictor::$plugin->log('No IP match for front-end but no redirect found, throwing exception');
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
                        IpRestrictor::$plugin->log('No IP match for control panel, rendering template '.$template);
                    } catch (\Throwable $th) {
                        IpRestrictor::$plugin->log('No IP match for control panel but error rendering template '.$template.', throwing exception');
                        throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                    }
                    Craft::$app->end();
                } else {
                    IpRestrictor::$plugin->log('No IP match for front-end but no template found, throwing exception');
                    throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
                }
            } else {
                IpRestrictor::$plugin->log('No IP match for front-end and no restriction method found, throwing exception');
                throw new HttpException(403, Craft::t('ip-restrictor', 'accessDenied'));
            }
        }
    }

    /**
     * @var bool
     */
    public static function checkIp($whitelist, $userIp): bool
    {
        foreach($whitelist as $ipCidr) {
            $result = IP::contains($ipCidr[0], $userIp);
            $match = $result->unwrap();

            if ($match) {
                return true;
            }
        }

        return false;
    }
}