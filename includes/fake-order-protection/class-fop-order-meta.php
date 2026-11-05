<?php
/**
 * Fake Order Protection - Order Meta Box & Admin Columns
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_Order_Meta {

    /**
     * Initialize hooks.
     */
    public static function init() {
        // Classic WP_Post order columns
        add_filter( 'manage_edit-shop_order_columns', [ __CLASS__, 'add_risk_column' ], 20 );
        add_action( 'manage_shop_order_posts_custom_column', [ __CLASS__, 'render_risk_column_content' ], 20, 2 );

        // HPOS order columns
        add_filter( 'manage_woocommerce_page_wc-orders_columns', [ __CLASS__, 'add_risk_column' ], 20 );
        add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ __CLASS__, 'render_hpos_risk_column_content' ], 20, 2 );

        // Order Edit screen meta box
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_order_meta_box' ] );

        // Admin styling for badges
        add_action( 'admin_head', [ __CLASS__, 'output_badge_styles' ] );

        // AJAX handler for quick list actions
        add_action( 'wp_ajax_w3opcfe_quick_list_action', [ __CLASS__, 'handle_quick_list_action' ] );
    }

    /**
     * Add Risk column header.
     *
     * @param array $columns
     * @return array
     */
    public static function add_risk_column( $columns ) {
        $new_columns = [];
        foreach ( $columns as $key => $column ) {
            $new_columns[ $key ] = $column;
            if ( 'order_status' === $key || 'order_total' === $key ) {
                $new_columns['w3opcfe_risk'] = __( 'Risk', 'on-page-checkout-for-elementor' );
            }
        }
        if ( ! isset( $new_columns['w3opcfe_risk'] ) ) {
            $new_columns['w3opcfe_risk'] = __( 'Risk', 'on-page-checkout-for-elementor' );
        }
        return $new_columns;
    }

    /**
     * Render Risk badge for classic order table.
     *
     * @param string $column
     * @param int    $post_id
     */
    public static function render_risk_column_content( $column, $post_id ) {
        if ( 'w3opcfe_risk' !== $column ) {
            return;
        }

        $order = wc_get_order( $post_id );
        if ( ! $order ) {
            echo '—';
            return;
        }

        self::render_badge( $order );
    }

    /**
     * Render Risk badge for HPOS order table.
     *
     * @param string    $column
     * @param \WC_Order $order
     */
    public static function render_hpos_risk_column_content( $column, $order ) {
        if ( 'w3opcfe_risk' !== $column || ! $order instanceof \WC_Order ) {
            return;
        }

        self::render_badge( $order );
    }

    /**
     * Render styled HTML badge with tooltip reasons.
     *
     * @param \WC_Order $order
     */
    public static function render_badge( \WC_Order $order ) {
        $badge = $order->get_meta( '_w3opcfe_risk_badge' );
        if ( empty( $badge ) ) {
            $badge = 'Clean';
        }

        $reasons = $order->get_meta( '_w3opcfe_risk_reasons' );
        if ( is_array( $reasons ) ) {
            $reason_text = implode( "\n• ", $reasons );
            if ( ! empty( $reason_text ) ) {
                $reason_text = '• ' . $reason_text;
            }
        } else {
            $reason_text = (string) $reasons;
        }

        $badge_class = 'w3opcfe-badge-' . strtolower( sanitize_html_class( $badge ) );
        $title_attr  = ! empty( $reason_text ) ? ' title="' . esc_attr( $reason_text ) . '"' : '';

        printf(
            '<span class="w3opcfe-risk-badge %s"%s>%s</span>',
            esc_attr( $badge_class ),
            $title_attr,
            esc_html( $badge )
        );
    }

    /**
     * Output inline styles for risk badges in admin.
     */
    public static function output_badge_styles() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || ( false === strpos( $screen->id, 'shop_order' ) && false === strpos( $screen->id, 'wc-orders' ) ) ) {
            return;
        }
        ?>
        <style>
            .w3opcfe-risk-badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 4px;
                font-size: 11px;
                font-weight: 600;
                line-height: 1.3;
                text-transform: uppercase;
                letter-spacing: 0.3px;
                cursor: help;
            }
            .w3opcfe-badge-clean {
                background: #e6f4ea;
                color: #137333;
                border: 1px solid #ceead6;
            }
            .w3opcfe-badge-flagged {
                background: #fef7e0;
                color: #b06000;
                border: 1px solid #feefc3;
            }
            .w3opcfe-badge-duplicate {
                background: #fce8e6;
                color: #c5221f;
                border: 1px solid #fad2cf;
            }
            .w3opcfe-badge-merged {
                background: #e8f0fe;
                color: #1a73e8;
                border: 1px solid #d2e3fc;
            }
            .w3opcfe-badge-blocked {
                background: #202124;
                color: #ffffff;
            }
        </style>
        <?php
    }

    /**
     * Register meta box on order edit screen.
     */
    public static function add_order_meta_box() {
        $screen = function_exists( 'wc_get_page_screen_id' ) ? wc_get_page_screen_id( 'shop_order' ) : 'shop_order';

        add_meta_box(
            'w3opcfe_order_risk_details',
            __( 'W3 OPC — Fake Order Protection', 'on-page-checkout-for-elementor' ),
            [ __CLASS__, 'render_order_meta_box' ],
            $screen,
            'side',
            'high'
        );
    }

    /**
     * Render the order edit meta box content.
     *
     * @param \WP_Post|\WC_Order $post_or_order
     */
    public static function render_order_meta_box( $post_or_order ) {
        $order = ( $post_or_order instanceof \WC_Order ) ? $post_or_order : wc_get_order( $post_or_order );
        if ( ! $order ) {
            echo '<p>' . esc_html__( 'Unable to load order details.', 'on-page-checkout-for-elementor' ) . '</p>';
            return;
        }

        $badge       = $order->get_meta( '_w3opcfe_risk_badge' ) ?: 'Clean';
        $reasons     = $order->get_meta( '_w3opcfe_risk_reasons' );
        $device_id   = $order->get_meta( '_w3opcfe_device_id' );
        $device_hash = $order->get_meta( '_w3opcfe_device_hash' );
        $ip          = $order->get_meta( '_w3opcfe_customer_ip' ) ?: $order->get_customer_ip_address();
        $phone       = $order->get_billing_phone();

        ?>
        <div class="w3opcfe-meta-box-wrap" style="font-size:12px; line-height:1.6;">
            <div style="margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #eee;">
                <strong><?php esc_html_e( 'Risk Assessment:', 'on-page-checkout-for-elementor' ); ?></strong>
                <div style="margin-top:4px;">
                    <?php self::render_badge( $order ); ?>
                </div>
                <?php if ( ! empty( $reasons ) ) : ?>
                    <ul style="margin:8px 0 0 16px; padding:0; list-style:disc; color:#b06000;">
                        <?php foreach ( (array) $reasons as $reason ) : ?>
                            <li><?php echo esc_html( $reason ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <p style="margin:4px 0;">
                <strong><?php esc_html_e( 'Normalized Phone:', 'on-page-checkout-for-elementor' ); ?></strong><br>
                <code><?php echo esc_html( $phone ?: '—' ); ?></code>
            </p>

            <p style="margin:4px 0;">
                <strong><?php esc_html_e( 'Customer IP:', 'on-page-checkout-for-elementor' ); ?></strong><br>
                <code><?php echo esc_html( $ip ?: '—' ); ?></code>
            </p>

            <p style="margin:4px 0;">
                <strong><?php esc_html_e( 'Device ID:', 'on-page-checkout-for-elementor' ); ?></strong><br>
                <code style="word-break:break-all; font-size:11px;"><?php echo esc_html( $device_id ?: '—' ); ?></code>
            </p>

            <?php if ( ! empty( $device_hash ) ) : ?>
                <p style="margin:4px 0;">
                    <strong><?php esc_html_e( 'Fingerprint Hash:', 'on-page-checkout-for-elementor' ); ?></strong><br>
                    <code style="font-size:11px;"><?php echo esc_html( $device_hash ); ?></code>
                </p>
            <?php endif; ?>

            <?php
            // Earlier orders lookup
            if ( ! empty( $phone ) ) {
                $earlier_orders = W3OPCFE_FOP_Velocity_Controller::find_recent_orders_by_phone( $phone, 168, $order->get_id() ); // 7 days
                if ( ! empty( $earlier_orders ) ) : ?>
                    <div style="margin-top:12px; padding-top:10px; border-top:1px solid #eee;">
                        <strong><?php printf( esc_html__( 'Other orders from this phone (%d):', 'on-page-checkout-for-elementor' ), count( $earlier_orders ) ); ?></strong>
                        <ul style="margin:6px 0 0 16px; padding:0; list-style:disc;">
                            <?php foreach ( $earlier_orders as $prev_order ) : ?>
                                <li>
                                    <a href="<?php echo esc_url( $prev_order->get_edit_order_url() ); ?>" target="_blank">
                                        #<?php echo esc_html( $prev_order->get_order_number() ); ?>
                                    </a>
                                    (<?php echo esc_html( wc_get_order_status_name( $prev_order->get_status() ) ); ?>,
                                    <?php echo esc_html( $prev_order->get_date_created() ? $prev_order->get_date_created()->date_i18n( 'M j, H:i' ) : '' ); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif;
            }
            ?>

            <!-- Quick List Actions -->
            <div style="margin-top:14px; padding-top:10px; border-top:1px solid #eee;">
                <strong style="display:block; margin-bottom:8px;"><?php esc_html_e( 'Quick Actions:', 'on-page-checkout-for-elementor' ); ?></strong>
                <div style="display:flex; flex-wrap:wrap; gap:6px;">
                    <?php if ( ! empty( $phone ) ) : ?>
                        <button type="button" class="button button-small w3opcfe-quick-btn" data-list="blocklist" data-type="phone" data-val="<?php echo esc_attr( $phone ); ?>" style="color:#b32d2e;">
                            <span class="dashicons dashicons-dismiss" style="font-size:14px; line-height:22px; width:14px; height:14px; vertical-align:middle;"></span>
                            <?php esc_html_e( 'Block Phone', 'on-page-checkout-for-elementor' ); ?>
                        </button>
                        <button type="button" class="button button-small w3opcfe-quick-btn" data-list="allowlist" data-type="phone" data-val="<?php echo esc_attr( $phone ); ?>" style="color:#137333;">
                            <span class="dashicons dashicons-yes-alt" style="font-size:14px; line-height:22px; width:14px; height:14px; vertical-align:middle;"></span>
                            <?php esc_html_e( 'Allow Phone', 'on-page-checkout-for-elementor' ); ?>
                        </button>
                    <?php endif; ?>

                    <?php if ( ! empty( $device_id ) ) : ?>
                        <button type="button" class="button button-small w3opcfe-quick-btn" data-list="blocklist" data-type="device_id" data-val="<?php echo esc_attr( $device_id ); ?>" style="color:#b06000;">
                            <span class="dashicons dashicons-smartphone" style="font-size:14px; line-height:22px; width:14px; height:14px; vertical-align:middle;"></span>
                            <?php esc_html_e( 'Block Device', 'on-page-checkout-for-elementor' ); ?>
                        </button>
                    <?php endif; ?>

                    <?php if ( ! empty( $ip ) && '127.0.0.1' !== $ip ) : ?>
                        <button type="button" class="button button-small w3opcfe-quick-btn" data-list="blocklist" data-type="ip" data-val="<?php echo esc_attr( $ip ); ?>">
                            <span class="dashicons dashicons-admin-site" style="font-size:14px; line-height:22px; width:14px; height:14px; vertical-align:middle;"></span>
                            <?php esc_html_e( 'Block IP', 'on-page-checkout-for-elementor' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
                <div class="w3opcfe-quick-feedback" style="margin-top:6px; font-weight:600; font-size:11px; min-height:16px;"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($){
            $('.w3opcfe-quick-btn').off('click').on('click', function(e){
                e.preventDefault();
                var $btn = $(this);
                var $box = $btn.closest('.w3opcfe-meta-box-wrap');
                var $fb = $box.find('.w3opcfe-quick-feedback');
                var listType = $btn.data('list');
                var entryType = $btn.data('type');
                var entryVal = $btn.data('val');

                $btn.prop('disabled', true).css('opacity', '0.6');
                $fb.text('<?php echo esc_js( __( 'Saving...', 'on-page-checkout-for-elementor' ) ); ?>').css('color', '#666');

                $.post(ajaxurl, {
                    action: 'w3opcfe_quick_list_action',
                    nonce: '<?php echo esc_js( wp_create_nonce( 'w3opcfe_quick_list_nonce' ) ); ?>',
                    order_id: <?php echo absint( $order->get_id() ); ?>,
                    list_type: listType,
                    entry_type: entryType,
                    entry_value: entryVal
                }, function(res){
                    if (res && res.success) {
                        $fb.text(res.data.message).css('color', '#137333');
                        $btn.text('✓ ' + (listType === 'blocklist' ? '<?php echo esc_js( __( 'Blocked', 'on-page-checkout-for-elementor' ) ); ?>' : '<?php echo esc_js( __( 'Allowlisted', 'on-page-checkout-for-elementor' ) ); ?>'));
                    } else {
                        $btn.prop('disabled', false).css('opacity', '1');
                        $fb.text(res && res.data && res.data.message ? res.data.message : '<?php echo esc_js( __( 'Error saving entry.', 'on-page-checkout-for-elementor' ) ); ?>').css('color', '#b32d2e');
                    }
                }).fail(function(){
                    $btn.prop('disabled', false).css('opacity', '1');
                    $fb.text('<?php echo esc_js( __( 'Network error.', 'on-page-checkout-for-elementor' ) ); ?>').css('color', '#b32d2e');
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for quick block/allow actions from Order Edit screen.
     */
    public static function handle_quick_list_action() {
        check_ajax_referer( 'w3opcfe_quick_list_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_shop_orders' ) ) {
            wp_send_json_error( [ 'message' => __( 'Permission denied.', 'on-page-checkout-for-elementor' ) ] );
        }

        $list_type   = sanitize_key( $_POST['list_type'] ?? 'blocklist' );
        $entry_type  = sanitize_key( $_POST['entry_type'] ?? 'phone' );
        $entry_value = sanitize_text_field( $_POST['entry_value'] ?? '' );
        $order_id    = absint( $_POST['order_id'] ?? 0 );

        if ( empty( $entry_value ) ) {
            wp_send_json_error( [ 'message' => __( 'Invalid value.', 'on-page-checkout-for-elementor' ) ] );
        }

        $reason = sprintf( __( 'Quick action from Order #%d', 'on-page-checkout-for-elementor' ), $order_id );

        $inserted = W3OPCFE_FOP_List_Manager::add_entry( $list_type, $entry_type, $entry_value, $reason, 'manual' );

        if ( $inserted ) {
            if ( $order_id ) {
                $order = wc_get_order( $order_id );
                if ( $order ) {
                    $order->add_order_note(
                        sprintf(
                            /* translators: 1: Entry type, 2: List type, 3: Value */
                            __( '[Fake Order Protection] %1$s added to %2$s: %3$s', 'on-page-checkout-for-elementor' ),
                            ucfirst( $entry_type ),
                            ucfirst( $list_type ),
                            $entry_value
                        )
                    );
                }
            }

            wp_send_json_success( [
                'message' => sprintf(
                    /* translators: 1: Type, 2: List */
                    __( 'Successfully added %1$s to %2$s.', 'on-page-checkout-for-elementor' ),
                    $entry_type,
                    $list_type
                ),
            ] );
        } else {
            wp_send_json_error( [ 'message' => __( 'Could not add entry or entry is already present.', 'on-page-checkout-for-elementor' ) ] );
        }
    }
}

