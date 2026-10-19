<?php
// AJAX handler functions

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3OPCFE_Ajax_Handler {

    public function __construct() {
        add_action( 'wp_ajax_w3opcfe_load_checkout_fields', [ $this, 'load_checkout_fields' ] );
        add_action( 'wp_ajax_nopriv_w3opcfe_load_checkout_fields', [ $this, 'load_checkout_fields' ] );
        add_action( 'wp_ajax_w3opcfe_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_w3opcfe_add_to_cart', [ $this, 'add_to_cart' ] );
    }

    public function load_checkout_fields() {
        check_ajax_referer( 'w3opcfe_nonce', 'nonce' );

        if ( ! class_exists( 'WooCommerce' ) || ! WC()->cart || ! WC()->customer || ! WC()->session ) {
            wp_send_json_error( [ 'message' => esc_html__( 'WooCommerce is not initialized.', 'on-page-checkout-for-elementor' ) ] );
        }

        $product_id   = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $quantity     = isset( $_POST['quantity'] ) ? max( 1, absint( wp_unslash( $_POST['quantity'] ) ) ) : 1;
        $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;

        $selected_products = [];
        if ( ! empty( $_POST['selected_products'] ) ) {
            $raw = $_POST['selected_products'];
            if ( is_string( $raw ) ) {
                $raw = json_decode( wp_unslash( $raw ), true );
            }
            if ( is_array( $raw ) ) {
                foreach ( $raw as $sp ) {
                    $sp_pid = isset( $sp['product_id'] ) ? absint( $sp['product_id'] ) : 0;
                    $sp_vid = isset( $sp['variation_id'] ) ? absint( $sp['variation_id'] ) : 0;
                    $sp_qty = isset( $sp['quantity'] ) ? max( 1, absint( $sp['quantity'] ) ) : 1;
                    if ( $sp_pid > 0 ) {
                        $selected_products[] = [
                            'product_id'   => $sp_pid,
                            'variation_id' => $sp_vid,
                            'quantity'     => $sp_qty,
                        ];
                    }
                }
            }
        }

        if ( empty( $selected_products ) && $product_id > 0 ) {
            $selected_products[] = [
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'quantity'     => $quantity,
            ];
        }

        if ( empty( $selected_products ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Please select at least one product.', 'on-page-checkout-for-elementor' ) ] );
        }

        WC()->cart->empty_cart();
        $added_to_cart  = false;
        $added_item_key = false;

        foreach ( $selected_products as $item_info ) {
            $p_id = $item_info['product_id'];
            $v_id = $item_info['variation_id'];
            $qty  = $item_info['quantity'];

            $item_product = wc_get_product( $p_id );
            if ( ! $item_product instanceof WC_Product || ! $item_product->is_purchasable() ) {
                continue;
            }

            if ( $item_product->is_type( 'variation' ) ) {
                $v_id         = $item_product->get_id();
                $p_id         = $item_product->get_parent_id();
                $item_product = wc_get_product( $p_id );
            }

            if ( $item_product->is_type( 'variable' ) ) {
                $variation = [];
                if ( 1 === count( $selected_products ) ) {
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
                    $matching   = $data_store->find_matching_product_variation( $item_product, $variation );
                    if ( $matching ) {
                        $v_id = $matching;
                    }
                }

                if ( $v_id ) {
                    $key = WC()->cart->add_to_cart( $p_id, $qty, $v_id, $variation );
                    if ( $key ) {
                        $added_to_cart  = true;
                        $added_item_key = $key;
                    }
                }
            } else {
                $key = WC()->cart->add_to_cart( $p_id, $qty );
                if ( $key ) {
                    $added_to_cart  = true;
                    $added_item_key = $key;
                }
            }
        }

        if ( ! $added_to_cart ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to add product to cart. Please verify product options.', 'on-page-checkout-for-elementor' ) ] );
        }

        $customer     = WC()->customer;
        $base_country = wc_get_base_location()['country'];

        // High Event Match Quality: Read persistent form cookies populated by PixelBeacon
        $persisted = [];
        if ( class_exists( 'PixelBeacon_Helpers' ) && method_exists( 'PixelBeacon_Helpers', 'get_persistence_user_data' ) ) {
            $persisted = \PixelBeacon_Helpers::get_persistence_user_data();
        } elseif ( class_exists( 'FBPixel_CAPI_Helpers' ) && method_exists( 'FBPixel_CAPI_Helpers', 'get_persistence_user_data' ) ) {
            $persisted = \FBPixel_CAPI_Helpers::get_persistence_user_data();
        }

        // Keep existing/default customer values when the field is absent from AJAX payload.
        $billing_first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : ( $customer->get_billing_first_name() ?: ( $persisted['fn'] ?? '' ) );
        $billing_last_name  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : ( $customer->get_billing_last_name() ?: ( $persisted['ln'] ?? '' ) );
        $billing_company    = isset( $_POST['billing_company'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) : $customer->get_billing_company();
        $billing_email      = isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : ( $customer->get_billing_email() ?: ( $persisted['em'] ?? '' ) );
        $billing_phone      = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : ( $customer->get_billing_phone() ?: ( $persisted['ph'] ?? '' ) );
        $billing_address_1  = isset( $_POST['billing_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address_1'] ) ) : $customer->get_billing_address_1();
        $billing_address_2  = isset( $_POST['billing_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address_2'] ) ) : $customer->get_billing_address_2();
        $billing_city       = isset( $_POST['billing_city'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_city'] ) ) : $customer->get_billing_city();
        $billing_state      = isset( $_POST['billing_state'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ) : $customer->get_billing_state();
        $billing_postcode   = isset( $_POST['billing_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ) ) : $customer->get_billing_postcode();
        $billing_country    = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : $customer->get_billing_country();
        if ( '' === $billing_country ) {
            $billing_country = $base_country;
        }

        // Set billing address
        $customer->set_billing_first_name( $billing_first_name );
        $customer->set_billing_last_name( $billing_last_name );
        $customer->set_billing_company( $billing_company );
        $customer->set_billing_email( $billing_email );
        $customer->set_billing_phone( $billing_phone );
        $customer->set_billing_address_1( $billing_address_1 );
        $customer->set_billing_address_2( $billing_address_2 );
        $customer->set_billing_city( $billing_city );
        $customer->set_billing_state( $billing_state );
        $customer->set_billing_postcode( $billing_postcode );
        $customer->set_billing_country( $billing_country );

        // Set shipping address
        if ( isset( $_POST['ship_to_different_address'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['ship_to_different_address'] ) ) ) {
            $shipping_first_name = isset( $_POST['shipping_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_first_name'] ) ) : $customer->get_shipping_first_name();
            $shipping_last_name  = isset( $_POST['shipping_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_last_name'] ) ) : $customer->get_shipping_last_name();
            $shipping_company    = isset( $_POST['shipping_company'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_company'] ) ) : $customer->get_shipping_company();
            $shipping_address_1  = isset( $_POST['shipping_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_address_1'] ) ) : $customer->get_shipping_address_1();
            $shipping_address_2  = isset( $_POST['shipping_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_address_2'] ) ) : $customer->get_shipping_address_2();
            $shipping_city       = isset( $_POST['shipping_city'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_city'] ) ) : $customer->get_shipping_city();
            $shipping_state      = isset( $_POST['shipping_state'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) ) : $customer->get_shipping_state();
            $shipping_postcode   = isset( $_POST['shipping_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_postcode'] ) ) : $customer->get_shipping_postcode();
            $shipping_country    = isset( $_POST['shipping_country'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_country'] ) ) : $customer->get_shipping_country();
            if ( '' === $shipping_country ) {
                $shipping_country = $base_country;
            }

            $customer->set_shipping_first_name( $shipping_first_name );
            $customer->set_shipping_last_name( $shipping_last_name );
            $customer->set_shipping_company( $shipping_company );
            $customer->set_shipping_address_1( $shipping_address_1 );
            $customer->set_shipping_address_2( $shipping_address_2 );
            $customer->set_shipping_city( $shipping_city );
            $customer->set_shipping_state( $shipping_state );
            $customer->set_shipping_postcode( $shipping_postcode );
            $customer->set_shipping_country( $shipping_country );
        } else {
            // Copy billing fields to shipping
            $customer->set_shipping_first_name( $billing_first_name );
            $customer->set_shipping_last_name( $billing_last_name );
            $customer->set_shipping_company( $billing_company );
            $customer->set_shipping_address_1( $billing_address_1 );
            $customer->set_shipping_address_2( $billing_address_2 );
            $customer->set_shipping_city( $billing_city );
            $customer->set_shipping_state( $billing_state );
            $customer->set_shipping_postcode( $billing_postcode );
            $customer->set_shipping_country( $billing_country );
        }

        $customer->save();

        if ( ! empty( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
            $chosen_methods = array_map( 'sanitize_text_field', wp_unslash( $_POST['shipping_method'] ) );
            WC()->session->set( 'chosen_shipping_methods', $chosen_methods );
        }

        // Persist session & recalculate totals
        WC()->session->set_customer_session_cookie( true );
        WC()->session->save_data();
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_totals();

        // Build checkout fields
        $checkout = WC()->checkout();
        $fields   = $checkout->get_checkout_fields();

        // Sanitize and whitelist widget settings coming from the browser
        $raw_settings_input = filter_input( INPUT_POST, 'widgetSettings', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        $raw_settings       = is_array( $raw_settings_input ) ? wp_unslash( $raw_settings_input ) : [];
        $widgetSettings = [
            'billingTitle'            => sanitize_text_field( $raw_settings['billingTitle'] ?? '' ),
            'shippingTitle'           => sanitize_text_field( $raw_settings['shippingTitle'] ?? '' ),
            'shippingMethodTitle'     => sanitize_text_field( $raw_settings['shippingMethodTitle'] ?? 'Shipping Method' ),
            'additionalInfoTitle'     => sanitize_text_field( $raw_settings['additionalInfoTitle'] ?? '' ),
            'orderSummaryTitle'       => sanitize_text_field( $raw_settings['orderSummaryTitle'] ?? '' ),
            'paymentMethodTitle'      => sanitize_text_field( $raw_settings['paymentMethodTitle'] ?? '' ),
            'orderButtonName'         => sanitize_text_field( $raw_settings['orderButtonName'] ?? '' ),
            'mobileSummaryLabel'      => sanitize_text_field( $raw_settings['mobileSummaryLabel'] ?? 'Order summary' ),
            'mobileSummaryCollapsed'  => sanitize_text_field( $raw_settings['mobileSummaryCollapsed'] ?? 'yes' ),
        ];

        $mobile_collapsed = ( 'no' !== $widgetSettings['mobileSummaryCollapsed'] );
        $initial_expanded_attr = $mobile_collapsed ? 'false' : 'true';
        $initial_wrapper_class = ( ! $mobile_collapsed ) ? ' opc-summary-open' : '';
        $mobile_btn_label = ! empty( $widgetSettings['mobileSummaryLabel'] ) ? $widgetSettings['mobileSummaryLabel'] : esc_html__( 'Order summary', 'on-page-checkout-for-elementor' );
        ?>

        <div class="opc-checkout-fields">
            <input type="hidden" name="woocommerce-process-checkout-nonce" value="<?php echo esc_attr( wp_create_nonce( 'woocommerce-process_checkout' ) ); ?>">
            <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( wc_get_checkout_url() ); ?>">

            <?php if ( ! empty( $fields['billing'] ) ) : ?>
                <div class="opc-billing-fields">
                    <?php if ( ! empty( $widgetSettings['billingTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['billingTitle'] ); ?></h4>
                    <?php endif; ?>
                    <?php foreach ( $fields['billing'] as $key => $field ) : ?>
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $fields['shipping'] ) && WC()->cart->needs_shipping() ) : ?>
                <div class="opc-shipping-fields">
                    <?php if ( ! empty( $widgetSettings['shippingTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['shippingTitle'] ); ?></h4>
                    <?php endif; ?>
                    <p class="form-row form-row-wide">
                        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                            <input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="ship_to_different_address" value="1" />
                            <span class="woocommerce-form__label-text"><?php esc_html_e( 'Ship to a different address?', 'on-page-checkout-for-elementor' ); ?></span>
                        </label>
                    </p>
                    <div class="shipping_address" style="display: none;">
                        <?php foreach ( $fields['shipping'] as $key => $field ) : ?>
                            <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $fields['order'] ) ) : ?>
                <div class="opc-order-fields">
                    <?php if ( ! empty( $widgetSettings['additionalInfoTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['additionalInfoTitle'] ); ?></h4>
                    <?php endif; ?>
                    <?php foreach ( $fields['order'] as $key => $field ) : ?>
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                <div class="opc-shipping-methods" id="opc_shipping_method_section">
                    <?php if ( ! empty( $widgetSettings['shippingMethodTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['shippingMethodTitle'] ); ?></h4>
                    <?php endif; ?>
                    <div class="opc-shipping-methods-content">
                        <?php
                        $packages = WC()->shipping()->get_packages();
                        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
                        foreach ( $packages as $i => $package ) {
                            $chosen_method = isset( $chosen_methods[ $i ] ) ? $chosen_methods[ $i ] : '';
                            $available_methods = $package['rates'];
                            ?>
                            <ul id="shipping_method" class="woocommerce-shipping-methods">
                                <?php if ( ! empty( $available_methods ) ) : ?>
                                    <?php foreach ( $available_methods as $method ) : ?>
                                        <li>
                                            <label for="shipping_method_<?php echo esc_attr( $i ); ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>">
                                                <input type="radio" name="shipping_method[<?php echo esc_attr( $i ); ?>]" data-index="<?php echo esc_attr( $i ); ?>" id="shipping_method_<?php echo esc_attr( $i ); ?>_<?php echo esc_attr( sanitize_title( $method->id ) ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" <?php checked( $method->id, $chosen_method ); ?> />
                                                <?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $method ) ); ?>
                                            </label>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <li><?php esc_html_e( 'No shipping methods available for your address.', 'on-page-checkout-for-elementor' ); ?></li>
                                <?php endif; ?>
                            </ul>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php do_action( 'woocommerce_review_order_before_payment' ); ?>

            <div class="opc-payment-methods woocommerce-checkout-payment" id="payment">
                <?php if ( ! empty( $widgetSettings['paymentMethodTitle'] ) ) : ?>
                    <h4><?php echo esc_html( $widgetSettings['paymentMethodTitle'] ); ?></h4>
                <?php endif; ?>
                <ul class="wc_payment_methods payment_methods methods">
                <?php
                $available_gateways = WC()->payment_gateways ? WC()->payment_gateways->get_available_payment_gateways() : [];
                $chosen_gateway     = WC()->session ? WC()->session->get( 'chosen_payment_method' ) : '';
                if ( empty( $chosen_gateway ) && ! empty( $available_gateways ) ) {
                    $chosen_gateway = current( array_keys( $available_gateways ) );
                }

                if ( ! empty( $available_gateways ) ) :
                    foreach ( $available_gateways as $gateway ) :
                        ?>
                        <li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                            <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                <input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->id, $chosen_gateway ); ?> />
                                <?php echo esc_html( $gateway->get_title() ); ?>
                                <?php echo wp_kses_post( $gateway->get_icon() ); ?>
                            </label>
                            <?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
                                <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" style="<?php echo ( $gateway->id === $chosen_gateway ) ? '' : 'display:none;'; ?>">
                                    <?php $gateway->payment_fields(); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <?php
                    endforeach;
                else :
                    echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . esc_html__( 'No payment methods are available.', 'woocommerce' ) . '</li>';
                endif;
                ?>
                </ul>
            </div>

            <?php if ( function_exists( 'wc_terms_and_conditions_page_id' ) && wc_terms_and_conditions_page_id() > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) : ?>
                <div class="woocommerce-terms-and-conditions-wrapper" style="margin-top:15px; margin-bottom:15px;">
                    <?php do_action( 'woocommerce_checkout_before_terms_and_conditions' ); ?>
                    <p class="form-row validate-required">
                        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); ?> id="terms" />
                            <span class="woocommerce-terms-and-conditions-checkbox-text"><?php function_exists( 'wc_terms_and_conditions_checkbox_text' ) ? wc_terms_and_conditions_checkbox_text() : esc_html_e( 'I have read and agree to the website terms and conditions', 'on-page-checkout-for-elementor' ); ?></span>&nbsp;<abbr class="required" title="<?php esc_attr_e( 'required', 'woocommerce' ); ?>">*</abbr>
                        </label>
                        <input type="hidden" name="terms-field" value="1" />
                    </p>
                    <?php do_action( 'woocommerce_checkout_after_terms_and_conditions' ); ?>
                </div>
            <?php endif; ?>

            <?php do_action( 'woocommerce_review_order_after_payment' ); ?>

            <?php do_action( 'woocommerce_review_order_before_submit' ); ?>

            <?php
            $order_button_name = ! empty( $widgetSettings['orderButtonName'] ) ? $widgetSettings['orderButtonName'] : __( 'Place Order', 'on-page-checkout-for-elementor' );
            echo '<button type="submit" class="opc-place-order-button woocommerce_checkout_place_order" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_name ) . '">' . esc_html( $order_button_name ) . '</button>';
            ?>

            <?php do_action( 'woocommerce_review_order_after_submit' ); ?>
        </div>
        <div id="order_review" class="order-details woocommerce-checkout-review-order">
            <div class="order-details-wrapper<?php echo esc_attr( $initial_wrapper_class ); ?>">
                <!-- Mobile Collapsible Order Summary Bar -->
                <button type="button" class="opc-mobile-order-summary-toggle" aria-expanded="<?php echo esc_attr( $initial_expanded_attr ); ?>">
                    <span class="opc-toggle-left">
                        <span class="opc-toggle-label"><?php echo esc_html( $mobile_btn_label ); ?></span>
                        <svg class="opc-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </span>
                    <span class="opc-toggle-total">
                        <?php echo wp_kses_post( WC()->cart->get_total() ); ?>
                    </span>
                </button>

                <div class="opc-order-review">
                    <?php if ( ! empty( $widgetSettings['orderSummaryTitle'] ) ) : ?>
                        <h4 class="opc-order-summary-desktop-title"><?php echo esc_html( $widgetSettings['orderSummaryTitle'] ); ?></h4>
                    <?php endif; ?>
                    <table class="shop_table woocommerce-checkout-review-order-table">
                        <thead>
                            <tr>
                                <th class="product-name"><?php esc_html_e( 'Product', 'on-page-checkout-for-elementor' ); ?></th>
                                <th class="product-total"><?php esc_html_e( 'Subtotal', 'on-page-checkout-for-elementor' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
                                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook.
                                    ?>
                                    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook. ?>">
                                        <td class="product-name">
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '&nbsp;'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook. ?>
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook. ?>
                                            <?php echo wp_kses_post( wc_get_formatted_cart_item_data( $cart_item ) ); ?>
                                        </td>
                                        <td class="product-total">
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WooCommerce core hook. ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="cart-subtotal">
                                <th><?php esc_html_e( 'Subtotal', 'on-page-checkout-for-elementor' ); ?></th>
                                <td><?php wc_cart_totals_subtotal_html(); ?></td>
                            </tr>
                            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                    <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                                    <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                                <tr class="woocommerce-shipping-totals shipping">
                                    <th><?php esc_html_e( 'Shipment', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td data-title="<?php esc_attr_e( 'Shipment', 'on-page-checkout-for-elementor' ); ?>">
                                        <?php
                                        $packages = WC()->shipping()->get_packages();
                                        $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
                                        $summary_output = [];
                                        foreach ( $packages as $i => $package ) {
                                            $chosen = isset( $chosen_methods[ $i ] ) ? $chosen_methods[ $i ] : '';
                                            if ( ! empty( $package['rates'] ) && isset( $package['rates'][ $chosen ] ) ) {
                                                $rate = $package['rates'][ $chosen ];
                                                $cost = (float) $rate->cost;
                                                $tax  = (float) array_sum( $rate->taxes );
                                                $total_rate_cost = $cost + ( WC()->cart->display_prices_including_tax() ? $tax : 0 );
                                                $summary_output[] = esc_html( $rate->get_label() ) . ': ' . ( $total_rate_cost > 0 ? wc_price( $total_rate_cost ) : esc_html__( 'Free shipping', 'on-page-checkout-for-elementor' ) );
                                            }
                                        }
                                        if ( ! empty( $summary_output ) ) {
                                            echo wp_kses_post( implode( '<br>', $summary_output ) );
                                        } else {
                                            echo esc_html__( 'Calculated at checkout', 'on-page-checkout-for-elementor' );
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                                <tr class="fee">
                                    <th><?php echo esc_html( $fee->name ); ?></th>
                                    <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                                <?php if ( 'itemized' === get_option( 'woocommerce_tax_display_cart' ) ) : ?>
                                    <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                                        <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                            <th><?php echo esc_html( $tax->label ); ?></th>
                                            <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <tr class="tax-total">
                                        <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                                        <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endif; ?>
                            <tr class="order-total">
                                <th><?php esc_html_e( 'Total', 'on-page-checkout-for-elementor' ); ?></th>
                                <td><?php wc_cart_totals_order_total_html(); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php
        $checkout_fields = ob_get_clean();

        // Build structured tracking data matching Meta Pixel & PixelBeacon CAPI requirements
        $content_ids    = [];
        $contents       = [];
        $content_names  = [];
        $all_categories = [];
        $all_tags       = [];
        $all_skus       = [];
        $all_brands     = [];
        $total_quantity = 0;

        if ( WC()->cart && ! WC()->cart->is_empty() ) {
            foreach ( WC()->cart->get_cart() as $cart_item ) {
                $item_id   = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
                $qty       = (int) $cart_item['quantity'];
                $price     = $qty > 0 ? (float) ( $cart_item['line_total'] / $qty ) : 0;
                $_prod     = isset( $cart_item['data'] ) ? $cart_item['data'] : wc_get_product( $item_id );

                if ( $_prod ) {
                    $content_names[] = $_prod->get_name();
                    $sku             = $_prod->get_sku();
                    if ( ! empty( $sku ) ) {
                        $all_skus[] = $sku;
                    }
                    $brand = $this->get_product_brand( $_prod );
                    if ( ! empty( $brand ) && ! in_array( $brand, $all_brands, true ) ) {
                        $all_brands[] = $brand;
                    }

                    $terms = get_the_terms( $cart_item['product_id'], 'product_cat' );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                        foreach ( $terms as $term ) {
                            if ( ! in_array( $term->name, $all_categories, true ) ) {
                                $all_categories[] = $term->name;
                            }
                        }
                    }

                    $tag_str = $this->get_product_tags( $cart_item['product_id'] );
                    if ( ! empty( $tag_str ) && ! in_array( $tag_str, $all_tags, true ) ) {
                        $all_tags[] = $tag_str;
                    }
                }

                $content_ids[] = (string) $item_id;
                $contents[]    = [
                    'id'         => (string) $item_id,
                    'quantity'   => $qty,
                    'item_price' => round( $price, 2 ),
                ];
                $total_quantity += $qty;
            }
        }

        $cat_string = implode( ', ', $all_categories );
        $tracking_data = [
            'content_name'     => implode( ', ', $content_names ),
            'content_category' => $cat_string,
            'category_name'    => $cat_string,
            'content_ids'      => $content_ids,
            'content_type'     => 'product',
            'contents'         => $contents,
            'value'            => WC()->cart ? (float) WC()->cart->get_total( 'edit' ) : 0,
            'subtotal'         => WC()->cart ? (float) WC()->cart->get_subtotal() : 0,
            'currency'         => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : 'USD',
            'num_items'        => $total_quantity,
            'shipping_cost'    => WC()->cart ? (float) WC()->cart->get_shipping_total() : 0,
            'tax'              => WC()->cart ? (float) WC()->cart->get_total_tax() : 0,
        ];

        if ( ! empty( $all_tags ) ) {
            $tracking_data['tags'] = implode( ', ', $all_tags );
        }
        if ( ! empty( $all_skus ) ) {
            $tracking_data['content_sku'] = implode( ', ', $all_skus );
        }
        if ( ! empty( $all_brands ) ) {
            $tracking_data['brand'] = implode( ', ', $all_brands );
        }

        $coupons = WC()->cart ? WC()->cart->get_applied_coupons() : [];
        if ( ! empty( $coupons ) ) {
            $tracking_data['coupon_used'] = 'yes';
            $tracking_data['coupon_name'] = implode( ', ', $coupons );
        }

        // Generate consistent event ID matching PixelBeacon format for deduplication
        $event_id = class_exists( 'PixelBeacon_Helpers' ) && method_exists( 'PixelBeacon_Helpers', 'guidv4' )
            ? \PixelBeacon_Helpers::guidv4()
            : ( function_exists( 'wp_generate_uuid4' ) ? wp_generate_uuid4() : sprintf( 'pb_%d_%s', time(), wp_generate_password( 8, false ) ) );

        // Collect cart fragments populated by PixelBeacon's AddToCart hook for synchronized browser/server firing
        $fragments = apply_filters( 'woocommerce_add_to_cart_fragments', [] );

        wp_send_json_success( [
            'fields'        => $checkout_fields,
            'cart_total'    => WC()->cart ? WC()->cart->get_total() : '',
            'cart_count'    => $total_quantity,
            'cart_hash'     => WC()->cart ? WC()->cart->get_cart_hash() : '',
            'fragments'     => $fragments,
            'product_id'    => $product_id,
            'variation_id'  => $variation_id,
            'quantity'      => $quantity,
            'currency'      => $tracking_data['currency'],
            'value'         => $tracking_data['value'],
            'event_id'      => $event_id,
            'tracking'      => $tracking_data,
        ] );
    }

    /**
     * Get product brand matching PixelBeacon convention
     *
     * @param \WC_Product $product
     * @return string
     */
    private function get_product_brand( $product ) {
        if ( ! $product instanceof \WC_Product ) {
            return '';
        }

        $brand            = '';
        $attributes       = $product->get_attributes();
        $brand_attributes = [ 'brand', 'pa_brand', 'product_brand', 'pa_product_brand' ];

        foreach ( $brand_attributes as $brand_attr ) {
            if ( isset( $attributes[ $brand_attr ] ) ) {
                $attribute = $attributes[ $brand_attr ];
                if ( is_object( $attribute ) && method_exists( $attribute, 'is_taxonomy' ) && $attribute->is_taxonomy() ) {
                    $terms = wp_get_post_terms( $product->get_id(), $attribute->get_name() );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                        $brand = $terms[0]->name;
                        break;
                    }
                } elseif ( is_object( $attribute ) && method_exists( $attribute, 'get_options' ) ) {
                    $opts  = $attribute->get_options();
                    $brand = ! empty( $opts[0] ) ? $opts[0] : '';
                    if ( $brand ) {
                        break;
                    }
                }
            }
        }

        if ( empty( $brand ) ) {
            $custom_brand_fields = [ '_brand', '_product_brand', 'brand', 'product_brand' ];
            foreach ( $custom_brand_fields as $field ) {
                $brand = get_post_meta( $product->get_id(), $field, true );
                if ( ! empty( $brand ) ) {
                    break;
                }
            }
        }

        return sanitize_text_field( $brand );
    }

    /**
     * Get product tags comma separated
     *
     * @param int $product_id
     * @return string
     */
    private function get_product_tags( $product_id ) {
        $terms = get_the_terms( $product_id, 'product_tag' );
        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return '';
        }

        $tags = [];
        foreach ( $terms as $term ) {
            $tags[] = $term->name;
        }

        return implode( ', ', $tags );
    }

    public function add_to_cart() {
        check_ajax_referer( 'w3opcfe_nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $quantity   = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Product ID is missing.', 'on-page-checkout-for-elementor' ) ] );
        }

        $result = WC()->cart->add_to_cart( $product_id, $quantity );

        if ( $result ) {
            wp_send_json_success( [ 'message' => esc_html__( 'Product added to cart.', 'on-page-checkout-for-elementor' ) ] );
        } else {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to add product to cart.', 'on-page-checkout-for-elementor' ) ] );
        }
    }
}

new W3OPCFE_Ajax_Handler();
