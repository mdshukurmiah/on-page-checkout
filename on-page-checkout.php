<?php
/**
 * Plugin Name: On-Page Checkout for Elementor
 * Description: A custom Elementor widget to display WooCommerce products and enable on-page checkout.
 * Version:     1.0.0
 * Author:      W3 Relations
 * Author URI:  https://w3relations.com/
 * Requires Plugins: elementor, woocommerce
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: on-page-checkout-for-elementor
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define Constants
define( 'W3OPCFE_PLUGIN_FILE', __FILE__ );
define( 'W3OPCFE_PLUGIN_DIR', plugin_dir_path( W3OPCFE_PLUGIN_FILE ) );
define( 'W3OPCFE_PLUGIN_URL', plugin_dir_url( W3OPCFE_PLUGIN_FILE ) );
define( 'W3OPCFE_VERSION', '1.0.0' );

/**
 * Main On Page Checkout Class.
 *
 * The init class that runs the On Page Checkout plugin.
 * Intended To make sure that the plugin's minimum requirements are met.
 *
 * @since 1.0.0
 */
final class W3OPCFE_On_Page_Checkout {

    /**
     * Plugin Version
     *
     * @since 1.0.0
     * @var string The plugin version.
     */
    const VERSION = W3OPCFE_VERSION;

    /**
     * Minimum Elementor Version
     *
     * @since 1.0.0
     * @var string Minimum Elementor version required to run the plugin.
     */
    const MINIMUM_ELEMENTOR_VERSION = '3.0.0';

    /**
     * Minimum WooCommerce Version
     *
     * @since 1.0.0
     * @var string Minimum WooCommerce version required to run the plugin.
     */
    const MINIMUM_WOOCOMMERCE_VERSION = '6.0.0';

    /**
     * Minimum PHP Version
     *
     * @since 1.0.0
     * @var string Minimum PHP version required to run the plugin.
     */
    const MINIMUM_PHP_VERSION = '7.4';

