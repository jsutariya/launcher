JS Launcher
====================================

## About

JS Launcher is a menu navigation launcher module for Magento inspired by <a href="https://github.com/astorm/PulsestormLauncher">PulseStorm Launcher</a> in Magento 1.
The module allows you to navigate through admin panel Menu, Configuration sections and Global Search. You can navigate to any page, configuration section, Products, Orders or Customers.

### Installation:
Install using composer:

`composer require jsutariya/launcher`

`php bin/magento module:enable JS_Launcher`

`php bin/magento setup:upgrade`

`php bin/magento cache:flush`

## Manual

To launch navigation launcher in admin panel, press ctrl+m as default key combination. Key combination can be changed from admin panel configuration section.

You can navigate to any Menu option or configuration section from launcher with just one click.

Refer <a href="https://jsutariya.wordpress.com/2019/04/17/magento-2-navigation-menu-launcher/" >JS Launcher Blog</a> for more details.

Press CTRL+M on any page in admin panel to initiate launcher. It will open a popup with Textbox, start typing where you want to go. It will give you all matching pages, navigate through them with up/down arrows. Press enter after selecting your desired page.

####If you want to change "Flat Rate" shipping method, just type in "flat rate".

<img src="https://jsutariya.files.wordpress.com/2019/04/js-launcher-shipping-method.gif" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />

####If you want to edit "Check Money Order" payment method, just type in "money" and it will give you a direct link to the payment method.

<img src="https://jsutariya.files.wordpress.com/2019/04/js-launcher-payment-methods.gif" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />

####Type in any order number to have a direct link to that order.

<img src="https://jsutariya.files.wordpress.com/2019/04/js-launcher-orders.gif" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />

####Type in customer name to get a link to the customer + orders which has customer name in billing addresses.

<img src="https://jsutariya.files.wordpress.com/2019/04/js-launcher-customers.gif" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />

####Type in any product name to get direct link to that product.

<img src="https://jsutariya.files.wordpress.com/2019/04/js-launcher-products.gif" title="Magento 2 JS Launcher" alt="Magento 2 JS Launcher" />