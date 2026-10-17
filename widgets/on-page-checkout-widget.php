<?php
namespace W3OPCFE\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3OPCFE_On_Page_Checkout_Widget extends Widget_Base {

    public function get_name() {
        return 'w3opcfe-on-page-checkout';
    }

    public function get_title() {
        return esc_html__( 'On Page Checkout', 'on-page-checkout-for-elementor' );
    }

    public function get_icon() {
        return 'eicon-checkout';
    }

    public function get_categories() {
        return [ 'general' ];
    }

    public function get_keywords() {
        return [ 'woocommerce', 'checkout', 'product', 'on page' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Products', 'on-page-checkout-for-elementor' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'product_id',
            [
                'label' => esc_html__( 'Product ID or SKU', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter Product ID or SKU', 'on-page-checkout-for-elementor' ),
                'description' => esc_html__( 'Enter the ID or SKU of the WooCommerce product.', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'product_list',
            [
                'label' => esc_html__( 'Product List', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'product_id' => '',
                    ],
                ],
                'title_field' => '{{{ product_id }}}',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_settings',
            [
                'label' => esc_html__( 'Settings', 'on-page-checkout-for-elementor' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_selection_heading',
            [
                'label' => esc_html__( 'Product selection heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Your Details', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Select a Product'
            ]
        );

        $this->add_control(
            'show_quantity',
            [
                'label' => esc_html__( 'Show Quantity Selector', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'on-page-checkout-for-elementor' ),
                'label_off' => esc_html__( 'Hide', 'on-page-checkout-for-elementor' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'preselect_product_param',
            [
                'label' => esc_html__( 'Preselect Product by Query String Parameter', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'e.g., product_id', 'on-page-checkout-for-elementor' ),
                'description' => esc_html__( 'Enter the query string parameter name (e.g., product_id) to preselect a product.', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'form_heading',
            [
                'label' => esc_html__( 'Checkout form heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Your Details', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Your Details'
            ]
        );

        $this->add_control(
            'billing_details_heading',
            [
                'label' => esc_html__( 'Billing details heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Billing Details', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Billing Details'
            ]
        );

        $this->add_control(
            'shipping_details_heading',
            [
                'label' => esc_html__( 'Shipping details heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Shipping Details', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Shipping Details'
            ]
        );

        $this->add_control(
            'additional_info_heading',
            [
                'label' => esc_html__( 'Additional info heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Additional info', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Additional Information'
            ]
        );

        $this->add_control(
            'order_summary_heading',
            [
                'label' => esc_html__( 'Order summary heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Your Order', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Your Order'
            ]
        );

        $this->add_control(
            'payment_method_heading',
            [
                'label' => esc_html__( 'Payment method heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Payment Method', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Payment Method'
            ]
        );
        $this->add_control(
            'order_button_name',
            [
                'label' => esc_html__( 'Order button name', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Place Order', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Place Order'
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Resolve product ID or SKU to a WC_Product object
     *
     * @param string|int $id_or_sku
     * @return \WC_Product|false
     */
    private function get_product_by_id_or_sku( $id_or_sku ) {
        if ( empty( $id_or_sku ) ) {
            return false;
        }

        $id_or_sku = trim( (string) $id_or_sku );
        $product_id = is_numeric( $id_or_sku ) ? absint( $id_or_sku ) : ( function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( $id_or_sku ) : 0 );
        if ( ! $product_id ) {
            return false;
        }

        $product = wc_get_product( $product_id );
        return ( $product instanceof \WC_Product ) ? $product : false;
    }

    /**
     * Render variation dropdown for variable products
     *
     * @param \WC_Product $product
     */
    private function render_variation_select( $product ) {
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }

        $product_id = $product->get_id();
        $available_variations = $product->get_available_variations();
        if ( empty( $available_variations ) ) {
            return;
        }

        $options = [];
        foreach ( $available_variations as $variation ) {
            $variation_id = isset( $variation['variation_id'] ) ? $variation['variation_id'] : 0;
            $attrs = isset( $variation['attributes'] ) ? (array) $variation['attributes'] : [];

            $parts = [];
            foreach ( $attrs as $attr_key => $attr_value ) {
                if ( '' === $attr_value || null === $attr_value ) {
                    continue;
                }

                $readable = $attr_value;
                if ( strpos( $attr_key, 'attribute_pa_' ) === 0 ) {
                    $taxonomy = str_replace( 'attribute_', '', $attr_key );
                    $term     = get_term_by( 'slug', $attr_value, $taxonomy );
                    if ( $term && ! is_wp_error( $term ) ) {
                        $readable = $term->name;
                    } else {
                        $readable = ucfirst( str_replace( '-', ' ', $attr_value ) );
                    }
                }

                $parts[] = $readable;
            }

            $label = ! empty( $parts ) ? implode( ' - ', $parts ) : '#' . $variation_id;
            if ( $variation_id ) {
                $options[ $variation_id ] = $label;
            }
        }

        if ( empty( $options ) ) {
            return;
        }

        echo '<div class="opc-combined-variants">';
        echo '<select name="variation_id" class="opc-combined-variant-select" data-product-id="' . esc_attr( $product_id ) . '">';
        $i = 0;
        foreach ( $options as $vid => $lab ) {
            echo '<option value="' . esc_attr( $vid ) . '"' . selected( $i, 0, false ) . '>' . esc_html( $lab ) . '</option>';
            $i++;
        }
        echo '</select>';
        echo '<input type="hidden" class="opc-avail-variations" value=\'' . esc_attr( wp_json_encode( $available_variations ) ) . '\' />';
        echo '</div>';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $product_ids = [];
        foreach ( $settings['product_list'] as $item ) {
            if ( ! empty( $item['product_id'] ) ) {
                $product_ids[] = $item['product_id'];
            }
        }

        if ( empty( $product_ids ) ) {
            echo '<div class="opc-no-products">' . esc_html__( 'No products selected for On Page Checkout.', 'on-page-checkout-for-elementor' ) . '</div>';
            return;
        }

        $preselected_product_id = 0;
        $preselect_param = ! empty( $settings['preselect_product_param'] ) ? sanitize_key( $settings['preselect_product_param'] ) : '';
        if ( '' !== $preselect_param ) {
            $preselected_value = filter_input( INPUT_GET, $preselect_param, FILTER_SANITIZE_NUMBER_INT );
            if ( null !== $preselected_value && false !== $preselected_value ) {
                $preselected_product_id = absint( $preselected_value );
            }
        }

        echo '<div class="on-page-checkout-widget" data-widget-id="' . esc_attr( $this->get_id() ) . '" data-shipping-title="' . esc_attr( $settings['shipping_details_heading'] ) . '" data-billing-title="' . esc_attr( $settings['billing_details_heading'] ) . '" data-order-summery-title="' . esc_attr( $settings['order_summary_heading'] ) . '" data-payment-method-title="' . esc_attr( $settings['payment_method_heading'] ) . '" data-additional-info-title="' . esc_attr( $settings['additional_info_heading'] ) . '" data-order-button-name="' . esc_attr( $settings['order_button_name'] ) . '">';
        echo '<form class="opc-checkout-form checkout woocommerce-checkout" method="post">';
        echo '<div class="opc-checkout-notices" style="display:none;"></div>';

        // Product selection (radio buttons or hidden input if only one product)
        if ( count( $product_ids ) > 1 ) {
            echo '<div class="opc-product-selection">';
            if ( ! empty( $settings['product_selection_heading'] ) ) {
                echo '<h3>' . esc_html( $settings['product_selection_heading'] ) . '</h3>';
            }
            echo '<div class="opc-products-wrapper">';
            $first_product = true;
            foreach ( $product_ids as $id_or_sku ) {
                $product = $this->get_product_by_id_or_sku( $id_or_sku );
                if ( ! $product ) {
                    continue;
                }

                $product_id = $product->get_id();
                $is_variable = $product->is_type( 'variable' );
                $checked = false;

                if ( $preselected_product_id === $product_id ) {
                    $checked = true;
                } elseif ( $first_product && empty( $preselected_product_id ) ) {
                    $checked = true;
                    $first_product = false;
                }

                // Prepare available variations JSON if variable
                $data_attr = '';
                if ( $is_variable ) {
                    $available_variations = $product->get_available_variations();
                    $data_attr = ' data-available-variations=\'' . esc_attr( wp_json_encode( $available_variations ) ) . '\'';
                }

                // Product item wrapper — include product id and available variations
                echo '<div class="opc-product-item" data-product-id="' . esc_attr( $product_id ) . '"' . wp_kses_post( $data_attr ) . '>';
                    echo '<label>';
                        echo '<input type="radio" name="w3opcfe_product_id" value="' . esc_attr( $product_id ) . '" ' . checked( $checked, true, false ) . '>';
                        echo '<div class="product-card">';
                            echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) );
                            echo '<div class="product-information">';
                                echo '<div class="product-title">' . esc_html( $product->get_name() ) . '</div>';
                                echo '<div class="product-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
                                $this->render_variation_select( $product );
                            echo '</div>';
                        echo '</div>';
                    echo '</label>';
                echo '</div>'; // .opc-product-item
            }
            echo '</div></div>';

        } else {
            // If only one product, pre-select it and hide the radio button
            $product = $this->get_product_by_id_or_sku( $product_ids[0] );
            if ( $product ) {
                $product_id = $product->get_id();
                $is_variable = $product->is_type( 'variable' );
                
                // Prepare available variations JSON if variable
                $data_attr = '';
                if ( $is_variable ) {
                    $available_variations = $product->get_available_variations();
                    $data_attr = ' data-available-variations=\'' . esc_attr( wp_json_encode( $available_variations ) ) . '\'';
                }

                echo '<div class="opc-products-wrapper">';
                    echo '<div class="opc-product-item" data-product-id="' . esc_attr( $product_id ) . '"' . wp_kses_post( $data_attr ) . '>';
                        echo '<label>';
                            echo '<input type="radio" name="w3opcfe_product_id" value="' . esc_attr( $product->get_id() ) . '" checked>';
                                echo '<div class="product-card">';
                                echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail' ) ); 
                                echo '<div class="product-information">';
                                echo '<div class="product-title">'. esc_html( $product->get_name() ) .'</div>';
                                echo '<div class="product-price">'. wp_kses_post( $product->get_price_html() ) .'</div>';
                                $this->render_variation_select( $product );
                                echo '</div>';
                            echo '</div>';
                        echo '</label>';   
                    echo '</div>';    
                echo '</div>';       
            }
        }

        // Quantity selector
        if ( 'yes' === $settings['show_quantity'] ) {
            echo '<div class="opc-quantity-selector">';
            echo '<label for="w3opcfe_quantity">' . esc_html__( 'Select Quantity:', 'on-page-checkout-for-elementor' ) . '</label>';
            echo '<div class="quantity-input-wrapper">';
                echo '<button type="button" class="qty-decrement" aria-label="' . esc_attr__( 'Decrease quantity', 'on-page-checkout-for-elementor' ) . '">-</button>';
                echo '<input type="text" id="w3opcfe_quantity" name="w3opcfe_quantity" value="1" min="1">';
                echo '<button type="button" class="qty-increment" aria-label="' . esc_attr__( 'Increase quantity', 'on-page-checkout-for-elementor' ) . '">+</button>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<input type="hidden" name="w3opcfe_quantity" value="1">';
        }

        // Checkout form container
        $form_heading = $settings['form_heading'];
        echo '<div class="opc-checkout-form-fields">';
        if ( ! empty( $form_heading ) ) {
            echo '<h3>' . esc_html( $form_heading ) . '</h3>';
        }
        echo '<div id="opc-checkout-fields-container-' . esc_attr( $this->get_id() ) . '" class="opc-checkout-fields-container">' . esc_html__( 'Loading checkout fields...', 'on-page-checkout-for-elementor' ) . '</div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
} 
