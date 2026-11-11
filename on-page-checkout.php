<?php
/**
 * Plugin Name: On-Page Checkout for WooCommerce
 * Description: Display WooCommerce products with a complete checkout on any Elementor page.
 * Version:     1.0.4
 * Author:      W3 Relations, shukur5674
 * Author URI:  https://w3relations.com/
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Requires Plugins: elementor, woocommerce
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: on-page-checkout-for-elementor
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Step aside when On-Page Checkout Pro is active: Pro includes everything Free does, and both editions
 * share class names, so only one may load. (Free loads before Pro, so check the active plugins list.)
 */
$w3opcfe_active_plugins = (array) get_option( 'active_plugins', [] );
if ( is_multisite() ) {
    $w3opcfe_active_plugins = array_merge( $w3opcfe_active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
}
if ( in_array( 'on-page-checkout-pro/on-page-checkout-pro.php', $w3opcfe_active_plugins, true ) ) {
    unset( $w3opcfe_active_plugins );
    return;
}
unset( $w3opcfe_active_plugins );

// Shared constants are guarded: while Free is being activated, Pro may already be loaded in the same request.
if ( ! defined( 'W3OPCFE_PLUGIN_FILE' ) ) {
    define( 'W3OPCFE_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'W3OPCFE_PLUGIN_DIR' ) ) {
    define( 'W3OPCFE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'W3OPCFE_PLUGIN_URL' ) ) {
    define( 'W3OPCFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'W3OPCFE_PLUGIN_BASENAME' ) ) {
    define( 'W3OPCFE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'W3OPCFE_VERSION' ) ) {
    define( 'W3OPCFE_VERSION', '1.0.4' );
}

/**
 * Activation confirmation modal (switching to Pro on the Plugins screen).
 */
if ( is_admin() ) {
    require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-activation-modal.php';
    W3OPCFE_Activation_Modal::init();
}

/**
 * Only one edition may be active: activating Free deactivates Pro, also when Free is activated
 * without the modal (bulk action, WP-CLI).
 */
register_activation_hook(
    __FILE__,
    function () {
        require_once plugin_dir_path( __FILE__ ) . 'includes/admin/class-activation-modal.php';
        W3OPCFE_Activation_Modal::deactivate_pro();
    }
);

if ( ! function_exists( 'w3opcfe_bootstrap' ) ) {
    /**
     * Bootstrap On-Page Checkout on plugins_loaded.
     */
    function w3opcfe_bootstrap() {
        // If Pro is active, Pro handles initialization.
        if ( defined( 'W3OPCFE_PRO_FILE' ) ) {
            return;
        }

        require_once W3OPCFE_PLUGIN_DIR . 'includes/class-w3opcfe-plugin.php';
        W3OPCFE_On_Page_Checkout::instance()->on_plugins_loaded();
    }
    add_action( 'plugins_loaded', 'w3opcfe_bootstrap', 20 );
}
