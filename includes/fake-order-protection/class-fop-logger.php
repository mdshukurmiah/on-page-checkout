<?php
/**
 * Fake Order Protection - Logger & Audit Trail
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_Logger {

    /**
     * Table name without prefix.
     */
    const TABLE_NAME = 'w3opcfe_fop_logs';

    /**
     * Get full table name with WordPress prefix.
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
            timestamp datetime NOT NULL,
            phone varchar(30) NOT NULL DEFAULT '',
            device_id varchar(64) NOT NULL DEFAULT '',
            device_hash varchar(64) NOT NULL DEFAULT '',
            ip_address varchar(45) NOT NULL DEFAULT '',
            decision varchar(20) NOT NULL DEFAULT 'allowed',
            reasons text NOT NULL,
            order_id bigint(20) unsigned NOT NULL DEFAULT 0,
            order_total decimal(10,2) NOT NULL DEFAULT 0.00,
            context varchar(30) NOT NULL DEFAULT 'on_page_checkout',
            PRIMARY KEY (id),
            KEY idx_phone (phone),
            KEY idx_device (device_id),
            KEY idx_ip (ip_address),
            KEY idx_timestamp (timestamp),
            KEY idx_decision (decision)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Log a checkout protection decision.
     *
     * @param array $args
     * @return int|false Inserted ID or false.
     */
    public static function log( array $args ) {
        global $wpdb;

        $table_name = self::get_table_name();

        $defaults = [
            'timestamp'   => current_time( 'mysql' ),
            'phone'       => '',
            'device_id'   => '',
            'device_hash' => '',
            'ip_address'  => '',
            'decision'    => 'allowed', // allowed, blocked, merged, flagged
            'reasons'     => '',
            'order_id'    => 0,
            'order_total' => 0.00,
            'context'     => 'on_page_checkout',
        ];

        $data = wp_parse_args( $args, $defaults );

        if ( is_array( $data['reasons'] ) ) {
            $data['reasons'] = implode( ' | ', $data['reasons'] );
        }

        $result = $wpdb->insert(
            $table_name,
            [
                'timestamp'   => $data['timestamp'],
                'phone'       => sanitize_text_field( $data['phone'] ),
                'device_id'   => sanitize_text_field( $data['device_id'] ),
                'device_hash' => sanitize_text_field( $data['device_hash'] ),
                'ip_address'  => sanitize_text_field( $data['ip_address'] ),
                'decision'    => sanitize_key( $data['decision'] ),
                'reasons'     => sanitize_textarea_field( $data['reasons'] ),
                'order_id'    => absint( $data['order_id'] ),
                'order_total' => floatval( $data['order_total'] ),
                'context'     => sanitize_key( $data['context'] ),
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%f', '%s' ]
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Auto-prune logs older than 90 days.
     *
     * @return int Number of deleted rows.
     */
    public static function prune() {
        global $wpdb;
        $table_name = self::get_table_name();
        $cutoff     = gmdate( 'Y-m-d H:i:s', time() - ( 90 * DAY_IN_SECONDS ) );

        return (int) $wpdb->query(
            $wpdb->prepare( "DELETE FROM {$table_name} WHERE timestamp < %s", $cutoff )
        );
    }
}

