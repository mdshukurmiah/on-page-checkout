<?php
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
define( 'WP_SMS_PRO_URL', plugin_dir_url( dirname( __FILE__ ) ) );
define( 'WP_SMS_PRO_DIR', plugin_dir_path( dirname( __FILE__ ) ) );


function greenweb_web_web(){
	$plugin_data = get_plugin_data( WP_SMS_PRO_DIR . 'wp-sms-pro.php' );
	define( 'WP_SMS_PRO_VERSION', '21.0.0' );
}
add_action( 'init','greenweb_web_web', 15 );

