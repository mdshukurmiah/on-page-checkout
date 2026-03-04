<?php
/**
 * Plugin Name: SMS Extra Integration
 * Plugin URI: http://www.greenweb.com.bd/
 * Description: This plugin will add woocommerce and others plugins integrational support
 * Version: 21.0.0
 * Author: Greenweb BD
 * Author URI: http://www.greenweb.com.bd/
 * Text Domain: greenwebsms-2
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/*
 * Load Defines
 */
 require_once 'includes/defines.php';
if (file_exists(dirname(__FILE__).'/greenweb.php')) { 
require_once dirname(__FILE__).'/greenweb.php';
} else {
require_once dirname(__FILE__).'/greenweb_tmp.php';	
}
// Get options
$wpsms_pro_option = get_option( 'wps_pp_settings' );

/*
 * Load Plugin
 */
include_once 'includes/class-wpsms-pro.php';

define( 'GWEB_VERSION', '21.0.0' );
define( 'GWEB_DOMAIN', 'gwebstock' );
define( 'GWEB_FILE_PATH', __FILE__.'/gwebstock/' );
define( 'GWEB_PATH', plugin_dir_path( __FILE__ ).'/gwebstock/' );
define( 'GWEB_URL_PATH', plugin_dir_url( __FILE__ ).'/gwebstock/' );
define( 'GWEB_FONTAWESOME_URL', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/fontawesome.min.css' );
define( 'GWEB_SUPPORT_URL', 'https://otp.li/help' );


require_once( GWEB_PATH . 'includes/gweb-loader.php' );

define("G_WEB_PATH",plugin_dir_path(__FILE__).'/otp/');
define("G_WEB_URL",plugins_url('',__FILE__).'/otp/');
define("G_WEB_PLUGIN_BASENAME",plugin_basename( __FILE__ ));
define("G_WEB_VERSION","21.0.0");
define("G_WEB_LITE",true);

function g_web_init(){
	

	do_action('g_web_before_plugin_activation');

	if ( ! class_exists( 'G_Web' ) ) {
		require G_WEB_PATH.'includes/class-g-web.php';
	}

	g_web();

	
}
add_action( 'plugins_loaded','g_web_init', 15 );

function g_web(){
	return G_Web::get_instance();
}


add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'gweblinks');
function gweblinks( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=greenweb-sms-extra' ) .
		'">' . __('Settings') . '</a>';
		$links[] = '<a href="' .
		admin_url( 'admin.php?page=greenweb-sms-extra&tab=wc' ) .
		'">' . __('WooCommerce Settings') . '</a>';
		$links[] = '<a href="' .
		admin_url( 'admin.php?page=gweb-stock-notify&tab=settings' ) .
		'">' . __('Stock Settings') . '</a>';
	return $links;
}
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
    }
} );
new WP_SMS\Pro();