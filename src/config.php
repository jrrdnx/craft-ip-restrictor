<?php
/**
 * Restrict what IP addresses can access the Craft control panel and front-end
 *
 * @link      https://github.com/jrrdnx/craft-ip-restrictor
 * @copyright Copyright (c) 2024 Jarrod D Nix
 */

/**
 * IP Restrictor config.php
 *
 * This file exists only as a template for the IP Restrictor settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'ip-restrictor.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
/*******************************************************************************
 *	CONTROL PANEL
 ******************************************************************************/
    // Enable IP restrictions for the control panel
    'enabledControlPanel' => false,

    // IP/CIDR whitelist for the control panel
    'ipWhitelistControlPanel' => [
        [ '::1', 'IPv6 localhost' ],
        [ '127.0.0.1', 'IPv4 localhost' ],
    ],

    // Restriction method for the control panel
    // 'redirect' or 'template'
    'restrictionMethodControlPanel' => 'redirect',

    // Url to redirect control panel if restriction method is 'redirect'
    'redirectControlPanel' => '$PRIMARY_SITE_URL',

    // Template to render for control panel if restriction method is 'template'
    'templateControlPanel' => '',

/*******************************************************************************
 *	FRONT-END
 ******************************************************************************/
    // Enable IP restrictions for the front-end
    'enabledFrontEnd' => false,

    // IP/CIDR whitelist for the front-end
    'ipWhitelistFrontEnd' => [
        [ '::1', 'IPv6 localhost' ],
        [ '127.0.0.1', 'IPv4 localhost' ],
    ],

    // Restriction method for the front-end
    // 'redirect' or 'template'
    'restrictionMethodFrontEnd' => 'redirect',

    // Url to redirect front-end if restriction method is 'redirect'
    'redirectFrontEnd' => 'https://craftcms.com',

    // Template to render for front-end if restriction method is 'template'
    'templateFrontEnd' => '',
];
