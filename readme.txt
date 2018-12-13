=== MultiPay ===

Contributors: etalented
Tags: paypal, stripe, worldpay, ecommerce, e-commerce, sales, sell, store, payments
Requires at least: 4.0
Tested up to: 5.0
Stable tag: 1.4.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Just want to take payments online? You don't need WooCommerce! With MultiPay you can take payments online quickly via PayPal, Stripe and WorldPay.

== Description ==

**MultiPay is a free eCommerce plugin that allows you to sell anything, simply**

Built to integrate seamlessly into your existing WordPress site, MultiPay allows store owners to easily receive payments from customers.

### SELL ANYTHING

MultiPay, not a complex eCommerce platform like WooCommerce, but your **eCommerce companion** to help you sell products and services in all different shapes and sizes.

Perhaps you would like to sell personalized t-shirts in different sizes and colors, or take deposits for different beauty treatments? It's all possible with MultiPay.

### MULTIPLE PAYMENT OPTIONS

With MultiPay you can receive payments via PayPal, Stripe and WorldPay - all of them together, or just the one.

### SHIPPPING, DATE SELECTION, COUPONS...

MultiPay gives you many of the advanced features of an eCommerce platform, in a simple, easy to use plugin. Offer free shipping, flat rate shipping or make real-time calculations. Allow your customers to choose the date on which they would like to receive a product or service. You can even support your marketing campaigns with the use of discount coupons or vouchers.

### YOUR DESIGN

Multipay is highly configurable. You can customize the design of your forms, change the feedback messages, and even send confirmation emails to your customers.

== Screenshots ==

1. Example payment form
2. Using Stripe as a payment method
3. Configuring the form fields
4. Configuring the form styling
5. Configuring Mailchimp, cancel and thank you pages, error and validation messages
6. Configuring an autoresponder
7. Upgrade to Pro for Transaction Logs, Multiple Forms, Coupons and Customer Emails

== Installation ==

1.  Login to your WordPress dashboard.
2.  Go to 'Plugins', 'Add New' then search for 'MultiPay'.
3.  Follow the on screen instructions.
4.  Activate the plugin.
5.  You will now see a new menu item in the menu called 'MultiPay'.
6.  Go to the MultiPay Settings page to configure your form and the payment methods.
7.  Use the shortcode `[multipay]` in your posts or pages or add the widget to the sidebar.

== Frequently Asked Questions ==

= How do I use this plugin in my post or page? =

Simply use the shortcode `[multipay]`. With the Pro version, you can use `[multipay form="[form name]"]`.

= How do I use this plugin in my sidebar? =

We have created a widget for you to easily add your form to your sidebar. Look for the widget called 'MultiPay'.

= What is the shortcode? =

`[multipay]` or `[multipay form="[form name]"]` with the Pro version.

= Can I use Paypal, Stripe, WorldPay and Amazon Payments? =

Yes, you can! You can use all or just one of these payment methods.

You can configure your form to accept all payment methods (Paypal, Stripe, WorldPay and Amazon Payments), so that your customer can choose the one that they pefer, or you can simply enable one.

In the MultiPay Settings page you will find a tab for each payment method. Each method can be setup and enabled individually.

= Can I create more than one payment form? =

Yes, you can! With the Pro version, you can create multiple payment forms, each with entirely different settings. So you could, for example, have different forms for the different products and services that you are selling.

= Can I see all of the transactions? =

Yes! By upgrading to Pro, you will be able to see a transaction log for all payments for each form that you create.

= Can I change the information that I gather from my customers? =

Yes, you can! MultiPay provides a wide range of pre-configured form fields for you to choose from including: Quantity, Shipping, Coupons, Date Selection, Email Address and Security Captcha. 

The form fields can be configured on the MultiPay Settings page under the 'Form Settings' tab.

= Can I change the design of the payment form? =

Yes! You can easily change the design of your form by configuring the styles on the MultiPay Settings page under the 'Styling' tab.

= Can I link to Mailchimp? =

Yes, you can! By setting up your Mailchimp List on the MultiPay Settings page, you can have your List populated automatically when your customers use your payment form.

Read [How do I use Add to Mailchimp?](#how%20do%20i%20use%20add%20to%20mailchimp%3F) below...

= Can I receive an email notification for each payment? =

Yes! MultiPay will automatically send a confirmation email to you for each paymemt.

= Can my customers receive a confirmation email? =

Yes! If you buy the Pro version, you can setup a confirmation email to be sent automatically to your customer.

With the Pro version, you will be able to customize everything about the email that is sent to the customer.

= Can I setup thank you or error pages? =

Yes, you can, on the MultiPay Settings page under the 'Processing' tab.

= Can I change the messages that appear on the form? =

Yes! On the MultiPay Settings page, you can change all of the error and validation messages that appear to your customers.

= How do I use Add to Mailchimp? =

When a customer uses your form, you can add their email address to any List on Mailchimp. 

Just follow these steps:

1.  Enable and make required the Email Address field in the 'Form Settings' tab in the MultiPay Settings
2.  Open your List on Mailchimp
3.  Go to Signup forms, then Embedded forms
4.  In the "Copy/paste onto your site" section, you will find a URL that looks like this: `https://etalented.us15.list-manage.com/subscribe/post?u=1b8595fad6a4c50dde29f98c7&amp;id=c195bc34f7` - copy and paste this URL into a text editor such as Notepad
5.  Go to the Processing tab in the MultiPay Settings, then copy and paste the correct part from the URL into the matching Mailchimp settings field using this template as a guide: `https://etalented.[Region].list-manage.com/subscribe/post?u=[User ID]&amp;id=[List ID]`
6.  Remember to Save Changes

== Changelog ==

= 1.4.1 =
*   Fixed email address field not showing to be required
*   Fixed custom styles not being correctly applied
*   Corrected incorrect links

= 1.4 =
*   Re-configuration of Admin Menus
*   Added explanation for maximum quantity
*   Removed "include postage and processing in the amount to pay" setting
*   Removed Google onClick setting
*   Removed currencies field
*   Fixed display of form validation overlays - now they close automatically
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
