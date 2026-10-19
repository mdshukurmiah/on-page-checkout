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
        if ( ! headers_sent() ) {
            nocache_headers();
        }

        check_ajax_referer( 'w3opcfe_nonce', 'nonce', false );

        if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => esc_html__( 'WooCommerce cart is not initialized.', 'on-page-checkout-for-elementor' ) ] );
        }

        $product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
        $coupon_code  = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : '';
        $selected_products = isset( $_POST['selected_products'] ) ? $_POST['selected_products'] : [];

        $added = $this->populate_cart( $selected_products, $product_id, $variation_id, $quantity );
        if ( ! $added ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one valid product.', 'on-page-checkout-for-elementor' ) ] );
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
        if ( ! headers_sent() ) {
            nocache_headers();
        }

        // Bootstrap WooCommerce session, cart, and customer in admin-ajax context if needed
        if ( function_exists( 'WC' ) ) {
            if ( null === WC()->session ) {
                $session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
                WC()->session  = new $session_class();
                WC()->session->init();
            }
            if ( null === WC()->cart ) {
                WC()->cart = new WC_Cart();
            }
            if ( null === WC()->customer ) {
                WC()->customer = new WC_Customer( get_current_user_id(), true );
            }
        }

        // Check either the custom w3opcfe_nonce or the WooCommerce core checkout process nonce
        $w3_nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : '';
        $wc_nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['woocommerce-process-checkout-nonce'] ) ) : '';

        $is_w3_valid = ! empty( $w3_nonce ) && wp_verify_nonce( $w3_nonce, 'w3opcfe_nonce' );
        $is_wc_valid = ! empty( $wc_nonce ) && wp_verify_nonce( $wc_nonce, 'woocommerce-process_checkout' );

        if ( ! $is_w3_valid && ! $is_wc_valid ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Your session has expired. Please refresh the page and try again.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Your session has expired. Please refresh the page and try again.', 'on-page-checkout-for-elementor' ),
            ] );
        }

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
        $selected_products = isset( $_POST['selected_products'] ) ? $_POST['selected_products'] : [];

        $added = $this->populate_cart( $selected_products, $product_id, $variation_id, $quantity );
        if ( ! $added ) {
            wp_send_json( [
                'result'   => 'failure',
                'messages' => '<ul class="woocommerce-error"><li>' . esc_html__( 'Please select at least one valid product.', 'on-page-checkout-for-elementor' ) . '</li></ul>',
                'message'  => esc_html__( 'Please select at least one valid product.', 'on-page-checkout-for-elementor' ),
            ] );
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

    /**
     * Add selected products to WooCommerce cart.
     *
     * @param mixed $selected_products
     * @param int   $fallback_product_id
     * @param int   $fallback_variation_id
     * @param int   $fallback_quantity
     * @return bool Whether at least one product was added.
     */
    public function populate_cart( $selected_products, $fallback_product_id = 0, $fallback_variation_id = 0, $fallback_quantity = 1 ) {
        $items = [];
        if ( ! empty( $selected_products ) ) {
            $raw = $selected_products;
            if ( is_string( $raw ) ) {
                $raw = json_decode( wp_unslash( $raw ), true );
            }
            if ( is_array( $raw ) ) {
                foreach ( $raw as $sp ) {
                    $p_id = isset( $sp['product_id'] ) ? absint( $sp['product_id'] ) : 0;
                    $v_id = isset( $sp['variation_id'] ) ? absint( $sp['variation_id'] ) : 0;
                    $qty  = isset( $sp['quantity'] ) ? max( 1, absint( $sp['quantity'] ) ) : 1;
                    if ( $p_id > 0 ) {
                        $items[] = [
                            'product_id'   => $p_id,
                            'variation_id' => $v_id,
                            'quantity'     => $qty,
                        ];
                    }
                }
            }
        }

        if ( empty( $items ) && $fallback_product_id > 0 ) {
            $items[] = [
                'product_id'   => $fallback_product_id,
                'variation_id' => $fallback_variation_id,
                'quantity'     => $fallback_quantity,
            ];
        }

        if ( empty( $items ) ) {
            return false;
        }

        WC()->cart->empty_cart();
        $added_any = false;

        foreach ( $items as $it ) {
            $p_id = $it['product_id'];
            $v_id = $it['variation_id'];
            $qty  = $it['quantity'];

            $product = wc_get_product( $p_id );
            if ( ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
                continue;
            }

            if ( $product->is_type( 'variation' ) ) {
                $v_id    = $product->get_id();
                $p_id    = $product->get_parent_id();
                $product = wc_get_product( $p_id );
            }

            if ( $product->is_type( 'variable' ) ) {
                $variation = [];
                if ( 1 === count( $items ) ) {
                    foreach ( $_POST as $k => $v ) {
                        if ( strpos( $k, 'attribute_' ) === 0 ) {
                            $variation[ sanitize_key( $k ) ] = sanitize_text_field( wp_unslash( $v ) );
                        }
                    }
                }

                if ( $v_id && empty( $variation ) ) {
                    $var_product = wc_get_product( $v_id );
                    if ( $var_product instanceof WC_Product_Variation ) {
                        $variation = $var_product->get_variation_attributes();
                    }
                }

                if ( ! $v_id && ! empty( $variation ) ) {
                    $data_store = WC_Data_Store::load( 'product' );
                    $matching   = $data_store->find_matching_product_variation( $product, $variation );
                    if ( $matching ) {
                        $v_id = $matching;
                    }
                }

                if ( $v_id ) {
                    $res = WC()->cart->add_to_cart( $p_id, $qty, $v_id, $variation );
                    if ( $res ) {
                        $added_any = true;
                    }
                }
            } else {
                $res = WC()->cart->add_to_cart( $p_id, $qty );
                if ( $res ) {
                    $added_any = true;
                }
            }
        }

        return $added_any;
    }

}

new W3OPCFE_WooCommerce_Integration();
