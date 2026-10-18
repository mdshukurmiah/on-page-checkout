<?php
/**
 * Product Countdown Widget
 *
 * @package On_Page_Checkout_For_Elementor
 */

namespace W3OPCFE\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class W3OPCFE_Countdown_Widget extends Widget_Base {

	public function get_name() {
		return 'w3opcfe-countdown';
	}

	public function get_title() {
		return esc_html__( 'Product Countdown', 'on-page-checkout-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-countdown';
	}

	public function get_categories() {
		return [ 'on-page-checkout', 'general' ];
	}

	public function get_keywords() {
		return [ 'countdown', 'timer', 'product', 'deal', 'urgency', 'sale', 'singlegrid' ];
	}

	public function get_script_depends() {
		return [ 'w3opcfe-countdown-script' ];
	}

	public function get_style_depends() {
		return [ 'w3opcfe-countdown-style' ];
	}

	protected function register_controls() {
		// Content Section: Timer Setup
		$this->start_controls_section(
			'section_timer',
			[
				'label' => esc_html__( 'Countdown Timer', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'due_date',
			[
				'label'       => esc_html__( 'Target Date & Time', 'on-page-checkout-for-elementor' ),
				'type'        => Controls_Manager::DATE_TIME,
				'default'     => gmdate( 'Y-m-d H:i', strtotime( '+7 days' ) ),
				'description' => esc_html__( 'Choose date and time to count down to.', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'auto_reset',
			[
				'label'        => esc_html__( 'Auto Reset Timer', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'On', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'Off', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'separator'    => 'before',
				'description'  => esc_html__( 'Automatically recalculate a target date when the timer ends.', 'on-page-checkout-for-elementor' ),
			]
		);

		$this->add_control(
			'reset_type',
			[
				'label'     => esc_html__( 'Reset Mode', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'interval' => esc_html__( 'Add Interval Days', 'on-page-checkout-for-elementor' ),
					'daily'    => esc_html__( 'Daily at Specific Time', 'on-page-checkout-for-elementor' ),
				],
				'default'   => 'interval',
				'condition' => [
					'auto_reset' => 'yes',
				],
			]
		);

		$this->add_control(
			'reset_interval_days',
			[
				'label'     => esc_html__( 'Days to Add', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 365,
				'step'      => 1,
				'default'   => 1,
				'condition' => [
					'auto_reset' => 'yes',
					'reset_type' => 'interval',
				],
			]
		);

		$this->add_control(
			'reset_daily_time',
			[
				'label'       => esc_html__( 'Daily Reset Time', 'on-page-checkout-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '00:00',
				'placeholder' => 'HH:MM (e.g. 18:30)',
				'description' => esc_html__( 'Enter the 24-hour format time for the timer to reset to daily.', 'on-page-checkout-for-elementor' ),
				'condition'   => [
					'auto_reset' => 'yes',
					'reset_type' => 'daily',
				],
			]
		);

		$this->end_controls_section();

		// Content Section: Display Settings
		$this->start_controls_section(
			'section_display',
			[
				'label' => esc_html__( 'Display Settings', 'on-page-checkout-for-elementor' ),
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
			'show_days',
			[
				'label'        => esc_html__( 'Days', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_hours',
			[
				'label'        => esc_html__( 'Hours', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_minutes',
			[
				'label'        => esc_html__( 'Minutes', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'show_seconds',
			[
				'label'        => esc_html__( 'Seconds', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'Hide', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'label_heading',
			[
				'label'     => esc_html__( 'Renaming Custom Labels', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'label_days',
			[
				'label'     => esc_html__( 'Days Label', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Days', 'on-page-checkout-for-elementor' ),
				'condition' => [
					'show_days' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_hours',
			[
				'label'     => esc_html__( 'Hours Label', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Hours', 'on-page-checkout-for-elementor' ),
				'condition' => [
					'show_hours' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_minutes',
			[
				'label'     => esc_html__( 'Minutes Label', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Minutes', 'on-page-checkout-for-elementor' ),
				'condition' => [
					'show_minutes' => 'yes',
				],
			]
		);

		$this->add_control(
			'label_seconds',
			[
				'label'     => esc_html__( 'Seconds Label', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'Seconds', 'on-page-checkout-for-elementor' ),
				'condition' => [
					'show_seconds' => 'yes',
				],
			]
		);

		$this->add_control(
			'separator_heading',
			[
				'label'     => esc_html__( 'Separators', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);

		$this->add_control(
			'show_separator',
			[
				'label'        => esc_html__( 'Show Separator', 'on-page-checkout-for-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'on-page-checkout-for-elementor' ),
				'label_off'    => esc_html__( 'No', 'on-page-checkout-for-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'separator_text',
			[
				'label'     => esc_html__( 'Custom Separator', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => ':',
				'condition' => [
					'show_separator' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		// Style Section: General Layout
		$this->start_controls_section(
			'section_layout_style',
			[
				'label' => esc_html__( 'Layout & Container', 'on-page-checkout-for-elementor' ),
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
					'{{WRAPPER}} .w3opcfe-countdown-wrapper, {{WRAPPER}} .singlegrid-pdp-countdown-wrapper' => 'justify-content: {{VALUE}};',
				],
				'default'   => 'center',
			]
		);

		$this->add_responsive_control(
			'gap',
			[
				'label'      => esc_html__( 'Gap Between Items (px)', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [ 'min' => 0, 'max' => 100, 'step' => 1 ],
				],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-countdown-wrapper, {{WRAPPER}} .singlegrid-pdp-countdown-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'default'    => [
					'size' => 15,
					'unit' => 'px',
				],
			]
		);

		$this->end_controls_section();

		// Style Section: Single Box Settings
		$this->start_controls_section(
			'section_box_style',
			[
				'label' => esc_html__( 'Countdown Box', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'box_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-countdown-box, {{WRAPPER}} .singlegrid-pdp-countdown-box' => 'background-color: {{VALUE}};',
				],
				'default'   => '#f8fafc',
			]
		);

		$this->add_responsive_control(
			'box_padding',
			[
				'label'      => esc_html__( 'Padding', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-countdown-box, {{WRAPPER}} .singlegrid-pdp-countdown-box' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default'    => [
					'top'      => '15',
					'right'    => '20',
					'bottom'   => '15',
					'left'     => '20',
					'unit'     => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'box_border',
				'selector' => '{{WRAPPER}} .w3opcfe-countdown-box, {{WRAPPER}} .singlegrid-pdp-countdown-box',
			]
		);

		$this->add_responsive_control(
			'box_border_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'on-page-checkout-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .w3opcfe-countdown-box, {{WRAPPER}} .singlegrid-pdp-countdown-box' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'default'    => [
					'top'      => '8',
					'right'    => '8',
					'bottom'   => '8',
					'left'     => '8',
					'unit'     => 'px',
					'isLinked' => true,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'box_shadow',
				'selector' => '{{WRAPPER}} .w3opcfe-countdown-box, {{WRAPPER}} .singlegrid-pdp-countdown-box',
			]
		);

		$this->end_controls_section();

		// Style Section: Digits Style
		$this->start_controls_section(
			'section_digits_style',
			[
				'label' => esc_html__( 'Digits', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'digits_color',
			[
				'label'     => esc_html__( 'Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-countdown-digit, {{WRAPPER}} .singlegrid-pdp-countdown-digit' => 'color: {{VALUE}};',
				],
				'default'   => '#0f172a',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'digits_typography',
				'selector' => '{{WRAPPER}} .w3opcfe-countdown-digit, {{WRAPPER}} .singlegrid-pdp-countdown-digit',
			]
		);

		$this->end_controls_section();

		// Style Section: Labels Style
		$this->start_controls_section(
			'section_labels_style',
			[
				'label' => esc_html__( 'Labels', 'on-page-checkout-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'labels_color',
			[
				'label'     => esc_html__( 'Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-countdown-label, {{WRAPPER}} .singlegrid-pdp-countdown-label' => 'color: {{VALUE}};',
				],
				'default'   => '#64748b',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'labels_typography',
				'selector' => '{{WRAPPER}} .w3opcfe-countdown-label, {{WRAPPER}} .singlegrid-pdp-countdown-label',
			]
		);

		$this->end_controls_section();

		// Style Section: Separator Style
		$this->start_controls_section(
			'section_separator_style',
			[
				'label'     => esc_html__( 'Separator', 'on-page-checkout-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_separator' => 'yes',
				],
			]
		);

		$this->add_control(
			'separator_color',
			[
				'label'     => esc_html__( 'Color', 'on-page-checkout-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .w3opcfe-countdown-separator, {{WRAPPER}} .singlegrid-pdp-countdown-separator' => 'color: {{VALUE}};',
				],
				'default'   => '#0f172a',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'separator_typography',
				'selector' => '{{WRAPPER}} .w3opcfe-countdown-separator, {{WRAPPER}} .singlegrid-pdp-countdown-separator',
			]
		);

		$this->end_controls_section();
	}

	protected function convert_to_bangla_number( $number ) {
		$eng = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];
		$bng = [ '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ];
		return str_replace( $eng, $bng, (string) $number );
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'w3opcfe-countdown-wrapper singlegrid-pdp-countdown-wrapper' );
		$this->add_render_attribute( 'wrapper', 'data-due-date', esc_attr( $settings['due_date'] ) );
		$this->add_render_attribute( 'wrapper', 'data-auto-reset', esc_attr( $settings['auto_reset'] ) );
		$this->add_render_attribute( 'wrapper', 'data-reset-type', esc_attr( $settings['reset_type'] ) );
		$this->add_render_attribute( 'wrapper', 'data-reset-interval', esc_attr( $settings['reset_interval_days'] ) );
		$this->add_render_attribute( 'wrapper', 'data-reset-time', esc_attr( $settings['reset_daily_time'] ) );
		$this->add_render_attribute( 'wrapper', 'data-bangla-numbers', esc_attr( $settings['bangla_numbers'] ) );

		$show_days     = 'yes' === $settings['show_days'];
		$show_hours    = 'yes' === $settings['show_hours'];
		$show_mins     = 'yes' === $settings['show_minutes'];
		$show_secs     = 'yes' === $settings['show_seconds'];
		$show_sep      = 'yes' === $settings['show_separator'];
		$sep_text      = esc_html( $settings['separator_text'] );
		$use_bangla    = 'yes' === $settings['bangla_numbers'];
		$default_digit = $use_bangla ? $this->convert_to_bangla_number( '00' ) : '00';
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
			
			<?php if ( $show_days ) : ?>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-days singlegrid-pdp-countdown-days">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-days-digit singlegrid-pdp-days-digit"><?php echo esc_html( $default_digit ); ?></span>
					<?php if ( ! empty( $settings['label_days'] ) ) : ?>
						<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label"><?php echo esc_html( $settings['label_days'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $show_sep && ( $show_hours || $show_mins || $show_secs ) ) : ?>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator"><?php echo esc_html( $sep_text ); ?></span>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $show_hours ) : ?>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-hours singlegrid-pdp-countdown-hours">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-hours-digit singlegrid-pdp-hours-digit"><?php echo esc_html( $default_digit ); ?></span>
					<?php if ( ! empty( $settings['label_hours'] ) ) : ?>
						<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label"><?php echo esc_html( $settings['label_hours'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $show_sep && ( $show_mins || $show_secs ) ) : ?>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator"><?php echo esc_html( $sep_text ); ?></span>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $show_mins ) : ?>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-minutes singlegrid-pdp-countdown-minutes">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-minutes-digit singlegrid-pdp-minutes-digit"><?php echo esc_html( $default_digit ); ?></span>
					<?php if ( ! empty( $settings['label_minutes'] ) ) : ?>
						<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label"><?php echo esc_html( $settings['label_minutes'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $show_sep && $show_secs ) : ?>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator"><?php echo esc_html( $sep_text ); ?></span>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $show_secs ) : ?>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-seconds singlegrid-pdp-countdown-seconds">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-seconds-digit singlegrid-pdp-seconds-digit"><?php echo esc_html( $default_digit ); ?></span>
					<?php if ( ! empty( $settings['label_seconds'] ) ) : ?>
						<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label"><?php echo esc_html( $settings['label_seconds'] ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

		</div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var show_days  = 'yes' === settings.show_days;
		var show_hours = 'yes' === settings.show_hours;
		var show_mins  = 'yes' === settings.show_minutes;
		var show_secs  = 'yes' === settings.show_seconds;
		var show_sep   = 'yes' === settings.show_separator;
		var sep_text   = settings.separator_text;
		#>
		<div class="w3opcfe-countdown-wrapper singlegrid-pdp-countdown-wrapper" 
			data-due-date="{{ settings.due_date }}"
			data-auto-reset="{{ settings.auto_reset }}"
			data-reset-type="{{ settings.reset_type }}"
			data-reset-interval="{{ settings.reset_interval_days }}"
			data-reset-time="{{ settings.reset_daily_time }}"
			data-bangla-numbers="{{ settings.bangla_numbers }}">

			<# if ( show_days ) { #>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-days singlegrid-pdp-countdown-days">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-days-digit singlegrid-pdp-days-digit">00</span>
					<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label">{{ settings.label_days }}</span>
				</div>
				<# if ( show_sep && ( show_hours || show_mins || show_secs ) ) { #>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator">{{ sep_text }}</span>
				<# } #>
			<# } #>

			<# if ( show_hours ) { #>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-hours singlegrid-pdp-countdown-hours">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-hours-digit singlegrid-pdp-hours-digit">00</span>
					<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label">{{ settings.label_hours }}</span>
				</div>
				<# if ( show_sep && ( show_mins || show_secs ) ) { #>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator">{{ sep_text }}</span>
				<# } #>
			<# } #>

			<# if ( show_mins ) { #>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-minutes singlegrid-pdp-countdown-minutes">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-minutes-digit singlegrid-pdp-minutes-digit">00</span>
					<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label">{{ settings.label_minutes }}</span>
				</div>
				<# if ( show_sep && show_secs ) { #>
					<span class="w3opcfe-countdown-separator singlegrid-pdp-countdown-separator">{{ sep_text }}</span>
				<# } #>
			<# } #>

			<# if ( show_secs ) { #>
				<div class="w3opcfe-countdown-box singlegrid-pdp-countdown-box w3opcfe-countdown-seconds singlegrid-pdp-countdown-seconds">
					<span class="w3opcfe-countdown-digit singlegrid-pdp-countdown-digit w3opcfe-seconds-digit singlegrid-pdp-seconds-digit">00</span>
					<span class="w3opcfe-countdown-label singlegrid-pdp-countdown-label">{{ settings.label_seconds }}</span>
				</div>
			<# } #>

		</div>
		<?php
	}
}

class W3OPCFE_Singlegrid_Countdown_Widget extends W3OPCFE_Countdown_Widget {
	public function get_name() {
		return 'singlegrid-pdp-product-countdown';
	}

	public function get_title() {
		return esc_html__( 'Product Countdown (Legacy)', 'on-page-checkout-for-elementor' );
	}

	public function show_in_panel() {
		return false;
	}
}
