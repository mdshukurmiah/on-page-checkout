<?php
/**
 * On-Page Checkout - Activation Confirmation Modal
 *
 * Only one edition (Free or Pro) may be active. On the Plugins screen, clicking "Activate" on the
 * Pro edition while Free is active opens a confirmation modal that deactivates Free and activates Pro.
 * Activating Free deactivates Pro through deactivate_pro(), called from Free's activation hook.
 *
 * @package On_Page_Checkout
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_Activation_Modal {

    /**
     * Nonce action.
     */
    const NONCE_ACTION = 'w3opcfe_switch_to_pro';

    /**
     * Pro edition plugin basename.
     */
    const PRO_PLUGIN = 'on-page-checkout-pro/on-page-checkout-pro.php';

    /**
     * Initialize hooks.
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'admin_footer', [ __CLASS__, 'render_modal' ] );
        add_action( 'admin_notices', [ __CLASS__, 'render_switched_notice' ] );
        add_action( 'wp_ajax_w3opcfe_switch_to_pro', [ __CLASS__, 'ajax_switch_to_pro' ] );
    }

    /**
     * Whether the Pro edition is installed.
     *
     * @return bool
     */
    private static function is_pro_installed() {
        return file_exists( WP_PLUGIN_DIR . '/' . self::PRO_PLUGIN );
    }

    /**
     * Deactivate the Pro edition if it is active. Called from Free's activation hook.
     */
    public static function deactivate_pro() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ( is_plugin_active( self::PRO_PLUGIN ) ) {
            deactivate_plugins( self::PRO_PLUGIN );
            set_transient( 'w3opcfe_free_switched_notice', true, 60 );
        }
    }

    /**
     * Enqueue assets on the Plugins screen.
     *
     * @param string $hook Admin page hook.
     */
    public static function enqueue_assets( $hook ) {
        if ( 'plugins.php' !== $hook || ! self::is_pro_installed() ) {
            return;
        }

        wp_enqueue_style(
            'w3opcfe-activation-modal',
            W3OPCFE_PLUGIN_URL . 'assets/css/admin-activation-modal.css',
            [],
            W3OPCFE_VERSION
        );

        wp_enqueue_script(
            'w3opcfe-activation-modal',
            W3OPCFE_PLUGIN_URL . 'assets/js/admin-activation-modal.js',
            [],
            W3OPCFE_VERSION,
            true
        );

        wp_localize_script(
            'w3opcfe-activation-modal',
            'w3opcfeActivationModal',
            [
                'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
                'nonce'        => wp_create_nonce( self::NONCE_ACTION ),
                'ajaxAction'   => 'w3opcfe_switch_to_pro',
                'targetPlugin' => self::PRO_PLUGIN,
                'i18n'         => [
                    'failed' => __( 'Switching failed. Please reload the page.', 'on-page-checkout' ),
                ],
            ]
        );
    }

    /**
     * Render the confirmation modal on the Plugins screen.
     */
    public static function render_modal() {
        global $pagenow;
        if ( 'plugins.php' !== $pagenow || ! self::is_pro_installed() ) {
            return;
        }
        ?>
        <div id="w3opcfe-activation-modal" class="w3opcfe-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="w3opcfe-modal-title">
            <div class="w3opcfe-modal-dialog">
                <button type="button" class="w3opcfe-modal-close" aria-label="<?php esc_attr_e( 'Close', 'on-page-checkout' ); ?>">✕</button>
                <div class="w3opcfe-modal-header">
                    <div class="w3opcfe-modal-icon">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                    </div>
                    <div class="w3opcfe-modal-title-wrap">
                        <h3 id="w3opcfe-modal-title" class="w3opcfe-modal-title"><?php esc_html_e( 'Deactivate Free Version?', 'on-page-checkout' ); ?></h3>
                        <p class="w3opcfe-modal-desc"><?php esc_html_e( "It's required to deactivate free version of this plugin. Do you want to continue?", 'on-page-checkout' ); ?></p>
                    </div>
                </div>
                <div class="w3opcfe-modal-footer">
                    <button type="button" class="button w3opcfe-modal-btn w3opcfe-modal-btn-cancel">
                        <span class="w3opcfe-btn-text"><?php esc_html_e( 'No, continue using free version', 'on-page-checkout' ); ?></span>
                    </button>
                    <button type="button" class="button button-primary w3opcfe-modal-btn w3opcfe-modal-btn-continue">
                        <span class="w3opcfe-spinner"></span>
                        <span class="w3opcfe-btn-text"><?php esc_html_e( 'Continue', 'on-page-checkout' ); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Notice shown after Free replaced the Pro edition.
     */
    public static function render_switched_notice() {
        if ( get_transient( 'w3opcfe_free_switched_notice' ) ) {
            delete_transient( 'w3opcfe_free_switched_notice' );
            echo '<div class="notice notice-success is-dismissible"><p>' .
                esc_html__( 'On-Page Checkout for WooCommerce (Free) is now active. The Pro version was deactivated.', 'on-page-checkout' ) .
                '</p></div>';
        }
    }

    /**
     * AJAX: deactivate Free, then hand back an activation link for the Pro edition.
     */
    public static function ajax_switch_to_pro() {
        check_ajax_referer( self::NONCE_ACTION, 'nonce' );

        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Permission denied. You cannot activate plugins.', 'on-page-checkout' ) ] );
        }

        if ( ! function_exists( 'deactivate_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $validation = validate_plugin( self::PRO_PLUGIN );
        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( [ 'message' => $validation->get_error_message() ] );
        }

        deactivate_plugins( W3OPCFE_PLUGIN_BASENAME );

        // Activate Pro in a fresh request: both editions share class names, so loading Pro here could fatal.
        // add_query_arg() rather than wp_nonce_url(), which HTML-escapes "&" and breaks the JS redirect.
        $activate_url = add_query_arg(
            [
                'action'   => 'activate',
                'plugin'   => rawurlencode( self::PRO_PLUGIN ),
                '_wpnonce' => wp_create_nonce( 'activate-plugin_' . self::PRO_PLUGIN ),
            ],
            admin_url( 'plugins.php' )
        );

        set_transient( 'w3opcfe_pro_switched_notice', true, 60 );

        wp_send_json_success( [ 'redirect_url' => $activate_url ] );
    }
}
