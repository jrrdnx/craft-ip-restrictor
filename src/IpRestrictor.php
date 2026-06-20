<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

namespace jrrdnx\iprestrictor;

use CraftCms\Cms\Plugin\Plugin;
use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Facades\I18N;
use CraftCms\Cms\Twig\TemplateRenderer;
use CraftCms\Cms\View\Events\CpTemplateRootsResolving;
use jrrdnx\iprestrictor\models\SettingsModel;
use jrrdnx\iprestrictor\services\RestrictService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
class IpRestrictor extends Plugin
{
    public bool $hasCpSettings = true;

    public function registerPlugin(): void
    {
        $this->app->singleton(RestrictService::class);
    }

    public function bootPlugin(): void
    {
        // HasViews::bootHasViews() uses self::getInstance() which resolves to Plugin::getInstance()
        // (PHP's self in traits binds to the trait's host class, not the subclass). Since Plugin is
        // abstract and not bound in the container, that call fails. Register our template root
        // explicitly here instead.
        // Note: $this->handle is uninitialized on the service provider instance (Plugin::create()
        // sets it on a separate instance). Use pluginsService like Plugin::boot() does.
        $handle = $this->pluginsService->getPluginHandleByClass(static::class);
        $templateDir = $this->getResourcesPath() . '/templates';
        Event::listen(function (CpTemplateRootsResolving $event) use ($handle, $templateDir) {
            if (is_dir($templateDir)) {
                $event->roots[$handle] = [$templateDir];
            }
        });

        if (app()->runningInConsole()) {
            return;
        }

        $request = request();

        if ($request->isCpRequest()) {
            app(RestrictService::class)->restrictControlPanel();
        } elseif ($request->isSiteRequest()) {
            app(RestrictService::class)->restrictFrontEnd();
        }

        Log::info(I18N::translate('{name} plugin loaded', ['name' => $handle], 'ip-restrictor'));
    }

    public function getSettings(): ?PluginSettings
    {
        $settings = parent::getSettings();
        if ($settings === null) {
            return null;
        }
        foreach ($this->_readConfigFile() as $key => $value) {
            if (property_exists($settings, $key)) {
                $settings->$key = $value;
            }
        }
        return $settings;
    }

    protected function createSettingsModel(): ?PluginSettings
    {
        return new SettingsModel();
    }

    protected function settingsHtml(): ?string
    {
        $restrictionMethods = [];
        foreach ($this->getSettings()->getRestrictionMethods() as $method) {
            $restrictionMethods[] = [
                'label' => I18N::translate($method, [], 'ip-restrictor'),
                'value' => $method,
            ];
        }

        return app(TemplateRenderer::class)->renderTemplate(
            'ip-restrictor/settings',
            [
                'restrictionMethods' => $restrictionMethods,
                'settings' => $this->getSettings(),
                'configFromFile' => $this->_readConfigFile(),
            ]
        );
    }

    public static function info(string $message): void
    {
        Log::info($message);
    }

    public static function error(string $message): void
    {
        Log::error($message);
    }

    /**
     * Returns the parsed contents of config/ip-restrictor.php, or [] if missing.
     */
    private function _readConfigFile(): array
    {
        $configFile = config_path('ip-restrictor.php');
        return file_exists($configFile) ? require $configFile : [];
    }
}
