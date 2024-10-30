# Busha Commerce for WooCommerce

A WooCommerce payment gateway that allows your customers to pay with cryptocurrency via Busha Commerce

## Installation

### From WordPress.org

This plugin is available on the [WordPress.org plugin repository], and can be installed either directly from there or from the admin dashboard within your website.

#### Within your WordPress dashboard

1. Visit ‘Plugins > Add New’
2. Search for ‘Busha Pay
3. Activate Busha Pay from your Plugins page.

#### From WordPress.org plugin repository

1. Download Busha Pay from <https://wordpress.org/plugins/busha-pay/>
2. Upload to your ‘/wp-content/plugins/’ directory, using your favorite method (ftp, sftp, scp, etc…)
3. Activate Busha Pay from your Plugins page.

### From this repository

Within the Github repository, click the Clone or Download button and Download a zip file of the repository, or clone it directly via command line.

Within your WordPress administration panel, go to Plugins > Add New and click the Upload Plugin button on the top of the page.

Alternatively, you can move the zip file into the `wp-content/plugins` folder of your website and unzip.

You will then need to go to your WordPress administration Plugins page, and activate the plugin.

## Configuring Busha Pay

You will need to set up an account on [Busha Pay].

Within the WordPress administration area, go to the WooCommerce > Settings > Payments page and you will see Busha in the table of payment gateways.

Clicking the Manage button on the right hand side will take you into the settings page, where you can configure the plugin for your store.

**Note: If you are running version of WooCommerce older than 3.4.x your Busha Pay tab will be underneath the WooCommerce > Settings > Checkout tab**

## Settings

### Enable / Disable

Turn the Busha Pay payment method on / off for visitors at checkout.

### Title

Title of the payment method on the checkout page

### Description

Description of the payment method on the checkout page

### API Key

Your Busha Pay API key. Available within the [Busha Pay settings page].

Using an API key allows your website to periodically check Busha Pay for payment confirmation.

### Webhook Shared Secret

Your webhook shared secret. Available within the [Busha Pay settings page].

Using webhooks allows Busha Pay to send payment confirmation messages to the website. To fill this out:

1. In your Busha Pay settings page, scroll to the 'Webhook subscriptions' section
2. Click 'Add an endpoint' and paste the URL from within your settings page.
3. Make sure to select "Send me all events", to receive all payment updates.
4. Click "Show shared secret" and paste into the box within your settings page.

### Debug log

Whether or not to store debug logs.

If this is checked, these are saved within your `wp-content/uploads/wc-logs/` folder in a .log file prefixed with `busha-`

## Prerequisites

To use this plugin with your WooCommerce store you will need:

-   [WordPress] (tested up to 4.9.7)
-   [WooCommerce] (tested up to 3.4.3)

## Frequently Asked Questions

**What cryptocurrencies does the plugin support?**

The plugin supports all cryptocurrencies available at [Busha Pay]

**Does Busha Pay have integrations with other commerce platforms?**

Yes, the list of all integrations can be found here: [Busha Pay Integrations](https://pay.busha.co/integrate).

== Screenshots ==
1. Admin setting
2. Checkout page
3. Payment page

## License

This project is licensed under the Apache 2.0 License

## Changelog

## 1.1.0

-   Added support for charge cancel url.
-   Handle cancelled events from API.
-   Add option to disable icons on checkout page.
-   Add Busha Pay transaction ID to WooCommerce order output (Admin order page, Customer order page, email confirmation).
-   Updated README.md

## 1.0.2

-   Tested against WordPress 4.9.4

## 1.0.1

-   Tested against WordPress 4.9.7
-   Tested against WooCommerce 3.4.3
-   Updated README.md
-   Updated plugin meta in busha-pay.php

## 1.0.0

-   Busha Pay

[//]: # 'Comments for storing reference material in. Stripped out when processing the markdown'
[busha pay]: https://pay.busha.co/
[busha pay settings page]: https://dashboard.pay.busha.co/settings/
[woocommerce]: https://woocommerce.com/
[wordpress]: https://wordpress.org/
[wordpress.org plugin repository]: https://wordpress.org/plugins/busha-pay/
