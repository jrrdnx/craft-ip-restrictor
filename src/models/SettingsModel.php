<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

namespace jrrdnx\iprestrictor\models;

use CraftCms\Cms\Plugin\PluginSettings;
use CraftCms\Cms\Support\Env;

/**
 * IpRestrictor Settings Model
 *
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
class SettingsModel extends PluginSettings
{
    public const METHOD_REDIRECT = 'redirect';
    public const METHOD_TEMPLATE = 'template';

    public bool $enabledControlPanel = false;

    public array $ipWhitelistControlPanel = [
        ['::1', 'IPv6 localhost'],
        ['127.0.0.1', 'IPv4 localhost'],
    ];

    public string $restrictionMethodControlPanel = self::METHOD_REDIRECT;

    public string $redirectControlPanel = '';

    public string $templateControlPanel = '';

    public bool $enabledFrontEnd = false;

    public array $ipWhitelistFrontEnd = [
        ['::1', 'IPv6 localhost'],
        ['127.0.0.1', 'IPv4 localhost'],
    ];

    public string $restrictionMethodFrontEnd = self::METHOD_REDIRECT;

    public string $redirectFrontEnd = '';

    public string $templateFrontEnd = '';

    public function getRestrictionMethods(): array
    {
        return [self::METHOD_REDIRECT, self::METHOD_TEMPLATE];
    }

    public function getEnabledControlPanel(): bool
    {
        return (bool)$this->enabledControlPanel;
    }

    public function getRestrictionMethodControlPanel(): string
    {
        return Env::parse($this->restrictionMethodControlPanel) ?? '';
    }

    public function getRedirectControlPanel(): string
    {
        return Env::parse($this->redirectControlPanel) ?? '';
    }

    public function getTemplateControlPanel(): string
    {
        return Env::parse($this->templateControlPanel) ?? '';
    }

    public function getEnabledFrontEnd(): bool
    {
        return (bool)$this->enabledFrontEnd;
    }

    public function getRestrictionMethodFrontEnd(): string
    {
        return Env::parse($this->restrictionMethodFrontEnd) ?? '';
    }

    public function getRedirectFrontEnd(): string
    {
        return Env::parse($this->redirectFrontEnd) ?? '';
    }

    public function getTemplateFrontEnd(): string
    {
        return Env::parse($this->templateFrontEnd) ?? '';
    }
}
