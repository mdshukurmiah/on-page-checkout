=== On-Page Checkout for Elementor ===
Contributors: w3relations
Tags: woocommerce, checkout, elementor, one-page-checkout, woocommerce-checkout
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom Elementor widget that lets customers browse WooCommerce products and complete checkout on a single page.

== Description ==

On-Page Checkout for Elementor adds a drag-and-drop Elementor widget that combines product selection and the full WooCommerce checkout form into a single page. No redirects, no separate cart page needed.

**Key Features**

* Add the On Page Checkout widget anywhere in Elementor
* Support for simple and variable products
* Built-in quantity selector
* Configurable section headings (billing, shipping, order summary, payment, button text)
* Pre-select a product via a URL query string parameter
* Enable / disable and relabel individual checkout fields from the admin settings page
* Full WooCommerce payment gateway compatibility
* Responsive layout out of the box

**Requirements**

* WordPress 6.0 or later
* WooCommerce 6.0 or later
* Elementor 3.0.0 or later
* PHP 7.4 or later

== Installation ==

1. Upload the `on-page-checkout` folder to the `/wp-content/plugins/` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Make sure both **Elementor** and **WooCommerce** are installed and activated.
4. Edit any page with Elementor, search for the **On Page Checkout** widget in the Elementor panel, and drag it onto the canvas.
5. In the widget settings panel, add one or more Product IDs or SKUs and configure the section headings as needed.
6. Optionally visit **W3 OPC → Checkout Fields** in the WordPress admin to enable, disable, or relabel individual billing and shipping fields.

== Frequently Asked Questions ==

= Does this work with all WooCommerce payment gateways? =

Yes. The widget renders all payment gateways that WooCommerce makes available on the checkout, the same as the standard WooCommerce checkout page.

= Can I use multiple products on the same widget? =

Yes. Add as many Product IDs or SKUs as you need in the widget settings. Customers will see radio buttons to select a product before filling in their details.

= Does it support variable products? =

Yes. For variable products a dropdown is shown that lets the customer pick a variation before checkout.

= Can I pre-select a product using a URL parameter? =

Yes. Set the **Preselect Product by Query String Parameter** field in the widget settings to a parameter name (e.g. `product_id`). Visiting `yoursite.com/checkout-page/?product_id=42` will pre-select product 42.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
