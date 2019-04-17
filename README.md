JS Launcher
====================================

## About

JS Launcher is a menu navigation launcher module for Magento inspired by <a href="https://github.com/astorm/PulsestormLauncher">PulseStorm Launcher</a> in Magento 1.
Current version only supports menu links in launcher. Configuration and Global search are coming soon with later versions.

### Installation:
Install using composer:

`composer require jsutariya/launcher`

`php bin/magento module:enable JS_Launcher`

`php bin/magento setup:upgrade`

`php bin/magento cache:flush`

## Manual

To launch navigation launcher in admin panel, press ctrl+m as default key combination. Key combination can be changed from admin panel configuration section.

Press ctrl+m on any page in admin panel to initiate launcher. It will open a popup with Textbox, start typing where you want to go. It will give you all matching pages, navigate throgh them with up/down arrows. Press enter after selecting your desired page.

<img src="https://jsutariya.files.wordpress.com/2019/04/screenshot-from-2019-04-17-075059.png" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />
