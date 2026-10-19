<?php
/**
 * Greenweb SMS Integration for On-Page Checkout
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Class W3OPCFE_Greenweb
 *
 * Handles integration between On-Page Checkout and Greenweb SMS plugins.
 */
class W3OPCFE_Greenweb {

    /**
     * Singleton instance.
     *
     * @var W3OPCFE_Greenweb|null
     */
    private static $instance = null;

    /**
     * Get singleton instance.
     *
     * @return W3OPCFE_Greenweb
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ], 20 );
    }

    /**
     * Check if Greenweb SMS base plugin and Pro plugin are active.
     *
     * @return bool
     */
    public static function is_greenweb_active() {
        $active_plugins = (array) get_option( 'active_plugins', [] );
        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) ) );
        }

        $base_active = class_exists( '\WP_SMS\Gateway' )
            || defined( 'GREENWEB_SMS_DIR' )
            || defined( 'WP_SMS_DIR' )
            || in_array( 'greenwebsms/wp-sms.php', $active_plugins, true );

        $pro_active = class_exists( 'Otp' )
            || class_exists( '\WP_SMS\Pro\WooCommerce\Otp' )
            || defined( 'GREENWEB_SMS_PRO_DIR' )
            || in_array( 'greenwebsms-2/wp-sms-pro.php', $active_plugins, true );

        return $base_active && $pro_active;
    }

    /**
     * Check if WooCommerce checkout OTP is enabled in Greenweb settings.
     *
     * @return bool
     */
    public static function is_otp_enabled() {
        if ( ! self::is_greenweb_active() ) {
            return false;
        }

        if ( class_exists( '\WP_SMS\Option' ) ) {
            return (bool) \WP_SMS\Option::getOption( 'wc_otp_enable', true );
        }

        return false;
    }

    /**
     * Enqueue Greenweb and bridge assets on pages with On-Page Checkout.
     */
    public function enqueue_assets() {
        if ( ! self::is_otp_enabled() ) {
            return;
        }

        $pro_url     = defined( 'GREENWEB_SMS_PRO_URL' ) ? GREENWEB_SMS_PRO_URL : plugins_url( 'greenwebsms-2/' );
        $pro_version = defined( 'GREENWEB_SMS_PRO_VERSION' ) ? GREENWEB_SMS_PRO_VERSION : '2.0.0';

        // Register and enqueue Greenweb's core wc-otp style and script if not already done
        if ( ! wp_script_is( 'wp-sms-woocommerce-otp', 'registered' ) && ! wp_script_is( 'wp-sms-woocommerce-otp', 'enqueued' ) ) {
            wp_register_style(
                'wp-sms-woocommerce-otp',
                $pro_url . 'assets/css/wc-otp.css',
                [],
                $pro_version
            );

            wp_register_script(
                'wp-sms-woocommerce-otp',
                $pro_url . 'assets/js/wc-otp.js',
                [ 'jquery' ],
                $pro_version,
                true
            );

            $filter = '';
            if ( class_exists( '\WP_SMS\Option' ) ) {
                $filter = \WP_SMS\Option::getOption( 'wc_gw_checkoutotp_filter', true );
                if ( empty( $filter ) ) {
                    $filter = \WP_SMS\Option::getOption( 'wc_checkoutotp_filter', true );
                }
            }

            wp_localize_script(
                'wp-sms-woocommerce-otp',
                'wp_sms_woocommerce_otp',
                [
                    'ajax'                => admin_url( 'admin-ajax.php' ),
                    'is_rtl'              => is_rtl() ? 1 : 0,
                    'gwebbutton'          => function_exists( 'wc_wp_theme_get_element_class_name' ) && wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '',
                    'intel_input'         => class_exists( '\WP_SMS\Option' ) && \WP_SMS\Option::getOption( 'international_mobile' ) ? 1 : 0,
                    'countries_whitelist' => class_exists( '\WP_SMS\Option' ) ? \WP_SMS\Option::getOption( 'wc_otp_countries_whitelist', true ) : '',
                    'checkoutotp_filter'  => $filter,
                    'lang'                => [
                        'error'      => __( 'Error', 'on-page-checkout-for-elementor' ),
                        'ajax_error' => __( 'Error occurred. please try again', 'on-page-checkout-for-elementor' ),
                    ],
                ]
            );
        }

        wp_enqueue_style( 'wp-sms-woocommerce-otp' );
        wp_enqueue_script( 'wp-sms-woocommerce-otp' );

        // Enqueue On-Page Checkout's Greenweb bridge assets
        $bridge_version = defined( 'W3OPCFE_VERSION' ) ? W3OPCFE_VERSION : '1.0.3';

        wp_enqueue_style(
            'w3opcfe-greenweb-otp',
            plugins_url( 'greenweb-otp.css', __FILE__ ),
            [ 'wp-sms-woocommerce-otp' ],
            $bridge_version
        );

        wp_enqueue_script(
            'w3opcfe-greenweb-otp',
            plugins_url( 'greenweb-otp.js', __FILE__ ),
            [ 'jquery', 'wp-sms-woocommerce-otp' ],
            $bridge_version,
            true
        );
    }
}