    /**
     * Instance
     *
     * @since 1.0.0
     * @access private
     * @static
     * @var W3OPCFE_On_Page_Checkout The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     * @static
     * @return W3OPCFE_On_Page_Checkout An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded' ] );
    }

    /**
     * On Plugins Loaded
     *
     * Checks whether Elementor and WooCommerce are installed and activated.
     * If not, it disables the plugin and displays a notice.
     *
     * @since 1.0.0
     * @access public
     */
    public function on_plugins_loaded() {
        if ( ! $this->check_dependencies() ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_dependencies' ] );
            return;
        }

        // Autoload classes
        spl_autoload_register( [ $this, 'autoload' ] );

        // Initialize the plugin
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Check Dependencies
     *
     * Checks if Elementor, WooCommerce and PHP versions meet the minimum requirements.
     *
     * @since 1.0.0
     * @access private
     * @return bool True if all dependencies are met, false otherwise.
     */
    private function check_dependencies() {
        // Check for Elementor
        if ( ! did_action( 'elementor/loaded' ) ) {
            return false;
        }

        // Check for WooCommerce
        if ( ! class_exists( 'WooCommerce' ) ) {
            return false;
        }

        // Check for PHP Version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            return false;
        }

        // Check for Elementor Version
        if ( version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
            return false;
        }

        // Check for WooCommerce Version
        if ( version_compare( WC_VERSION, self::MINIMUM_WOOCOMMERCE_VERSION, '<' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Admin Notice Missing Dependencies
     *
     * Displays an admin notice when Elementor or WooCommerce are not installed or activated.
     *
     * @since 1.0.0
     * @access public
     */
    public function admin_notice_missing_dependencies() {
        $message = sprintf(
            /* translators: 1: Plugin name, 2: Elementor, 3: WooCommerce */
            esc_html__( '%1$s requires %2$s and %3$s to be installed and activated.', 'on-page-checkout-for-elementor' ),
            '<strong>' . esc_html__( 'On Page Checkout', 'on-page-checkout-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'on-page-checkout-for-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'WooCommerce', 'on-page-checkout-for-elementor' ) . '</strong>'
        );

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', wp_kses_post( $message ) );
    }

    /**
     * Autoload
     *
     * Autoloads plugin classes.
     *
     * @since 1.0.0
     * @access private
     * @param string $class The name of the class to load.
     */
    private function autoload( $class ) {
        if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
            return;
        }

        $filename = str_replace( '_', '-', strtolower( $class ) );
        $filename = str_replace( __NAMESPACE__ . '-', '', $filename );

        $file = W3OPCFE_PLUGIN_DIR . 'includes/' . $filename . '.php';

        if ( file_exists( $file ) ) {
            require $file;
        }
    }

    /**
     * Includes
     *
     * Include necessary files.
     *
     * @since 1.0.0
     * @access private
     */
    private function includes() {
        require_once W3OPCFE_PLUGIN_DIR . 'includes/elementor-widget.php';
        require_once W3OPCFE_PLUGIN_DIR . 'includes/woocommerce-integration.php';
        require_once W3OPCFE_PLUGIN_DIR . 'includes/ajax-handler.php';
        require_once W3OPCFE_PLUGIN_DIR . 'includes/option-pages.php';
    }

    /**
     * Init Hooks
     *
     * Initializes plugin hooks.
     *
     * @since 1.0.0
     * @access private
     */
    private function init_hooks() {
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        add_action( 'elementor/frontend/after_enqueue_scripts', [ $this, 'enqueue_elementor_scripts' ] );
    }

    /**
     * Register Widgets
     *
     * Register Elementor widgets.
     *
     * @since 1.0.0
     * @access public
     * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
     */
    public function register_widgets( $widgets_manager ) {
        require_once( W3OPCFE_PLUGIN_DIR . 'widgets/on-page-checkout-widget.php' );
        $widgets_manager->register( new \W3OPCFE\Widgets\W3OPCFE_On_Page_Checkout_Widget() );
    }

    /**
     * Enqueue Frontend Scripts
     *
     * Enqueue plugin scripts and styles.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style( 'w3opcfe-frontend-style', W3OPCFE_PLUGIN_URL . 'assets/css/on-page-checkout.css', [], W3OPCFE_VERSION );
        wp_enqueue_script( 'w3opcfe-frontend-script', W3OPCFE_PLUGIN_URL . 'assets/js/on-page-checkout.js', [ 'jquery' ], W3OPCFE_VERSION, true );

        wp_localize_script( 'w3opcfe-frontend-script', 'w3opcfe_ajax_object', [
            'ajax_url'               => admin_url( 'admin-ajax.php' ),
            'nonce'                  => wp_create_nonce( 'w3opcfe_nonce' ),
            'loading_text'           => esc_html__( 'Loading...', 'on-page-checkout-for-elementor' ),
            'error_text'             => esc_html__( 'An error occurred. Please try again.', 'on-page-checkout-for-elementor' ),
            'processing_text'        => esc_html__( 'Processing...', 'on-page-checkout-for-elementor' ),
            'no_product_selected_text' => esc_html__( 'Please select a product.', 'on-page-checkout-for-elementor' ),
            'place_order_text'       => esc_html__( 'Place Order', 'on-page-checkout-for-elementor' ),
        ] );
    }

    /**
     * Enqueue Admin Scripts
     *
     * Enqueue plugin admin scripts and styles.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_admin_scripts( $hook ) {
        $allowed_pages = [
            'toplevel_page_w3opcfe_checkout_fields_edit',
        ];

        if ( ! in_array( $hook, $allowed_pages, true ) ) {
            return; // Don't load on other admin pages
        }

        wp_enqueue_style(
            'w3opcfe-admin-style',
            W3OPCFE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            W3OPCFE_VERSION
        );

        wp_enqueue_script(
            'w3opcfe-admin-script',
            W3OPCFE_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            W3OPCFE_VERSION,
            true
        );
    }

    /**
     * Enqueue Elementor Scripts
     *
     * Enqueue Elementor specific scripts.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue_elementor_scripts() {
        // Enqueue scripts specific to Elementor frontend editing if needed
    }
}

W3OPCFE_On_Page_Checkout::instance();
