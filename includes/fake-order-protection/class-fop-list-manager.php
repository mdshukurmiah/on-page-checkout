<?php
/**
 * Fake Order Protection - Blocklists & Allowlists Manager
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_List_Manager {

    /**
     * Table name.
     */
    const TABLE_NAME = 'w3opcfe_fop_lists';

    /**
     * Initialize admin action hooks.
     */
    public static function init() {
        add_action( 'admin_post_w3opcfe_fop_export_csv', [ __CLASS__, 'handle_export_csv' ] );
        add_action( 'admin_post_w3opcfe_fop_import_csv', [ __CLASS__, 'handle_import_csv' ] );
        add_action( 'admin_post_w3opcfe_fop_add_entry', [ __CLASS__, 'handle_add_entry' ] );
        add_action( 'admin_post_w3opcfe_fop_delete_entry', [ __CLASS__, 'handle_delete_entry' ] );
    }

    /**
     * Get full table name with prefix.
     *
     * @return string
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_NAME;
    }

    /**
     * Create table if not exists using dbDelta.
     */
    public static function create_table() {
        global $wpdb;
        $table_name      = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            list_type varchar(20) NOT NULL DEFAULT 'blocklist',
            entry_type varchar(30) NOT NULL DEFAULT 'phone',
            entry_value varchar(255) NOT NULL,
            reason varchar(255) NOT NULL DEFAULT '',
            source varchar(30) NOT NULL DEFAULT 'manual',
            created_at datetime NOT NULL,
            expires_at datetime NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_lookup (list_type, entry_type, entry_value(191)),
            KEY idx_type (list_type),
            KEY idx_expires (expires_at)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Register custom "Returned" order status in WooCommerce.
     */
    public static function register_returned_order_status() {
        register_post_status( 'wc-returned', [
            'label'                     => _x( 'Returned', 'Order status', 'on-page-checkout-for-elementor' ),
            'public'                    => true,
            'exclude_from_search'       => false,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            /* translators: %s: number of orders */
            'label_count'               => _n_noop( 'Returned <span class="count">(%s)</span>', 'Returned <span class="count">(%s)</span>', 'on-page-checkout-for-elementor' ),
        ] );
    }

    /**
     * Add "Returned" status to WooCommerce statuses list.
     *
     * @param array $statuses
     * @return array
     */
    public static function add_returned_to_order_statuses( $statuses ) {
        $new_statuses = [];
        foreach ( $statuses as $key => $status ) {
            $new_statuses[ $key ] = $status;
            if ( 'wc-refunded' === $key || 'wc-completed' === $key ) {
                $new_statuses['wc-returned'] = _x( 'Returned', 'Order status', 'on-page-checkout-for-elementor' );
            }
        }
        if ( ! isset( $new_statuses['wc-returned'] ) ) {
            $new_statuses['wc-returned'] = _x( 'Returned', 'Order status', 'on-page-checkout-for-elementor' );
        }
        return $new_statuses;
    }

    /**
     * Add an entry to blocklist or allowlist.
     * Automatically normalizes phone numbers if entry_type is 'phone'.
     *
     * @param string      $list_type 'blocklist' or 'allowlist'
     * @param string      $entry_type 'phone', 'device_id', 'fingerprint', 'ip', 'address_keyword'
     * @param string      $value
     * @param string      $reason
     * @param string      $source 'manual', 'auto', 'network', 'import'
     * @param string|null $expires_at 'Y-m-d H:i:s' or null
     * @return int|false
     */
    public static function add_entry( $list_type, $entry_type, $value, $reason = '', $source = 'manual', $expires_at = null ) {
        global $wpdb;

        $value = trim( (string) $value );
        if ( '' === $value ) {
            return false;
        }

        // Phone normalization
        if ( 'phone' === $entry_type ) {
            $value = W3OPCFE_FOP_Input_Validator::normalize_phone( $value );
            if ( empty( $value ) ) {
                return false;
            }
        }

        $table_name = self::get_table_name();

        // Check if duplicate already exists
        $existing = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE list_type = %s AND entry_type = %s AND entry_value = %s",
                $list_type,
                $entry_type,
                $value
            )
        );

        if ( $existing ) {
            // Update reason and expiry if needed
            $wpdb->update(
                $table_name,
                [
                    'reason'     => sanitize_text_field( $reason ),
                    'source'     => sanitize_key( $source ),
                    'expires_at' => $expires_at,
                ],
                [ 'id' => $existing ],
                [ '%s', '%s', '%s' ],
                [ '%d' ]
            );
            return (int) $existing;
        }

        $result = $wpdb->insert(
            $table_name,
            [
                'list_type'   => sanitize_key( $list_type ),
                'entry_type'  => sanitize_key( $entry_type ),
                'entry_value' => sanitize_text_field( $value ),
                'reason'      => sanitize_text_field( $reason ),
                'source'      => sanitize_key( $source ),
                'created_at'  => current_time( 'mysql' ),
                'expires_at'  => $expires_at,
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete entry by ID.
     *
     * @param int $id
     * @return bool
     */
    public static function delete_entry( $id ) {
        global $wpdb;
        $table_name = self::get_table_name();
        return (bool) $wpdb->delete( $table_name, [ 'id' => absint( $id ) ], [ '%d' ] );
    }

    /**
     * Check if a phone, device, or IP is in the Allowlist.
     * The Allowlist ALWAYS wins over any blocklist or velocity check.
     *
     * @param string $phone
     * @param string $device_id
     * @param string $ip
     * @return array|false Matched entry array or false.
     */
    public static function check_allowlist( $phone = '', $device_id = '', $ip = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();
        $now        = current_time( 'mysql' );

        if ( ! empty( $phone ) ) {
            $norm = W3OPCFE_FOP_Input_Validator::normalize_phone( $phone );
            $row  = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'allowlist' AND entry_type = 'phone' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $norm,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        if ( ! empty( $device_id ) ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'allowlist' AND entry_type = 'device_id' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $device_id,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        return false;
    }

    /**
     * Check if customer identifiers match any active entry in the Blocklist.
     *
     * @param string $phone
     * @param string $device_id
     * @param string $device_hash
     * @param string $ip
     * @param string $address
     * @return array|false Matched entry array or false.
     */
    public static function check_blocklist( $phone = '', $device_id = '', $device_hash = '', $ip = '', $address = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();
        $now        = current_time( 'mysql' );

        // 1. Phone check
        if ( ! empty( $phone ) ) {
            $norm = W3OPCFE_FOP_Input_Validator::normalize_phone( $phone );
            $row  = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'blocklist' AND entry_type = 'phone' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $norm,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        // 2. Device ID check
        if ( ! empty( $device_id ) ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'blocklist' AND entry_type = 'device_id' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $device_id,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        // 3. Fingerprint Hash check
        if ( ! empty( $device_hash ) ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'blocklist' AND entry_type = 'fingerprint' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $device_hash,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        // 4. IP address check
        if ( ! empty( $ip ) && '127.0.0.1' !== $ip ) {
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'blocklist' AND entry_type = 'ip' AND entry_value = %s AND (expires_at IS NULL OR expires_at > %s) LIMIT 1",
                    $ip,
                    $now
                ),
                ARRAY_A
            );
            if ( $row ) {
                return $row;
            }
        }

        // 5. Address keywords check
        if ( ! empty( $address ) ) {
            $keywords = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE list_type = 'blocklist' AND entry_type = 'address_keyword' AND (expires_at IS NULL OR expires_at > %s)",
                    $now
                ),
                ARRAY_A
            );

            if ( ! empty( $keywords ) ) {
                $lower_addr = mb_strtolower( $address );
                foreach ( $keywords as $kw_row ) {
                    $kw = mb_strtolower( trim( $kw_row['entry_value'] ) );
                    if ( '' !== $kw && mb_stripos( $lower_addr, $kw ) !== false ) {
                        return $kw_row;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check phone against external Network Blocklist API (Pro, opt-in).
     * Sends HMAC-SHA256(phone, secret), fails open on timeout (2s) or error.
     *
     * @param string $phone Normalized phone.
     * @param array  $settings Plugin settings.
     * @return array|false Returns API match data or false.
     */
    public static function check_network_blocklist( $phone, array $settings ) {
        if ( empty( $settings['enable_network_blocklist'] ) || empty( $phone ) ) {
            return false;
        }

        $api_url = ! empty( $settings['network_api_url'] ) ? esc_url_raw( $settings['network_api_url'] ) : '';
        $secret  = ! empty( $settings['network_license_secret'] ) ? $settings['network_license_secret'] : 'w3opcfe_fop_secret';

        if ( empty( $api_url ) ) {
            return false;
        }

        // HMAC-SHA256 hash — never send raw phone
        $phone_hash = hash_hmac( 'sha256', $phone, $secret );

        // 12-hour transient cache
        $cache_key = 'w3opcfe_net_' . substr( $phone_hash, 0, 32 );
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $response = wp_remote_post( $api_url, [
            'timeout' => 2.0, // 2-second strict timeout
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body' => wp_json_encode( [
                'phone_hash'  => $phone_hash,
                'license_key' => $settings['network_license_key'] ?? '',
            ] ),
        ] );

        // Fail open on connection error or timeout
        if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
            return false;
        }

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( ! is_array( $data ) ) {
            return false;
        }

        $min_reports = ! empty( $settings['network_min_reports'] ) ? absint( $settings['network_min_reports'] ) : 3;
        $reports     = absint( $data['reports'] ?? 0 );

        $is_flagged = ( $reports >= $min_reports );
        $result     = $is_flagged ? [ 'reports' => $reports, 'risk' => $data['risk'] ?? 'high' ] : false;

        // Cache for 12 hours
        set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );

        return $result;
    }

    /**
     * Auto-blocklist trigger when order status changes to cancelled, failed, or returned.
     *
     * @param int       $order_id
     * @param string    $old_status
     * @param string    $new_status
     * @param \WC_Order $order
     */
    public static function handle_order_status_change( $order_id, $old_status, $new_status, $order = null ) {
        $settings = W3OPCFE_FOP_Settings::get_settings();

        if ( empty( $settings['enable_protection'] ) ) {
            return;
        }

        if ( ! $order instanceof \WC_Order ) {
            $order = wc_get_order( $order_id );
        }
        if ( ! $order ) {
            return;
        }

        $phone = $order->get_billing_phone();
        if ( empty( $phone ) ) {
            return;
        }

        $norm_phone = W3OPCFE_FOP_Input_Validator::normalize_phone( $phone );
        if ( empty( $norm_phone ) ) {
            return;
        }

        // --- 1. Auto-Allowlist Check on "completed" status ---
        if ( 'completed' === $new_status && ! empty( $settings['enable_auto_allowlist'] ) ) {
            $threshold = ! empty( $settings['auto_allowlist_threshold'] ) ? absint( $settings['auto_allowlist_threshold'] ) : 3;
            $completed_count = count( wc_get_orders( [
                'billing_phone' => $norm_phone,
                'status'        => 'completed',
                'limit'         => $threshold + 1,
                'return'        => 'ids',
            ] ) );

            if ( $completed_count >= $threshold ) {
                self::add_entry(
                    'allowlist',
                    'phone',
                    $norm_phone,
                    sprintf( __( 'Auto-allowlisted after %d completed orders', 'on-page-checkout-for-elementor' ), $completed_count ),
                    'auto'
                );
            }
        }

        // --- 2. Auto-Blocklist Check on bad statuses (cancelled, failed, returned) ---
        $bad_statuses = [ 'cancelled', 'failed', 'returned' ];
        if ( in_array( $new_status, $bad_statuses, true ) && ! empty( $settings['enable_auto_blocklist'] ) ) {
            $threshold = ! empty( $settings['auto_blocklist_threshold'] ) ? absint( $settings['auto_blocklist_threshold'] ) : 2;

            $bad_count = count( wc_get_orders( [
                'billing_phone' => $norm_phone,
                'status'        => $bad_statuses,
                'limit'         => $threshold + 1,
                'return'        => 'ids',
            ] ) );

            if ( $bad_count >= $threshold ) {
                $expiry_days = ! empty( $settings['auto_blocklist_expiry_days'] ) ? absint( $settings['auto_blocklist_expiry_days'] ) : 0;
                $expires_at  = $expiry_days > 0 ? gmdate( 'Y-m-d H:i:s', time() + ( $expiry_days * DAY_IN_SECONDS ) ) : null;

                $inserted = self::add_entry(
                    'blocklist',
                    'phone',
                    $norm_phone,
                    sprintf( __( 'Auto-blocked after %d cancelled/returned/failed orders', 'on-page-checkout-for-elementor' ), $bad_count ),
                    'auto',
                    $expires_at
                );

                if ( $inserted ) {
                    $order->add_order_note(
                        sprintf( __( '[Fake Order Protection] Phone %s was automatically added to the Blocklist (%d bad orders threshold reached).', 'on-page-checkout-for-elementor' ), $norm_phone, $bad_count ),
                        false,
                        false
                    );
                }
            }
        }
    }

    /**
     * Get paginated entries for admin list table.
     *
     * @param string $list_type 'blocklist' or 'allowlist'
     * @param int    $per_page
     * @param int    $offset
     * @param string $search
     * @param string $entry_type_filter
     * @return array
     */
    public static function get_entries( $list_type = 'blocklist', $per_page = 20, $offset = 0, $search = '', $entry_type_filter = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();
        $where      = [ $wpdb->prepare( "list_type = %s", $list_type ) ];

        if ( ! empty( $search ) ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where[] = $wpdb->prepare( "(entry_value LIKE %s OR reason LIKE %s)", $like, $like );
        }

        if ( ! empty( $entry_type_filter ) ) {
            $where[] = $wpdb->prepare( "entry_type = %s", $entry_type_filter );
        }

        $where_sql = implode( ' AND ', $where );

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE {$where_sql} ORDER BY id DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ),
            ARRAY_A
        );
    }

    /**
     * Get total count of entries matching filters.
     *
     * @param string $list_type
     * @param string $search
     * @param string $entry_type_filter
     * @return int
     */
    public static function get_total_count( $list_type = 'blocklist', $search = '', $entry_type_filter = '' ) {
        global $wpdb;
        $table_name = self::get_table_name();
        $where      = [ $wpdb->prepare( "list_type = %s", $list_type ) ];

        if ( ! empty( $search ) ) {
            $like = '%' . $wpdb->esc_like( $search ) . '%';
            $where[] = $wpdb->prepare( "(entry_value LIKE %s OR reason LIKE %s)", $like, $like );
        }

        if ( ! empty( $entry_type_filter ) ) {
            $where[] = $wpdb->prepare( "entry_type = %s", $entry_type_filter );
        }

        $where_sql = implode( ' AND ', $where );

        return (int) $wpdb->get_var( "SELECT COUNT(id) FROM {$table_name} WHERE {$where_sql}" );
    }

    /**
     * Export list entries to CSV.
     *
     * @param string $list_type 'blocklist' or 'allowlist'
     */
    public static function export_csv( $list_type = 'blocklist' ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'on-page-checkout-for-elementor' ) );
        }

        check_admin_referer( 'w3opcfe_export_nonce' );

        global $wpdb;
        $table_name = self::get_table_name();
        $rows       = $wpdb->get_results(
            $wpdb->prepare( "SELECT entry_type, entry_value, reason, source, created_at, expires_at FROM {$table_name} WHERE list_type = %s ORDER BY id ASC", $list_type ),
            ARRAY_A
        );

        $filename = sprintf( 'w3opcfe-%s-%s.csv', $list_type, gmdate( 'Y-m-d' ) );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, [ 'entry_type', 'entry_value', 'reason', 'source', 'created_at', 'expires_at' ] );

        foreach ( $rows as $row ) {
            fputcsv( $output, [
                $row['entry_type'],
                $row['entry_value'],
                $row['reason'],
                $row['source'],
                $row['created_at'],
                $row['expires_at'] ?? '',
            ] );
        }

        fclose( $output );
        exit;
    }

    /**
     * Import list entries from an uploaded CSV file.
     * Normalizes phone numbers, skips duplicates, and returns summary stats.
     *
     * @param string $list_type 'blocklist' or 'allowlist'
     * @param string $file_path Path to uploaded CSV file.
     * @return array ['imported' => int, 'skipped' => int, 'errors' => int]
     */
    public static function import_csv( $list_type, $file_path ) {
        if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
            return [ 'imported' => 0, 'skipped' => 0, 'errors' => 1 ];
        }

        $handle = fopen( $file_path, 'r' );
        if ( ! $handle ) {
            return [ 'imported' => 0, 'skipped' => 0, 'errors' => 1 ];
        }

        $imported = 0;
        $skipped  = 0;
        $row_idx  = 0;

        while ( ( $row = fgetcsv( $handle, 2048, ',', '"', '\\' ) ) !== false ) {
            $row_idx++;
            // Skip header row if present
            if ( 1 === $row_idx && ( 'entry_type' === strtolower( $row[0] ?? '' ) || 'type' === strtolower( $row[0] ?? '' ) ) ) {
                continue;
            }

            // Supports single column (just value) or full columns (type, value, reason, ...)
            if ( count( $row ) === 1 ) {
                $val = trim( $row[0] );
                $type = 'phone';
                $reason = 'CSV Import';
            } else {
                $type   = sanitize_key( $row[0] ?? 'phone' );
                $val    = trim( $row[1] ?? '' );
                $reason = sanitize_text_field( $row[2] ?? 'CSV Import' );
            }

            if ( empty( $val ) ) {
                continue;
            }

            $valid_types = [ 'phone', 'device_id', 'fingerprint', 'ip', 'address_keyword' ];
            if ( ! in_array( $type, $valid_types, true ) ) {
                $type = 'phone';
            }

            $res = self::add_entry( $list_type, $type, $val, $reason, 'import' );
            if ( $res ) {
                $imported++;
            } else {
                $skipped++;
            }
        }

        fclose( $handle );

        return [
            'imported' => $imported,
            'skipped'  => $skipped,
            'errors'   => 0,
        ];
    }

    /**
     * Admin POST handler for CSV export.
     */
    public static function handle_export_csv() {
        $list_type = isset( $_GET['list_type'] ) && 'allowlist' === $_GET['list_type'] ? 'allowlist' : 'blocklist';
        self::export_csv( $list_type );
    }

    /**
     * Admin POST handler for CSV import.
     */
    public static function handle_import_csv() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'on-page-checkout-for-elementor' ) );
        }
        check_admin_referer( 'w3opcfe_import_csv_nonce' );

        $list_type = isset( $_POST['list_type'] ) && 'allowlist' === $_POST['list_type'] ? 'allowlist' : 'blocklist';
        $redirect  = admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=' . $list_type );

        if ( empty( $_FILES['csv_file']['tmp_name'] ) ) {
            wp_safe_redirect( add_query_arg( 'import_status', 'no_file', $redirect ) );
            exit;
        }

        $result = self::import_csv( $list_type, $_FILES['csv_file']['tmp_name'] );

        wp_safe_redirect( add_query_arg( [
            'import_status' => 'success',
            'imported'      => $result['imported'],
            'skipped'       => $result['skipped'],
        ], $redirect ) );
        exit;
    }

    /**
     * Admin POST handler for adding an entry manually.
     */
    public static function handle_add_entry() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'on-page-checkout-for-elementor' ) );
        }
        check_admin_referer( 'w3opcfe_add_entry_nonce' );

        $list_type   = isset( $_POST['list_type'] ) && 'allowlist' === $_POST['list_type'] ? 'allowlist' : 'blocklist';
        $entry_type  = sanitize_key( $_POST['entry_type'] ?? 'phone' );
        $entry_value = sanitize_text_field( $_POST['entry_value'] ?? '' );
        $reason      = sanitize_text_field( $_POST['reason'] ?? '' );
        $expires_in  = absint( $_POST['expires_in_days'] ?? 0 );
        $expires_at  = $expires_in > 0 ? gmdate( 'Y-m-d H:i:s', time() + ( $expires_in * DAY_IN_SECONDS ) ) : null;

        $res = self::add_entry( $list_type, $entry_type, $entry_value, $reason, 'manual', $expires_at );

        $redirect = admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=' . $list_type );
        if ( $res ) {
            wp_safe_redirect( add_query_arg( 'entry_added', '1', $redirect ) );
        } else {
            wp_safe_redirect( add_query_arg( 'entry_error', '1', $redirect ) );
        }
        exit;
    }

    /**
     * Admin POST handler for deleting an entry.
     */
    public static function handle_delete_entry() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Permission denied.', 'on-page-checkout-for-elementor' ) );
        }
        check_admin_referer( 'w3opcfe_delete_entry_nonce' );

        $id        = absint( $_GET['id'] ?? 0 );
        $list_type = isset( $_GET['list_type'] ) && 'allowlist' === $_GET['list_type'] ? 'allowlist' : 'blocklist';
        if ( $id ) {
            self::delete_entry( $id );
        }

        $redirect = admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=' . $list_type );
        wp_safe_redirect( add_query_arg( 'entry_deleted', '1', $redirect ) );
        exit;
    }
}
