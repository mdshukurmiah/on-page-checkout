<?php
// Placeholder for WooCommerce integration functions

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3_Opc_WooCommerce_Integration {

    public function __construct() {
        add_action( 'wp_ajax_opc_get_checkout_form', [ $this, 'get_checkout_form' ] );
        add_action( 'wp_ajax_nopriv_opc_get_checkout_form', [ $this, 'get_checkout_form' ] );
        add_action( 'wp_ajax_opc_process_checkout', [ $this, 'process_checkout' ] );
        add_action( 'wp_ajax_nopriv_opc_process_checkout', [ $this, 'process_checkout' ] );
    }

    public function get_checkout_form() {
        check_ajax_referer( 'opc-nonce', 'nonce' );

        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $coupon_code  = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        $product      = wc_get_product( $product_id );

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Product ID is missing.', 'on-page-checkout' ) ] );
        }

        WC()->cart->empty_cart();

        if ( $product->is_type( 'variable' ) ) {
            $variation = [];
            foreach ( $_POST as $k => $v ) {
                if ( strpos( $k, 'attribute_' ) === 0 ) {
                    $variation[ sanitize_key( $k ) ] = sanitize_text_field( wp_unslash( $v ) );
                }
            }

            // Try server-side matching if no variation_id but attributes present
            if ( ! $variation_id && ! empty( $variation ) ) {
                $data_store = WC_Data_Store::load( 'product' );
                $matching   = $data_store->find_matching_product_variation( $product, $variation );
                if ( $matching ) {
                    $variation_id = $matching;
                }
            }
            WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

        } else {
            WC()->cart->add_to_cart( $product_id, $quantity );
        }

        if ( ! empty( $coupon_code ) ) {
            WC()->cart->apply_coupon( $coupon_code );
        }

        WC()->cart->calculate_totals();

        ob_start();
        woocommerce_checkout_form();
        $checkout_form = ob_get_clean();

        wp_send_json_success( [ 'form' => $checkout_form ] );
    }

    public function process_checkout() {
        check_ajax_referer( 'opc-nonce', 'nonce' );

        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $coupon_code  = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        $product      = wc_get_product( $product_id );

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Product ID is missing.', 'on-page-checkout' ) ] );
        }

        // Reset cart
        WC()->cart->empty_cart();

        if ( $product->is_type( 'variable' ) ) {
            $variation = [];
            foreach ( $_POST as $k => $v ) {
                if ( strpos( $k, 'attribute_' ) === 0 ) {
                    $variation[ sanitize_key( $k ) ] = sanitize_text_field( wp_unslash( $v ) );
                }
            }

            // Try server-side matching if no variation_id but attributes present
            if ( ! $variation_id && ! empty( $variation ) ) {
                $data_store = WC_Data_Store::load( 'product' );
                $matching   = $data_store->find_matching_product_variation( $product, $variation );
                if ( $matching ) {
                    $variation_id = $matching;
                }
            }
            WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );

        } else {
            WC()->cart->add_to_cart( $product_id, $quantity );
        }

        if ( ! empty( $coupon_code ) ) {
            WC()->cart->apply_coupon( $coupon_code );
        }

        WC()->cart->calculate_totals();

        $checkout = WC()->checkout();

        // Collect and sanitize checkout fields
        $posted_data = [
            'billing_first_name' => sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ?? '' ) ),
            'billing_last_name'  => sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ?? '' ) ),
            'billing_email'      => sanitize_email( wp_unslash( $_POST['billing_email'] ?? '' ) ),
            'billing_phone'      => sanitize_text_field( wp_unslash( $_POST['billing_phone'] ?? '' ) ),
            'billing_country'    => sanitize_text_field( wp_unslash( $_POST['billing_country'] ?? '' ) ),
            'billing_address_1'  => sanitize_text_field( wp_unslash( $_POST['billing_address_1'] ?? '' ) ),
            'billing_address_2'  => sanitize_text_field( wp_unslash( $_POST['billing_address_2'] ?? '' ) ),
            'billing_city'       => sanitize_text_field( wp_unslash( $_POST['billing_city'] ?? '' ) ),
            'billing_state'      => sanitize_text_field( wp_unslash( $_POST['billing_state'] ?? '' ) ),
            'billing_postcode'   => sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ?? '' ) ),

            'shipping_first_name' => sanitize_text_field( wp_unslash( $_POST['shipping_first_name'] ?? '' ) ),
            'shipping_last_name'  => sanitize_text_field( wp_unslash( $_POST['shipping_last_name'] ?? '' ) ),
            'shipping_address_1'  => sanitize_text_field( wp_unslash( $_POST['shipping_address_1'] ?? '' ) ),
            'shipping_address_2'  => sanitize_text_field( wp_unslash( $_POST['shipping_address_2'] ?? '' ) ),
            'shipping_city'       => sanitize_text_field( wp_unslash( $_POST['shipping_city'] ?? '' ) ),
            'shipping_state'      => sanitize_text_field( wp_unslash( $_POST['shipping_state'] ?? '' ) ),
            'shipping_postcode'   => sanitize_text_field( wp_unslash( $_POST['shipping_postcode'] ?? '' ) ),
            'shipping_country'    => sanitize_text_field( wp_unslash( $_POST['shipping_country'] ?? '' ) ),

            'order_comments'            => sanitize_textarea_field( wp_unslash( $_POST['order_comments'] ?? '' ) ),
            'payment_method'            => sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) ),
            'ship_to_different_address' => ! empty( $_POST['ship_to_different_address'] ) ? 1 : 0,
        ];

        // Validate payment method
        if ( empty( $posted_data['payment_method'] ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Please select a payment method.', 'on-page-checkout' ) ] );
        }

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! isset( $available_gateways[ $posted_data['payment_method'] ] ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid payment method.', 'on-page-checkout' ) ] );
        }

        try {
            $result = $checkout->process_checkout( $posted_data );

            if ( isset( $result['result'] ) && 'success' === $result['result'] ) {
                wp_send_json_success( [ 'redirect' => $result['redirect'] ] );
            }
            wp_send_json_error( [ 'message' => esc_html__( 'Checkout failed. Please try again.', 'on-page-checkout' ) ] );

        } catch ( Exception $e ) {
            wp_send_json_error( [ 'message' => esc_html__( 'An error occurred while processing your order. Please try again.', 'on-page-checkout' ) ] );
        }
    }

}

new W3_Opc_WooCommerce_Integration();
