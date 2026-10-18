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
	}

	protected function convert_to_bangla_number( $number ) {
		$eng = [ '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ];
		$bng = [ '০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯' ];
		return str_replace( $eng, $bng, (string) $number );
	}

	protected function get_blinker_svg( $color = '#ff0d00' ) {
		return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="100%" height="100%" viewBox="0 0 20 20"><g id="i0"><g transform="translate(10,10)"><g transform="scale(1.25,1.25)"><animateTransform repeatCount="indefinite" type="scale" attributeName="transform" dur="1s" begin="0s" calcMode="spline" values="1.25 1.25; 1.5 1.5; 1.25 1.25" keyTimes="0; 0.5; 1" keySplines="0.333 0 0.667 1; 0.333 0 0.667 1" fill="freeze"/><g transform="translate(-0.265,-0.709)"><g id="i1" transform="matrix(1.47,0,0,1.47,0.265,0.709)"><path fill="' . esc_attr( $color ) . '" fill-opacity="0.5" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></g></g></g><g transform="matrix(0.75,0,0,0.75,10,10)" id="i2"><g id="i1" transform="matrix(1.47,0,0,1.47,0,0)"><path fill="' . esc_attr( $color ) . '" fill-opacity="0.59" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></svg>';
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		$this->add_render_attribute( 'wrapper', 'class', 'w3opcfe-scarcity-wrapper singlegrid-pdp-scarcity-wrapper' );
		$this->add_render_attribute( 'wrapper', 'data-min', esc_attr( $settings['min_val'] ) );
		$this->add_render_attribute( 'wrapper', 'data-max', esc_attr( $settings['max_val'] ) );
		$this->add_render_attribute( 'wrapper', 'data-decrement-mode', esc_attr( $settings['decrement_mode'] ) );
		$this->add_render_attribute( 'wrapper', 'data-decrement-step', esc_attr( $settings['decrement_step'] ) );
		$this->add_render_attribute( 'wrapper', 'data-reset-at-min', esc_attr( $settings['reset_at_min'] ) );
		$this->add_render_attribute( 'wrapper', 'data-bangla-numbers', esc_attr( $settings['bangla_numbers'] ) );
		$this->add_render_attribute( 'wrapper', 'data-count-up', esc_attr( $settings['counting_up'] ) );
		$this->add_render_attribute( 'wrapper', 'data-widget-id', esc_attr( $this->get_id() ) );

		$icon_type      = $settings['icon_type'];
		$blinker_color  = ! empty( $settings['blinker_color'] ) ? $settings['blinker_color'] : '#ff0d00';
		$use_bangla     = 'yes' === $settings['bangla_numbers'];
		$count_up       = 'yes' === $settings['counting_up'];
		$base_number    = $count_up ? $settings['min_val'] : $settings['max_val'];
		$display_number = $use_bangla ? $this->convert_to_bangla_number( $base_number ) : $base_number;
		?>
		<div <?php $this->print_render_attribute_string( 'wrapper' ); ?>>
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
			<?php elseif ( 'custom' === $icon_type && ! empty( $settings['custom_icon']['value'] ) ) : ?>
				<div class="w3opcfe-scarcity-icon singlegrid-pdp-scarcity-icon">
					<?php Icons_Manager::render_icon( $settings['custom_icon'], [ 'aria-hidden' => 'true' ] ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $settings['prefix_text'] ) ) : ?>
				<span class="w3opcfe-scarcity-prefix singlegrid-pdp-scarcity-prefix"><?php echo esc_html( $settings['prefix_text'] ); ?></span>
			<?php endif; ?>
			<span class="w3opcfe-scarcity-number singlegrid-pdp-scarcity-number"><?php echo esc_html( $display_number ); ?></span>
			<?php if ( ! empty( $settings['suffix_text'] ) ) : ?>
				<span class="w3opcfe-scarcity-suffix singlegrid-pdp-scarcity-suffix"><?php echo esc_html( $settings['suffix_text'] ); ?></span>
			<?php endif; ?>
		</div>
		<?php
	}

	protected function content_template() {
		?>
		<#
		var blinker_color = settings.blinker_color ? settings.blinker_color : '#ff0d00';
		#>
		<div class="w3opcfe-scarcity-wrapper singlegrid-pdp-scarcity-wrapper" 
			data-min="{{ settings.min_val }}" 
			data-max="{{ settings.max_val }}" 
			data-decrement-mode="{{ settings.decrement_mode }}"
			data-decrement-step="{{ settings.decrement_step }}"
			data-reset-at-min="{{ settings.reset_at_min }}"
			data-bangla-numbers="{{ settings.bangla_numbers }}"
			data-count-up="{{ settings.counting_up }}"
			data-widget-id="editor-preview">
			
			<# if ( settings.icon_type === 'blinker' ) { 
				var blinker_color = settings.blinker_color ? settings.blinker_color : '#ff0d00';
			#>
				<div class="w3opcfe-scarcity-blinker singlegrid-pdp-scarcity-blinker">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="100%" height="100%" viewBox="0 0 20 20"><g id="i0"><g transform="translate(10,10)"><g transform="scale(1.25,1.25)"><animateTransform repeatCount="indefinite" type="scale" attributeName="transform" dur="1s" begin="0s" calcMode="spline" values="1.25 1.25; 1.5 1.5; 1.25 1.25" keyTimes="0; 0.5; 1" keySplines="0.333 0 0.667 1; 0.333 0 0.667 1" fill="freeze"/><g transform="translate(-0.265,-0.709)"><g id="i1" transform="matrix(1.47,0,0,1.47,0.265,0.709)"><path fill="{{ blinker_color }}" fill-opacity="0.5" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></g></g></g><g transform="matrix(0.75,0,0,0.75,10,10)" id="i2"><g id="i1" transform="matrix(1.47,0,0,1.47,0,0)"><path fill="{{ blinker_color }}" fill-opacity="0.59" d="M0,-4.344C-2.399,-4.344,-4.344,-2.399,-4.344,0C-4.344,2.399,-2.399,4.344,0,4.344C2.399,4.344,4.344,2.399,4.344,0C4.344,-2.399,2.399,-4.344,0,-4.344Z"/></g></g></svg>
				</div>
			<# } else if ( settings.icon_type === 'custom' && settings.custom_icon && settings.custom_icon.value ) { 
				var iconHTML = elementor.helpers.renderIcon( view, settings.custom_icon, { 'aria-hidden': true }, 'i' , 'object' ); 
			#>
				<div class="w3opcfe-scarcity-icon singlegrid-pdp-scarcity-icon">
					{{{ iconHTML.value }}}
				</div>
			<# } #>

			<# if ( settings.prefix_text ) { #>
				<span class="w3opcfe-scarcity-prefix singlegrid-pdp-scarcity-prefix">{{ settings.prefix_text }}</span>
			<# } #>
			<span class="w3opcfe-scarcity-number singlegrid-pdp-scarcity-number">
				<# if ( settings.counting_up === 'yes' ) { #>
					{{ settings.min_val }}
				<# } else { #>
					{{ settings.max_val }}
				<# } #>
			</span>
			<# if ( settings.suffix_text ) { #>
				<span class="w3opcfe-scarcity-suffix singlegrid-pdp-scarcity-suffix">{{ settings.suffix_text }}</span>
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
