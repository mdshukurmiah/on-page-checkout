<?php
// Placeholder for WooCommerce integration functions

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3OPCFE_WooCommerce_Integration {

    public function __construct() {
        add_action( 'wp_ajax_w3opcfe_get_checkout_form', [ $this, 'get_checkout_form' ] );
        add_action( 'wp_ajax_nopriv_w3opcfe_get_checkout_form', [ $this, 'get_checkout_form' ] );
        add_action( 'wp_ajax_w3opcfe_process_checkout', [ $this, 'process_checkout' ] );
        add_action( 'wp_ajax_nopriv_w3opcfe_process_checkout', [ $this, 'process_checkout' ] );
    }

    public function get_checkout_form() {
        check_ajax_referer( 'w3opcfe_nonce', 'nonce' );

        if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => esc_html__( 'WooCommerce cart is not initialized.', 'on-page-checkout-for-elementor' ) ] );
        }

        $product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
        $coupon_code  = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Product ID is missing.', 'on-page-checkout-for-elementor' ) ] );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product instanceof WC_Product ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid product selected.', 'on-page-checkout-for-elementor' ) ] );
        }

        if ( ! $product->is_purchasable() ) {
            wp_send_json_error( [ 'message' => esc_html__( 'This product is not currently available for purchase.', 'on-page-checkout-for-elementor' ) ] );
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
        check_ajax_referer( 'w3opcfe_nonce', 'nonce' );

        if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->checkout() ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'WooCommerce is not initialized.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'WooCommerce is not initialized.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        $product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
        $coupon_code  = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';

        if ( ! $product_id ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Product ID is missing.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Product ID is missing.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        $product = wc_get_product( $product_id );
        if ( ! $product instanceof WC_Product ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Invalid product selected.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Invalid product selected.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        if ( ! $product->is_purchasable() ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'This product is not currently available for purchase.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'This product is not currently available for purchase.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        if ( ! $product->is_in_stock() ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Sorry, this product is currently out of stock.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Sorry, this product is currently out of stock.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        // Reset cart and add item
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

        // Validate payment method
        $payment_method = sanitize_text_field( wp_unslash( $_POST['payment_method'] ?? '' ) );
        if ( empty( $payment_method ) ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Please select a payment method.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Please select a payment method.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        $available_gateways = WC()->payment_gateways ? WC()->payment_gateways->get_available_payment_gateways() : [];
        if ( ! isset( $available_gateways[ $payment_method ] ) ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Invalid payment method selected.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Invalid payment method selected.', 'on-page-checkout-for-elementor' ),
            ] );
        }

        try {
            $created_order_id = 0;
            add_action( 'woocommerce_checkout_order_processed', function( $order_id ) use ( &$created_order_id ) {
                $created_order_id = absint( $order_id );
                if ( class_exists( 'PixelBeacon_Helpers' ) && method_exists( 'PixelBeacon_Helpers', 'save_fb_cookies_to_order' ) ) {
                    \PixelBeacon_Helpers::save_fb_cookies_to_order( $order_id );
                }
            }, 5, 1 );

            // Note: WooCommerce WC_Checkout::process_checkout reads directly from $_POST.
            // In an AJAX context, WooCommerce will often output JSON and exit directly.
            // If execution continues or returns an array, handle it below.
            $result = $checkout->process_checkout();

            if ( wc_notice_count( 'error' ) > 0 ) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
                wp_send_json( [
                    'result'   => 'failure',
                    'messages' => $messages,
                    'message'  => wp_strip_all_tags( $messages ),
                ] );
            }

            if ( is_array( $result ) && isset( $result['result'] ) && 'success' === $result['result'] ) {
                wp_send_json( [
                    'result'   => 'success',
                    'redirect' => $result['redirect'] ?? '',
                    'order_id' => $created_order_id,
                ] );
            }

            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Checkout failed. Please verify your fields and try again.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Checkout failed. Please verify your fields and try again.', 'on-page-checkout-for-elementor' ),
            ] );

        } catch ( Exception $e ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html( $e->getMessage() ) . '</li></ul>',
                'message'  => esc_html( $e->getMessage() ),
            ] );
        }
    }

}

new W3OPCFE_WooCommerce_Integration();
