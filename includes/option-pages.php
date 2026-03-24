<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * One-time migration from legacy `w3_opc_*` option keys to `w3opcfe_*`.
 */
function w3opcfe_migrate_options() {
    $migrations = [
        'w3_opc_general_options'        => 'w3opcfe_general_options',
        'w3_opc_checkout_fields'        => 'w3opcfe_checkout_fields',
        'w3_opc_checkout_fields_master' => 'w3opcfe_checkout_fields_master',
    ];

    foreach ( $migrations as $old_key => $new_key ) {
        if ( false !== get_option( $old_key, false ) && false === get_option( $new_key, false ) ) {
            update_option( $new_key, get_option( $old_key ) );
        }
    }
}

w3opcfe_migrate_options();

add_action( 'admin_menu', 'w3opcfe_register_settings_pages' );

function w3opcfe_register_settings_pages() {
    // Top-level/default page: Checkout Fields Edit
    add_menu_page(
        __( 'Checkout Fields Edit', 'on-page-checkout-for-elementor' ),
        __( 'W3 OPC', 'on-page-checkout-for-elementor' ),
        'manage_options',
        'w3opcfe_checkout_fields_edit',
        'w3opcfe_checkout_fields_edit_page',
        'dashicons-admin-generic',
        60
    );
}

