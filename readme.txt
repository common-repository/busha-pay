=== Busha Pay Payment Gateway for WooCommerce ===
Contributors: busha
Plugin URL: https://pay.busha.co/
Tags: busha, woo, woocommerce, ecommerce, bitcoin, ethereum, litecoin, bitcash, blockchain, payment, pay, payment gateways, checkout, crypto, cryptocurrency
Requires at least: 3.0
Requires PHP: 5.6
Tested up to: 5.2
Stable tag: 1.1.3
License: GPLv2 or later

== Description ==

Accept cryptocurrencies through Busha Pay such as Bitcoin, Ethereum, Litecoin and Bitcoin Cash on your WooCommerce store.

== Installation ==

= From your WordPress dashboard =

1. Visit 'Plugins > Add New'
2. Search for 'Busha Pay'
3. Activate Busha Pay from your Plugins page.

= From WordPress.org =

1. Download Busha Pay.
2. Upload to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate Busha Pay from your Plugins page.

= Once Activated =

1. Go to WooCommerce > Settings > Payments
2. Configure the plugin for your store

= Configuring Busha Pay =

* You will need to set up an account on https://pay.busha.co/
* Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Busha in the table of payment gateways.
* Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Busha Pay tab will be underneath the WooCommerce > Settings > Checkout tab**

= Enable / Disable =

Turn the Busha Pay payment method on / off for visitors at checkout.

= Title =

Title of the payment method on the checkout page

= Description =

Description of the payment method on the checkout page

= API Key =

Your Busha Pay API key. Available within the https://pay.busha.co/dashboard/settings/

Using an API key allows your website to periodically check Busha Pay for payment confirmation.

= Webhook Shared Secret =

Your webhook shared secret. Available within the https://pay.busha.co/dashboard/settings/

Using webhooks allows Busha Pay to send payment confirmation messages to the website. To fill this out:

1. In your Busha Pay settings page, scroll to the 'Webhook subscriptions' section
2. Click 'Add an endpoint' and paste the URL from within your settings page.
3. Make sure to select "Send me all events", to receive all payment updates.
4. Click "Show shared secret" and paste into the box within your settings page.

= Debug log =

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `busha-`


== Frequently Asked Questions ==

= What cryptocurrencies does the plugin support?

The plugin supports all cryptocurrencies available at https://pay.busha.co/

= Prerequisites=

To use this plugin with your WooCommerce store you will need:
* WooCommerce plugin


== Upgrade Notice ==

None

== Screenshots ==

1. Admin panel
2. Busha Pay payment gateway on checkout page
3. Cryptocurrency payment screen


== Changelog ==

= 1.0.0 =
* Busha Pay
