<?php
namespace W3OPCFE\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

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
        return [ 'on-page-checkout', 'general' ];
    }

    public function get_keywords() {
        return [ 'woocommerce', 'checkout', 'product', 'on page' ];
    }

    public function get_script_depends() {
        return [ 'w3opcfe-frontend-script' ];
    }

    public function get_style_depends() {
        return [ 'w3opcfe-frontend-style' ];
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Products', 'on-page-checkout-for-elementor' ),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'product_selection_source',
            [
                'label'   => esc_html__( 'Selection Mode', 'on-page-checkout-for-elementor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'multi_select',
                'options' => [
                    'multi_select' => esc_html__( 'Searchable Multi-Select', 'on-page-checkout-for-elementor' ),
                    'repeater'     => esc_html__( 'Product List (Custom Order Repeater)', 'on-page-checkout-for-elementor' ),
                ],
            ]
        );

        $this->add_control(
            'selected_products',
            [
                'label'       => esc_html__( 'Select Products', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_product_options(),
                'multiple'    => true,
                'label_block' => true,
                'description' => esc_html__( 'Search and select products by title, SKU, or ID.', 'on-page-checkout-for-elementor' ),
                'condition'   => [
                    'product_selection_source' => 'multi_select',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'product_id',
            [
                'label'       => esc_html__( 'Select Product', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_product_options(),
                'default'     => '',
                'label_block' => true,
                'multiple'    => false,
                'description' => esc_html__( 'Search and select a WooCommerce product.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $repeater->add_control(
            'custom_badge_text',
            [
                'label'       => esc_html__( 'Custom Badge Text', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'e.g. Best Value, Popular, 20% Off', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'custom_badge_color',
            [
                'label'     => esc_html__( 'Badge Text Color (Optional)', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'custom_badge_text!' => '',
                ],
            ]
        );

        $repeater->add_control(
            'custom_badge_bg_color',
            [
                'label'     => esc_html__( 'Badge Background Color (Optional)', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'condition' => [
                    'custom_badge_text!' => '',
                ],
            ]
        );

        $this->add_control(
            'product_list',
            [
                'label'       => esc_html__( 'Product List', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'product_id' => '',
                    ],
                ],
                'title_field' => '{{{ product_id }}} <# if (custom_badge_text) { #> [{{{ custom_badge_text }}}] <# } #>',
                'condition'   => [
                    'product_selection_source' => 'repeater',
                ],
            ]
        );

        $badge_repeater = new Repeater();

        $badge_repeater->add_control(
            'product_id',
            [
                'label'       => esc_html__( 'Select Product', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::SELECT2,
                'options'     => $this->get_product_options(),
                'default'     => '',
                'label_block' => true,
                'multiple'    => false,
                'description' => esc_html__( 'Choose product to assign custom badge.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $badge_repeater->add_control(
            'badge_text',
            [
                'label'       => esc_html__( 'Badge Text', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'e.g. Best Value, Popular, 20% Off', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
            ]
        );

        $badge_repeater->add_control(
            'badge_color',
            [
                'label'     => esc_html__( 'Badge Text Color (Optional)', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
            ]
        );

        $badge_repeater->add_control(
            'badge_bg_color',
            [
                'label'     => esc_html__( 'Badge Background Color (Optional)', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
            ]
        );

        $this->add_control(
            'product_custom_badges',
            [
                'label'       => esc_html__( 'Product Custom Badges', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $badge_repeater->get_controls(),
                'title_field' => '{{{ badge_text }}}',
                'condition'   => [
                    'product_selection_source' => 'multi_select',
                ],
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
            'show_variations_as_cards',
            [
                'label'        => esc_html__( 'Show Variations as Separate Cards', 'on-page-checkout-for-elementor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
                'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => esc_html__( 'Display each variation as an individual product card instead of a dropdown inside the card.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'enable_multi_product_selection',
            [
                'label'        => esc_html__( 'Enable Multi Product Selection', 'on-page-checkout-for-elementor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
                'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
                'return_value' => 'yes',
                'default'      => 'no',
                'description'  => esc_html__( 'Allow customers to select multiple products or variations at once.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'out_of_stock_badge_text',
            [
                'label'       => esc_html__( 'Out of Stock Badge Text', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'default'     => esc_html__( 'Out of Stock', 'on-page-checkout-for-elementor' ),
                'placeholder' => esc_html__( 'Out of Stock', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
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
            'mobile_order_summary_label',
            [
                'label'       => esc_html__( 'Mobile Summary Button Label', 'on-page-checkout-for-elementor' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Order summary', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default'     => 'Order summary',
                'description' => esc_html__( 'Button text displayed on the mobile collapsible summary bar.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'mobile_order_summary_default_collapsed',
            [
                'label'        => esc_html__( 'Hide Order Summary by Default on Mobile', 'on-page-checkout-for-elementor' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
                'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => esc_html__( 'Keep the review table collapsed initially on mobile until the user clicks the button.', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'shipping_method_heading',
            [
                'label' => esc_html__( 'Shipping method heading', 'on-page-checkout-for-elementor' ),
                'type' => Controls_Manager::TEXT,
                'placeholder' => esc_html__( 'Shipping Method', 'on-page-checkout-for-elementor' ),
                'label_block' => true,
                'default' => 'Shipping Method'
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

        /* ========================================================
         * STYLE TAB - PRODUCT CARDS
         * ======================================================== */
        $this->start_controls_section(
            'section_style_products',
            [
                'label' => esc_html__( 'Product Cards', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'product_cards_columns',
            [
                'label'          => esc_html__( 'Columns', 'on-page-checkout-for-elementor' ),
                'type'           => Controls_Manager::SELECT,
                'default'        => '2',
                'tablet_default' => '2',
                'mobile_default' => '1',
                'options'        => [
                    '1' => esc_html__( '1 Column', 'on-page-checkout-for-elementor' ),
                    '2' => esc_html__( '2 Columns', 'on-page-checkout-for-elementor' ),
                    '3' => esc_html__( '3 Columns', 'on-page-checkout-for-elementor' ),
                    '4' => esc_html__( '4 Columns', 'on-page-checkout-for-elementor' ),
                    '5' => esc_html__( '5 Columns', 'on-page-checkout-for-elementor' ),
                    '6' => esc_html__( '6 Columns', 'on-page-checkout-for-elementor' ),
                ],
                'selectors'      => [
                    '{{WRAPPER}} .opc-products-wrapper' => 'display: grid; grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
                    '{{WRAPPER}} .opc-products-wrapper .opc-product-item' => 'flex: none; width: 100%; max-width: 100%; min-width: 0;',
                ],
            ]
        );

        $this->add_responsive_control(
            'products_wrapper_gap',
            [
                'label'      => esc_html__( 'Gap Between Cards', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_card_padding',
            [
                'label'      => esc_html__( 'Card Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .opc-product-item > label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'product_card_bg_color',
            [
                'label'     => esc_html__( 'Card Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper .opc-product-item > label' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'product_card_active_bg_color',
            [
                'label'     => esc_html__( 'Active Card Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper label:has(input:checked)' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'product_card_border',
                'label'    => esc_html__( 'Card Border', 'on-page-checkout-for-elementor' ),
                'selector' => '{{WRAPPER}} .opc-products-wrapper .opc-product-item > label',
            ]
        );

        $this->add_responsive_control(
            'product_card_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .opc-product-item > label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'product_card_active_border_color',
            [
                'label'     => esc_html__( 'Active Outline/Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper label:has(input:checked)' => 'outline-color: {{VALUE}}; border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'product_card_box_shadow',
                'selector' => '{{WRAPPER}} .opc-products-wrapper .opc-product-item > label',
            ]
        );

        // Product Image Styles
        $this->add_control(
            'heading_product_image_style',
            [
                'label'     => esc_html__( 'Product Image', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'product_image_width',
            [
                'label'      => esc_html__( 'Image Width', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 40, 'max' => 200 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .product-card img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_image_height',
            [
                'label'      => esc_html__( 'Image Height', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'rem' ],
                'range'      => [
                    'px' => [ 'min' => 40, 'max' => 200 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .product-card img' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_image_border_radius',
            [
                'label'      => esc_html__( 'Image Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .product-card img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Product Title Styles
        $this->add_control(
            'heading_product_title_style',
            [
                'label'     => esc_html__( 'Product Title', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'product_title_color',
            [
                'label'     => esc_html__( 'Title Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper .product-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'product_title_typography',
                'selector' => '{{WRAPPER}} .opc-products-wrapper .product-title',
            ]
        );

        $this->add_responsive_control(
            'product_title_margin_bottom',
            [
                'label'      => esc_html__( 'Bottom Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 40 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-products-wrapper .product-title' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Product Price Styles
        $this->add_control(
            'heading_product_price_style',
            [
                'label'     => esc_html__( 'Product Price', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'product_price_color',
            [
                'label'     => esc_html__( 'Price Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper .product-price, {{WRAPPER}} .opc-products-wrapper .product-price ins, {{WRAPPER}} .opc-products-wrapper .product-price .amount' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'product_price_typography',
                'selector' => '{{WRAPPER}} .opc-products-wrapper .product-price, {{WRAPPER}} .opc-products-wrapper .product-price ins, {{WRAPPER}} .opc-products-wrapper .product-price .amount',
            ]
        );

        // Product Original Price Styles
        $this->add_control(
            'heading_product_original_price_style',
            [
                'label'     => esc_html__( 'Original Price', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'product_original_price_color',
            [
                'label'     => esc_html__( 'Original Price Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-products-wrapper .product-price del, {{WRAPPER}} .opc-products-wrapper .product-price del .amount, {{WRAPPER}} .opc-products-wrapper .product-price del span' => 'color: {{VALUE}}; opacity: 1;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'product_original_price_typography',
                'selector' => '{{WRAPPER}} .opc-products-wrapper .product-price del, {{WRAPPER}} .opc-products-wrapper .product-price del .amount, {{WRAPPER}} .opc-products-wrapper .product-price del span, {{WRAPPER}} .opc-products-wrapper .product-price del bdi',
            ]
        );

        // Out of Stock Badge Styles
        $this->add_control(
            'heading_stock_badge_style',
            [
                'label'     => esc_html__( 'Out of Stock Badge', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'stock_badge_color',
            [
                'label'     => esc_html__( 'Badge Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-stock-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'stock_badge_bg_color',
            [
                'label'     => esc_html__( 'Badge Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-stock-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'stock_badge_typography',
                'selector' => '{{WRAPPER}} .opc-stock-badge',
            ]
        );

        $this->add_responsive_control(
            'stock_badge_padding',
            [
                'label'      => esc_html__( 'Badge Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-stock-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'stock_badge_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-stock-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_custom_badge_style',
            [
                'label'     => esc_html__( 'Custom Badge', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'custom_badge_position',
            [
                'label'   => esc_html__( 'Badge Position', 'on-page-checkout-for-elementor' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'top-right',
                'options' => [
                    'top-right' => esc_html__( 'Top Right', 'on-page-checkout-for-elementor' ),
                    'top-left'  => esc_html__( 'Top Left', 'on-page-checkout-for-elementor' ),
                ],
            ]
        );

        $this->add_control(
            'custom_badge_color',
            [
                'label'     => esc_html__( 'Badge Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .opc-custom-badge' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'custom_badge_bg_color',
            [
                'label'     => esc_html__( 'Badge Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'default'   => '#016520',
                'selectors' => [
                    '{{WRAPPER}} .opc-custom-badge' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'custom_badge_typography',
                'selector' => '{{WRAPPER}} .opc-custom-badge',
            ]
        );

        $this->add_responsive_control(
            'custom_badge_padding',
            [
                'label'      => esc_html__( 'Badge Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-custom-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'custom_badge_border',
                'selector' => '{{WRAPPER}} .opc-custom-badge',
            ]
        );

        $this->add_responsive_control(
            'custom_badge_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-custom-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'custom_badge_box_shadow',
                'selector' => '{{WRAPPER}} .opc-custom-badge',
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - VARIANT SELECTION
         * ======================================================== */
        $this->start_controls_section(
            'section_style_variants',
            [
                'label' => esc_html__( 'Variant Selection', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'variant_select_typography',
                'selector' => '{{WRAPPER}} select.opc-combined-variant-select',
            ]
        );

        $this->add_control(
            'variant_select_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.opc-combined-variant-select' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'variant_select_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} select.opc-combined-variant-select' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'variant_select_border',
                'selector' => '{{WRAPPER}} select.opc-combined-variant-select',
            ]
        );

        $this->add_responsive_control(
            'variant_select_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} select.opc-combined-variant-select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'variant_select_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} select.opc-combined-variant-select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'variant_select_margin_top',
            [
                'label'      => esc_html__( 'Top Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 30 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-combined-variants' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - QUANTITY SELECTOR
         * ======================================================== */
        $this->start_controls_section(
            'section_style_quantity',
            [
                'label' => esc_html__( 'Quantity Selector', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'heading_quantity_label',
            [
                'label' => esc_html__( 'Label', 'on-page-checkout-for-elementor' ),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'quantity_label_color',
            [
                'label'     => esc_html__( 'Label Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-quantity-selector label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'quantity_label_typography',
                'selector' => '{{WRAPPER}} .opc-quantity-selector label',
            ]
        );

        $this->add_responsive_control(
            'quantity_container_padding',
            [
                'label'      => esc_html__( 'Container Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-quantity-selector, {{WRAPPER}} .opc-card-quantity' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_quantity_box',
            [
                'label'     => esc_html__( 'Quantity Box & Buttons', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'quantity_box_bg',
            [
                'label'     => esc_html__( 'Box Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'quantity_box_border',
                'selector' => '{{WRAPPER}} .quantity-input-wrapper',
            ]
        );

        $this->add_responsive_control(
            'quantity_box_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .quantity-input-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
                ],
            ]
        );

        $this->add_responsive_control(
            'quantity_box_padding',
            [
                'label'      => esc_html__( 'Box Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .quantity-input-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_btn_color',
            [
                'label'     => esc_html__( 'Button Icon/Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper button' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_btn_bg_color',
            [
                'label'     => esc_html__( 'Button Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper button' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_btn_hover_color',
            [
                'label'     => esc_html__( 'Button Hover Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper button:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_btn_hover_bg_color',
            [
                'label'     => esc_html__( 'Button Hover Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper button:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'quantity_btn_padding',
            [
                'label'      => esc_html__( 'Button Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .quantity-input-wrapper button, {{WRAPPER}} .opc-card-quantity .quantity-input-wrapper button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quantity_input_color',
            [
                'label'     => esc_html__( 'Number Input Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .quantity-input-wrapper input' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'quantity_input_typography',
                'selector' => '{{WRAPPER}} .quantity-input-wrapper input',
            ]
        );

        $this->add_responsive_control(
            'quantity_input_padding',
            [
                'label'      => esc_html__( 'Input Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .quantity-input-wrapper input, {{WRAPPER}} .opc-card-quantity .quantity-input-wrapper input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - HEADINGS & FORM TYPOGRAPHY
         * ======================================================== */
        $this->start_controls_section(
            'section_style_typography',
            [
                'label' => esc_html__( 'Headings & Form Fields', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        // Section Headings (H3)
        $this->add_control(
            'heading_section_titles_style',
            [
                'label' => esc_html__( 'Section Headings (H3)', 'on-page-checkout-for-elementor' ),
                'type'  => Controls_Manager::HEADING,
            ]
        );

        $this->add_control(
            'section_heading_color',
            [
                'label'     => esc_html__( 'Heading Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .on-page-checkout-widget h3' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'section_heading_typography',
                'selector' => '{{WRAPPER}} .on-page-checkout-widget h3',
            ]
        );

        $this->add_responsive_control(
            'section_heading_margin',
            [
                'label'      => esc_html__( 'Heading Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .on-page-checkout-widget h3' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Sub Headings (H4)
        $this->add_control(
            'heading_subtitles_style',
            [
                'label'     => esc_html__( 'Sub Headings (H4)', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'sub_heading_color',
            [
                'label'     => esc_html__( 'Sub Heading Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .on-page-checkout-widget h4' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'sub_heading_typography',
                'selector' => '{{WRAPPER}} .on-page-checkout-widget h4',
            ]
        );

        $this->add_responsive_control(
            'sub_heading_margin',
            [
                'label'      => esc_html__( 'Sub Heading Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .on-page-checkout-widget h4' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Checkout Field Labels
        $this->add_control(
            'heading_field_labels_style',
            [
                'label'     => esc_html__( 'Field Labels', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'field_label_color',
            [
                'label'     => esc_html__( 'Label Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-checkout-fields label, {{WRAPPER}} .woocommerce-form__label-text' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'field_label_typography',
                'selector' => '{{WRAPPER}} .opc-checkout-fields label, {{WRAPPER}} .woocommerce-form__label-text',
            ]
        );

        // Checkout Inputs & Textareas
        $this->add_control(
            'heading_inputs_style',
            [
                'label'     => esc_html__( 'Form Input Fields', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'input_typography',
                'selector' => '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'input_border',
                'selector' => '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea',
            ]
        );

        $this->add_responsive_control(
            'input_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'input_focus_border_color',
            [
                'label'     => esc_html__( 'Focus Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-checkout-fields input.input-text:focus, {{WRAPPER}} .opc-checkout-fields select:focus, {{WRAPPER}} .opc-checkout-fields textarea:focus' => 'border-color: {{VALUE}}; outline-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_padding',
            [
                'label'      => esc_html__( 'Input Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-checkout-fields input.input-text, {{WRAPPER}} .opc-checkout-fields select, {{WRAPPER}} .opc-checkout-fields textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - ORDER REVIEW & SUMMARY
         * ======================================================== */
        $this->start_controls_section(
            'section_style_order_review',
            [
                'label' => esc_html__( 'Order Review Table', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'checkout_layout_gap',
            [
                'label'      => esc_html__( 'Gap Between Form & Review Table', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', '%', 'em', 'rem', 'vw' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 200 ],
                    '%'  => [ 'min' => 0, 'max' => 30 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-checkout-fields-container' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'separator'  => 'after',
            ]
        );

        $this->add_control(
            'review_box_bg',
            [
                'label'     => esc_html__( 'Table Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-order-review' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'review_box_border',
                'selector' => '{{WRAPPER}} .opc-order-review',
            ]
        );

        $this->add_responsive_control(
            'review_box_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-order-review' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'review_box_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-order-review' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_table_header_style',
            [
                'label'     => esc_html__( 'Table Header', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'table_header_bg_color',
            [
                'label'     => esc_html__( 'Header Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-checkout-review-order-table thead, {{WRAPPER}} .woocommerce-checkout-review-order-table thead tr, {{WRAPPER}} .woocommerce-checkout-review-order-table thead th' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'table_header_color',
            [
                'label'     => esc_html__( 'Header Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-checkout-review-order-table thead th' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'table_header_typography',
                'selector' => '{{WRAPPER}} .woocommerce-checkout-review-order-table thead th',
            ]
        );

        $this->add_control(
            'table_row_color',
            [
                'label'     => esc_html__( 'Table Items Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-checkout-review-order-table tbody td, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody td *, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody .product-name, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody .amount, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody bdi, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) .amount, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) bdi' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'table_row_typography',
                'selector' => '{{WRAPPER}} .woocommerce-checkout-review-order-table tbody td, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody .product-name, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) th, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot tr:not(.order-total) td',
            ]
        );

        $this->add_control(
            'table_divider_color',
            [
                'label'     => esc_html__( 'Table Divider / Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce-checkout-review-order-table, {{WRAPPER}} .woocommerce-checkout-review-order-table th, {{WRAPPER}} .woocommerce-checkout-review-order-table td, {{WRAPPER}} .woocommerce-checkout-review-order-table thead th, {{WRAPPER}} .woocommerce-checkout-review-order-table tbody td, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot th, {{WRAPPER}} .woocommerce-checkout-review-order-table tfoot td, {{WRAPPER}} .woocommerce-checkout-review-order-table tr' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'heading_order_total_style',
            [
                'label'     => esc_html__( 'Total Price Row', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'order_total_color',
            [
                'label'     => esc_html__( 'Total Price Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .order-total th, {{WRAPPER}} .order-total td, {{WRAPPER}} .order-total td *, {{WRAPPER}} .order-total .amount, {{WRAPPER}} .order-total .amount bdi, {{WRAPPER}} .order-total strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - ORDER SUMMARY TOGGLE BUTTON
         * ======================================================== */
        $this->start_controls_section(
            'section_style_order_summary_toggle',
            [
                'label' => esc_html__( 'Order Summary Toggle Button', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'toggle_label_typography',
                'label'    => esc_html__( 'Label Typography', 'on-page-checkout-for-elementor' ),
                'selector' => '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-label',
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'toggle_total_typography',
                'label'    => esc_html__( 'Total Price Typography', 'on-page-checkout-for-elementor' ),
                'selector' => '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-total, {{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-total *',
            ]
        );

        $this->start_controls_tabs( 'tabs_toggle_style' );

        // Normal Tab
        $this->start_controls_tab(
            'tab_toggle_normal',
            [
                'label' => esc_html__( 'Normal', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'toggle_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_label_color',
            [
                'label'     => esc_html__( 'Label Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-label, {{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-left' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_icon_color',
            [
                'label'     => esc_html__( 'Icon Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-icon' => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_total_color',
            [
                'label'     => esc_html__( 'Total Amount Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-total, {{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-total *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_border_color_normal',
            [
                'label'     => esc_html__( 'Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'tab_toggle_hover',
            [
                'label' => esc_html__( 'Hover', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'toggle_hover_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_hover_label_color',
            [
                'label'     => esc_html__( 'Label Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle:hover .opc-toggle-label, {{WRAPPER}} .opc-mobile-order-summary-toggle:hover .opc-toggle-left' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_hover_icon_color',
            [
                'label'     => esc_html__( 'Icon Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle:hover .opc-toggle-icon' => 'stroke: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_hover_total_color',
            [
                'label'     => esc_html__( 'Total Amount Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle:hover .opc-toggle-total, {{WRAPPER}} .opc-mobile-order-summary-toggle:hover .opc-toggle-total *' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'toggle_hover_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'toggle_icon_size',
            [
                'label'      => esc_html__( 'Icon Size', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 10, 'max' => 40 ],
                ],
                'separator'  => 'before',
                'selectors'  => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_icon_spacing',
            [
                'label'      => esc_html__( 'Icon & Label Spacing', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 30 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle .opc-toggle-left' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'toggle_border',
                'selector'  => '{{WRAPPER}} .opc-mobile-order-summary-toggle',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'toggle_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'toggle_box_shadow',
                'selector' => '{{WRAPPER}} .opc-mobile-order-summary-toggle',
            ]
        );

        $this->add_responsive_control(
            'toggle_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'toggle_margin',
            [
                'label'      => esc_html__( 'Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-mobile-order-summary-toggle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - SHIPPING METHODS
         * ======================================================== */
        $this->start_controls_section(
            'section_style_shipping_methods',
            [
                'label' => esc_html__( 'Shipping Methods', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'shipping_method_container_bg',
            [
                'label'     => esc_html__( 'Container Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-shipping-methods' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'shipping_method_container_border',
                'selector' => '{{WRAPPER}} .opc-shipping-methods',
            ]
        );

        $this->add_responsive_control(
            'shipping_method_container_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-shipping-methods' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'shipping_method_container_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-shipping-methods' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'shipping_method_container_margin',
            [
                'label'      => esc_html__( 'Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-shipping-methods' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_shipping_method_labels_style',
            [
                'label'     => esc_html__( 'Method Titles & Radio', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'shipping_method_label_color',
            [
                'label'     => esc_html__( 'Method Label Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-shipping-methods label, {{WRAPPER}} .opc-shipping-methods .amount, {{WRAPPER}} .opc-shipping-methods h4' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'shipping_method_label_typography',
                'selector' => '{{WRAPPER}} .opc-shipping-methods label, {{WRAPPER}} .opc-shipping-methods .amount',
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - PAYMENT METHODS
         * ======================================================== */
        $this->start_controls_section(
            'section_style_payment_methods',
            [
                'label' => esc_html__( 'Payment Methods', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'payment_container_bg',
            [
                'label'     => esc_html__( 'Container Background', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-payment-methods' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'payment_container_border',
                'selector' => '{{WRAPPER}} .opc-payment-methods',
            ]
        );

        $this->add_responsive_control(
            'payment_container_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-payment-methods' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'payment_container_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-payment-methods' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'payment_container_margin',
            [
                'label'      => esc_html__( 'Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-payment-methods' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'heading_payment_method_labels_style',
            [
                'label'     => esc_html__( 'Method Titles & Radio', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'payment_method_label_color',
            [
                'label'     => esc_html__( 'Method Label Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .wc_payment_methods li.wc_payment_method label, {{WRAPPER}} .opc-payment-methods h4' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'payment_method_label_typography',
                'selector' => '{{WRAPPER}} .wc_payment_methods li.wc_payment_method label',
            ]
        );

        $this->add_control(
            'payment_box_heading',
            [
                'label'     => esc_html__( 'Payment Description Box', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'payment_box_bg',
            [
                'label'     => esc_html__( 'Box Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .payment_box' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'payment_box_text_color',
            [
                'label'     => esc_html__( 'Box Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .payment_box, {{WRAPPER}} .payment_box p' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'payment_box_typography',
                'selector' => '{{WRAPPER}} .payment_box, {{WRAPPER}} .payment_box p',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'payment_box_border',
                'selector' => '{{WRAPPER}} .payment_box',
            ]
        );

        $this->add_responsive_control(
            'payment_box_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .payment_box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'payment_box_padding',
            [
                'label'      => esc_html__( 'Box Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .payment_box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        /* ========================================================
         * STYLE TAB - PLACE ORDER BUTTON
         * ======================================================== */
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => esc_html__( 'Place Order Button', 'on-page-checkout-for-elementor' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'button_typography',
                'selector' => '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order',
            ]
        );

        $this->start_controls_tabs( 'tabs_button_style' );

        // Normal Tab
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Hover', 'on-page-checkout-for-elementor' ),
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label'     => esc_html__( 'Text Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-place-order-button:hover, {{WRAPPER}} button#place_order:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_bg_color',
            [
                'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-place-order-button:hover, {{WRAPPER}} button#place_order:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_border_color',
            [
                'label'     => esc_html__( 'Border Color', 'on-page-checkout-for-elementor' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .opc-place-order-button:hover, {{WRAPPER}} button#place_order:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'      => 'button_border',
                'selector'  => '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order',
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'button_border_radius',
            [
                'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order',
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', 'rem' ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_margin_top',
            [
                'label'      => esc_html__( 'Top Margin', 'on-page-checkout-for-elementor' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em' ],
                'range'      => [
                    'px' => [ 'min' => 0, 'max' => 60 ],
                ],
                'selectors'  => [
                    '{{WRAPPER}} .opc-place-order-button, {{WRAPPER}} button#place_order' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

    }

    /**
     * Get list of WooCommerce products for Select2 options
     *
     * @return array
     */
    private function get_product_options() {
        $options = [];

        if ( ! post_type_exists( 'product' ) && ! function_exists( 'wc_get_products' ) ) {
            return $options;
        }

        $posts = get_posts( [
            'post_type'        => 'product',
            'post_status'      => [ 'publish', 'private' ],
            'posts_per_page'   => -1,
            'orderby'          => 'title',
            'order'            => 'ASC',
            'suppress_filters' => true,
        ] );

        if ( ! empty( $posts ) ) {
            foreach ( $posts as $post ) {
                $extra_info = [];
                $sku = '';
                if ( function_exists( 'wc_get_product' ) ) {
                    $prod = wc_get_product( $post->ID );
                    if ( $prod ) {
                        if ( $prod->is_type( 'variable' ) ) {
                            $extra_info[] = esc_html__( 'Variable', 'on-page-checkout-for-elementor' );
                        }
                        $sku = $prod->get_sku();
                        if ( ! empty( $sku ) ) {
                            $extra_info[] = 'SKU: ' . $sku;
                        }
                    }
                }
                $extra_str = ! empty( $extra_info ) ? ' [' . implode( ', ', $extra_info ) . ']' : '';
                $label = sprintf( '%s%s (#%d)', $post->post_title, $extra_str, $post->ID );
                $options[ (string) $post->ID ] = $label;

                // Also register SKU as option alias so legacy repeater items saved with SKU display their label in Select2
                if ( ! empty( $sku ) && ! isset( $options[ $sku ] ) ) {
                    $options[ $sku ] = $label;
                }
            }
        }

        return $options;
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
    /**
     * Get a clean, human-readable variation name from variation data or object.
     *
     * @param array $variation
     * @param WC_Product_Variation|null $variation_obj
     * @return string
     */
    private function get_variation_readable_name( $variation, $variation_obj = null ) {
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
                    $readable = ucfirst( str_replace( [ '-', '_' ], ' ', $attr_value ) );
                }
            } else {
                $readable = ucfirst( str_replace( [ '-', '_' ], ' ', $attr_value ) );
            }

            $parts[] = $readable;
        }

        if ( empty( $parts ) && $variation_obj instanceof WC_Product_Variation ) {
            $formatted_name = $variation_obj->get_attribute_summary();
            if ( ! empty( $formatted_name ) ) {
                return $formatted_name;
            }
        }

        return ! empty( $parts ) ? implode( ' - ', $parts ) : '';
    }

    /**
     * Render a variation dropdown select inside the product card.
     *
     * @param WC_Product $product
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
            $var_name     = $this->get_variation_readable_name( $variation );
            $label        = ! empty( $var_name ) ? $var_name : '#' . $variation_id;
            $is_in_stock  = ! empty( $variation['is_in_stock'] );
            if ( ! $is_in_stock ) {
                $label .= ' (' . esc_html__( 'Out of stock', 'on-page-checkout-for-elementor' ) . ')';
            }
            if ( $variation_id ) {
                $options[ $variation_id ] = [
                    'label'       => $label,
                    'is_in_stock' => $is_in_stock ? '1' : '0',
                ];
            }
        }

        if ( empty( $options ) ) {
            return;
        }

        echo '<div class="opc-combined-variants">';
        echo '<select name="variation_id" class="opc-combined-variant-select" data-product-id="' . esc_attr( $product_id ) . '">';
        $i = 0;
        foreach ( $options as $vid => $opt ) {
            echo '<option value="' . esc_attr( $vid ) . '" data-in-stock="' . esc_attr( $opt['is_in_stock'] ) . '"' . selected( $i, 0, false ) . '>' . esc_html( $opt['label'] ) . '</option>';
            $i++;
        }
        echo '</select>';
        echo '<input type="hidden" class="opc-avail-variations" value=\'' . esc_attr( wp_json_encode( $available_variations ) ) . '\' />';
        echo '</div>';
    }

    /**
     * Render an individual card quantity selector for multi-product selection mode.
     */
    private function render_card_quantity() {
        echo '<div class="opc-card-quantity">';
            echo '<div class="quantity-input-wrapper">';
                echo '<button type="button" class="qty-decrement opc-card-qty-btn" aria-label="' . esc_attr__( 'Decrease quantity', 'on-page-checkout-for-elementor' ) . '">-</button>';
                echo '<input type="text" class="opc-card-qty-input" value="1" min="1">';
                echo '<button type="button" class="qty-increment opc-card-qty-btn" aria-label="' . esc_attr__( 'Increase quantity', 'on-page-checkout-for-elementor' ) . '">+</button>';
            echo '</div>';
        echo '</div>';
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $product_ids = [];
        $selection_mode = $settings['product_selection_source'] ?? '';

        if ( 'multi_select' === $selection_mode ) {
            if ( ! empty( $settings['selected_products'] ) && is_array( $settings['selected_products'] ) ) {
                $product_ids = $settings['selected_products'];
            }
        } elseif ( 'repeater' === $selection_mode ) {
            if ( ! empty( $settings['product_list'] ) && is_array( $settings['product_list'] ) ) {
                foreach ( $settings['product_list'] as $item ) {
                    if ( ! empty( $item['product_id'] ) ) {
                        $product_ids[] = $item['product_id'];
                    }
                }
            }
        } else {
            // Fallback for legacy widgets or switching
            if ( ! empty( $settings['selected_products'] ) && is_array( $settings['selected_products'] ) ) {
                $product_ids = $settings['selected_products'];
            } elseif ( ! empty( $settings['product_list'] ) && is_array( $settings['product_list'] ) ) {
                foreach ( $settings['product_list'] as $item ) {
                    if ( ! empty( $item['product_id'] ) ) {
                        $product_ids[] = $item['product_id'];
                    }
                }
            }
        }

        if ( empty( $product_ids ) ) {
            echo '<div class="opc-no-products">' . esc_html__( 'No products selected for On Page Checkout.', 'on-page-checkout-for-elementor' ) . '</div>';
            return;
        }

        $preselected_product_ids = [];
        $is_elementor_editor = false;
        if ( class_exists( '\Elementor\Plugin' ) ) {
            $is_elementor_editor = ( \Elementor\Plugin::$instance->editor && \Elementor\Plugin::$instance->editor->is_edit_mode() )
                || ( \Elementor\Plugin::$instance->preview && \Elementor\Plugin::$instance->preview->is_preview_mode() );
        }

        // Only parse preselect parameter on frontend to avoid collision with editor URL query args (e.g. post ID)
        if ( ! $is_elementor_editor ) {
            $preselect_param = ! empty( $settings['preselect_product_param'] ) ? sanitize_key( $settings['preselect_product_param'] ) : '';
            if ( '' !== $preselect_param && isset( $_GET[ $preselect_param ] ) ) {
                $raw_val = sanitize_text_field( wp_unslash( $_GET[ $preselect_param ] ) );
                $parts   = array_map( 'absint', explode( ',', $raw_val ) );
                $preselected_product_ids = array_values( array_filter( $parts ) );
            }
        }

        $mobile_summary_label = ! empty( $settings['mobile_order_summary_label'] ) ? $settings['mobile_order_summary_label'] : esc_html__( 'Order summary', 'on-page-checkout-for-elementor' );
        $mobile_summary_collapsed = ( 'no' === ( $settings['mobile_order_summary_default_collapsed'] ?? 'yes' ) ) ? 'no' : 'yes';
        $show_variations_as_cards = ( 'yes' === ( $settings['show_variations_as_cards'] ?? 'no' ) );
        $is_multi_select          = ( 'yes' === ( $settings['enable_multi_product_selection'] ?? 'no' ) );
        $input_type               = $is_multi_select ? 'checkbox' : 'radio';
        $input_name               = $is_multi_select ? 'w3opcfe_product_id[]' : 'w3opcfe_product_id';

        // Build custom badges map (supports both multi-select custom badges repeater and product list repeater)
        $custom_badges_map = [];
        if ( ! empty( $settings['product_custom_badges'] ) && is_array( $settings['product_custom_badges'] ) ) {
            foreach ( $settings['product_custom_badges'] as $cb ) {
                if ( ! empty( $cb['product_id'] ) && ! empty( $cb['badge_text'] ) ) {
                    $custom_badges_map[ (string) $cb['product_id'] ] = [
                        'text'     => $cb['badge_text'],
                        'color'    => $cb['badge_color'] ?? '',
                        'bg_color' => $cb['badge_bg_color'] ?? '',
                    ];
                }
            }
        }
        if ( ! empty( $settings['product_list'] ) && is_array( $settings['product_list'] ) ) {
            foreach ( $settings['product_list'] as $p_item ) {
                if ( ! empty( $p_item['product_id'] ) && ! empty( $p_item['custom_badge_text'] ) ) {
                    $custom_badges_map[ (string) $p_item['product_id'] ] = [
                        'text'     => $p_item['custom_badge_text'],
                        'color'    => $p_item['custom_badge_color'] ?? '',
                        'bg_color' => $p_item['custom_badge_bg_color'] ?? '',
                    ];
                }
            }
        }

        // Assemble render items (expand variable products into separate cards if enabled)
        $render_items = [];
        foreach ( $product_ids as $id_or_sku ) {
            $product = $this->get_product_by_id_or_sku( $id_or_sku );
            if ( ! $product ) {
                continue;
            }

            $product_id = $product->get_id();

            if ( $show_variations_as_cards && $product->is_type( 'variable' ) ) {
                $available_variations = $product->get_available_variations();
                if ( ! empty( $available_variations ) ) {
                    foreach ( $available_variations as $var_data ) {
                        $var_id = isset( $var_data['variation_id'] ) ? absint( $var_data['variation_id'] ) : 0;
                        if ( ! $var_id ) {
                            continue;
                        }

                        $var_obj  = wc_get_product( $var_id );
                        $var_name = $this->get_variation_readable_name( $var_data, $var_obj );

                        // Card title: 'Product title - variant name'
                        $card_title = ! empty( $var_name ) ? ( $product->get_name() . ' - ' . $var_name ) : $product->get_name();

                        // Card image: variant image if available, else product featured image
                        if ( $var_obj && $var_obj->get_image_id() > 0 ) {
                            $card_image = $var_obj->get_image( 'woocommerce_thumbnail' );
                        } else {
                            $card_image = $product->get_image( 'woocommerce_thumbnail' );
                        }

                        // Card price
                        $card_price = ( $var_obj && '' !== $var_obj->get_price_html() ) ? $var_obj->get_price_html() : $product->get_price_html();

                        // In stock check
                        $is_in_stock = $var_obj ? $var_obj->is_in_stock() : ( ! empty( $var_data['is_in_stock'] ) );

                        $badge_data = $custom_badges_map[ (string) $var_id ] ?? ( $custom_badges_map[ (string) $product_id ] ?? ( $custom_badges_map[ (string) $id_or_sku ] ?? null ) );

                        $render_items[] = [
                            'product_id'            => $product_id,
                            'variation_id'          => $var_id,
                            'title'                 => $card_title,
                            'image'                 => $card_image,
                            'price'                 => $card_price,
                            'is_variation_card'     => true,
                            'available_variations'  => null,
                            'product'               => $product,
                            'variation_obj'         => $var_obj,
                            'is_in_stock'           => $is_in_stock,
                            'custom_badge_text'     => $badge_data['text'] ?? '',
                            'custom_badge_color'    => $badge_data['color'] ?? '',
                            'custom_badge_bg_color' => $badge_data['bg_color'] ?? '',
                        ];
                    }
                    continue;
                }
            }

            // Simple product or variable product without separate variation cards
            $is_variable = $product->is_type( 'variable' );
            $available_variations = $is_variable ? $product->get_available_variations() : null;
            $default_variation_id = 0;
            $is_in_stock = $product->is_in_stock();

            if ( $is_variable && ! empty( $available_variations ) ) {
                $default_variation_id = isset( $available_variations[0]['variation_id'] ) ? absint( $available_variations[0]['variation_id'] ) : 0;
                $is_in_stock = ! empty( $available_variations[0]['is_in_stock'] );
            }

            $badge_data = $custom_badges_map[ (string) $product_id ] ?? ( $custom_badges_map[ (string) $id_or_sku ] ?? null );
            if ( ! $badge_data && ! empty( $default_variation_id ) ) {
                $badge_data = $custom_badges_map[ (string) $default_variation_id ] ?? null;
            }

            $render_items[] = [
                'product_id'            => $product_id,
                'variation_id'          => $default_variation_id,
                'title'                 => $product->get_name(),
                'image'                 => $product->get_image( 'woocommerce_thumbnail' ),
                'price'                 => $product->get_price_html(),
                'is_variation_card'     => false,
                'available_variations'  => $available_variations,
                'product'               => $product,
                'variation_obj'         => null,
                'is_in_stock'           => $is_in_stock,
                'custom_badge_text'     => $badge_data['text'] ?? '',
                'custom_badge_color'    => $badge_data['color'] ?? '',
                'custom_badge_bg_color' => $badge_data['bg_color'] ?? '',
            ];
        }

        if ( empty( $render_items ) ) {
            echo '<div class="opc-no-products">' . esc_html__( 'No products available for On Page Checkout.', 'on-page-checkout-for-elementor' ) . '</div>';
            return;
        }

        // Determine preselected cards (only select in-stock items)
        $selected_indices = [];
        if ( ! empty( $preselected_product_ids ) ) {
            foreach ( $render_items as $idx => $item ) {
                if ( in_array( (int) $item['variation_id'], $preselected_product_ids, true ) || in_array( (int) $item['product_id'], $preselected_product_ids, true ) ) {
                    if ( ! empty( $item['is_in_stock'] ) ) {
                        $selected_indices[] = $idx;
                        if ( ! $is_multi_select ) {
                            break;
                        }
                    }
                }
            }
        }
        if ( empty( $selected_indices ) ) {
            foreach ( $render_items as $idx => $item ) {
                if ( ! empty( $item['is_in_stock'] ) ) {
                    $selected_indices = [ $idx ];
                    break;
                }
            }
        }
        $initial_variation_id = ! empty( $selected_indices ) ? ( $render_items[ $selected_indices[0] ]['variation_id'] ?? 0 ) : 0;

        $badge_text = ! empty( $settings['out_of_stock_badge_text'] ) ? $settings['out_of_stock_badge_text'] : esc_html__( 'Out of Stock', 'on-page-checkout-for-elementor' );
        $custom_badge_pos = ! empty( $settings['custom_badge_position'] ) ? $settings['custom_badge_position'] : 'top-right';
        $custom_badge_pos_class = ( 'top-left' === $custom_badge_pos ) ? 'opc-badge-pos-top-left' : 'opc-badge-pos-top-right';

        echo '<div class="on-page-checkout-widget" data-widget-id="' . esc_attr( $this->get_id() ) . '" data-multi-select="' . esc_attr( $is_multi_select ? 'yes' : 'no' ) . '" data-shipping-title="' . esc_attr( $settings['shipping_details_heading'] ) . '" data-shipping-method-title="' . esc_attr( $settings['shipping_method_heading'] ?? esc_html__( 'Shipping Method', 'on-page-checkout-for-elementor' ) ) . '" data-billing-title="' . esc_attr( $settings['billing_details_heading'] ) . '" data-order-summery-title="' . esc_attr( $settings['order_summary_heading'] ) . '" data-payment-method-title="' . esc_attr( $settings['payment_method_heading'] ) . '" data-additional-info-title="' . esc_attr( $settings['additional_info_heading'] ) . '" data-order-button-name="' . esc_attr( $settings['order_button_name'] ) . '" data-mobile-summary-label="' . esc_attr( $mobile_summary_label ) . '" data-mobile-summary-collapsed="' . esc_attr( $mobile_summary_collapsed ) . '" data-out-of-stock-label="' . esc_attr( $badge_text ) . '">';
        echo '<form class="opc-checkout-form checkout woocommerce-checkout" method="post">';
        echo '<div class="opc-checkout-notices" style="display:none;"></div>';
        if ( class_exists( '\W3OPCFE_Greenweb' ) && \W3OPCFE_Greenweb::is_otp_enabled() ) {
            echo '<input type="hidden" name="wps_user_meta_field" value="billing_phone">';
        }

        // Product selection cards
        if ( count( $render_items ) > 1 ) {
            echo '<div class="opc-product-selection">';
            if ( ! empty( $settings['product_selection_heading'] ) ) {
                echo '<h3>' . esc_html( $settings['product_selection_heading'] ) . '</h3>';
            }
            echo '<div class="opc-products-wrapper">';
            foreach ( $render_items as $idx => $item ) {
                $is_out_of_stock = empty( $item['is_in_stock'] );
                $checked = ( in_array( $idx, $selected_indices, true ) && ! $is_out_of_stock );
                $item_classes = 'opc-product-item' . ( $is_out_of_stock ? ' opc-out-of-stock' : '' );
                $disabled_attr = $is_out_of_stock ? ' disabled' : '';

                $data_attr = '';
                if ( ! empty( $item['available_variations'] ) ) {
                    $data_attr = ' data-available-variations=\'' . esc_attr( wp_json_encode( $item['available_variations'] ) ) . '\'';
                }

                echo '<div class="' . esc_attr( $item_classes ) . '" data-product-id="' . esc_attr( $item['product_id'] ) . '" data-variation-id="' . esc_attr( $item['variation_id'] ) . '"' . ( $is_out_of_stock ? ' data-out-of-stock="1"' : '' ) . wp_kses_post( $data_attr ) . '>';
                    echo '<label>';
                        echo '<span class="opc-stock-badge opc-out-of-stock-badge"' . ( $is_out_of_stock ? '' : ' style="display:none;"' ) . '>' . esc_html( $badge_text ) . '</span>';
                        if ( ! empty( $item['custom_badge_text'] ) ) {
                            $custom_badge_inline = '';
                            if ( ! empty( $item['custom_badge_color'] ) ) {
                                $custom_badge_inline .= 'color:' . esc_attr( $item['custom_badge_color'] ) . ';';
                            }
                            if ( ! empty( $item['custom_badge_bg_color'] ) ) {
                                $custom_badge_inline .= 'background-color:' . esc_attr( $item['custom_badge_bg_color'] ) . ';';
                            }
                            $custom_badge_style_attr = ! empty( $custom_badge_inline ) ? ' style="' . esc_attr( $custom_badge_inline ) . '"' : '';
                            echo '<span class="opc-custom-badge ' . esc_attr( $custom_badge_pos_class ) . '"' . $custom_badge_style_attr . '>' . esc_html( $item['custom_badge_text'] ) . '</span>';
                        }
                        echo '<input type="' . esc_attr( $input_type ) . '" class="opc-product-input" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $item['product_id'] ) . '" data-variation-id="' . esc_attr( $item['variation_id'] ) . '" ' . checked( $checked, true, false ) . $disabled_attr . '>';
                        echo '<div class="product-card">';
                            echo wp_kses_post( $item['image'] );
                            echo '<div class="product-information">';
                                echo '<div class="product-title">' . esc_html( $item['title'] ) . '</div>';
                                echo '<div class="product-price">' . wp_kses_post( $item['price'] ) . '</div>';
                                if ( ! $item['is_variation_card'] && $item['product']->is_type( 'variable' ) ) {
                                    $this->render_variation_select( $item['product'] );
                                }
                                if ( $is_multi_select && 'yes' === $settings['show_quantity'] ) {
                                    $this->render_card_quantity();
                                }
                            echo '</div>';
                        echo '</div>';
                    echo '</label>';
                echo '</div>'; // .opc-product-item
            }
            echo '</div></div>';

        } else {
            // If only one product/card, pre-select it
            $item = $render_items[0];
            $is_out_of_stock = empty( $item['is_in_stock'] );
            $item_classes = 'opc-product-item' . ( $is_out_of_stock ? ' opc-out-of-stock' : '' );
            $disabled_attr = $is_out_of_stock ? ' disabled' : '';
            $checked = ! $is_out_of_stock;

            $data_attr = '';
            if ( ! empty( $item['available_variations'] ) ) {
                $data_attr = ' data-available-variations=\'' . esc_attr( wp_json_encode( $item['available_variations'] ) ) . '\'';
            }

            echo '<div class="opc-products-wrapper">';
                echo '<div class="' . esc_attr( $item_classes ) . '" data-product-id="' . esc_attr( $item['product_id'] ) . '" data-variation-id="' . esc_attr( $item['variation_id'] ) . '"' . ( $is_out_of_stock ? ' data-out-of-stock="1"' : '' ) . wp_kses_post( $data_attr ) . '>';
                    echo '<label>';
                        echo '<span class="opc-stock-badge opc-out-of-stock-badge"' . ( $is_out_of_stock ? '' : ' style="display:none;"' ) . '>' . esc_html( $badge_text ) . '</span>';
                        if ( ! empty( $item['custom_badge_text'] ) ) {
                            $custom_badge_inline = '';
                            if ( ! empty( $item['custom_badge_color'] ) ) {
                                $custom_badge_inline .= 'color:' . esc_attr( $item['custom_badge_color'] ) . ';';
                            }
                            if ( ! empty( $item['custom_badge_bg_color'] ) ) {
                                $custom_badge_inline .= 'background-color:' . esc_attr( $item['custom_badge_bg_color'] ) . ';';
                            }
                            $custom_badge_style_attr = ! empty( $custom_badge_inline ) ? ' style="' . esc_attr( $custom_badge_inline ) . '"' : '';
                            echo '<span class="opc-custom-badge ' . esc_attr( $custom_badge_pos_class ) . '"' . $custom_badge_style_attr . '>' . esc_html( $item['custom_badge_text'] ) . '</span>';
                        }
                        echo '<input type="' . esc_attr( $input_type ) . '" class="opc-product-input" name="' . esc_attr( $input_name ) . '" value="' . esc_attr( $item['product_id'] ) . '" data-variation-id="' . esc_attr( $item['variation_id'] ) . '" ' . checked( $checked, true, false ) . $disabled_attr . '>';
                        echo '<div class="product-card">';
                            echo wp_kses_post( $item['image'] );
                            echo '<div class="product-information">';
                                echo '<div class="product-title">' . esc_html( $item['title'] ) . '</div>';
                                echo '<div class="product-price">' . wp_kses_post( $item['price'] ) . '</div>';
                                if ( ! $item['is_variation_card'] && $item['product']->is_type( 'variable' ) ) {
                                    $this->render_variation_select( $item['product'] );
                                }
                                if ( $is_multi_select && 'yes' === $settings['show_quantity'] ) {
                                    $this->render_card_quantity();
                                }
                            echo '</div>';
                        echo '</div>';
                    echo '</label>';
                echo '</div>';
            echo '</div>';
        }

        echo '<input type="hidden" name="variation_id" class="opc-selected-variation-id" value="' . esc_attr( $initial_variation_id ) . '">';

        // Quantity selector (only shown when multi-selection is disabled; in multi-select mode each card has its own quantity selector)
        if ( 'yes' === $settings['show_quantity'] && ! $is_multi_select ) {
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
