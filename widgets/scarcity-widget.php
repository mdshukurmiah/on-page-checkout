<?php
/**
 * Product Scarcity Widget
 *
 * @package On_Page_Checkout_For_Elementor
 */

namespace W3OPCFE\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class W3OPCFE_Scarcity_Widget extends Widget_Base {

	public function get_name() {
		return 'w3opcfe-scarcity';
	}

	public function get_title() {
		return esc_html__( 'Product Scarcity', 'on-page-checkout-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-alert';
	}

	public function get_categories() {
		return [ 'on-page-checkout', 'general' ];
	}

	public function get_keywords() {
		return [ 'scarcity', 'stock', 'urgency', 'blinker', 'inventory', 'alert', 'singlegrid' ];
	}

	public function get_script_depends() {
		return [ 'w3opcfe-scarcity-script' ];
	}

	public function get_style_depends() {
		return [ 'w3opcfe-scarcity-style' ];
	}

	protected function register_controls() {
		// Content Section: Settings
		$this->start_controls_section(
			'section_scarcity',
			[
				'label' => esc_html__( 'Scarcity Settings', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'layout',
			[
				'label'   => esc_html__( 'Layout', 'on-page-checkout-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'inline' => esc_html__( 'Inline (Classic)', 'on-page-checkout-for-elementor' ),
					'bar'    => esc_html__( 'Progress Bar (Text with Scarcity Bar)', 'on-page-checkout-for-elementor' ),
				],
				'default' => 'inline',
			]
		);

		$this->add_control(
			'bar_max',
			[
				'label'       => esc_html__( 'Total Stock Reference (Bar Scale)', 'on-page-checkout-for-elementor' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 1,
				'default'     => '',
				'placeholder' => esc_html__( 'e.g. 50 (Defaults to Max Count)', 'on-page-checkout-for-elementor' ),
				'description' => esc_html__( 'The total capacity used to calculate the progress bar percentage. If left empty, Maximum Count is used.', 'on-page-checkout-for-elementor' ),
				'condition'   => [
					'layout' => 'bar',
				],
			]
		);

		$this->add_control(
			'bar_calculation',
			[
				'label'     => esc_html__( 'Bar Progress Mode', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'remaining'      => esc_html__( 'Remaining Stock (Count / Total)', 'on-page-checkout-for-elementor' ),
					'depleted'       => esc_html__( 'Sold / Depleted Stock ((Total - Count) / Total)', 'on-page-checkout-for-elementor' ),
					'direct_percent' => esc_html__( 'Direct Percentage (Count is %)', 'on-page-checkout-for-elementor' ),
				],
				'default'   => 'remaining',
				'condition' => [
					'layout' => 'bar',
				],
			]
		);

		$this->add_control(
			'bangla_numbers',
			[
				'label'        => esc_html__( 'Bangla Numbers', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'counting_up',
			[
				'label'        => esc_html__( 'Count Up (Inverse)', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'description'  => esc_html__( 'If enabled, the number will increase on each refresh instead of decreasing.', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'icon_type',
			[
				'label'   => esc_html__( 'Indicator Icon', 'on-page-checkout-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'warning' => esc_html__( 'Warning Triangle (Alert)', 'on-page-checkout-for-elementor' ),
					'blinker' => esc_html__( 'Pulsing Blinker SVG', 'on-page-checkout-for-elementor' ),
					'custom'  => esc_html__( 'Custom Icon', 'on-page-checkout-for-elementor' ),
					'none'    => esc_html__( 'None', 'on-page-checkout-for-elementor' ),
				],
				'default' => 'blinker',
			]
		);

		$this->add_control(
			'blinker_color',
			[
				'label'     => esc_html__( 'Blinker Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ff0d00',
				'condition' => [
					'icon_type' => 'blinker',
				],
			]
		);

		$this->add_control(
			'custom_icon',
			[
				'label'     => esc_html__( 'Choose Icon', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [
					'value'   => 'fas fa-fire',
					'library' => 'solid',
				],
				'condition' => [
					'icon_type' => 'custom',
				],
			]
		);

		$this->add_control(
			'custom_icon_color',
			[
				'label'     => esc_html__( 'Icon Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-icon, {{WRAPPER}} .singlegrid-pdp-scarcity-icon' => 'color: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-scarcity-icon i, {{WRAPPER}} .singlegrid-pdp-scarcity-icon i' => 'color: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-scarcity-icon svg, {{WRAPPER}} .singlegrid-pdp-scarcity-icon svg' => 'fill: {{VALUE}}; color: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-warning-svg, {{WRAPPER}} .singlegrid-pdp-warning-svg' => 'stroke: {{VALUE}}; color: {{VALUE}};',
				],
				'condition' => [
					'icon_type' => [ 'custom', 'warning' ],
				],
			]
		);

		$this->add_responsive_control(
			'icon_size',
			[
				'label'      => esc_html__( 'Icon Size', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 10, 'max' => 60 ],
					'em' => [ 'min' => 0.5, 'max' => 4, 'step' => 0.1 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-icon, {{WRAPPER}} .singlegrid-pdp-scarcity-icon' => 'font-size: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .w3opcfe-scarcity-icon svg, {{WRAPPER}} .singlegrid-pdp-scarcity-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .w3opcfe-scarcity-blinker, {{WRAPPER}} .singlegrid-pdp-scarcity-blinker' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'icon_type!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'icon_spacing',
			[
				'label'      => esc_html__( 'Icon Spacing', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 6,
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-blinker, {{WRAPPER}} .singlegrid-pdp-scarcity-blinker, {{WRAPPER}} .w3opcfe-scarcity-icon, {{WRAPPER}} .singlegrid-pdp-scarcity-icon' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'icon_type!' => 'none',
				],
			]
		);

		$this->add_control(
			'prefix_text',
			[
				'label'       => esc_html__( 'Prefix Text', 'on-page-checkout-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( 'Only ', 'on-page-checkout-for-elementor' ),
				'placeholder' => esc_html__( 'Only ', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'suffix_text',
			[
				'label'       => esc_html__( 'Suffix Text', 'on-page-checkout-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => esc_html__( ' items left in stock!', 'on-page-checkout-for-elementor' ),
				'placeholder' => esc_html__( ' items left in stock!', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'min_val',
			[
				'label'   => esc_html__( 'Minimum Count', 'on-page-checkout-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 0,
				'default' => 5,
			]
		);

		$this->add_control(
			'max_val',
			[
				'label'   => esc_html__( 'Maximum Count', 'on-page-checkout-for-elementor' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'default' => 35,
			]
		);

		$this->add_control(
			'decrement_mode',
			[
				'label'   => esc_html__( 'Decrease Mode', 'on-page-checkout-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'random' => esc_html__( 'Random (1 - 3)', 'on-page-checkout-for-elementor' ),
					'fixed'  => esc_html__( 'Fixed Amount', 'on-page-checkout-for-elementor' ),
				],
				'default' => 'random',
			]
		);

		$this->add_control(
			'decrement_step',
			[
				'label'     => esc_html__( 'Fixed Amount', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'default'   => 1,
				'condition' => [
					'decrement_mode' => 'fixed',
				],
			]
		);

		$this->add_control(
			'reset_at_min',
			[
				'label'        => esc_html__( 'Loop Count When Reaching Limit', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'description'  => esc_html__( 'If enabled, count resets to maximum/minimum when it reaches the end threshold.', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->end_controls_section();

		// Style Section: Layout
		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => esc_html__( 'Container Style', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label'     => esc_html__( 'Alignment', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'flex-start' => [ 'title' => esc_html__( 'Left', 'on-page-checkout-for-elementor' ), 'icon' => 'eicon-text-align-left' ],
					'center'     => [ 'title' => esc_html__( 'Center', 'on-page-checkout-for-elementor' ), 'icon' => 'eicon-text-align-center' ],
					'flex-end'   => [ 'title' => esc_html__( 'Right', 'on-page-checkout-for-elementor' ), 'icon' => 'eicon-text-align-right' ],
				],
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper' => 'justify-content: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-scarcity-wrapper.w3opcfe-scarcity-has-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper.w3opcfe-scarcity-has-bar' => 'align-items: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-scarcity-header, {{WRAPPER}} .singlegrid-pdp-scarcity-header' => 'justify-content: {{VALUE}};',
				],
				'default'   => 'flex-start',
			]
		);

		$this->add_control(
			'bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'padding',
			[
				'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'border',
				'selector' => '{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper',
			]
		);

		$this->add_responsive_control(
			'border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .w3opcfe-scarcity-wrapper, {{WRAPPER}} .singlegrid-pdp-scarcity-wrapper',
			]
		);

		$this->end_controls_section();

		// Style Section: Text
		$this->start_controls_section(
			'section_text_style',
			[
				'label' => esc_html__( 'Text Style', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => esc_html__( 'Text Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-prefix, {{WRAPPER}} .w3opcfe-scarcity-suffix, {{WRAPPER}} .singlegrid-pdp-scarcity-prefix, {{WRAPPER}} .singlegrid-pdp-scarcity-suffix' => 'color: {{VALUE}};',
					'{{WRAPPER}} .w3opcfe-scarcity-warning-icon, {{WRAPPER}} .singlegrid-pdp-scarcity-warning-icon' => 'color: {{VALUE}};',
				],
				'default'   => '#334155',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'text_typography',
				'selector' => '{{WRAPPER}} .w3opcfe-scarcity-prefix, {{WRAPPER}} .w3opcfe-scarcity-suffix, {{WRAPPER}} .singlegrid-pdp-scarcity-prefix, {{WRAPPER}} .singlegrid-pdp-scarcity-suffix',
			]
		);

		$this->end_controls_section();

		// Style Section: Highlighted Number
		$this->start_controls_section(
			'section_number_style',
			[
				'label' => esc_html__( 'Count Number', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'number_color',
			[
				'label'     => esc_html__( 'Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number' => 'color: {{VALUE}};',
				],
				'default'   => '#dc2626',
			]
		);

		$this->add_control(
			'number_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'number_typography',
				'selector' => '{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number',
			]
		);

		$this->add_responsive_control(
			'number_padding',
			[
				'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'number_border',
				'selector' => '{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number',
			]
		);

		$this->add_responsive_control(
			'number_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-number, {{WRAPPER}} .singlegrid-pdp-scarcity-number' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		// Style Section: Progress Bar (Active when layout is 'bar')
		$this->start_controls_section(
			'section_bar_style',
			[
				'label'     => esc_html__( 'Progress Bar Style', 'on-page-checkout-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout' => 'bar',
				],
			]
		);

		$this->add_responsive_control(
			'bar_height',
			[
				'label'      => esc_html__( 'Height', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 2, 'max' => 40 ],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 8,
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-bar' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'bar_width',
			[
				'label'      => esc_html__( 'Width', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ '%', 'px' ],
				'range'      => [
					'%'  => [ 'min' => 10, 'max' => 100 ],
					'px' => [ 'min' => 50, 'max' => 1000 ],
				],
				'default'    => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-bar' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'bar_spacing',
			[
				'label'      => esc_html__( 'Top Spacing', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 50 ],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-bar' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'bar_fill_color',
			[
				'label'     => esc_html__( 'Fill Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#eeb029',
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-fill, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-fill' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'bar_bg_color',
			[
				'label'     => esc_html__( 'Track (Background) Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255, 255, 255, 0.15)',
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-bar' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'bar_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'default'    => [
					'top'      => '999',
					'right'    => '999',
					'bottom'   => '999',
					'left'     => '999',
					'unit'     => 'px',
					'isLinked' => true,
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-scarcity-progress-bar, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-bar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}} .w3opcfe-scarcity-progress-fill, {{WRAPPER}} .singlegrid-pdp-scarcity-progress-fill' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function convert_to_bangla_number( $number ) {
		$eng = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];
		$bng = [ '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ];
		return str_replace( $eng, $bng, (string) $number );
	}

	protected function get_blinker_svg( $color = '#ff0d00' ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="100%" height="100%" viewBox="0 0 20 20"><g id="i0"><g transform="translate(10,10)"><g transform="scale(1.25,1.25)"><animateTransform repeatCount="indefinite" type="scale" attributeName="transform" dur="1s" begin="0s" calcMode="spline" values="1.25 1.25; 1.5 1.5; 1.25 1.25" keyTimes="0; 0.5; 1" keySplines="0.333 0 0.667 1; 0.333 0 0.667 1" fill="freeze"/><g transform="translate(-0.265,-0.709)"><g id="i1" transform="matrix(1.47,0,0,1.47,0.265,0.709)"><path fill="' . esc_attr( $color ) . '" fill-opacity="0.5" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></g></g></g><g transform="matrix(0.75,0,0,0.75,10,10)" id="i2"><g id="i1" transform="matrix(1.47,0,0,1.47,0,0)"><path fill="' . esc_attr( $color ) . '" fill-opacity="0.59" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></svg>';
	}

	protected function get_warning_svg( $color = 'currentColor' ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="' . esc_attr( $color ) . '" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w3opcfe-warning-svg singlegrid-pdp-warning-svg"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$layout = ! empty( $settings['layout'] ) ? $settings['layout'] : 'inline';
		$wrapper_classes = 'w3opcfe-scarcity-wrapper singlegrid-pdp-scarcity-wrapper';
		if ( 'bar' === $layout ) {
			$wrapper_classes .= ' w3opcfe-scarcity-has-bar singlegrid-pdp-scarcity-has-bar';
		}

		$this->add_render_attribute( 'wrapper', 'class', $wrapper_classes );
		$this->add_render_attribute( 'wrapper', 'data-min', esc_attr( $settings['min_val'] ) );
		$this->add_render_attribute( 'wrapper', 'data-max', esc_attr( $settings['max_val'] ) );
		$this->add_render_attribute( 'wrapper', 'data-decrement-mode', esc_attr( $settings['decrement_mode'] ) );
		$this->add_render_attribute( 'wrapper', 'data-decrement-step', esc_attr( $settings['decrement_step'] ) );
		$this->add_render_attribute( 'wrapper', 'data-reset-at-min', esc_attr( $settings['reset_at_min'] ) );
		$this->add_render_attribute( 'wrapper', 'data-bangla-numbers', esc_attr( $settings['bangla_numbers'] ) );
		$this->add_render_attribute( 'wrapper', 'data-count-up', esc_attr( $settings['counting_up'] ) );
		$this->add_render_attribute( 'wrapper', 'data-widget-id', esc_attr( $this->get_id() ) );
		$this->add_render_attribute( 'wrapper', 'data-layout', esc_attr( $layout ) );

		$bar_max = ! empty( $settings['bar_max'] ) ? absint( $settings['bar_max'] ) : absint( $settings['max_val'] );
		if ( $bar_max <= 0 ) {
			$bar_max = 100;
		}
		$bar_calc = ! empty( $settings['bar_calculation'] ) ? $settings['bar_calculation'] : 'remaining';
		$this->add_render_attribute( 'wrapper', 'data-bar-max', esc_attr( $bar_max ) );
		$this->add_render_attribute( 'wrapper', 'data-bar-calc', esc_attr( $bar_calc ) );

		$icon_type      = $settings['icon_type'];
		$blinker_color  = ! empty( $settings['blinker_color'] ) ? $settings['blinker_color'] : '#ff0d00';
		$use_bangla     = 'yes' === $settings['bangla_numbers'];
		$count_up       = 'yes' === $settings['counting_up'];
		$base_number    = $count_up ? $settings['min_val'] : $settings['max_val'];
		$display_number = $use_bangla ? $this->convert_to_bangla_number( $base_number ) : $base_number;

		$initial_percent = 50;
		if ( 'direct_percent' === $bar_calc ) {
			$initial_percent = max( 0, min( 100, (int) $base_number ) );
		} elseif ( 'depleted' === $bar_calc ) {
			$initial_percent = max( 0, min( 100, round( ( ( $bar_max - $base_number ) / $bar_max ) * 100 ) ) );
		} else {
			$initial_percent = max( 0, min( 100, round( ( $base_number / $bar_max ) * 100 ) ) );
		}
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			<div class="w3opcfe-scarcity-header singlegrid-pdp-scarcity-header">
				<?php if ( 'blinker' === $icon_type ) : ?>
					<div class="w3opcfe-scarcity-blinker singlegrid-pdp-scarcity-blinker">
						<?php
						echo wp_kses(
							$this->get_blinker_svg( $blinker_color ),
							[
								'svg'              => [
									'xmlns'   => [],
									'fill'    => [],
									'width'   => [],
									'height'  => [],
									'viewbox' => [],
								],
								'g'                => [
									'id'        => [],
									'transform' => [],
								],
								'path'             => [
									'fill'         => [],
									'fill-opacity' => [],
									'd'            => [],
								],
								'animatetransform' => [
									'repeatcount'   => [],
									'type'          => [],
									'attributename' => [],
									'dur'           => [],
									'begin'         => [],
									'calcmode'      => [],
									'values'        => [],
									'keytimes'      => [],
									'keysplines'    => [],
									'fill'          => [],
								],
							]
						);
						?>
					</div>
				<?php elseif ( 'warning' === $icon_type ) : ?>
					<div class="w3opcfe-scarcity-icon w3opcfe-scarcity-warning-icon singlegrid-pdp-scarcity-icon">
						<?php
						echo wp_kses(
							$this->get_warning_svg( ! empty( $settings['custom_icon_color'] ) ? $settings['custom_icon_color'] : 'currentColor' ),
							[
								'svg'  => [
									'xmlns'           => [],
									'viewbox'         => [],
									'width'           => [],
									'height'          => [],
									'fill'            => [],
									'stroke'          => [],
									'stroke-width'    => [],
									'stroke-linecap'  => [],
									'stroke-linejoin' => [],
									'class'           => [],
								],
								'path' => [
									'd' => [],
								],
								'line' => [
									'x1' => [],
									'y1' => [],
									'x2' => [],
									'y2' => [],
								],
							]
						);
						?>
					</div>
				<?php elseif ( 'custom' === $icon_type && ! empty( $settings['custom_icon']['value'] ) ) : ?>
					<div class="w3opcfe-scarcity-icon singlegrid-pdp-scarcity-icon">
						<?php Icons_Manager::render_icon( $settings['custom_icon'], [ 'aria-hidden' => 'true' ] ); ?>
					</div>
				<?php endif; ?>

				<div class="w3opcfe-scarcity-text singlegrid-pdp-scarcity-text">
					<?php if ( ! empty( $settings['prefix_text'] ) ) : ?>
						<span class="w3opcfe-scarcity-prefix singlegrid-pdp-scarcity-prefix"><?php echo esc_html( $settings['prefix_text'] ); ?></span>
					<?php endif; ?>
					<span class="w3opcfe-scarcity-number singlegrid-pdp-scarcity-number"><?php echo esc_html( $display_number ); ?></span>
					<?php if ( ! empty( $settings['suffix_text'] ) ) : ?>
						<span class="w3opcfe-scarcity-suffix singlegrid-pdp-scarcity-suffix"><?php echo esc_html( $settings['suffix_text'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( 'bar' === $layout ) : ?>
				<div class="w3opcfe-scarcity-progress-bar singlegrid-pdp-scarcity-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $initial_percent ); ?>">
					<div class="w3opcfe-scarcity-progress-fill singlegrid-pdp-scarcity-progress-fill" style="width: <?php echo esc_attr( $initial_percent ); ?>%;"></div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var blinker_color = settings.blinker_color ? settings.blinker_color : '#ff0d00';
		var layout = settings.layout ? settings.layout : 'inline';
		var isBar = ( layout === 'bar' );
		var wrapperClasses = 'w3opcfe-scarcity-wrapper singlegrid-pdp-scarcity-wrapper' + ( isBar ? ' w3opcfe-scarcity-has-bar singlegrid-pdp-scarcity-has-bar' : '' );
		var baseNum = ( settings.counting_up === 'yes' ) ? settings.min_val : settings.max_val;
		var barMax = settings.bar_max ? parseInt( settings.bar_max, 10 ) : parseInt( settings.max_val, 10 );
		if ( ! barMax || barMax <= 0 ) {
			barMax = 100;
		}
		var initialPercent = Math.max( 0, Math.min( 100, Math.round( ( parseInt( baseNum, 10 ) / barMax ) * 100 ) ) );
		#>
		<div class="{{ wrapperClasses }}" 
			data-min="{{ settings.min_val }}" 
			data-max="{{ settings.max_val }}" 
			data-decrement-mode="{{ settings.decrement_mode }}"
			data-decrement-step="{{ settings.decrement_step }}"
			data-reset-at-min="{{ settings.reset_at_min }}"
			data-bangla-numbers="{{ settings.bangla_numbers }}"
			data-count-up="{{ settings.counting_up }}"
			data-layout="{{ layout }}"
			data-bar-max="{{ barMax }}"
			data-bar-calc="{{ settings.bar_calculation || 'remaining' }}"
			data-widget-id="editor-preview">
			
			<div class="w3opcfe-scarcity-header singlegrid-pdp-scarcity-header">
				<# if ( settings.icon_type === 'blinker' ) { #>
					<div class="w3opcfe-scarcity-blinker singlegrid-pdp-scarcity-blinker">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="100%" height="100%" viewBox="0 0 20 20"><g id="i0"><g transform="translate(10,10)"><g transform="scale(1.25,1.25)"><animateTransform repeatCount="indefinite" type="scale" attributeName="transform" dur="1s" begin="0s" calcMode="spline" values="1.25 1.25; 1.5 1.5; 1.25 1.25" keyTimes="0; 0.5; 1" keySplines="0.333 0 0.667 1; 0.333 0 0.667 1" fill="freeze"/><g transform="translate(-0.265,-0.709)"><g id="i1" transform="matrix(1.47,0,0,1.47,0.265,0.709)"><path fill="{{ blinker_color }}" fill-opacity="0.5" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></g></g></g><g transform="matrix(0.75,0,0,0.75,10,10)" id="i2"><g id="i1" transform="matrix(1.47,0,0,1.47,0,0)"><path fill="{{ blinker_color }}" fill-opacity="0.59" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></svg>
					</div>
				<# } else if ( settings.icon_type === 'warning' ) { #>
					<div class="w3opcfe-scarcity-icon w3opcfe-scarcity-warning-icon singlegrid-pdp-scarcity-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1em" height="1em" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="w3opcfe-warning-svg singlegrid-pdp-warning-svg"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
					</div>
				<# } else if ( settings.icon_type === 'custom' && settings.custom_icon && settings.custom_icon.value ) { 
					var iconHTML = elementor.helpers.renderIcon( view, settings.custom_icon, { 'aria-hidden': true }, 'i' , 'object' ); 
				#>
					<div class="w3opcfe-scarcity-icon singlegrid-pdp-scarcity-icon">
						{{{ iconHTML.value }}}
					</div>
				<# } #>

				<div class="w3opcfe-scarcity-text singlegrid-pdp-scarcity-text">
					<# if ( settings.prefix_text ) { #>
						<span class="w3opcfe-scarcity-prefix singlegrid-pdp-scarcity-prefix">{{ settings.prefix_text }}</span>
					<# } #>
					<span class="w3opcfe-scarcity-number singlegrid-pdp-scarcity-number">{{ baseNum }}</span>
					<# if ( settings.suffix_text ) { #>
						<span class="w3opcfe-scarcity-suffix singlegrid-pdp-scarcity-suffix">{{ settings.suffix_text }}</span>
					<# } #>
				</div>
			</div>

			<# if ( isBar ) { #>
				<div class="w3opcfe-scarcity-progress-bar singlegrid-pdp-scarcity-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ initialPercent }}">
					<div class="w3opcfe-scarcity-progress-fill singlegrid-pdp-scarcity-progress-fill" style="width: {{ initialPercent }}%;"></div>
				</div>
			<# } #>
		</div>
		<?php
	}
}

class W3OPCFE_Singlegrid_Scarcity_Widget extends W3OPCFE_Scarcity_Widget {
	public function get_name() {
		return 'singlegrid-pdp-product-scarcity';
	}

	public function get_title() {
		return esc_html__( 'Product Scarcity (Legacy)', 'on-page-checkout-for-elementor' );
	}

	public function show_in_panel() {
		return false;
	}
}
