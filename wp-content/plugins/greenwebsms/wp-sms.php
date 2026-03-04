<?php
/*
Plugin Name: Greenweb WP SMS
Plugin URI: http://sms.greenweb.com.bd/
Description: Send SMS From Your Wordpress site using Greenweb BD SMS plugin.
Version: 89.0.0
Author: Greenweb BD
Author URI: https://www.greenweb.com.bd/
Text Domain: greenweb
Tested up to: 6.8.1
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
/**
 * Load Plugin Defines
 */
require_once 'includes/defines.php';

if (file_exists(dirname(__FILE__).'/greenweb.php')) { 
require_once dirname(__FILE__).'/greenweb.php';
} else {
require_once dirname(__FILE__).'/greenweb_tmp.php';	
}

add_filter('rest_pre_dispatch' , 'rest_pre_dispatch_overwrite', 11);
function rest_pre_dispatch_overwrite($request) {
	if (is_wp_error($request)) {
		if($request->get_error_data('jwt_auth_bad_auth_header')) {
			return NULL;
		}
	}

        return $request;
}
/**
 * Load plugin Special Functions
 */
require_once WP_SMS_DIR . 'includes/functions.php';

/**
 * Get plugin options
 */
$wpsms_option = get_option( 'wpsms_settings' );

/**
 * Initial gateway
 */
require_once WP_SMS_DIR . 'includes/class-wpsms-gateway.php';

$sms = wp_sms_initial_gateway();

/**
 * Load Plugin
 */
require WP_SMS_DIR . 'includes/class-wpsms.php';

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'gweblink');
function gweblink( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=greenweb-sms-settings&tab=gateway' ) .
		'">' . __('Gateway Settings') . '</a>';
	return $links;
}
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
    }
} );

new WP_SMS();