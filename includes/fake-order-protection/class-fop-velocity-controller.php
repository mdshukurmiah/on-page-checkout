<?php
/**
 * Fake Order Protection - Duplicate and Velocity Controller
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_Velocity_Controller {

    /**
     * Accurately retrieve client IP address, handling Cloudflare and reverse proxies.
     *
     * @return string
     */
    public static function get_client_ip() {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ( $headers as $header ) {
            if ( empty( $_SERVER[ $header ] ) ) {
                continue;
            }

            $raw_val = (string) $_SERVER[ $header ];

            // HTTP_X_FORWARDED_FOR may contain multiple comma-separated IPs
            $ips = explode( ',', $raw_val );
            foreach ( $ips as $ip ) {
                $ip = trim( $ip );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    // Check if public IP (not private or loopback)
                    if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                        return $ip;
                    }
                    // Fallback to validated IP if no public found yet
                    $fallback_ip = $ip;
                }
            }

            if ( ! empty( $fallback_ip ) ) {
                return $fallback_ip;
            }
        }

        return ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';
    }

    /**
     * Retrieve device UUID from POST payload or Cookie.
     *
     * @return string
     */
    public static function get_device_id() {
        $id = '';
        if ( ! empty( $_POST['w3opcfe_device_id'] ) ) {
            $id = sanitize_text_field( wp_unslash( $_POST['w3opcfe_device_id'] ) );
        } elseif ( ! empty( $_COOKIE['w3opcfe_device_id'] ) ) {
            $id = sanitize_text_field( wp_unslash( $_COOKIE['w3opcfe_device_id'] ) );
        }

        // Validate UUIDv4 format: 8-4-4-4-12 hex chars
        if ( preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id ) ) {
            return strtolower( $id );
        }

        return '';
    }

    /**
     * Retrieve lightweight browser fingerprint hash.
     *
     * @return string
     */
    public static function get_device_hash() {
        $hash = '';
        if ( ! empty( $_POST['w3opcfe_device_hash'] ) ) {
            $hash = sanitize_text_field( wp_unslash( $_POST['w3opcfe_device_hash'] ) );
        } elseif ( ! empty( $_COOKIE['w3opcfe_device_hash'] ) ) {
            $hash = sanitize_text_field( wp_unslash( $_COOKIE['w3opcfe_device_hash'] ) );
        }

        // Alphanumeric hash 8-64 chars
        if ( preg_match( '/^[a-z0-9_-]{8,64}$/i', $hash ) ) {
            return strtolower( $hash );
        }

        return '';
    }

    /**
     * Find existing orders placed with the same normalized phone within X hours.
     *
     * @param string $phone Normalized phone.
     * @param int    $hours Lookback window.
     * @param int    $exclude_order_id Exclude current order ID if updating.
     * @return \WC_Order[]
     */
    public static function find_recent_orders_by_phone( $phone, $hours = 24, $exclude_order_id = 0 ) {
        if ( empty( $phone ) ) {
            return [];
        }

        $after = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, $hours ) * HOUR_IN_SECONDS ) );

        $query_args = [
            'billing_phone' => $phone,
            'date_created'  => '>=' . $after,
            'status'        => [ 'pending', 'processing', 'on-hold', 'completed' ],
            'limit'         => 10,
            'orderby'       => 'date',
            'order'         => 'DESC',
        ];

        if ( $exclude_order_id > 0 ) {
            $query_args['exclude'] = [ $exclude_order_id ];
        }

        return wc_get_orders( $query_args );
    }

    /**
     * Check if a recent order within Y minutes contains any of the same products.
     *
     * @param string $phone
     * @param int    $minutes
     * @param array  $product_ids Array of product or variation IDs being ordered.
     * @return \WC_Order|false Matched earlier order or false.
     */
    public static function find_duplicate_product_order( $phone, $minutes = 60, array $product_ids = [] ) {
        if ( empty( $phone ) || empty( $product_ids ) ) {
            return false;
        }

        $after = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, $minutes ) * MINUTE_IN_SECONDS ) );

        $recent_orders = wc_get_orders( [
            'billing_phone' => $phone,
            'date_created'  => '>=' . $after,
            'status'        => [ 'pending', 'processing', 'on-hold', 'completed' ],
            'limit'         => 5,
            'orderby'       => 'date',
            'order'         => 'DESC',
        ] );

        foreach ( $recent_orders as $order ) {
            foreach ( $order->get_items() as $item ) {
                $pid = $item->get_product_id();
                $vid = $item->get_variation_id();
                if ( in_array( $pid, $product_ids, true ) || ( $vid > 0 && in_array( $vid, $product_ids, true ) ) ) {
                    return $order;
                }
            }
        }

        return false;
    }

    /**
     * Count orders placed by this device ID or fingerprint hash in the last 1 hour.
     *
     * @param string $device_id
     * @param string $device_hash
     * @param int    $hours
     * @return int
     */
    public static function count_device_orders( $device_id, $device_hash = '', $hours = 1 ) {
        if ( empty( $device_id ) && empty( $device_hash ) ) {
            return 0;
        }

        $after = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, $hours ) * HOUR_IN_SECONDS ) );

        // Search by meta query
        $meta_queries = [ 'relation' => 'OR' ];
        if ( ! empty( $device_id ) ) {
            $meta_queries[] = [
                'key'   => '_w3opcfe_device_id',
                'value' => $device_id,
            ];
        }
        if ( ! empty( $device_hash ) ) {
            $meta_queries[] = [
                'key'   => '_w3opcfe_device_hash',
                'value' => $device_hash,
            ];
        }

        $orders = wc_get_orders( [
            'date_created' => '>=' . $after,
            'status'       => [ 'pending', 'processing', 'on-hold', 'completed' ],
            'limit'        => 50,
            'meta_query'   => $meta_queries,
            'return'       => 'ids',
        ] );

        return count( $orders );
    }

    /**
     * Count orders placed from this IP address in the last 1 hour.
     *
     * @param string $ip
     * @param int    $hours
     * @return int
     */
    public static function count_ip_orders( $ip, $hours = 1 ) {
        if ( empty( $ip ) || '127.0.0.1' === $ip ) {
            return 0;
        }

        $after = gmdate( 'Y-m-d H:i:s', time() - ( max( 1, $hours ) * HOUR_IN_SECONDS ) );

        $orders = wc_get_orders( [
            'date_created' => '>=' . $after,
            'customer_ip_address' => $ip,
            'status'       => [ 'pending', 'processing', 'on-hold', 'completed' ],
            'limit'        => 50,
            'return'       => 'ids',
        ] );

        return count( $orders );
    }

    /**
     * Merge items into an existing pending/on-hold/processing order.
     *
     * @param \WC_Order $existing_order Target order.
     * @param array     $items Array of items: [['product_id'=>X, 'variation_id'=>Y, 'quantity'=>Z], ...]
     * @return bool
     */
    public static function merge_into_order( \WC_Order $existing_order, array $items ) {
        if ( empty( $items ) ) {
            return false;
        }

        // Only merge if existing order is still open/editable (not shipped, completed, or cancelled)
        $mergeable_statuses = [ 'pending', 'on-hold', 'processing' ];
        if ( ! in_array( $existing_order->get_status(), $mergeable_statuses, true ) ) {
            return false;
        }

        $added_names = [];

        foreach ( $items as $item ) {
            $pid = absint( $item['product_id'] ?? 0 );
            $vid = absint( $item['variation_id'] ?? 0 );
            $qty = max( 1, absint( $item['quantity'] ?? 1 ) );

            $product = wc_get_product( $vid > 0 ? $vid : $pid );
            if ( ! $product instanceof \WC_Product ) {
                continue;
            }

            // Check if product already exists in the order to increment quantity instead of duplicate line
            $existing_line_item = null;
            foreach ( $existing_order->get_items() as $line_item ) {
                $line_pid = $line_item->get_product_id();
                $line_vid = $line_item->get_variation_id();
                if ( ( $vid > 0 && $line_vid === $vid ) || ( 0 === $vid && $line_pid === $pid ) ) {
                    $existing_line_item = $line_item;
                    break;
                }
            }

            if ( $existing_line_item ) {
                $new_qty = $existing_line_item->get_quantity() + $qty;
                $existing_line_item->set_quantity( $new_qty );
                $existing_line_item->set_subtotal( wc_get_price_excluding_tax( $product, [ 'qty' => $new_qty ] ) );
                $existing_line_item->set_total( wc_get_price_excluding_tax( $product, [ 'qty' => $new_qty ] ) );
                $added_names[] = sprintf( '%s (qty increased to %d)', $product->get_name(), $new_qty );
            } else {
                $item_id = $existing_order->add_product( $product, $qty );
                if ( $item_id ) {
                    $added_names[] = sprintf( '%s x %d', $product->get_name(), $qty );
                }
            }
        }

        if ( empty( $added_names ) ) {
            return false;
        }

        // Recalculate totals
        $existing_order->calculate_totals();

        // Note linking the merge
        $note = sprintf(
            /* translators: 1: List of merged items, 2: Formatted total */
            __( '[Fake Order Protection — Order Merged] Customer submitted a repeat order from same phone within lookback window. Added items: %1$s. New Order Total: %2$s', 'on-page-checkout-for-elementor' ),
            implode( ', ', $added_names ),
            wp_strip_all_tags( wc_price( $existing_order->get_total(), [ 'currency' => $existing_order->get_currency() ] ) )
        );

        $existing_order->add_order_note( $note, false, true );

        // Update risk badge to Merged
        $existing_order->update_meta_data( '_w3opcfe_risk_badge', 'Merged' );
        $existing_order->update_meta_data( '_w3opcfe_merged_at', current_time( 'mysql' ) );

        $existing_order->save();

        return true;
    }
}

