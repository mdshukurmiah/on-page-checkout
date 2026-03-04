<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'astra-theme-css','astra-menu-animation','woocommerce-layout','woocommerce-smallscreen','woocommerce-general' ) );
       	wp_enqueue_style( 'bangla-fonts', get_stylesheet_directory_uri() . '/bangla-fonts/fonts.css' );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

// END ENQUEUE PARENT ACTION


function update_font_display() {

	return 'Asavari'; // Or any other value.

}
add_filter( 'elementor_pro/custom_fonts/font_display', 'update_font_display' );


// Order Status pending by default
add_filter( 'woocommerce_payment_complete_order_status', 'change_default_order_status', 10, 2 );
function change_default_order_status( $status, $order_id ) {
    $order = new WC_Order( $order_id );
    $status = 'pending';
    return $status;
}


// Custom Js
function enqueue_child_theme_scripts() {
  wp_enqueue_script( 'child-theme-script', get_stylesheet_directory_uri() . '/custom.js', array('jquery'), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_child_theme_scripts' );



// add_filter( 'woocommerce_checkout_fields', 'customize_woocommerce_checkout_fields' );

// function customize_woocommerce_checkout_fields( $fields ) {
// 	unset( $fields['billing']['billing_last_name'] );
//     unset( $fields['billing']['billing_company'] );
//     unset( $fields['billing']['billing_address_2'] );
// 	unset( $fields['shipping']['billing_address_2'] );
// 	unset( $fields['billing']['billing_postcode'] );
// 	unset( $fields['shipping']['shipping_postcode'] );
	

//     $fields['billing']['billing_country']['default'] = 'BD'; 
//     $fields['shipping']['shipping_country']['default'] = 'BD';
// 	unset( $fields['billing']['billing_country'] );
//     unset( $fields['shipping']['shipping_country'] );
	
// 	$fields['billing']['billing_email']['required'] = false; 
	
// 	$fields['billing']['billing_first_name']['label'] = 'আপনার নাম';
//     $fields['billing']['billing_address_1']['label'] = 'ঠিকানা(গ্রাম/রাস্তা/হোল্ডিং নং)';
//     $fields['billing']['billing_city']['label'] = 'শহর/থানা';
//     $fields['billing']['billing_state']['label'] = 'জেলা';
//     $fields['billing']['billing_phone']['label'] = 'মোবাইল নাম্বার';
//     $fields['billing']['billing_email']['label'] = 'ইমেইল এড্রেস(যদি থাকে)';
	

	
//     return $fields;
// }

add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

add_filter('woocommerce_order_formatted_billing_address', 'show_district_name_instead_of_code', 10, 2);
function show_district_name_instead_of_code($address, $order) {
    $state_code = $order->get_billing_state(); // BD-31

    // Get all BD states
    $states = WC()->countries->get_states('BD');

    // Replace state code with readable name if available
    if (isset($states[$state_code])) {
        $address['state'] = $states[$state_code];
    }

    return $address;
}




function phone_order_text(){
	echo '<div class="text-bn order-via-phone"><a href="tel:01700506959">ফোনে অর্ডার করতে কল করুন 01700506959</a></div>';
	echo '<button type="button" class="custom-modal-button">কিভাবে অর্ডার করবেন?</button>';
}

add_action('woocommerce_after_add_to_cart_form', 'phone_order_text' );

// Add Buy Now button on WooCommerce product page
function add_buy_now_button() {
    global $product;

    if ( $product->is_type( 'simple' ) ) {
        echo '<a href="' . esc_url( add_query_arg( array( 'add-to-cart' => $product->get_id(), 'buy_now' => 'true' ), site_url() ) ) . '" class="button buy-now-button text-bn">অর্ডার করুন</a>';
    }
}
add_action( 'woocommerce_after_add_to_cart_button', 'add_buy_now_button' );

// Redirect to checkout if 'buy_now' parameter is set
function custom_redirect_to_checkout() {
    if ( isset( $_GET['buy_now'] ) ) {
        wp_safe_redirect( wc_get_checkout_url() );
        exit;
    }
}
add_action( 'template_redirect', 'custom_redirect_to_checkout' );

// Change 'Add to Cart' text on WooCommerce shop and product pages
function custom_woocommerce_add_to_cart_text() {
    return __( 'কার্টে যোগ করুন', 'woocommerce' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'custom_woocommerce_add_to_cart_text' );
add_filter( 'woocommerce_product_add_to_cart_text', 'custom_woocommerce_add_to_cart_text' );

// Change the text of the Checkout button in WooCommerce
function custom_woocommerce_checkout_button_text() {
    return __( 'অর্ডার সম্পন্ন করুন', 'woocommerce' ); // Replace 'Complete Purchase' with your desired text
}
add_filter( 'woocommerce_order_button_text', 'custom_woocommerce_checkout_button_text' );

// Change checkout button text in Astra theme cart drawer to Bangla
function custom_astra_cart_checkout_button_text_bangla( $text ) {
    return __( 'অর্ডার করুন', 'astra' ); // Replace with desired Bangla text
}

add_filter( 'astra_woo_mini_cart_button_text', 'custom_astra_cart_checkout_button_text_bangla' );



include(get_stylesheet_directory() . '/google-ads.php');
include(get_stylesheet_directory() . '/woocommerce-additional.php');