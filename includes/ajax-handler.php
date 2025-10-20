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

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid product selected.', 'on-page-checkout' ) ] );
        }

        // Try to add product (handle variable)
        $added_to_cart = false;
        $added_item_key = false;

        if ( $product->is_type( 'variable' ) ) {
            $variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;

            // Collect attribute_* fields from POST
            $variation = [];
            foreach ( $_POST as $k => $v ) {
                if ( strpos( $k, 'attribute_' ) === 0 ) {
                    $variation[ $k ] = sanitize_text_field( $v );
                }
            }

            // Try server-side matching if no variation_id but attributes present
            if ( ! $variation_id && ! empty( $variation ) ) {
                $data_store = WC_Data_Store::load( 'product' );
                $matching = $data_store->find_matching_product_variation( $product, $variation );
                if ( $matching ) {
                    $variation_id = $matching;
                }
            }

            if ( $variation_id ) {
                // Optionally empty cart here if you want single-product behaviour:
                WC()->cart->empty_cart();

                $added_item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation );
                if ( $added_item_key ) {
                    $added_to_cart = true;
                }
            } else {
                wp_send_json_error( [ 'message' => sprintf( __( $product->get_type() . 'Please choose product options by visiting <a href="%1$s">%2$s</a>.', 'on-page-checkout' ), get_permalink( $product_id ), $product->get_name() ) ] );
            }
        } else {
            // simple product
            WC()->cart->empty_cart();
            $added_item_key = WC()->cart->add_to_cart( $product_id, $quantity );
            $added_to_cart = (bool) $added_item_key;
        }

        if ( ! $added_to_cart ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Failed to add product to cart. Please verify product options.', 'on-page-checkout' ) ] );
        }


        $customer = WC()->customer;

        // Set billing address
        $customer->set_billing_first_name( $_POST['billing_first_name'] ?? '' );
        $customer->set_billing_last_name( $_POST['billing_last_name'] ?? '' );
        $customer->set_billing_company( $_POST['billing_company'] ?? '' );
        $customer->set_billing_email( $_POST['billing_email'] ?? '' );
        $customer->set_billing_phone( $_POST['billing_phone'] ?? '' );
        $customer->set_billing_address_1( $_POST['billing_address_1'] ?? '' );
        $customer->set_billing_address_2( $_POST['billing_address_2'] ?? '' );
        $customer->set_billing_city( $_POST['billing_city'] ?? '' );
        $customer->set_billing_state( $_POST['billing_state'] ?? '' );
        $customer->set_billing_postcode( $_POST['billing_postcode'] ?? '' );
        $customer->set_billing_country( !empty($_POST['billing_country']) ? $_POST['billing_country'] : 'BD');

        // Set shipping address
        if ( isset($_POST['ship_to_different_address']) && $_POST['ship_to_different_address'] == 1 ) {
            $customer->set_shipping_first_name( $_POST['shipping_first_name'] ?? '' );
            $customer->set_shipping_last_name( $_POST['shipping_last_name'] ?? '' );
            $customer->set_shipping_company( $_POST['shipping_company'] ?? '' );
            $customer->set_shipping_address_1( $_POST['shipping_address_1'] ?? '' );
            $customer->set_shipping_address_2( $_POST['shipping_address_2'] ?? '' );
            $customer->set_shipping_city( $_POST['shipping_city'] ?? '' );
            $customer->set_shipping_state( $_POST['shipping_state'] ?? '' );
            $customer->set_shipping_postcode( $_POST['shipping_postcode'] ?? '' );
            $customer->set_shipping_country( !empty($_POST['shipping_country']) ? $_POST['shipping_country'] : 'BD' );
        } else {
            // Copy billing fields to shipping
            $customer->set_shipping_first_name( $_POST['billing_first_name'] ?? '' );
            $customer->set_shipping_last_name( $_POST['billing_last_name'] ?? '' );
            $customer->set_shipping_company( $_POST['billing_company'] ?? '' );
            $customer->set_shipping_address_1( $_POST['billing_address_1'] ?? '' );
            $customer->set_shipping_address_2( $_POST['billing_address_2'] ?? '' );
            $customer->set_shipping_city( $_POST['billing_city'] ?? '' );
            $customer->set_shipping_state( $_POST['billing_state'] ?? '' );
            $customer->set_shipping_postcode( $_POST['billing_postcode'] ?? '' );
            $customer->set_shipping_country( !empty($_POST['billing_country']) ? $_POST['billing_country'] : 'BD' );
        }

        $customer->save();

        if ( ! empty( $_POST['shipping_method'] ) && is_array( $_POST['shipping_method'] ) ) {
            $chosen_methods = array_map( 'sanitize_text_field', $_POST['shipping_method'] );
            WC()->session->set( 'chosen_shipping_methods', $chosen_methods );
        }

        // Persist session & recalc totals (important)
        WC()->session->set_customer_session_cookie( true );
        WC()->session->save_data();
        WC()->cart->calculate_totals();
        WC()->cart->calculate_shipping();

        // Build checkout fields as before
        $checkout = WC()->checkout();
        $fields = $checkout->get_checkout_fields();

        ob_start();
        ?>

        <div class="opc-checkout-fields">
            <input type="hidden" name="woocommerce-process-checkout-nonce" value="<?php echo wp_create_nonce( 'woocommerce-process_checkout' ); ?>">
            <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( wc_get_checkout_url() ); ?>">

            <?php if ( ! empty( $fields['billing'] ) ) : ?>
                <div class="opc-billing-fields">
                    <h4><?php esc_html_e( 'Billing Details', 'on-page-checkout' ); ?></h4>
                    <?php foreach ( $fields['billing'] as $key => $field ) : ?>
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $fields['shipping'] ) && WC()->cart->needs_shipping() ) : ?>
                <div class="opc-shipping-fields">
                    <h4><?php esc_html_e( 'Shipping Details', 'on-page-checkout' ); ?></h4>
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
                    <h4><?php esc_html_e( 'Additional Information', 'on-page-checkout' ); ?></h4>
                    <?php foreach ( $fields['order'] as $key => $field ) : ?>
                        <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="order-details" id="order_review" class="woocommerce-checkout-review-order">
            <div class="order-details-wrapper">
                <div class="opc-order-review">
                    <h4><?php esc_html_e( 'Your Order', 'on-page-checkout' ); ?></h4>
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
                                            <?php echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
                                            <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                                        </td>
                                        <td class="product-total">
                                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
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
                    <h4><?php esc_html_e( 'Payment Method', 'on-page-checkout' ); ?></h4>
                    <ul class="wc_payment_methods payment_methods methods">
                    <?php 
                    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
                    if ( ! empty( $available_gateways ) ) :
                        foreach ( $available_gateways as $gateway ) :
                            ?>
                            
                            <li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
                                    <input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->id, WC()->session->get('chosen_payment_method', $gateway->id) ); ?> />
                                        <?php echo $gateway->get_title(); ?>
                                        <?php echo $gateway->get_icon(); ?>
                                </label>
                                <?php if ( $gateway->has_fields() || $gateway->get_description() ) : 
                                    ?>
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
                    echo '<button type="submit" class="opc-place-order-button woocommerce_checkout_place_order" id="place_order">' . esc_html__( 'Place Order', 'on-page-checkout' ) . '</button>';
                ?>
            </div>
        </div>
        <?php
        $checkout_fields = ob_get_clean();

        wp_send_json_success( [ 'fields' => $checkout_fields, 'cart' => WC()->cart ] );
    }

    public function add_to_cart() {
        check_ajax_referer( 'opc-nonce', 'nonce' );

        $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $quantity   = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

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

