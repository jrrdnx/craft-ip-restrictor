<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

namespace jrrdnx\iprestrictor\models;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;
use IPTools\Range;

/**
 * IpRestrictor Settings Model
 *
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
class SettingsModel extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Restriction methods
     */
    public const METHOD_REDIRECT = 'redirect';
    public const METHOD_TEMPLATE = 'template';

    /**
     * @var bool
     */
    public bool $enabledControlPanel = false;

    /**
     *
     */
    public array $ipWhitelistControlPanel = [
        [ '::1', 'IPv6 localhost' ],
        [ '127.0.0.1', 'IPv4 localhost' ]
    ];

    /**
     * @var string
     */
    public string $restrictionMethodControlPanel = self::METHOD_REDIRECT;

    /**
     * @var string
     */
    public string $redirectControlPanel = '';

    /**
     * @var string
     */
    public string $templateControlPanel = '';

	/**
     * @var bool
     */
    public bool $enabledFrontEnd = false;

    /**
     *
     */
    public array $ipWhitelistFrontEnd = [
        [ '::1', 'IPv6 localhost' ],
        [ '127.0.0.1', 'IPv4 localhost' ]
    ];

    /**
     * @var string
     */
    public string $restrictionMethodFrontEnd = self::METHOD_REDIRECT;

    /**
     * @var string
     */
    public string $redirectFrontEnd = '';

    /**
     * @var string
     */
    public string $templateFrontEnd = '';

    // Public Methods
	// =========================================================================

    public function getRestrictionMethods(): array
    {
        return [
            self::METHOD_REDIRECT,
            self::METHOD_TEMPLATE
        ];
    }

	public function getEnabledControlPanel(): string
    {
        return App::parseEnv($this->enabledControlPanel);
	}

    public function getRestrictionMethodControlPanel(): string
    {
        return App::parseEnv($this->restrictionMethodControlPanel);
    }

    public function getRedirectControlPanel(): string
    {
        return App::parseEnv($this->redirectControlPanel);
	}

    public function getTemplateControlPanel(): string
    {
        return App::parseEnv($this->templateControlPanel);
	}

    public function getEnabledFrontEnd(): string
    {
        return App::parseEnv($this->enabledFrontEnd);
	}

    public function getRestrictionMethodFrontEnd(): string
    {
        return App::parseEnv($this->restrictionMethodFrontEnd);
    }

    public function getRedirectFrontEnd(): string
    {
        return App::parseEnv($this->redirectFrontEnd);
	}

    public function getTemplateFrontEnd(): string
    {
        return App::parseEnv($this->templateFrontEnd);
	}

    protected function defineAttributes()
    {
        return [
            'enabledControlPanel',
            'ipWhitelistControlPanel',
            'restrictionMethodControlPanel',
            'redirectControlPanel',
            'templateControlPanel',
            'enabledFrontEnd',
            'ipWhitelistFrontEnd',
            'restrictionMethodFrontEnd',
            'redirectFrontEnd',
            'templateFrontEnd'
        ];
    }

    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['enabledControlPanel', 'restrictionMethodControlPanel', 'redirectControlPanel', 'templateControlPanel', 'enabledFrontEnd', 'restrictionMethodFrontEnd', 'redirectFrontEnd', 'templateFrontEnd'],
            ],
        ];
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules[] = [['enabledControlPanel', 'enabledFrontEnd'], 'boolean'];
		$rules[] = [['enabledControlPanel', 'enabledFrontEnd'], 'default', 'value' => false];
        $rules[] = [['ipWhitelistControlPanel', 'ipWhitelistFrontEnd'], 'validateIpCidr'];
        $rules[] = [['ipWhitelistControlPanel', 'ipWhitelistFrontEnd'], 'default', 'value' => [
            [ '::1', 'IPv6 localhost' ],
            [ '127.0.0.1', 'IPv4 localhost' ]
        ]];
        $rules[] = [['restrictionMethodControlPanel', 'redirectControlPanel', 'templateControlPanel', 'restrictionMethodFrontEnd', 'redirectFrontEnd', 'templateFrontEnd'], 'string'];
        $rules[] = [['restrictionMethodControlPanel', 'redirectControlPanel', 'templateControlPanel', 'restrictionMethodFrontEnd', 'redirectFrontEnd', 'templateFrontEnd'], 'validateMethods'];

        return $rules;
    }

    /**
     * Validate IP/CIDR on save
     */
    public function validateIpCidr($attribute)
    {
        foreach($this->$attribute as &$row) {
            try {
                Range::parse($row[0]);
            } catch (\Exception $e) {
                $row[0] = ['value' => $row[0], 'hasErrors' => true];
                $this->addError($attribute, Craft::t('ip-restrictor', 'pleaseProvideValidIpCidr'));
            }
        }
    }

    /**
     * Require Redirect URL if method is redirect
     */
    public function validateMethods($attribute)
    {
        // Control panel
        if($this->enabledControlPanel) {
            if(!in_array($this->restrictionMethodControlPanel, $this->getRestrictionMethods())) {
                $this->addError('restrictionMethodControlPanel', Craft::t('ip-restrictor', 'pleaseProvideValidRestrictionMethod'));
            } else
            if($this->restrictionMethodControlPanel == self::METHOD_REDIRECT && empty($this->redirectControlPanel)) {
                $this->addError('redirectControlPanel', Craft::t('ip-restrictor', 'pleaseProvideValidUrl'));
            } else
            if($this->restrictionMethodControlPanel == self::METHOD_TEMPLATE && empty($this->templateControlPanel)) {
                $this->addError('templateControlPanel', Craft::t('ip-restrictor', 'pleaseProvideValidTemplate'));
            }
        }

        // Front-End
        if($this->enabledFrontEnd) {
            if(!in_array($this->restrictionMethodFrontEnd, $this->getRestrictionMethods())) {
                $this->addError('restrictionMethodFrontEnd', Craft::t('ip-restrictor', 'pleaseProvideValidRestrictionMethod'));
            } else
            if($this->restrictionMethodFrontEnd == self::METHOD_REDIRECT && empty($this->redirectFrontEnd)) {
                $this->addError('redirectFrontEnd', Craft::t('ip-restrictor', 'pleaseProvideValidUrl'));
            } else
            if($this->restrictionMethodFrontEnd == self::METHOD_TEMPLATE && empty($this->templateFrontEnd)) {
                $this->addError('templateFrontEnd', Craft::t('ip-restrictor', 'pleaseProvideValidTemplate'));
            }
        }
    }
}
