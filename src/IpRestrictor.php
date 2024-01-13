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
use craft\helpers\FileHelper;
use craft\helpers\UrlHelper;
use craft\services\Plugins;

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
                    Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/ip-restrictor'))->send();
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
	 * Log plugin actions
	 *
	 * @return void
	 */
	public static function log($message): void
	{
        $today = new \DateTime();

		$file = Craft::getAlias('@storage/logs/ip-restrictor-' . $today->format('Y-m-d') . '.log');
		$log = $today->format('Y-m-d H:i:s').' '.$message."\n";

		FileHelper::writeToFile($file, $log, ['append' => true]);
	}
}
