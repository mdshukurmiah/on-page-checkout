=== On-Page Checkout for Elementor ===
Contributors: w3relations
Tags: woocommerce, checkout, elementor, one-page-checkout, woocommerce-checkout
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A custom Elementor widget that lets customers browse WooCommerce products and complete checkout on a single page.

== Description ==

On-Page Checkout for Elementor adds a drag-and-drop Elementor widget that combines product selection and the full WooCommerce checkout form into a single page. No redirects, no separate cart page needed.

**Key Features**

* Add the On Page Checkout widget anywhere in Elementor
* Support for simple and variable products
* Display variations as separate product cards with variation-specific images
* Single and multi-product selection modes
* Built-in per-card and global quantity selectors
* Out-of-stock badge with dynamic disabled state handling
* Searchable product selection control in Elementor editor
* Product Countdown widget with target date, auto-reset, and custom styles
* Product Scarcity widget with animated pulsing indicator, warning triangle, and live stock progress bars
* Configurable section headings (billing, shipping, order summary, payment, button text)
* Pre-select a product via a URL query string parameter
* Enable / disable and relabel individual checkout fields from the admin settings page
* Mobile collapsible order summary toggle button
* Responsive column controls and spacing controls
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

Yes. Add as many Product IDs or SKUs as you need in the widget settings. Customers can select a single product or enable multi-product selection to purchase multiple products at once.

= Does it support variable products? =

Yes. For variable products an in-card dropdown is shown that lets the customer pick a variation, or you can enable displaying variations as individual separate cards.

= Can I pre-select a product using a URL parameter? =

Yes. Set the **Preselect Product by Query String Parameter** field in the widget settings to a parameter name (e.g. `product_id`). Visiting `yoursite.com/checkout-page/?product_id=42` will pre-select product 42.

== Changelog ==

= 1.0.4 =
* Feature: Added new "Progress Bar" layout for the Product Scarcity widget with customizable bar height, fill color, track background, border radius, and top spacing.
* Feature: Added built-in outline Alert/Warning Triangle SVG icon option matching scarcity progress banners.
* Feature: Added responsive icon sizing and spacing controls for scarcity indicators.
* Feature: Added configurable bar reference scale and progress calculation modes (remaining, depleted/sold, direct percentage).

= 1.0.3 =
* Feature: Added Greenweb SMS WooCommerce OTP verification integration with a dedicated modular bridge (`greenweb/`).
* Feature: Converted the inline OTP container into an interactive, centered modal popup with a frosted backdrop overlay.
* Feature: Added modal backdrop click-to-close, ESC key dismissal, scroll-locking, and real-time digit auto-focus.
* Feature: Added custom icon color styling controls in the Scarcity widget.
* Feature: Added custom badge options per product card with configurable text, text color, background color, and top-left / top-right positioning.
* Feature: Added typography and color settings for original/regular price alongside active price styling in product cards.
* Enhancement: Added activity checks to strictly initialize OTP features only when both Greenweb SMS base and Pro plugins are active with checkout OTP enabled.
* Fix: Resolved payment method detection fallback when a single payment method (e.g. COD) is present as a hidden input.
* Fix: Fixed global namespace class resolution for `\W3OPCFE_Greenweb` inside namespaced widget files.

= 1.0.2 =
* Feature: Added Countdown widget with target date/time, interval and daily auto-reset, Bangla number conversions, and full typography/box styling.
* Feature: Added Scarcity widget with pulsing SVG indicator, customizable icons, count up/down modes, persistent localStorage counts, and style controls.
* Feature: Added dedicated "On Page Checkout" Elementor widget category for all plugin widgets.
* Feature: Added multi-product selection support with customizable selection types (checkbox vs radio).
* Feature: Added individual per-card quantity selectors in multi-selection mode with responsive padding controls.
* Feature: Added option to display product variations as standalone separate product cards with custom title and variation images.
* Feature: Added out-of-stock badge display and disabled selection for out-of-stock products and variations, with dynamic in-card variation switching.
* Feature: Added searchable product autocomplete selection for easily adding products in Elementor.
* Feature: Added responsive column controls for product cards across desktop, tablet, and mobile.
* Feature: Added responsive gap settings between checkout form and order review table.
* Feature: Added styling controls for order review table header background.
* Feature: Added collapsible mobile order summary toggle button with configurable initial state.
* Fix: Resolved Elementor editor preview issue where the checkout form did not render for the default selected product.
* Enhancement: Performance optimization with on-demand conditional asset loading.

= 1.0.1 =
* Fix: Minor bug fixes and improvements.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.3 =
Adds Greenweb SMS OTP verification modal integration, custom product badges, original price styling, scarcity icon color options, and payment method fallback improvements.

= 1.0.2 =
Adds new Countdown and Scarcity widgets, multi-product selection, variation card display, out-of-stock badges, searchable products, and responsive styling controls.

= 1.0.1 =
Fix minor issues
