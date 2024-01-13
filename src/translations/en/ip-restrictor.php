<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

/**
 * IP Restrictor English Translation
 *
 * Returns an array with the string to be translated (as passed to `Craft::t('ip-restrictor', '...')`) as
 * the key, and the translation as the value.
 *
 * http://www.yiiframework.com/doc-2.0/guide-tutorial-i18n.html
 *
 * @author    Jarrod D Nix
 * @package   IpRestrictor
 * @since     1.0.0
 */
return [
	// Settings
	'enabledControlPanel'								=> 'Enabled for the control panel',
    'ipWhitelistControlPanel'                           => 'Whitelist for the control panel',
    'restrictionMethodControlPanel'                     => 'Restriction method for the control panel',
    'enabledFrontEnd'							        => 'Enabled for the front-end',
    'ipWhitelistFrontEnd'                               => 'Whitelist for the front-end',
    'restrictionMethodFrontEnd'                         => 'Restriction method for the front-end',
    'ipCidrAddress'                                     => 'IP/CIDR Address',
    'note'                                              => 'Note',
    'pleaseProvideValidIpCidr'                          => 'Please provide a valid IPv4 or IPv6 address or range',
    'pleaseProvideValidRestrictionMethod'               => 'Please provide a valid Restriction Method',
    'pleaseProvideValidUrl'                             => 'Please provide a valid URL',
    'pleaseProvideValidTemplate'                        => 'Please provide a valid Template',
    'addAnIpCidrAddress'                                => 'Add an IP/CIDR Address',
    'redirect'                                          => 'Redirect URL',
    'template'                                          => 'Template',
    'overridden'                                        => 'This is being overridden by the `{name}` setting in the `config/ip-restrictor.php` file.',
    'accessDenied'                                      => 'Access Denied'
];
