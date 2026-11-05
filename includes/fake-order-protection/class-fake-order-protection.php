<?php
/**
 * Fake Order Protection - Main Orchestrator
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/class-fop-input-validator.php';
require_once __DIR__ . '/class-fop-settings.php';
require_once __DIR__ . '/class-fop-velocity-controller.php';
require_once __DIR__ . '/class-fop-logger.php';
require_once __DIR__ . '/class-fop-order-meta.php';
require_once __DIR__ . '/class-fop-list-manager.php';

class W3OPCFE_Fake_Order_Protection {

    /**
     * Singleton instance.
     *
     * @var W3OPCFE_Fake_Order_Protection|null
     */
    private static $instance = null;

    /**
     * Temporary storage for current evaluation during checkout.
     *
     * @var array|null
     */
    private $current_evaluation = null;

    /**
     * Get singleton instance.
     *
     * @return W3OPCFE_Fake_Order_Protection
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        // Admin settings
        add_action( 'admin_init', [ 'W3OPCFE_FOP_Settings', 'register_settings' ] );

        // Admin Order List & Meta Box
        W3OPCFE_FOP_Order_Meta::init();

        // List Manager Admin Actions (CSV & CRUD)
        W3OPCFE_FOP_List_Manager::init();

        // Database table setup and scheduled daily cleanup
        add_action( 'init', [ $this, 'maybe_init_db' ] );
        add_action( 'w3opcfe_fop_daily_cleanup', [ 'W3OPCFE_FOP_Logger', 'prune' ] );

        // Custom "Returned" order status & status transitions
        add_action( 'init', [ 'W3OPCFE_FOP_List_Manager', 'register_returned_order_status' ] );
        add_filter( 'wc_order_statuses', [ 'W3OPCFE_FOP_List_Manager', 'add_returned_to_order_statuses' ] );
        add_action( 'woocommerce_order_status_changed', [ 'W3OPCFE_FOP_List_Manager', 'handle_order_status_change' ], 10, 4 );

        // Classic Store Checkout validation
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_classic_checkout' ], 10, 2 );

        // Ensure order phone is stored in normalized format and save risk metadata
        add_action( 'woocommerce_checkout_create_order', [ $this, 'normalize_order_phone' ], 10, 2 );
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'attach_order_protection_meta' ], 10, 3 );

        // Live JS normalization & device tracking script data
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ], 20 );
    }

    /**
     * Initialize DB table and schedule daily pruning.
     */
    public function maybe_init_db() {
        if ( ! wp_next_scheduled( 'w3opcfe_fop_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'w3opcfe_fop_daily_cleanup' );
        }

        // Lazy create tables if not exists
        if ( ! get_option( 'w3opcfe_fop_db_installed' ) ) {
            W3OPCFE_FOP_Logger::create_table();
            W3OPCFE_FOP_List_Manager::create_table();
            update_option( 'w3opcfe_fop_db_installed', 1 );
        }
    }

    /**
     * Get active settings.
     *
     * @return array
     */
    public function get_settings() {
        return W3OPCFE_FOP_Settings::get_settings();
    }

    /**
     * Determine if protection is enabled.
     *
     * @return bool
     */
    public function is_enabled() {
        $settings = $this->get_settings();
        return ! empty( $settings['enable_protection'] );
    }

    /**
     * Check if running in log-only mode.
     *
     * @return bool
     */
    public function is_log_only() {
        $settings = $this->get_settings();
        return ! empty( $settings['log_only_mode'] );
    }

    /**
     * Get formatted generic block message replacing {store_phone}.
     *
     * @return string
     */
    public function get_generic_block_message() {
        $settings    = $this->get_settings();
        $store_phone = ! empty( $settings['store_contact_phone'] ) ? $settings['store_contact_phone'] : get_option( 'admin_email' );
        $template    = ! empty( $settings['msg_blocked_generic'] ) 
            ? $settings['msg_blocked_generic'] 
            : 'Sorry, we are unable to process your order at this time. For more information, please contact us at: {store_phone}';

        return str_replace( '{store_phone}', $store_phone, $template );
    }

    /**
     * Comprehensive evaluation pipeline for an incoming checkout request.
     * Evaluates Feature 1 (Input Validation) and Feature 2 (Duplicate & Velocity Control).
     *
     * @param array  &$data Posted form fields data.
     * @param array  $items Array of items: [['product_id'=>X, 'variation_id'=>Y, 'quantity'=>Z], ...]
     * @param string $context 'on_page_checkout' or 'store_checkout'.
     * @return array
     */
    public function evaluate_checkout( array &$data, array $items = [], $context = 'on_page_checkout' ) {
        if ( ! $this->is_enabled() ) {
            return [
                'decision'      => 'allow',
                'errors'        => [],
                'block_message' => '',
                'risk_badge'    => 'Clean',
                'risk_reasons'  => [],
                'merged_order'  => null,
                'phone'         => '',
                'device_id'     => '',
                'device_hash'   => '',
                'ip_address'    => W3OPCFE_FOP_Velocity_Controller::get_client_ip(),
            ];
        }

        $settings = $this->get_settings();
        $is_log_only = $this->is_log_only();

        // 1. Phone Normalization
        $raw_phone        = $data['billing_phone'] ?? ( $_POST['billing_phone'] ?? '' );
        $normalized_phone = W3OPCFE_FOP_Input_Validator::normalize_phone( $raw_phone );
        if ( '' !== $normalized_phone ) {
            $data['billing_phone']  = $normalized_phone;
            $_POST['billing_phone'] = $normalized_phone;
        }

        // Device identifiers & IP
        $device_id   = W3OPCFE_FOP_Velocity_Controller::get_device_id();
        $device_hash = W3OPCFE_FOP_Velocity_Controller::get_device_hash();
        $ip_address  = W3OPCFE_FOP_Velocity_Controller::get_client_ip();

        $validation_errors = [];
        $risk_badge        = 'Clean';
        $risk_reasons      = [];

        // Address & Name fields
        $first_name = trim( (string) ( $data['billing_first_name'] ?? ( $_POST['billing_first_name'] ?? '' ) ) );
        $last_name  = trim( (string) ( $data['billing_last_name'] ?? ( $_POST['billing_last_name'] ?? '' ) ) );
        $full_name  = trim( $first_name . ' ' . $last_name );
        $address_1  = trim( (string) ( $data['billing_address_1'] ?? ( $_POST['billing_address_1'] ?? '' ) ) );
        $address_2  = trim( (string) ( $data['billing_address_2'] ?? ( $_POST['billing_address_2'] ?? '' ) ) );
        $full_addr  = trim( $address_1 . ' ' . $address_2 );

        // --- STEP 0: ALLOWLIST CHECK (FEATURE 3 - ALLOWLIST ALWAYS WINS OVER ALL CHECKS) ---
        if ( ! empty( $settings['enable_allowlist'] ) ) {
            $allow_match = W3OPCFE_FOP_List_Manager::check_allowlist( $normalized_phone, $device_id, $ip_address );
            if ( $allow_match ) {
                W3OPCFE_FOP_Logger::log( [
                    'phone'       => $normalized_phone,
                    'device_id'   => $device_id,
                    'device_hash' => $device_hash,
                    'ip_address'  => $ip_address,
                    'decision'    => 'allowed',
                    'reasons'     => sprintf( 'Allowlisted customer (%s)', $allow_match['reason'] ?: 'Trusted' ),
                    'context'     => $context,
                ] );

                return [
                    'decision'      => 'allow',
                    'errors'        => [],
                    'block_message' => '',
                    'risk_badge'    => 'Clean',
                    'risk_reasons'  => [ sprintf( __( 'Allowlisted: %s', 'on-page-checkout-for-elementor' ), $allow_match['reason'] ?: 'Trusted Customer' ) ],
                    'merged_order'  => null,
                    'phone'         => $normalized_phone,
                    'device_id'     => $device_id,
                    'device_hash'   => $device_hash,
                    'ip_address'    => $ip_address,
                ];
            }
        }

        // --- STEP 0B: BLOCKLIST CHECK (FEATURE 3) ---
        if ( ! empty( $settings['enable_blocklist'] ) ) {
            $block_match = W3OPCFE_FOP_List_Manager::check_blocklist( $normalized_phone, $device_id, $device_hash, $ip_address, $full_addr );
            if ( $block_match ) {
                $block_reason = sprintf(
                    /* translators: 1: Entry type, 2: Reason */
                    __( 'Blocklist match by %1$s: %2$s', 'on-page-checkout-for-elementor' ),
                    $block_match['entry_type'],
                    $block_match['reason'] ?: 'Blacklisted'
                );

                if ( ! $is_log_only ) {
                    W3OPCFE_FOP_Logger::log( [
                        'phone'       => $normalized_phone,
                        'device_id'   => $device_id,
                        'device_hash' => $device_hash,
                        'ip_address'  => $ip_address,
                        'decision'    => 'blocked',
                        'reasons'     => $block_reason,
                        'context'     => $context,
                    ] );

                    return [
                        'decision'      => 'block',
                        'errors'        => [ $this->get_generic_block_message() ],
                        'block_message' => $this->get_generic_block_message(),
                        'risk_badge'    => 'Blocked',
                        'risk_reasons'  => [ $block_reason ],
                        'merged_order'  => null,
                        'phone'         => $normalized_phone,
                        'device_id'     => $device_id,
                        'device_hash'   => $device_hash,
                        'ip_address'    => $ip_address,
                    ];
                } else {
                    $risk_badge     = 'Flagged';
                    $risk_reasons[] = '[Log-Only Block] ' . $block_reason;
                }
            }
        }

        // --- STEP 0C: NETWORK BLOCKLIST CHECK (FEATURE 3 - PRO, OPT-IN) ---
        if ( ! empty( $settings['enable_network_blocklist'] ) && ! empty( $normalized_phone ) ) {
            $net_match = W3OPCFE_FOP_List_Manager::check_network_blocklist( $normalized_phone, $settings );
            if ( $net_match ) {
                $net_reason = sprintf(
                    /* translators: %d: Report count */
                    __( 'Network blocklist: phone reported by %d stores', 'on-page-checkout-for-elementor' ),
                    $net_match['reports']
                );

                $net_action = $settings['network_action'] ?? 'flag';
                if ( 'block' === $net_action && ! $is_log_only ) {
                    W3OPCFE_FOP_Logger::log( [
                        'phone'       => $normalized_phone,
                        'device_id'   => $device_id,
                        'device_hash' => $device_hash,
                        'ip_address'  => $ip_address,
                        'decision'    => 'blocked',
                        'reasons'     => $net_reason,
                        'context'     => $context,
                    ] );

                    return [
                        'decision'      => 'block',
                        'errors'        => [ $this->get_generic_block_message() ],
                        'block_message' => $this->get_generic_block_message(),
                        'risk_badge'    => 'Blocked',
                        'risk_reasons'  => [ $net_reason ],
                        'merged_order'  => null,
                        'phone'         => $normalized_phone,
                        'device_id'     => $device_id,
                        'device_hash'   => $device_hash,
                        'ip_address'    => $ip_address,
                    ];
                } else {
                    $risk_badge     = 'Flagged';
                    $risk_reasons[] = $net_reason;
                }
            }
        }

        // --- STEP 1: INPUT VALIDATION (FEATURE 1) ---
        if ( ! empty( $settings['enable_input_validation'] ) ) {
            // Phone validation
            if ( ! empty( $settings['enable_phone_validation'] ) ) {
                if ( empty( $normalized_phone ) || ! W3OPCFE_FOP_Input_Validator::is_valid_bd_phone( $normalized_phone ) ) {
                    $validation_errors[] = ! empty( $settings['msg_invalid_phone'] ) 
                        ? $settings['msg_invalid_phone'] 
                        : 'Please enter a valid 11-digit mobile number (e.g. 017XXXXXXXX)';
                }
            }

            // Name validation
            if ( ! empty( $settings['enable_name_validation'] ) ) {
                $name_check = W3OPCFE_FOP_Input_Validator::validate_name( $full_name, $settings );
                if ( ! $name_check['valid'] ) {
                    $validation_errors[] = $name_check['error'];
                }
            }

            // Address validation
            if ( ! empty( $settings['enable_address_validation'] ) ) {
                if ( '' !== $full_addr ) {
                    $addr_check = W3OPCFE_FOP_Input_Validator::validate_address( $full_addr, $settings );
                    if ( ! $addr_check['valid'] ) {
                        $validation_errors[] = $addr_check['error'];
                    }
                }
            }
        }

        // If input validation failed, reject immediately (unless log-only mode)
        if ( ! empty( $validation_errors ) ) {
            if ( ! $is_log_only ) {
                W3OPCFE_FOP_Logger::log( [
                    'phone'       => $normalized_phone,
                    'device_id'   => $device_id,
                    'device_hash' => $device_hash,
                    'ip_address'  => $ip_address,
                    'decision'    => 'blocked',
                    'reasons'     => $validation_errors,
                    'context'     => $context,
                ] );

                return [
                    'decision'      => 'block',
                    'errors'        => $validation_errors,
                    'block_message' => implode( ' ', $validation_errors ),
                    'risk_badge'    => 'Blocked',
                    'risk_reasons'  => $validation_errors,
                    'merged_order'  => null,
                    'phone'         => $normalized_phone,
                    'device_id'     => $device_id,
                    'device_hash'   => $device_hash,
                    'ip_address'    => $ip_address,
                ];
            } else {
                $risk_badge   = 'Flagged';
                $risk_reasons = array_merge( $risk_reasons, $validation_errors );
            }
        }

        // --- STEP 2: DUPLICATE AND VELOCITY CONTROL (FEATURE 2) ---
        $merged_order = null;

        if ( ! empty( $settings['enable_velocity_control'] ) && ! empty( $normalized_phone ) ) {
            // Extract product IDs being ordered
            $product_ids = [];
            if ( ! empty( $items ) ) {
                foreach ( $items as $it ) {
                    if ( ! empty( $it['product_id'] ) ) {
                        $product_ids[] = absint( $it['product_id'] );
                    }
                    if ( ! empty( $it['variation_id'] ) ) {
                        $product_ids[] = absint( $it['variation_id'] );
                    }
                }
            } elseif ( function_exists( 'WC' ) && WC()->cart ) {
                foreach ( WC()->cart->get_cart() as $cart_item ) {
                    $product_ids[] = $cart_item['product_id'];
                    if ( ! empty( $cart_item['variation_id'] ) ) {
                        $product_ids[] = $cart_item['variation_id'];
                    }
                }
            }
            $product_ids = array_unique( $product_ids );

            // Check 2A: Same Phone + Same Product in Y minutes (Auto-flag Duplicate)
            if ( ! empty( $settings['enable_duplicate_product_check'] ) && ! empty( $product_ids ) ) {
                $dup_minutes = ! empty( $settings['duplicate_product_window'] ) ? absint( $settings['duplicate_product_window'] ) : 60;
                $dup_order   = W3OPCFE_FOP_Velocity_Controller::find_duplicate_product_order( $normalized_phone, $dup_minutes, $product_ids );
                if ( $dup_order instanceof \WC_Order ) {
                    $risk_badge = 'Duplicate';
                    $risk_reasons[] = sprintf(
                        /* translators: 1: Minutes window, 2: Earlier order ID */
                        __( 'Same phone + same product within %1$d minutes (Earlier Order #%2$d)', 'on-page-checkout-for-elementor' ),
                        $dup_minutes,
                        $dup_order->get_id()
                    );
                }
            }

            // Check 2B: Same Phone Lookback within X hours
            $phone_hours   = ! empty( $settings['same_phone_hours'] ) ? absint( $settings['same_phone_hours'] ) : 24;
            $recent_orders = W3OPCFE_FOP_Velocity_Controller::find_recent_orders_by_phone( $normalized_phone, $phone_hours );

            if ( ! empty( $recent_orders ) ) {
                $earlier_order = $recent_orders[0];
                $phone_action  = $settings['same_phone_action'] ?? 'merge';

                if ( 'block' === $phone_action ) {
                    $reason = sprintf(
                        /* translators: 1: Hours, 2: Earlier order ID */
                        __( 'Same phone limit exceeded within %1$d hours (Earlier Order #%2$d)', 'on-page-checkout-for-elementor' ),
                        $phone_hours,
                        $earlier_order->get_id()
                    );

                    if ( ! $is_log_only ) {
                        W3OPCFE_FOP_Logger::log( [
                            'phone'       => $normalized_phone,
                            'device_id'   => $device_id,
                            'device_hash' => $device_hash,
                            'ip_address'  => $ip_address,
                            'decision'    => 'blocked',
                            'reasons'     => $reason,
                            'context'     => $context,
                        ] );

                        return [
                            'decision'      => 'block',
                            'errors'        => [ $this->get_generic_block_message() ],
                            'block_message' => $this->get_generic_block_message(),
                            'risk_badge'    => 'Blocked',
                            'risk_reasons'  => [ $reason ],
                            'merged_order'  => null,
                            'phone'         => $normalized_phone,
                            'device_id'     => $device_id,
                            'device_hash'   => $device_hash,
                            'ip_address'    => $ip_address,
                        ];
                    } else {
                        $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                        $risk_reasons[] = '[Log-Only Block] ' . $reason;
                    }
                } elseif ( 'merge' === $phone_action ) {
                    // Attempt to merge items into earlier order if still open (pending, on-hold, processing)
                    $can_merge = in_array( $earlier_order->get_status(), [ 'pending', 'on-hold', 'processing' ], true );

                    if ( $can_merge && ! empty( $items ) && ! $is_log_only ) {
                        $merged_success = W3OPCFE_FOP_Velocity_Controller::merge_into_order( $earlier_order, $items );
                        if ( $merged_success ) {
                            W3OPCFE_FOP_Logger::log( [
                                'phone'       => $normalized_phone,
                                'device_id'   => $device_id,
                                'device_hash' => $device_hash,
                                'ip_address'  => $ip_address,
                                'decision'    => 'merged',
                                'reasons'     => sprintf( 'Merged into existing Order #%d', $earlier_order->get_id() ),
                                'order_id'    => $earlier_order->get_id(),
                                'order_total' => $earlier_order->get_total(),
                                'context'     => $context,
                            ] );

                            return [
                                'decision'      => 'merge',
                                'errors'        => [],
                                'block_message' => '',
                                'risk_badge'    => 'Merged',
                                'risk_reasons'  => [ sprintf( 'Merged into Order #%d', $earlier_order->get_id() ) ],
                                'merged_order'  => $earlier_order,
                                'phone'         => $normalized_phone,
                                'device_id'     => $device_id,
                                'device_hash'   => $device_hash,
                                'ip_address'    => $ip_address,
                            ];
                        }
                    } else {
                        // If earlier order already shipped/completed, fallback to Flag
                        $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                        $risk_reasons[] = sprintf(
                            /* translators: 1: Hours, 2: Earlier order ID, 3: Earlier status */
                            __( 'Repeat order from same phone within %1$d hours (Earlier Order #%2$d is %3$s)', 'on-page-checkout-for-elementor' ),
                            $phone_hours,
                            $earlier_order->get_id(),
                            $earlier_order->get_status()
                        );
                    }
                } else {
                    // Flag action
                    $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                    $risk_reasons[] = sprintf(
                        __( 'Repeat order from same phone within %1$d hours (Earlier Order #%2$d)', 'on-page-checkout-for-elementor' ),
                        $phone_hours,
                        $earlier_order->get_id()
                    );
                }
            }

            // Check 2C: Device Order Limit
            if ( ! empty( $settings['enable_device_limit'] ) && ( ! empty( $device_id ) || ! empty( $device_hash ) ) ) {
                $max_device_orders = ! empty( $settings['max_device_orders_per_hour'] ) ? absint( $settings['max_device_orders_per_hour'] ) : 2;
                $device_orders_count = W3OPCFE_FOP_Velocity_Controller::count_device_orders( $device_id, $device_hash, 1 );

                if ( $device_orders_count >= $max_device_orders ) {
                    $reason = sprintf(
                        /* translators: 1: Order count */
                        __( 'Device order limit reached (%1$d orders in 1 hour)', 'on-page-checkout-for-elementor' ),
                        $device_orders_count
                    );

                    if ( 'block' === ( $settings['device_limit_action'] ?? 'block' ) ) {
                        if ( ! $is_log_only ) {
                            W3OPCFE_FOP_Logger::log( [
                                'phone'       => $normalized_phone,
                                'device_id'   => $device_id,
                                'device_hash' => $device_hash,
                                'ip_address'  => $ip_address,
                                'decision'    => 'blocked',
                                'reasons'     => $reason,
                                'context'     => $context,
                            ] );

                            return [
                                'decision'      => 'block',
                                'errors'        => [ $this->get_generic_block_message() ],
                                'block_message' => $this->get_generic_block_message(),
                                'risk_badge'    => 'Blocked',
                                'risk_reasons'  => [ $reason ],
                                'merged_order'  => null,
                                'phone'         => $normalized_phone,
                                'device_id'     => $device_id,
                                'device_hash'   => $device_hash,
                                'ip_address'    => $ip_address,
                            ];
                        } else {
                            $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                            $risk_reasons[] = '[Log-Only Block] ' . $reason;
                        }
                    } else {
                        $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                        $risk_reasons[] = $reason;
                    }
                }
            }

            // Check 2D: IP Velocity Limit (Weak Signal)
            if ( ! empty( $settings['enable_ip_limit'] ) && ! empty( $ip_address ) && '127.0.0.1' !== $ip_address ) {
                $max_ip_orders = ! empty( $settings['max_ip_orders_per_hour'] ) ? absint( $settings['max_ip_orders_per_hour'] ) : 10;
                $ip_orders_count = W3OPCFE_FOP_Velocity_Controller::count_ip_orders( $ip_address, 1 );

                if ( $ip_orders_count >= $max_ip_orders ) {
                    $reason = sprintf(
                        /* translators: 1: Count, 2: IP */
                        __( 'High IP velocity (%1$d orders in 1 hour from IP %2$s)', 'on-page-checkout-for-elementor' ),
                        $ip_orders_count,
                        $ip_address
                    );

                    if ( 'block' === ( $settings['ip_limit_action'] ?? 'flag' ) ) {
                        if ( ! $is_log_only ) {
                            W3OPCFE_FOP_Logger::log( [
                                'phone'       => $normalized_phone,
                                'device_id'   => $device_id,
                                'device_hash' => $device_hash,
                                'ip_address'  => $ip_address,
                                'decision'    => 'blocked',
                                'reasons'     => $reason,
                                'context'     => $context,
                            ] );

                            return [
                                'decision'      => 'block',
                                'errors'        => [ $this->get_generic_block_message() ],
                                'block_message' => $this->get_generic_block_message(),
                                'risk_badge'    => 'Blocked',
                                'risk_reasons'  => [ $reason ],
                                'merged_order'  => null,
                                'phone'         => $normalized_phone,
                                'device_id'     => $device_id,
                                'device_hash'   => $device_hash,
                                'ip_address'    => $ip_address,
                            ];
                        } else {
                            $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                            $risk_reasons[] = '[Log-Only Block] ' . $reason;
                        }
                    } else {
                        $risk_badge = ( 'Duplicate' !== $risk_badge ) ? 'Flagged' : $risk_badge;
                        $risk_reasons[] = $reason;
                    }
                }
            }
        }

        $result = [
            'decision'      => ( 'Clean' === $risk_badge ) ? 'allow' : 'flag',
            'errors'        => [],
            'block_message' => '',
            'risk_badge'    => $risk_badge,
            'risk_reasons'  => $risk_reasons,
            'merged_order'  => null,
            'phone'         => $normalized_phone,
            'device_id'     => $device_id,
            'device_hash'   => $device_hash,
            'ip_address'    => $ip_address,
        ];

        // Store evaluation on instance so attach_order_protection_meta can read it
        $this->current_evaluation = $result;

        return $result;
    }

    /**
     * Classic WooCommerce Checkout validation hook.
     *
     * @param array     $data Posted checkout fields data.
     * @param \WP_Error $errors Checkout errors collection.
     */
    public function validate_classic_checkout( $data, $errors ) {
        $result = $this->evaluate_checkout( $data, [], 'store_checkout' );

        if ( 'block' === $result['decision'] ) {
            $msg = ! empty( $result['block_message'] ) ? $result['block_message'] : $this->get_generic_block_message();
            $errors->add( 'w3opcfe_blocked', esc_html( $msg ) );
        }
    }

    /**
     * Store normalized phone into WooCommerce order on creation.
     *
     * @param \WC_Order $order
     * @param array     $data
     */
    public function normalize_order_phone( $order, $data ) {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $phone = $order->get_billing_phone();
        if ( ! empty( $phone ) ) {
            $normalized = W3OPCFE_FOP_Input_Validator::normalize_phone( $phone );
            if ( '' !== $normalized ) {
                $order->set_billing_phone( $normalized );
            }
        }

        $shipping_phone = $order->get_shipping_phone();
        if ( ! empty( $shipping_phone ) ) {
            $norm_ship = W3OPCFE_FOP_Input_Validator::normalize_phone( $shipping_phone );
            if ( '' !== $norm_ship ) {
                $order->set_shipping_phone( $norm_ship );
            }
        }
    }

    /**
     * Attach risk evaluation metadata, device ID, IP, and audit log to the newly processed order.
     *
     * @param int       $order_id
     * @param array     $posted_data
     * @param \WC_Order $order
     */
    public function attach_order_protection_meta( $order_id, $posted_data = [], $order = null ) {
        if ( ! $this->is_enabled() ) {
            return;
        }

        if ( ! $order instanceof \WC_Order ) {
            $order = wc_get_order( $order_id );
        }
        if ( ! $order ) {
            return;
        }

        $eval = $this->current_evaluation;
        $device_id   = $eval['device_id'] ?? W3OPCFE_FOP_Velocity_Controller::get_device_id();
        $device_hash = $eval['device_hash'] ?? W3OPCFE_FOP_Velocity_Controller::get_device_hash();
        $ip_address  = $eval['ip_address'] ?? W3OPCFE_FOP_Velocity_Controller::get_client_ip();
        $risk_badge  = $eval['risk_badge'] ?? 'Clean';
        $risk_reasons= $eval['risk_reasons'] ?? [];
        $phone       = $order->get_billing_phone();

        // Save order meta
        $order->update_meta_data( '_w3opcfe_risk_badge', $risk_badge );
        $order->update_meta_data( '_w3opcfe_risk_reasons', $risk_reasons );
        if ( ! empty( $device_id ) ) {
            $order->update_meta_data( '_w3opcfe_device_id', $device_id );
        }
        if ( ! empty( $device_hash ) ) {
            $order->update_meta_data( '_w3opcfe_device_hash', $device_hash );
        }
        if ( ! empty( $ip_address ) ) {
            $order->update_meta_data( '_w3opcfe_customer_ip', $ip_address );
        }

        // Add internal order note if flagged or duplicate
        if ( in_array( $risk_badge, [ 'Flagged', 'Duplicate' ], true ) && ! empty( $risk_reasons ) ) {
            $note_body = sprintf(
                "[Fake Order Protection — %s]\n• %s",
                $risk_badge,
                implode( "\n• ", $risk_reasons )
            );
            $order->add_order_note( $note_body, false, false );
        }

        $order->save();

        // Log decision to audit trail table
        W3OPCFE_FOP_Logger::log( [
            'phone'       => $phone,
            'device_id'   => $device_id,
            'device_hash' => $device_hash,
            'ip_address'  => $ip_address,
            'decision'    => ( 'Clean' === $risk_badge ) ? 'allowed' : strtolower( $risk_badge ),
            'reasons'     => $risk_reasons,
            'order_id'    => $order_id,
            'order_total' => $order->get_total(),
            'context'     => ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? 'on_page_checkout' : 'store_checkout',
        ] );

        // Reset temporary evaluation
        $this->current_evaluation = null;
    }

    /**
     * Enqueue frontend script additions for live phone normalization and device fingerprinting.
     */
    public function enqueue_frontend_assets() {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $settings = $this->get_settings();

        // Attach validation & device config to the localized object for w3opcfe-frontend-script
        wp_localize_script( 'w3opcfe-frontend-script', 'w3opcfe_fop_object', [
            'enabled'            => true,
            'validate_phone'     => ! empty( $settings['enable_phone_validation'] ),
            'msg_invalid_phone'  => $settings['msg_invalid_phone'],
            'validate_name'      => ! empty( $settings['enable_name_validation'] ),
            'min_name_length'    => absint( $settings['min_name_length'] ),
            'msg_invalid_name'   => $settings['msg_invalid_name'],
            'validate_address'   => ! empty( $settings['enable_address_validation'] ),
            'min_address_length' => absint( $settings['min_address_length'] ),
            'min_address_words'  => absint( $settings['min_address_words'] ),
            'msg_invalid_address'=> $settings['msg_invalid_address'],
        ] );

        // If on WooCommerce store checkout page, ensure the live normalization & device script is enqueued
        if ( function_exists( 'is_checkout' ) && is_checkout() && ! is_order_received_page() ) {
            wp_enqueue_script( 'w3opcfe-frontend-script' );
        }
    }
}
