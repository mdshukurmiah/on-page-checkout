<?php
// AJAX handler functions

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3_Opc_Ajax_Handler {

    public function __construct() {
        add_action( 'wp_ajax_opc_load_checkout_fields', [ $this, 'load_checkout_fields' ] );
        add_action( 'wp_ajax_nopriv_opc_load_checkout_fields', [ $this, 'load_checkout_fields' ] );
        add_action( 'wp_ajax_opc_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wp_ajax_nopriv_opc_add_to_cart', [ $this, 'add_to_cart' ] );
    }

    public function load_checkout_fields() {
        check_ajax_referer( 'opc-nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $quantity   = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid product selected.', 'on-page-checkout' ) ] );
        }

        // Try to add product (handle variable)
        $added_to_cart = false;
        $added_item_key = false;

        if ( $product->is_type( 'variable' ) ) {
            $variation_id = isset( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;

            // Collect attribute_* fields from POST
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

            if ( $variation_id ) {
                WC()->cart->empty_cart();
                $added_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
                if ( $added_item_key ) {
                    $added_to_cart = true;
                }
            } else {
                wp_send_json_error( [
                    'message' => wp_kses_post( sprintf(
                        /* translators: 1: URL to product page, 2: product name */
                        __( 'Please choose product options by visiting <a href="%1$s">%2$s</a>.', 'on-page-checkout' ),
                        esc_url( get_permalink( $product_id ) ),
                        esc_html( $product->get_name() )
                    ) ),
                ] );
            }
        } else {
            // Simple product
            WC()->cart->empty_cart();
            $added_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
            $added_to_cart  = (bool) $added_item_key;
        }

        if ( ! $added_to_cart ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to add product to cart. Please verify product options.', 'on-page-checkout' ) ] );
        }

        $customer     = WC()->customer;
        $base_country = wc_get_base_location()['country'];

        // Keep existing/default customer values when the field is absent from AJAX payload.
        $billing_first_name = isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : $customer->get_billing_first_name();
        $billing_last_name  = isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : $customer->get_billing_last_name();
        $billing_company    = isset( $_POST['billing_company'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_company'] ) ) : $customer->get_billing_company();
        $billing_email      = isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : $customer->get_billing_email();
        $billing_phone      = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : $customer->get_billing_phone();
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
            'billingTitle'        => sanitize_text_field( $raw_settings['billingTitle'] ?? '' ),
            'shippingTitle'       => sanitize_text_field( $raw_settings['shippingTitle'] ?? '' ),
            'additionalInfoTitle' => sanitize_text_field( $raw_settings['additionalInfoTitle'] ?? '' ),
            'orderSummaryTitle'   => sanitize_text_field( $raw_settings['orderSummaryTitle'] ?? '' ),
            'paymentMethodTitle'  => sanitize_text_field( $raw_settings['paymentMethodTitle'] ?? '' ),
            'orderButtonName'     => sanitize_text_field( $raw_settings['orderButtonName'] ?? '' ),
        ];

        ob_start();
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
                            <span class="woocommerce-form__label-text"><?php esc_html_e( 'Ship to a different address?', 'on-page-checkout' ); ?></span>
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
        </div>
        <div class="order-details" id="order_review" class="woocommerce-checkout-review-order">
            <div class="order-details-wrapper">
                <div class="opc-order-review">
                    <?php if ( ! empty( $widgetSettings['orderSummaryTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['orderSummaryTitle'] ); ?></h4>
                    <?php endif; ?>
                    <table class="shop_table woocommerce-checkout-review-order-table">
                        <thead>
                            <tr>
                                <th class="product-name"><?php esc_html_e( 'Product', 'on-page-checkout' ); ?></th>
                                <th class="product-total"><?php esc_html_e( 'Subtotal', 'on-page-checkout' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                                    ?>
                                    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
                                        <td class="product-name">
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ) . '&nbsp;'; ?>
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ) ); ?>
                                            <?php echo wp_kses_post( wc_get_formatted_cart_item_data( $cart_item ) ); ?>
                                        </td>
                                        <td class="product-total">
                                            <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="cart-subtotal">
                                <th><?php esc_html_e( 'Subtotal', 'on-page-checkout' ); ?></th>
                                <td><?php wc_cart_totals_subtotal_html(); ?></td>
                            </tr>
                            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                                <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                    <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                                    <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                                <?php wc_cart_totals_shipping_html(); ?>
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
                                <th><?php esc_html_e( 'Total', 'on-page-checkout' ); ?></th>
                                <td><?php wc_cart_totals_order_total_html(); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="opc-payment-methods woocommerce-checkout-payment" id="payment">
                    <?php if ( ! empty( $widgetSettings['paymentMethodTitle'] ) ) : ?>
                        <h4><?php echo esc_html( $widgetSettings['paymentMethodTitle'] ); ?></h4>
                    <?php endif; ?>
                    <ul class="wc_payment_methods payment_methods methods">
                    <?php
                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                    if ( ! empty( $available_gateways ) ) :
                        foreach ( $available_gateways as $gateway ) :
                            ?>
                            <li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                    <input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->id, WC()->session->get( 'chosen_payment_method', $gateway->id ) ); ?> />
                                    <?php echo esc_html( $gateway->get_title() ); ?>
                                    <?php echo wp_kses_post( $gateway->get_icon() ); ?>
                                </label>
                                <?php if ( $gateway->has_fields() || $gateway->get_description() ) : ?>
                                    <div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                        <?php $gateway->payment_fields(); ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                            <?php
                        endforeach;
                    endif;
                    ?>
                    </ul>
                </div>
                <?php
                $order_button_name = ! empty( $widgetSettings['orderButtonName'] ) ? $widgetSettings['orderButtonName'] : __( 'Place Order', 'on-page-checkout' );
                echo '<button type="submit" class="opc-place-order-button woocommerce_checkout_place_order" id="place_order">' . esc_html( $order_button_name ) . '</button>';
                ?>
            </div>
        </div>
        <?php
        $checkout_fields = ob_get_clean();

        wp_send_json_success( [ 'fields' => $checkout_fields, 'cart' => WC()->cart ] );
    }

    public function add_to_cart() {
        check_ajax_referer( 'opc-nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        $quantity   = isset( $_POST['quantity'] ) ? absint( wp_unslash( $_POST['quantity'] ) ) : 1;

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Product ID is missing.', 'on-page-checkout' ) ] );
        }

        $result = WC()->cart->add_to_cart( $product_id, $quantity );

        if ( $result ) {
            wp_send_json_success( [ 'message' => esc_html__( 'Product added to cart.', 'on-page-checkout' ) ] );
        } else {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to add product to cart.', 'on-page-checkout' ) ] );
        }
    }
}

new W3_Opc_Ajax_Handler();