function w3opcfe_general_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'W3 OPC General Settings', 'on-page-checkout-for-elementor' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'w3opcfe_general_settings_group' );
            do_settings_sections( 'w3opcfe_general_settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function w3opcfe_checkout_fields_edit_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'W3 OPC Checkout Fields Edit', 'on-page-checkout-for-elementor' ); ?></h1>
        <?php settings_errors( 'w3opcfe_checkout_fields_group' ); ?>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'w3opcfe_checkout_fields_group' );
            do_settings_sections( 'w3opcfe-checkout-fields' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init', 'w3opcfe_register_settings' );

add_action( 'admin_init', 'w3opcfe_maybe_show_saved_notice' );

function w3opcfe_maybe_show_saved_notice() {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen ) {
        return;
    }
    $opc_screens = [ 'toplevel_page_w3opcfe_checkout_fields_edit' ];
    if ( ! in_array( $screen->id, $opc_screens, true ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $settings_updated = isset( $_GET['settings-updated'] ) ? sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) : '';
    $nonce            = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

    if ( '1' === $settings_updated && ! empty( $nonce ) && wp_verify_nonce( $nonce, 'w3opcfe_checkout_fields_group-options' ) ) {
        add_settings_error(
            'w3opcfe_checkout_fields_group',
            'w3opcfe_settings_saved',
            __( 'Checkout fields settings saved successfully!', 'on-page-checkout-for-elementor' ),
            'updated'
        );
    }
}

function w3opcfe_register_settings() {
    // General Settings
    register_setting(
        'w3opcfe_general_settings_group',
        'w3opcfe_general_options',
        [
            'sanitize_callback' => 'w3opcfe_sanitize_general_options',
            'type'              => 'array',
            'default'           => [],
        ]
    );

    add_settings_section(
        'w3opcfe_general_section',
        __( 'General Options', 'on-page-checkout-for-elementor' ),
        function () {
            echo '<p>' . esc_html__( 'Set up your general plugin options here.', 'on-page-checkout-for-elementor' ) . '</p>';
        },
        'w3opcfe_general_settings'
    );

    add_settings_field(
        'enable_opc',
        __( 'Enable One Page Checkout', 'on-page-checkout-for-elementor' ),
        function () {
            $options = get_option( 'w3opcfe_general_options' );
            ?>
            <input type="checkbox" name="w3opcfe_general_options[enable_opc]" value="1" <?php checked( 1, $options['enable_opc'] ?? 0 ); ?> />
            <?php
        },
        'w3opcfe_general_settings',
        'w3opcfe_general_section'
    );
}

function w3opcfe_sanitize_general_options( $input ) {
    if ( ! is_array( $input ) ) {
        return [];
    }

    return [
        'enable_opc' => ! empty( $input['enable_opc'] ) ? 1 : 0,
    ];
}

class W3OPCFE_Checkout_Fields_Settings {

    private $option_name = 'w3opcfe_checkout_fields';

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_filter( 'woocommerce_checkout_fields', [ $this, 'modify_checkout_fields' ] );
    }

    public function register_settings() {
        register_setting(
            'w3opcfe_checkout_fields_group',
            $this->option_name,
            [
                'sanitize_callback' => [ $this, 'sanitize_fields' ],
                'type'              => 'array',
                'default'           => [],
            ]
        ); 

        register_setting(
            'w3opcfe_checkout_fields_group',
            'w3opcfe_checkout_fields_master',
            [
                'type'              => 'boolean',
                'sanitize_callback' => function ( $val ) {
                    return ! empty( $val ) ? 1 : 0;
                },
                'default'           => 1,
            ]
        );

        add_settings_section(
            'w3opcfe_checkout_fields_billing_section',
            __( 'Billing Fields Settings', 'on-page-checkout-for-elementor' ),
            '__return_false',
            'w3opcfe-checkout-fields'
        );

        add_settings_section(
            'w3opcfe_checkout_fields_shipping_section',
            __( 'Shipping Fields Settings', 'on-page-checkout-for-elementor' ),
            '__return_false',
            'w3opcfe-checkout-fields'
        );

        add_settings_section(
            'w3opcfe_checkout_fields_other_section',
            __( 'Other Settings', 'on-page-checkout-for-elementor' ),
            '__return_false',
            'w3opcfe-checkout-fields'
        );

        $other_fields = [
            'order_notes' => [ 'label' => __( 'Order note', 'on-page-checkout-for-elementor' ) ],
        ];

        $checkout        = WC()->checkout();
        $billing_fields  = $checkout ? $checkout->get_checkout_fields( 'billing' ) : [];
        $shipping_fields = $checkout ? $checkout->get_checkout_fields( 'shipping' ) : [];

        foreach ( $billing_fields as $field_key => $field ) {
            add_settings_field(
                "billing_{$field_key}",
                isset( $field['label'] ) ? $field['label'] : $field_key,
                [ $this, 'render_field' ],
                'w3opcfe-checkout-fields',
                'w3opcfe_checkout_fields_billing_section',
                [ 'field_key' => "billing_$field_key", 'field' => $field ]
            );
        }

        foreach ( $shipping_fields as $field_key => $field ) {
            add_settings_field(
                "shipping_{$field_key}",
                isset( $field['label'] ) ? $field['label'] : $field_key,
                [ $this, 'render_field' ],
                'w3opcfe-checkout-fields',
                'w3opcfe_checkout_fields_shipping_section',
                [ 'field_key' => "shipping_$field_key", 'field' => $field ]
            );
        }

        foreach ( $other_fields as $field_key => $field ) {
            add_settings_field(
                "other_{$field_key}",
                $field['label'],
                [ $this, 'render_other_field' ],
                'w3opcfe-checkout-fields',
                'w3opcfe_checkout_fields_other_section',
                [ 'field_key' => $field_key, 'field' => $field ]
            );
        }
    }

    public function render_field( $args ) {
        $options   = get_option( $this->option_name, [] );
        $field_key = $args['field_key'];
        $field     = $args['field'];

        $saved    = isset( $options[ $field_key ] ) ? $options[ $field_key ] : [];
        $enabled  = isset( $saved['enabled'] ) ? $saved['enabled'] : 1;
        $label    = isset( $saved['label'] ) ? $saved['label'] : ( $field['label'] ?? '' );
        $required = isset( $saved['required'] ) ? $saved['required'] : ( ! empty( $field['required'] ) ? 1 : 0 );
        ?>
        <div class="w3opcfe-field-wrapper" style="margin-bottom: 12px;">
            <label style="margin-right:10px;">
                <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]" value="0">
                <input class="w3opcfe-enable-field" type="checkbox"
                    name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]"
                    value="1" <?php checked( 1, $enabled ); ?>>
                <?php esc_html_e( 'Enable', 'on-page-checkout-for-elementor' ); ?>
            </label>

            <div class="w3opcfe-dependent-fields"
                style="<?php echo empty( $enabled ) ? 'display:none;' : ''; ?> margin-left:20px;">
                <label style="margin-bottom:4px;">
                    <strong><?php esc_html_e( 'Label:', 'on-page-checkout-for-elementor' ); ?></strong>
                    <input type="text"
                        name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][label]"
                        value="<?php echo esc_attr( $label ); ?>"
                        style="width:300px;">
                </label>
                <label>
                    <input type="hidden"
                        name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]"
                        value="0">
                    <input type="checkbox"
                        name="<?php echo esc_attr( $this->option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]"
                        value="1" <?php checked( 1, $required ); ?>>
                    <?php esc_html_e( 'Required', 'on-page-checkout-for-elementor' ); ?>
                </label>
            </div>
        </div>
        <?php
    }

    public function render_other_field( $args ) {
        $options   = get_option( $this->option_name, [] );
        $field_key = $args['field_key'];
        $field     = $args['field'];

        $saved   = isset( $options[ "other_$field_key" ] ) ? $options[ "other_$field_key" ] : [];
        $enabled = isset( $saved['enabled'] ) ? $saved['enabled'] : 1;
        $label   = isset( $saved['label'] ) ? $saved['label'] : $field['label'];
        ?>
        <div class="w3opcfe-field-wrapper">
            <label style="margin-right:10px;">
                <input type="hidden" name="<?php echo esc_attr( $this->option_name ); ?>[other_<?php echo esc_attr( $field_key ); ?>][enabled]" value="0">
                <input class="w3opcfe-enable-field" type="checkbox"
                    name="<?php echo esc_attr( $this->option_name ); ?>[other_<?php echo esc_attr( $field_key ); ?>][enabled]"
                    value="1" <?php checked( 1, $enabled ); ?>>
                <?php esc_html_e( 'Enable', 'on-page-checkout-for-elementor' ); ?>
            </label>

            <div class="w3opcfe-dependent-fields" style="<?php echo empty( $enabled ) ? 'display:none;' : ''; ?> margin-left:20px;">
                <label style="display:block; margin-bottom:4px;">
                    <?php esc_html_e( 'Label:', 'on-page-checkout-for-elementor' ); ?>
                    <input type="text"
                        name="<?php echo esc_attr( $this->option_name ); ?>[other_<?php echo esc_attr( $field_key ); ?>][label]"
                        value="<?php echo esc_attr( $label ); ?>"
                        style="width:250px;">
                </label>
            </div>
        </div>
        <?php
    }

    public function sanitize_fields( $input ) {
        $output = [];

        if ( ! is_array( $input ) ) {
            return $output;
        }

        foreach ( $input as $key => $field ) {
            $output[ sanitize_key( $key ) ] = [
                'enabled'  => ! empty( $field['enabled'] ) ? 1 : 0,
                'label'    => sanitize_text_field( $field['label'] ?? '' ),
                'required' => ! empty( $field['required'] ) ? 1 : 0,
                'title'    => sanitize_text_field( $field['title'] ?? '' ),
            ];
        }
        return $output;
    }

    public function modify_checkout_fields( $fields ) {
        // Do not modify fields in admin area — return original so settings page sees all fields
        if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return $fields;
        }

        $options = get_option( $this->option_name, [] );

        foreach ( $options as $field_key => $settings ) {
            if ( strpos( $field_key, 'billing_' ) === 0 ) {
                $wc_key = substr( $field_key, strlen( 'billing_' ) );
                if ( isset( $fields['billing'][ $wc_key ] ) ) {
                    if ( empty( $settings['enabled'] ) ) {
                        unset( $fields['billing'][ $wc_key ] );
                    } else {
                        if ( ! empty( $settings['label'] ) ) {
                            $fields['billing'][ $wc_key ]['label'] = $settings['label'];
                        }
                        $fields['billing'][ $wc_key ]['required'] = ! empty( $settings['required'] );
                    }
                }
            } elseif ( strpos( $field_key, 'shipping_' ) === 0 ) {
                $wc_key = substr( $field_key, strlen( 'shipping_' ) );
                if ( isset( $fields['shipping'][ $wc_key ] ) ) {
                    if ( empty( $settings['enabled'] ) ) {
                        unset( $fields['shipping'][ $wc_key ] );
                    } else {
                        if ( ! empty( $settings['label'] ) ) {
                            $fields['shipping'][ $wc_key ]['label'] = $settings['label'];
                        }
                        $fields['shipping'][ $wc_key ]['required'] = ! empty( $settings['required'] );
                    }
                }
            } elseif ( $field_key === 'other_order_notes' || strpos( $field_key, 'other_' ) === 0 ) {
                if ( isset( $options['other_order_notes'] ) && empty( $options['other_order_notes']['enabled'] ) ) {
                    unset( $fields['order']['order_comments'] );
                } else {
                    if ( $field_key === 'other_order_notes' && ! empty( $settings['label'] ) ) {
                        $fields['order']['order_comments']['label'] = $settings['label'];
                    }
                }
            }
        }

        return $fields;
    }

}

new W3OPCFE_Checkout_Fields_Settings();
