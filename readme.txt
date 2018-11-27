=== Stripe, PayPal and WorldPay payments in one combined form ===

Contributors: etalented
Tags: paypal, stripe, worldpay, payment form, ecommence
Requires at least: 4.0
Tested up to: 4.9.8
Stable tag: 1.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A simple, single form payment gateway that connects to a range of vendors such as PayPal, Stripe, Amazon and WorldPay.

== Description ==

**This plugin is under new ownership and is now being actively maintained. Please raise your issues and bugs in the Support Forum.**

If you just want a simple payment form but not a full eCommerce site you are often limited to a single payment provider. This plugin lets you use a variety of payment options all in a single form and without ever going off site to complete the payment.

= Features =
*   Accepts PayPal, Stripe, WordPay and Amazon (in the USA) with more to come
*   Fixed or variable payment amounts
*   A range of optional form fields
*   Loads of styling options

== Screenshots ==

1. An example payment form
2. The form creator page
3. The autoresponder editor

== Installation ==

1.  Login to your WordPress dashboard.
2.  Go to 'Plugins', 'Add New' then search for 'MultiPay'.
3.  Follow the on screen instructions.
4.  Activate the plugin.
5.  Go to the plugin 'Settings' page to configure the form and set up the payment gateways.
7.  Use the shortcode `[multipay]` in your posts or page or add the widget to a sidebar.
8.  To use the form in your theme files use the code `<?php echo do_shortcode('[multipay]'); ?>`.

== Frequently Asked Questions ==

= How do I change the labels and captions? =
Go to your plugin list and scroll down until you see 'MultiPay' and click on 'Settings'.
Click on the form settings tab and edit as required

= What's the shortcode? =
[multipay]

= Where can I get the gateways settings? = 
Log into your merchant account and look for the API section (each one is different). There is also some guidance on the gateway tabs in the plugin settings.

= How do I change the styles and colours? =
Use the plugin settings style page.

= Can I have more than one payment form on a page? =
Yes. But you need the Pro version for this. You create the forms on the setup page.

= Where can I see all the payments? =
Upgrade to Pro and at the bottom of the dashboard menu is a link called 'MultiPay'.

= It's all gone wrong! =
If it all goes wrong, just reinstall the plugin and start again. If you need help then please use the Support Forum.

== Changelog ==

= 1.4 =
*   Re-configuration of Admin Menus
*   Added explanation for maximum quantity
*   Removed "include postage and processing in the amount to pay" setting
*   Removed Google onClick setting
*   Removed currencies field
*   Fixed changing email address for confirmation message for non-upgrade version
*   Fixed payment methods will go to thank you or cancelled URLs
*   Fixed submit button position and size
*   Fixed background image and colour display
*   Fixed display of form borders
*   Fixed form width options
*   Fixed options field display and required
*   Fixed amount field display
*   Fixed reference field display
*   Fixed email address required
*   Fixed issue with activation of multiple fields in admin

= 1.3 =
*   Upgrade update
*   Support information update

= 1.2 =
*   Readme update
*   Support information update
*   Some code fixes

= 1.1 =
*   Bug fix for apple products
*   Interstitial modal for payment validation and confirmation
*   Added Amazon payments
*   Added option to change default currency
*   Improved script loading

= 1.0 =
*   Initial Issue
