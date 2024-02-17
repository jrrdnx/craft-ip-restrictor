# IP Restrictor

Restrict what IP addresses can access the Craft control panel and front-end

[Open an Issue](https://github.com/jrrdnx/craft-ip-restrictor/issues)

## Requirements

This plugin requires Craft CMS ^4.0.0 or ^5.0.0 and PHP ^8.0.2

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

```sh
cd /path/to/project
```

2. Then tell Composer to load the plugin:

```sh
composer require jrrdnx/craft-ip-restrictor
```

3. In the Control Panel, go to Settings -> Plugins and click the "Install" button for IP Restrictor. You can also use the CLI to install and enable:

```sh
php craft plugin/install ip-restrictor
php craft plugin/enable ip-restrictor
```

## Configuring IP Restrictor

- In the Control Panel, go to Settings and click on the IP Restictor icon to configure this plugin's settings.

OR

- Copy the vendor/jrrdnx/craft-ip-restrictor/src/config.php file to config/ip-restrictor.php and modify the default settings as necessary.

```sh
cp vendor/jrrdnx/craft-ip-restrictor/src/config.php config/ip-restrictor.php
```

## Full documentation

Full documentation can be viewed at [https://jarrodnix.dev/plugins/ip-restrictor/v1](https://jarrodnix.dev/plugins/ip-restrictor/v1)

## IP Restrictor Roadmap

[Open an Issue](https://github.com/jrrdnx/craft-ip-restrictor/issues) to report any bugs or request a new feature.

Brought to you by [Jarrod D Nix](https://jarrodnix.dev)
