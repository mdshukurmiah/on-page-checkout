<?php
namespace OnPageCheckout\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class W3_Opc_On_Page_Checkout_Widget extends Widget_Base {

    public function get_name() {
        return 'on-page-checkout';
    }

    public function get_title() {
        return esc_html__( 'On Page Checkout', 'on-page-checkout' );
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
                'label' => esc_html__( 'Products', 'on-page-checkout' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'product_id',
            [
                'label' => esc_html__( 'Product ID or SKU', 'on-page-checkout' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Enter Product ID or SKU', 'on-page-checkout' ),
                'description' => esc_html__( 'Enter the ID or SKU of the WooCommerce product.', 'on-page-checkout' ),
                'label_block' => true,
            ]
        );

        $this->add_control(
            'product_list',
            [
                'label' => esc_html__( 'Product List', 'on-page-checkout' ),
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
                'label' => esc_html__( 'Settings', 'on-page-checkout' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_quantity',
            [
                'label' => esc_html__( 'Show Quantity Selector', 'on-page-checkout' ),
                'type' => Controls_Manager::SWITCHER,
                'label_on' => esc_html__( 'Show', 'on-page-checkout' ),
                'label_off' => esc_html__( 'Hide', 'on-page-checkout' ),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'preselect_product_param',
            [
                'label' => esc_html__( 'Preselect Product by Query String Parameter', 'on-page-checkout' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'e.g., product_id', 'on-page-checkout' ),
                'description' => esc_html__( 'Enter the query string parameter name (e.g., product_id) to preselect a product.', 'on-page-checkout' ),
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        // Add styling controls later
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
            echo '<div class="opc-no-products">' . esc_html__( 'No products selected for On Page Checkout.', 'on-page-checkout' ) . '</div>';
            return;
        }

        $preselected_product_id = 0;
        if ( ! empty( $settings['preselect_product_param'] ) && isset( $_GET[ $settings['preselect_product_param'] ] ) ) {
            $preselected_product_id = absint( $_GET[ $settings['preselect_product_param'] ] );
        }

        echo '<div class="on-page-checkout-widget" data-widget-id="' . $this->get_id() . '">';
        echo '<form class="opc-checkout-form checkout woocommerce-checkout" method="post">';


        // Product selection (radio buttons or hidden input if only one product)
        if ( count( $product_ids ) > 1 ) {
            echo '<div class="opc-product-selection">';
            echo '<h3>' . esc_html__( 'Select a Product', 'on-page-checkout' ) . '</h3>';
            echo '<div class="opc-products-wrapper">';
            $first_product = true;
            foreach ( $product_ids as $id_or_sku ) {
                $product = wc_get_product( $id_or_sku );
                if ( ! $product ) {
                    continue;
                }

                $product_id = $product->get_id();
                $is_variable = $product->is_type( 'variable' );
                $checked = '';

                if ( $preselected_product_id === $product_id ) {
                    $checked = 'checked';
                } elseif ( $first_product && empty( $preselected_product_id ) ) {
                    $checked = 'checked';
                    $first_product = false;
                }

                // Prepare available variations JSON if variable
                $data_attr = '';
                if ( $is_variable ) {
                    $available_variations = $product->get_available_variations(); // array
                    $json_variations = wp_json_encode( $available_variations );
                    $data_attr = ' data-available-variations=\'' . esc_attr( $json_variations ) . '\'';
                }

                // Product item wrapper — include product id and available variations
                echo '<div class="opc-product-item" data-product-id="' . esc_attr( $product_id ) . '"' . $data_attr . '>';
                    echo '<label>';
                        echo '<input type="radio" name="opc_product_id" value="' . esc_attr( $product_id ) . '" ' . $checked . '>';
                        echo '<div class="product-card">';
                            echo $product->get_image( 'woocommerce_thumbnail' );
                            echo '<div class="product-information">';
                                echo '<div class="product-title">' . esc_html( $product->get_name() ) . '</div>';
                                echo '<div class="product-price">' . wp_kses_post( $product->get_price_html() ) . '</div>';
                                 // Variation selects container — render selects for the product's attributes (hidden by default)
                                if ( $is_variable ) {
                                    $available_variations = $product->get_available_variations(); // array
                                    if ( ! empty( $available_variations ) ) {

                                        // Build options array: variation_id => label
                                        $options = [];
                                        foreach ( $available_variations as $variation ) {
                                            $variation_id = isset( $variation['variation_id'] ) ? $variation['variation_id'] : 0;
                                            $attrs = isset( $variation['attributes'] ) ? (array) $variation['attributes'] : [];

                                            // Build label from attributes: normalize values to readable text
                                            $parts = [];
                                            foreach ( $attrs as $attr_key => $attr_value ) {
                                                if ( empty( $attr_value ) ) continue;

                                                // $attr_key example: 'attribute_pa_grade' or 'attribute_grade'
                                                // Convert attribute key label:
                                                $label = wc_attribute_label( str_replace( 'attribute_', '', $attr_key ) );

                                                // Attribute value can be slug. Try to get human readable term if possible
                                                $readable = $attr_value;

                                                // If it's a pa_ taxonomy, get term name if exists
                                                if ( strpos( $attr_key, 'attribute_pa_' ) === 0 ) {
                                                    $term = get_term_by( 'slug', $attr_value, str_replace( 'attribute_', '', $attr_key ) );
                                                    if ( $term && ! is_wp_error( $term ) ) {
                                                        $readable = $term->name;
                                                    } else {
                                                        // sometimes value already human readable
                                                        $readable = ucfirst( str_replace( '-', ' ', $attr_value ) );
                                                    }
                                                } else {
                                                    // not pa_ taxonomy — likely direct option label
                                                    $readable = $attr_value;
                                                }

                                                $parts[] = $readable;
                                            }

                                            // Example label: "Grade A - 500 GM" or "A / 500GM" — choose format
                                            $label = implode( ' - ', $parts );

                                            if ( $variation_id ) {
                                                $options[ $variation_id ] = $label;
                                            }
                                        }

                                        if ( ! empty( $options ) ) {
                                            echo '<div class="opc-combined-variants">';
                                            echo '<select name="variation_id" class="opc-combined-variant-select" data-product-id="' . esc_attr( $product_id ) . '">';

                                            $i = 0;
                                            foreach ( $options as $vid => $lab ) {
                                                $selected = ( $i === 0 ) ? ' selected' : '';
                                                echo '<option value="' . esc_attr( $vid ) . '"' . $selected . '>' . esc_html( $lab ) . '</option>';
                                                $i++;
                                            }

                                            echo '</select>';

                                            // embed full available_variations JSON for JS matching if needed
                                            echo '<input type="hidden" class="opc-avail-variations" value=\'' . esc_attr( wp_json_encode( $available_variations ) ) . '\' />';
                                            echo '</div>';
                                        }
                                    }
                                }
                            echo '</div>';
                        echo '</div>';
                    echo '</label>';
                echo '</div>'; // .opc-product-item
            }
            echo '</div></div>';

        } else {
            // If only one product, pre-select it and hide the radio button
            $product = wc_get_product( $product_ids[0] );
            if ( $product ) {
                echo '<input type="hidden" name="opc_product_id" value="' . esc_attr( $product->get_id() ) . '">';
                    echo '<div class="opc-single-product-info">';
                    echo '<div class="opc-product-item"><label>';
                    echo '<input type="radio" name="opc_product_id" value="' . esc_attr( $product->get_id() ) . '" ' . $checked . '>';
                    echo '<div class="product-card">';
                    echo $product->get_image( 'woocommerce_thumbnail' ); 
                    echo '<div class="product-information">';
                    echo '<div class="product-title">'. esc_html( $product->get_name() ) .'</div>';
                    echo '<div class="product-price">'. wp_kses_post( $product->get_price_html()) .'</div>';
                    echo '</div>';
                    echo '</div></label></div>';
                echo '</div>';
            }
        }

        // Quantity selector
        if ( 'yes' === $settings['show_quantity'] ) {
            echo '<div class="opc-quantity-selector">';
            echo '<label for="opc_quantity">' . esc_html__( 'Quantity:', 'on-page-checkout' ) . '</label>';
            echo '<input type="number" id="opc_quantity" name="opc_quantity" value="1" min="1">';
            echo '</div>';
        } else {
            echo '<input type="hidden" name="opc_quantity" value="1">';
        }

        // Checkout form container
        echo '<div class="opc-checkout-form-fields">';
        echo '<h3>' . esc_html__( 'Your Details', 'on-page-checkout' ) . '</h3>';
        echo '<div id="opc-checkout-fields-container-' . $this->get_id() . '" class="opc-checkout-fields-container">' . esc_html__( 'Loading checkout fields...', 'on-page-checkout' ) . '</div>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
}

