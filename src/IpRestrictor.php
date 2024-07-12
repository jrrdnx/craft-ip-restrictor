<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

namespace jrrdnx\iprestrictor;

use jrrdnx\iprestrictor\models\SettingsModel;
use jrrdnx\iprestrictor\services\RestrictService;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\log\MonologTarget;
use craft\services\Plugins;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use yii\base\Event;

/**
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 *
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class IpRestrictor extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var IpRestrictor
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $schemaVersion = '1.0.0';

    public function init()
    {
        if(Craft::$app->getRequest()->getIsConsoleRequest())
		{
			$this->controllerNamespace = 'jrrdnx\iprestrictor\console\controllers';
		}

		parent::init();
        self::$plugin = $this;

        $this->_registerLogTarget();

        $this->setComponents([
			'restrict' => RestrictService::class,
		]);

        if(Craft::$app->getRequest()->getIsCpRequest()) {
            $this->restrict->restrictControlPanel();
        }

        if(Craft::$app->getRequest()->getIsSiteRequest()) {
            $this->restrict->restrictFrontEnd();
        }

        // Redirect to plugin settings after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    if(!Craft::$app->getRequest()->getIsConsoleRequest()) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/ip-restrictor'))->send();
                    }
                }
            }
        );

		Craft::info(
            Craft::t(
                'ip-restrictor',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
	}

    /**
     * Create and return the model used to store the plugin's settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel(): ?Model
    {
        return new SettingsModel();
    }

    /**
     * Return the rendered settings HTML
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $restrictionMethods = [];
        foreach($this->getSettings()->getRestrictionMethods() as $restrictionMethod) {
            $restrictionMethods[] = [
                'label' => Craft::t('ip-restrictor', $restrictionMethod),
                'value' => $restrictionMethod
            ];
        }

        return Craft::$app->view->renderTemplate(
            'ip-restrictor/settings',
            [
                'restrictionMethods' => $restrictionMethods,
                'settings' => $this->getSettings()
            ]
        );
    }

    /**
     * Logs an informational message to our custom log target.
     */
    public static function info(string $message): void
    {
        Craft::info($message, 'ip-restrictor');
    }

    /**
     * Logs an error message to our custom log target.
     */
    public static function error(string $message): void
    {
        Craft::error($message, 'ip-restrictor');
    }

    /**
     * Registers a custom log target, keeping the format as simple as possible.
     */
    private function _registerLogTarget(): void
    {
        Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
            'name' => 'ip-restrictor',
            'categories' => ['ip-restrictor'],
            'level' => LogLevel::INFO,
            'logContext' => false,
            'allowLineBreaks' => false,
            'formatter' => new LineFormatter(
                format: "[%datetime%] %message%\n",
                dateFormat: 'Y-m-d H:i:s',
            ),
        ]);
    }
}
