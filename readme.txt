=== Laybuy Payment Extension for WooCommerce ===
Requires at least: 4.1
Tested up to: 4.9

A payment gateway extension for laybuy.com

== Description ==
This extension allows you to integrate your WooCommerce store platform with the https://laybuy.com payment system

SETUP

1. Once installed log into your admin dashboard

2. Navigate to WooCommerce > Settings > Checkout and select the 'Laybuy' payment settings tab

3. Select 'enable'

4. Change the title label if you wish

5. Select the 'Production' environment if not already selected or select Sandbox for testing

6. Enter your Laybuy merchant user ID and API. These will be provided by laybuy.com

7. Save

== Installation ==

REQUIREMENTS

PHP version 5.2.4 or greater (PHP 5.6+ is recommended)

MySQL version 5.0 or greater (MySQL 5.6+ is recommended)

WooCommerce 3.0+ / Requires WordPress 4.1+

INSTALLATION

1. Use the github's download feature to download a zip of the plugin (Clone or Download -> Download ZIP)

2. Once logged into your admin dashboard navigate to plugins > add new

3. Select upload plugin and locate the WooCommerce-Laybuy-master.zip plugin and then click 'Install Now'

4. Activate the plugin and you are all ready to go

5. Browse to Admin -> Wocommerce -> Settings -> Checkout -> Laybuy, here you can set your Laybuy Merchant details and choose to display the product price breakdown. The breakdown is displayed with Woocommerce's Product actions, there is a link in the Description to show you where these will display.

== Changelog ==
3.1.6 Fixed an issue with order stock handling and correct the order complete handling
3.0   Pulled in Lots of updates by Larry from private update service. Notably this includes the price breakdowns.
2.0   Reworked name of plugin to be inline with other woocommerce gateways, added logging via the WC logging class, updated the way items are sent to laybuy so there is less chance of a calualtion error, updated logo and description of the payment page.
