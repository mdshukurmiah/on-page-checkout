<?php
/**
 * Fake Order Protection - Settings Management & UI
 *
 * @package On_Page_Checkout_For_Elementor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class W3OPCFE_FOP_Settings {

    const OPTION_NAME = 'w3opcfe_fop_settings';

    /**
     * Get default settings for Fake Order Protection.
     *
     * @return array
     */
    public static function get_defaults() {
        return [
            // Master toggles
            'enable_protection'         => 1,
            'log_only_mode'             => 0, // When active, rules run and log without blocking/altering

            // 1. Input Validation
            'enable_input_validation'       => 1,
            'enable_phone_validation'       => 1,
            'enable_name_validation'        => 1,
            'min_name_length'               => 3,
            'enable_address_validation'     => 1,
            'min_address_length'            => 10,
            'min_address_words'             => 2,
            'reject_repeated_chars'         => 1,
            'junk_keywords'                 => "test, fake, asdf, qwerty, sample, demo, none, null, 1234, admin",

            // 2. Duplicate and Velocity Control
            'enable_velocity_control'       => 1,
            'same_phone_hours'              => 24,
            'same_phone_action'             => 'merge', // merge, block, flag
            'enable_duplicate_product_check'=> 1,
            'duplicate_product_window'      => 60, // minutes
            'enable_device_limit'           => 1,
            'max_device_orders_per_hour'    => 2,
            'device_limit_action'           => 'block', // block, flag
            'enable_ip_limit'               => 1,
            'max_ip_orders_per_hour'        => 10,
            'ip_limit_action'               => 'flag', // flag (default), block
            'store_contact_phone'           => '',

            // 3. Blocklists and Allowlists
            'enable_blocklist'              => 1,
            'enable_allowlist'              => 1,
            'enable_auto_blocklist'         => 1,
            'auto_blocklist_threshold'      => 2,
            'auto_blocklist_expiry_days'    => 0, // 0 = permanent
            'enable_auto_allowlist'         => 1,
            'auto_allowlist_threshold'      => 3,
            'enable_network_blocklist'      => 0,
            'network_api_url'               => '',
            'network_license_key'           => '',
            'network_license_secret'        => '',
            'network_min_reports'           => 3,
            'network_action'                => 'flag', // flag, block

            // Customizable Error Messages
            'msg_invalid_phone'         => 'Please enter a valid 11-digit mobile number (e.g. 017XXXXXXXX)',
            'msg_invalid_name'          => 'Please enter a valid and complete name (at least 3 characters)',
            'msg_invalid_address'       => 'Please enter a detailed delivery address (at least 10 characters and 2 words)',
            'msg_junk_keyword'          => 'Test or invalid junk information in name or address is not allowed',
            'msg_repeated_chars'        => 'Excessive repeated characters in name or address are not allowed',
            'msg_blocked_generic'       => 'Sorry, we are unable to process your order at this time. For more information, please contact us at: {store_phone}',
        ];
    }

    /**
     * Retrieve settings merged with defaults.
     *
     * @return array
     */
    public static function get_settings() {//
        $saved = get_option( self::OPTION_NAME, [] );
        if ( ! is_array( $saved ) ) {
            $saved = [];
        }
        $defaults = self::get_defaults();

        // Migrate any legacy default Bengali messages to English defaults
        $bengali_defaults = [
            'msg_invalid_phone'   => 'সঠিক ১১ ডিজিটের মোবাইল নম্বর দিন (যেমনঃ 017XXXXXXXX)',
            'msg_invalid_name'    => 'অনুগ্রহ করে সঠিক ও পূর্ণ নাম লিখুন (কমপক্ষে ৩ অক্ষর)',
            'msg_invalid_address' => 'অনুগ্রহ করে বিস্তারিত ও সঠিক ঠিকানা লিখুন (কমপক্ষে ১০ অক্ষর ও ২টি শব্দ)',
            'msg_junk_keyword'    => 'নাম বা ঠিকানায় অনাকাঙ্ক্ষিত বা টেস্ট তথ্য গ্রহণযোগ্য নয়',
            'msg_repeated_chars'  => 'নাম বা ঠিকানায় অতিরিক্ত পুনরাবৃত্ত অক্ষর গ্রহণযোগ্য নয়',
            'msg_blocked_generic' => 'দুঃখিত, আপনার অর্ডারটি গ্রহণ করা সম্ভব হচ্ছে না। বিস্তারিত জানতে অনুগ্রহ করে আমাদের সাথে যোগাযোগ করুন: {store_phone}',
        ];

        foreach ( $bengali_defaults as $key => $bn_val ) {
            if ( isset( $saved[ $key ] ) && $saved[ $key ] === $bn_val ) {
                $saved[ $key ] = $defaults[ $key ];
            }
        }

        if ( isset( $saved['junk_keywords'] ) && false !== strpos( $saved['junk_keywords'], 'টেস্ট' ) ) {
            $saved['junk_keywords'] = $defaults['junk_keywords'];
        }

        return wp_parse_args( $saved, $defaults );
    }

    /**
     * Register settings with WordPress.
     */
    public static function register_settings() {
        register_setting(
            'w3opcfe_fop_group',
            self::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [ __CLASS__, 'sanitize_settings' ],
                'default'           => self::get_defaults(),
            ]
        );
    }

    /**
     * Sanitize settings input.
     *
     * @param array $input
     * @return array
     */
    public static function sanitize_settings( $input ) {
        if ( ! is_array( $input ) ) {
            return self::get_defaults();
        }

        $current = self::get_settings();
        $output  = $current;

        // Master toggles
        $output['enable_protection']       = ! empty( $input['enable_protection'] ) ? 1 : 0;
        $output['log_only_mode']           = ! empty( $input['log_only_mode'] ) ? 1 : 0;

        // Feature 1: Input Validation
        $output['enable_input_validation'] = ! empty( $input['enable_input_validation'] ) ? 1 : 0;
        $output['enable_phone_validation'] = ! empty( $input['enable_phone_validation'] ) ? 1 : 0;
        $output['enable_name_validation']  = ! empty( $input['enable_name_validation'] ) ? 1 : 0;
        $output['min_name_length']         = max( 1, absint( $input['min_name_length'] ?? 3 ) );
        $output['enable_address_validation'] = ! empty( $input['enable_address_validation'] ) ? 1 : 0;
        $output['min_address_length']      = max( 1, absint( $input['min_address_length'] ?? 10 ) );
        $output['min_address_words']       = max( 1, absint( $input['min_address_words'] ?? 2 ) );
        $output['reject_repeated_chars']   = ! empty( $input['reject_repeated_chars'] ) ? 1 : 0;
        $output['junk_keywords']           = sanitize_textarea_field( $input['junk_keywords'] ?? '' );

        // Feature 2: Duplicate and Velocity Control
        $output['enable_velocity_control']        = ! empty( $input['enable_velocity_control'] ) ? 1 : 0;
        $output['same_phone_hours']               = max( 1, absint( $input['same_phone_hours'] ?? 24 ) );
        $valid_phone_actions                      = [ 'merge', 'block', 'flag' ];
        $phone_action                             = $input['same_phone_action'] ?? 'merge';
        $output['same_phone_action']              = in_array( $phone_action, $valid_phone_actions, true ) ? $phone_action : 'merge';
        $output['enable_duplicate_product_check'] = ! empty( $input['enable_duplicate_product_check'] ) ? 1 : 0;
        $output['duplicate_product_window']       = max( 1, absint( $input['duplicate_product_window'] ?? 60 ) );
        $output['enable_device_limit']            = ! empty( $input['enable_device_limit'] ) ? 1 : 0;
        $output['max_device_orders_per_hour']     = max( 1, absint( $input['max_device_orders_per_hour'] ?? 2 ) );
        $valid_device_actions                     = [ 'block', 'flag' ];
        $device_action                            = $input['device_limit_action'] ?? 'block';
        $output['device_limit_action']            = in_array( $device_action, $valid_device_actions, true ) ? $device_action : 'block';
        $output['enable_ip_limit']                = ! empty( $input['enable_ip_limit'] ) ? 1 : 0;
        $output['max_ip_orders_per_hour']         = max( 1, absint( $input['max_ip_orders_per_hour'] ?? 10 ) );
        $valid_ip_actions                         = [ 'flag', 'block' ];
        $ip_action                                = $input['ip_limit_action'] ?? 'flag';
        $output['ip_limit_action']                = in_array( $ip_action, $valid_ip_actions, true ) ? $ip_action : 'flag';
        $output['store_contact_phone']            = sanitize_text_field( $input['store_contact_phone'] ?? '' );

        // Feature 3: Blocklists and Allowlists
        $output['enable_blocklist']           = ! empty( $input['enable_blocklist'] ) ? 1 : 0;
        $output['enable_allowlist']           = ! empty( $input['enable_allowlist'] ) ? 1 : 0;
        $output['enable_auto_blocklist']      = ! empty( $input['enable_auto_blocklist'] ) ? 1 : 0;
        $output['auto_blocklist_threshold']   = max( 1, absint( $input['auto_blocklist_threshold'] ?? 2 ) );
        $output['auto_blocklist_expiry_days'] = absint( $input['auto_blocklist_expiry_days'] ?? 0 );
        $output['enable_auto_allowlist']      = ! empty( $input['enable_auto_allowlist'] ) ? 1 : 0;
        $output['auto_allowlist_threshold']   = max( 1, absint( $input['auto_allowlist_threshold'] ?? 3 ) );
        $output['enable_network_blocklist']   = ! empty( $input['enable_network_blocklist'] ) ? 1 : 0;
        $output['network_api_url']            = esc_url_raw( $input['network_api_url'] ?? '' );
        $output['network_license_key']        = sanitize_text_field( $input['network_license_key'] ?? '' );
        $output['network_license_secret']     = sanitize_text_field( $input['network_license_secret'] ?? '' );
        $output['network_min_reports']        = max( 1, absint( $input['network_min_reports'] ?? 3 ) );
        $valid_net_actions                    = [ 'flag', 'block' ];
        $net_action                           = $input['network_action'] ?? 'flag';
        $output['network_action']             = in_array( $net_action, $valid_net_actions, true ) ? $net_action : 'flag';

        // Messages
        $output['msg_invalid_phone']       = sanitize_text_field( $input['msg_invalid_phone'] ?? '' );
        $output['msg_invalid_name']        = sanitize_text_field( $input['msg_invalid_name'] ?? '' );
        $output['msg_invalid_address']     = sanitize_text_field( $input['msg_invalid_address'] ?? '' );
        $output['msg_junk_keyword']        = sanitize_text_field( $input['msg_junk_keyword'] ?? '' );
        $output['msg_repeated_chars']      = sanitize_text_field( $input['msg_repeated_chars'] ?? '' );
        $output['msg_blocked_generic']     = sanitize_text_field( $input['msg_blocked_generic'] ?? '' );

        return $output;
    }

    /**
     * Render the Fake Order Protection admin settings page.
     */
    public static function render_page() {
        $settings = self::get_settings();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin tab parameter.
        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'validation';

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only admin notice query args.
        $entry_added    = isset( $_GET['entry_added'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $entry_deleted  = isset( $_GET['entry_deleted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $entry_error    = isset( $_GET['entry_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $import_status  = isset( $_GET['import_status'] ) ? sanitize_key( wp_unslash( $_GET['import_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $imported_count = isset( $_GET['imported'] ) ? absint( wp_unslash( $_GET['imported'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $skipped_count  = isset( $_GET['skipped'] ) ? absint( wp_unslash( $_GET['skipped'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        ?>
        <div class="wrap w3opcfe-fop-admin-wrap">
            <h1>
                <span class="dashicons dashicons-shield-alt" style="font-size:32px; width:32px; height:32px; vertical-align:middle; margin-right:8px; color:#2271b1;"></span>
                <?php esc_html_e( 'Fake Order Protection', 'on-page-checkout-for-elementor' ); ?>
            </h1>
            <p class="description">
                <?php esc_html_e( 'Protect your store from fake orders, non-serious customers, and courier return losses with automated server-side verification.', 'on-page-checkout-for-elementor' ); ?>
            </p>

            <?php settings_errors( 'w3opcfe_fop_group' ); ?>

            <?php if ( $entry_added ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry successfully added to the list.', 'on-page-checkout-for-elementor' ); ?></p></div>
            <?php elseif ( $entry_deleted ) : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Entry successfully removed.', 'on-page-checkout-for-elementor' ); ?></p></div>
            <?php elseif ( $entry_error ) : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Failed to add entry. Please check the value and try again.', 'on-page-checkout-for-elementor' ); ?></p></div>
            <?php elseif ( 'success' === $import_status ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        printf(
                            /* translators: 1: Number of imported entries, 2: Number of duplicate entries skipped. */
                            esc_html__( 'CSV Import finished: %1$d imported, %2$d duplicates skipped.', 'on-page-checkout-for-elementor' ),
                            absint( $imported_count ),
                            absint( $skipped_count )
                        );
                        ?>
                    </p>
                </div>
            <?php elseif ( 'no_file' === $import_status ) : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'No file selected for CSV import.', 'on-page-checkout-for-elementor' ); ?></p></div>
            <?php endif; ?>

            <nav class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=validation' ) ); ?>" class="nav-tab <?php echo 'validation' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-forms" style="vertical-align:text-top; margin-right:4px;"></span>
                    <?php esc_html_e( '1. Input Validation', 'on-page-checkout-for-elementor' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=velocity' ) ); ?>" class="nav-tab <?php echo 'velocity' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-clock" style="vertical-align:text-top; margin-right:4px;"></span>
                    <?php esc_html_e( '2. Duplicate & Velocity Control', 'on-page-checkout-for-elementor' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists' ) ); ?>" class="nav-tab <?php echo 'blocklists' === $active_tab ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-dismiss" style="vertical-align:text-top; margin-right:4px;"></span>
                    <?php esc_html_e( '3. Blocklists & Allowlists', 'on-page-checkout-for-elementor' ); ?>
                </a>
            </nav>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'w3opcfe_fop_group' );
                ?>

                <!-- Global Protection Controls Bar -->
                <div class="card" style="max-width: 900px; margin-bottom: 24px; padding: 16px 20px; background:#fff; border-left:4px solid #2271b1;">
                    <div style="display:flex; flex-wrap:wrap; gap:30px; align-items:center;">
                        <div class="w3opcfe-switch-wrapper">
                            <label class="w3opcfe-switch">
                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_protection]" value="0">
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_protection]" value="1" <?php checked( 1, $settings['enable_protection'] ); ?>>
                                <span class="w3opcfe-slider"></span>
                            </label>
                            <div>
                                <strong style="display:block;"><?php esc_html_e( 'Enable Fake Order Protection', 'on-page-checkout-for-elementor' ); ?></strong>
                                <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Master switch for all automated order protection rules.', 'on-page-checkout-for-elementor' ); ?></span>
                            </div>
                        </div>

                        <div class="w3opcfe-switch-wrapper">
                            <label class="w3opcfe-switch">
                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[log_only_mode]" value="0">
                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[log_only_mode]" value="1" <?php checked( 1, $settings['log_only_mode'] ); ?>>
                                <span class="w3opcfe-slider"></span>
                            </label>
                            <div>
                                <strong style="display:block;"><?php esc_html_e( 'Log-Only Test Mode', 'on-page-checkout-for-elementor' ); ?></strong>
                                <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Evaluate all checks and log decisions without blocking or merging.', 'on-page-checkout-for-elementor' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ( 'validation' === $active_tab ) : ?>
                    <!-- TAB 1: INPUT VALIDATION -->
                    <div class="card" style="max-width: 900px; padding: 20px;">
                        <h2><?php esc_html_e( 'Input Validation Settings', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Instant client-side formatting and strict server-side validation to ensure genuine customer data.', 'on-page-checkout-for-elementor' ); ?>
                        </p>

                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Input Validation Module', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_input_validation]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_input_validation]" value="1" <?php checked( 1, $settings['enable_input_validation'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Enable real-time phone normalization and name/address quality verification.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        <?php esc_html_e( 'BD Phone Validation', 'on-page-checkout-for-elementor' ); ?>
                                    </th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_phone_validation]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_phone_validation]" value="1" <?php checked( 1, $settings['enable_phone_validation'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Enforce valid Bangladesh mobile operator format: 01[3-9]XXXXXXXX (11 digits).', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <p class="description">
                                            <?php esc_html_e( 'Converts Bangla numerals (০-৯) to English digits live in form, strips spaces/dashes, and normalizes prefixes (+880, 880, 00880, 10-digit). Normalized number is used for order, duplicate checking, and blocklists.', 'on-page-checkout-for-elementor' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Phone Error Message', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_invalid_phone]" value="<?php echo esc_attr( $settings['msg_invalid_phone'] ); ?>" class="large-text">
                                        <p class="description"><?php esc_html_e( 'Shown when the phone number is invalid or has wrong operator prefix.', 'on-page-checkout-for-elementor' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Name Quality Check', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_name_validation]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_name_validation]" value="1" <?php checked( 1, $settings['enable_name_validation'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Reject digits-only, symbols-only, or fake names.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <label>
                                            <?php esc_html_e( 'Minimum Name Length (characters):', 'on-page-checkout-for-elementor' ); ?>
                                            <input type="number" min="1" max="50" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[min_name_length]" value="<?php echo esc_attr( $settings['min_name_length'] ); ?>" style="width:70px;">
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Name Error Message', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_invalid_name]" value="<?php echo esc_attr( $settings['msg_invalid_name'] ); ?>" class="large-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Address Quality Check', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_address_validation]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_address_validation]" value="1" <?php checked( 1, $settings['enable_address_validation'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Ensure detailed delivery address to avoid undelivered courier parcels.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <div style="display:flex; gap:20px; align-items:center;">
                                            <label>
                                                <?php esc_html_e( 'Minimum Length (characters):', 'on-page-checkout-for-elementor' ); ?>
                                                <input type="number" min="3" max="100" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[min_address_length]" value="<?php echo esc_attr( $settings['min_address_length'] ); ?>" style="width:70px;">
                                            </label>
                                            <label>
                                                <?php esc_html_e( 'Minimum Words:', 'on-page-checkout-for-elementor' ); ?>
                                                <input type="number" min="1" max="10" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[min_address_words]" value="<?php echo esc_attr( $settings['min_address_words'] ); ?>" style="width:70px;">
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Address Error Message', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_invalid_address]" value="<?php echo esc_attr( $settings['msg_invalid_address'] ); ?>" class="large-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Reject Repeated Characters', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[reject_repeated_chars]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[reject_repeated_chars]" value="1" <?php checked( 1, $settings['reject_repeated_chars'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Block 4 or more repeated consecutive characters (e.g. "aaaa", "1111", "....") in name or address.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_repeated_chars]" value="<?php echo esc_attr( $settings['msg_repeated_chars'] ); ?>" class="large-text">
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Junk / Test Keywords', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[junk_keywords]" rows="3" class="large-text code"><?php echo esc_textarea( $settings['junk_keywords'] ); ?></textarea>
                                        <p class="description">
                                            <?php esc_html_e( 'Comma-separated list of words rejected in customer name or address (case-insensitive).', 'on-page-checkout-for-elementor' ); ?>
                                        </p>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_junk_keyword]" value="<?php echo esc_attr( $settings['msg_junk_keyword'] ); ?>" class="large-text" style="margin-top:6px;" placeholder="<?php esc_attr_e( 'Junk keyword error message', 'on-page-checkout-for-elementor' ); ?>">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ( 'velocity' === $active_tab ) : ?>
                    <!-- TAB 2: DUPLICATE & VELOCITY CONTROL -->
                    <div class="card" style="max-width: 900px; padding: 20px;">
                        <h2><?php esc_html_e( 'Duplicate and Velocity Control', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Prevent repeated submissions, abusive order spam, and accidental double orders using phone velocity, device fingerprinting, and proxy-aware IP tracking.', 'on-page-checkout-for-elementor' ); ?>
                        </p>

                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Velocity Control Module', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_velocity_control]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_velocity_control]" value="1" <?php checked( 1, $settings['enable_velocity_control'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Master toggle for duplicate detection, device tracking, and rate limiting.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Same-Phone Lookback & Action', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:center; margin-bottom:8px;">
                                            <label>
                                                <?php esc_html_e( 'Lookback Window:', 'on-page-checkout-for-elementor' ); ?>
                                                <input type="number" min="1" max="168" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[same_phone_hours]" value="<?php echo esc_attr( $settings['same_phone_hours'] ); ?>" style="width:70px;">
                                                <?php esc_html_e( 'hours', 'on-page-checkout-for-elementor' ); ?>
                                            </label>
                                            <label>
                                                <?php esc_html_e( 'Action:', 'on-page-checkout-for-elementor' ); ?>
                                                <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[same_phone_action]">
                                                    <option value="merge" <?php selected( 'merge', $settings['same_phone_action'] ); ?>><?php esc_html_e( 'Merge — Add items to earlier order if pending/on-hold (Recommended)', 'on-page-checkout-for-elementor' ); ?></option>
                                                    <option value="block" <?php selected( 'block', $settings['same_phone_action'] ); ?>><?php esc_html_e( 'Block — Prevent new order attempt with generic message', 'on-page-checkout-for-elementor' ); ?></option>
                                                    <option value="flag" <?php selected( 'flag', $settings['same_phone_action'] ); ?>><?php esc_html_e( 'Flag — Allow order but tag as Flagged in admin', 'on-page-checkout-for-elementor' ); ?></option>
                                                </select>
                                            </label>
                                        </div>
                                        <p class="description">
                                            <?php esc_html_e( 'Merging combines items into the customer’s open order, recalculates totals, and links them with an order note. If earlier order has already shipped or completed, it safely falls back to flagging.', 'on-page-checkout-for-elementor' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Same Phone + Same Product Check', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_duplicate_product_check]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_duplicate_product_check]" value="1" <?php checked( 1, $settings['enable_duplicate_product_check'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Auto-flag as "Duplicate" when the same customer re-orders the exact same item in a short timeframe.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <label>
                                            <?php esc_html_e( 'Time Window (minutes):', 'on-page-checkout-for-elementor' ); ?>
                                            <input type="number" min="5" max="1440" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[duplicate_product_window]" value="<?php echo esc_attr( $settings['duplicate_product_window'] ); ?>" style="width:80px;">
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Device Limit (Fingerprint + UUID)', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_device_limit]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_device_limit]" value="1" <?php checked( 1, $settings['enable_device_limit'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Track device orders via first-party UUID (cookie + localStorage) and lightweight browser fingerprint.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
                                            <label>
                                                <?php esc_html_e( 'Max Orders Per Device Per Hour:', 'on-page-checkout-for-elementor' ); ?>
                                                <input type="number" min="1" max="50" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[max_device_orders_per_hour]" value="<?php echo esc_attr( $settings['max_device_orders_per_hour'] ); ?>" style="width:70px;">
                                            </label>
                                            <label>
                                                <?php esc_html_e( 'Action:', 'on-page-checkout-for-elementor' ); ?>
                                                <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[device_limit_action]">
                                                    <option value="block" <?php selected( 'block', $settings['device_limit_action'] ); ?>><?php esc_html_e( 'Block (Recommended for device limit)', 'on-page-checkout-for-elementor' ); ?></option>
                                                    <option value="flag" <?php selected( 'flag', $settings['device_limit_action'] ); ?>><?php esc_html_e( 'Flag only', 'on-page-checkout-for-elementor' ); ?></option>
                                                </select>
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'IP Limit (Weak Signal)', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_ip_limit]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_ip_limit]" value="1" <?php checked( 1, $settings['enable_ip_limit'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Rate-limit orders per IP. Accurately identifies IPs behind Cloudflare and reverse proxies.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                        <div style="display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
                                            <label>
                                                <?php esc_html_e( 'Max Orders Per IP Per Hour:', 'on-page-checkout-for-elementor' ); ?>
                                                <input type="number" min="1" max="100" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[max_ip_orders_per_hour]" value="<?php echo esc_attr( $settings['max_ip_orders_per_hour'] ); ?>" style="width:70px;">
                                            </label>
                                            <label>
                                                <?php esc_html_e( 'Action:', 'on-page-checkout-for-elementor' ); ?>
                                                <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[ip_limit_action]">
                                                    <option value="flag" <?php selected( 'flag', $settings['ip_limit_action'] ); ?>><?php esc_html_e( 'Flag only (Recommended — BD carrier NAT shares mobile IPs)', 'on-page-checkout-for-elementor' ); ?></option>
                                                    <option value="block" <?php selected( 'block', $settings['ip_limit_action'] ); ?>><?php esc_html_e( 'Block', 'on-page-checkout-for-elementor' ); ?></option>
                                                </select>
                                            </label>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Store Contact Phone', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[store_contact_phone]" value="<?php echo esc_attr( $settings['store_contact_phone'] ); ?>" class="regular-text" placeholder="e.g. 01700000000">
                                        <p class="description">
                                            <?php esc_html_e( 'Used in the {store_phone} variable in the generic block message so genuine customers can still reach you.', 'on-page-checkout-for-elementor' ); ?>
                                        </p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Generic Block Message', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <textarea name="<?php echo esc_attr( self::OPTION_NAME ); ?>[msg_blocked_generic]" rows="3" class="large-text"><?php echo esc_textarea( $settings['msg_blocked_generic'] ); ?></textarea>
                                        <p class="description">
                                            <?php esc_html_e( 'Always display a neutral message to blocked users. Never reveal the exact rule that triggered to prevent fraudsters from probing your thresholds.', 'on-page-checkout-for-elementor' ); ?>
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                <?php elseif ( 'blocklists' === $active_tab ) : ?>
                    <!-- TAB 3: AUTOMATED & NETWORK RULES SETTINGS CARD -->
                    <div class="card" style="max-width: 900px; padding: 20px;">
                        <h2><?php esc_html_e( 'Automated Blocklist & Allowlist Rules', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description">
                            <?php esc_html_e( 'Configure automated list triggers, trust allowlists, and anonymous community network protection.', 'on-page-checkout-for-elementor' ); ?>
                        </p>

                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Allowlist (Trusted Buyers)', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_allowlist]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_allowlist]" value="1" <?php checked( 1, $settings['enable_allowlist'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Allowlisted customers skip ALL verification checks (allowlist always wins).', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>

                                        <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
                                            <label class="w3opcfe-switch-wrapper">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_auto_allowlist]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_auto_allowlist]" value="1" <?php checked( 1, $settings['enable_auto_allowlist'] ); ?>>
                                                <span><?php esc_html_e( 'Auto-Allowlist after', 'on-page-checkout-for-elementor' ); ?></span>
                                            </label>
                                            <input type="number" min="1" max="100" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[auto_allowlist_threshold]" value="<?php echo esc_attr( $settings['auto_allowlist_threshold'] ); ?>" style="width:70px;">
                                            <span><?php esc_html_e( 'completed orders', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><?php esc_html_e( 'Auto-Blocklist on Bad Orders', 'on-page-checkout-for-elementor' ); ?></th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_blocklist]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_blocklist]" value="1" <?php checked( 1, $settings['enable_blocklist'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Enforce local store blocklist for matched phones, devices, fingerprints, IPs, and address keywords.', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>

                                        <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:16px; align-items:center;">
                                            <label class="w3opcfe-switch-wrapper">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_auto_blocklist]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_auto_blocklist]" value="1" <?php checked( 1, $settings['enable_auto_blocklist'] ); ?>>
                                                <span><?php esc_html_e( 'Auto-Block phone after', 'on-page-checkout-for-elementor' ); ?></span>
                                            </label>
                                            <input type="number" min="1" max="20" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[auto_blocklist_threshold]" value="<?php echo esc_attr( $settings['auto_blocklist_threshold'] ); ?>" style="width:70px;">
                                            <span><?php esc_html_e( 'cancelled, failed, or Returned orders', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>

                                        <div style="margin-top:10px; display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                                            <span><?php esc_html_e( 'Auto-Block expiry:', 'on-page-checkout-for-elementor' ); ?></span>
                                            <input type="number" min="0" max="365" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[auto_blocklist_expiry_days]" value="<?php echo esc_attr( $settings['auto_blocklist_expiry_days'] ); ?>" style="width:70px;">
                                            <span><?php esc_html_e( 'days (enter 0 for permanent)', 'on-page-checkout-for-elementor' ); ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row">
                                        <?php esc_html_e( 'Network Blocklist (Pro, Opt-in)', 'on-page-checkout-for-elementor' ); ?>
                                    </th>
                                    <td>
                                        <div class="w3opcfe-switch-wrapper" style="margin-bottom:8px;">
                                            <label class="w3opcfe-switch">
                                                <input type="hidden" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_network_blocklist]" value="0">
                                                <input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[enable_network_blocklist]" value="1" <?php checked( 1, $settings['enable_network_blocklist'] ); ?>>
                                                <span class="w3opcfe-slider"></span>
                                            </label>
                                            <span class="w3opcfe-switch-desc"><strong><?php esc_html_e( 'Crowdsourced Fake Order Intelligence Network', 'on-page-checkout-for-elementor' ); ?></strong></span>
                                        </div>
                                        <p class="description" style="margin-bottom:12px;">
                                            <?php esc_html_e( 'Check phones against anonymous fraud data shared across participating stores. Raw phone numbers are never transmitted — requests are strictly encrypted with HMAC-SHA256, cached for 12 hours, and fail open on 2s timeout.', 'on-page-checkout-for-elementor' ); ?>
                                        </p>

                                        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap:14px;">
                                            <div>
                                                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Network API Endpoint URL:', 'on-page-checkout-for-elementor' ); ?></label>
                                                <input type="url" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[network_api_url]" value="<?php echo esc_attr( $settings['network_api_url'] ); ?>" class="large-text" placeholder="https://api.yourdomain.com/v1/fraud-check">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'License / API Key:', 'on-page-checkout-for-elementor' ); ?></label>
                                                <input type="text" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[network_license_key]" value="<?php echo esc_attr( $settings['network_license_key'] ); ?>" class="large-text" placeholder="w3opc_live_...">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Secret Key (for HMAC hashing):', 'on-page-checkout-for-elementor' ); ?></label>
                                                <input type="password" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[network_license_secret]" value="<?php echo esc_attr( $settings['network_license_secret'] ); ?>" class="large-text">
                                            </div>
                                            <div>
                                                <label style="display:block; font-size:12px; font-weight:600; margin-bottom:4px;"><?php esc_html_e( 'Action when reported by stores:', 'on-page-checkout-for-elementor' ); ?></label>
                                                <div style="display:flex; gap:10px; align-items:center; margin-top:4px;">
                                                    <span><?php esc_html_e( 'Reported by ≥', 'on-page-checkout-for-elementor' ); ?></span>
                                                    <input type="number" min="1" max="50" name="<?php echo esc_attr( self::OPTION_NAME ); ?>[network_min_reports]" value="<?php echo esc_attr( $settings['network_min_reports'] ); ?>" style="width:60px;">
                                                    <span><?php esc_html_e( 'stores:', 'on-page-checkout-for-elementor' ); ?></span>
                                                    <select name="<?php echo esc_attr( self::OPTION_NAME ); ?>[network_action]">
                                                        <option value="flag" <?php selected( 'flag', $settings['network_action'] ); ?>><?php esc_html_e( 'Flag only', 'on-page-checkout-for-elementor' ); ?></option>
                                                        <option value="block" <?php selected( 'block', $settings['network_action'] ); ?>><?php esc_html_e( 'Block order', 'on-page-checkout-for-elementor' ); ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <div style="margin-top:20px;">
                    <?php submit_button(); ?>
                </div>
            </form>

            <?php if ( 'blocklists' === $active_tab ) : ?>
                <?php
                // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only search, filter and pagination parameters.
                $current_list = ( isset( $_GET['list'] ) && 'allowlist' === sanitize_key( wp_unslash( $_GET['list'] ) ) ) ? 'allowlist' : 'blocklist'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $search       = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $type_filter  = isset( $_GET['entry_type'] ) ? sanitize_key( wp_unslash( $_GET['entry_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $paged        = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                // phpcs:enable WordPress.Security.NonceVerification.Recommended
                $per_page     = 20;
                $offset       = ( $paged - 1 ) * $per_page;

                $total_count  = W3OPCFE_FOP_List_Manager::get_total_count( $current_list, $search, $type_filter );
                $entries      = W3OPCFE_FOP_List_Manager::get_entries( $current_list, $per_page, $offset, $search, $type_filter );
                $total_pages  = ceil( $total_count / $per_page );

                $block_count  = W3OPCFE_FOP_List_Manager::get_total_count( 'blocklist' );
                $allow_count  = W3OPCFE_FOP_List_Manager::get_total_count( 'allowlist' );
                ?>

                <!-- List Management Card -->
                <div class="card" style="max-width: 900px; padding: 20px; margin-top: 30px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; margin-bottom:20px; border-bottom:1px solid #e2e8f0; padding-bottom:14px;">
                        <div style="display:flex; gap:10px;">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=blocklist' ) ); ?>" class="button <?php echo 'blocklist' === $current_list ? 'button-primary' : ''; ?>" style="font-weight:600;">
                                <span class="dashicons dashicons-dismiss" style="vertical-align:middle; font-size:16px; margin-right:4px;"></span>
                                <?php
                                /* translators: %d: Number of blocklist entries. */
                                printf( esc_html__( 'Blocklist (%d)', 'on-page-checkout-for-elementor' ), absint( $block_count ) );
                                ?>
                            </a>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=allowlist' ) ); ?>" class="button <?php echo 'allowlist' === $current_list ? 'button-primary' : ''; ?>" style="font-weight:600;">
                                <span class="dashicons dashicons-yes-alt" style="vertical-align:middle; font-size:16px; margin-right:4px;"></span>
                                <?php
                                /* translators: %d: Number of allowlist entries. */
                                printf( esc_html__( 'Allowlist (%d)', 'on-page-checkout-for-elementor' ), absint( $allow_count ) );
                                ?>
                            </a>
                        </div>

                        <!-- Action buttons -->
                        <div style="display:flex; gap:8px;">
                            <button type="button" class="button button-secondary" onclick="jQuery('#w3opcfe-add-entry-panel').slideToggle();">
                                <span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;"></span>
                                <?php
                                /* translators: %s: Target list name. */
                                printf( esc_html__( '+ Add to %s', 'on-page-checkout-for-elementor' ), esc_html( ucfirst( $current_list ) ) );
                                ?>
                            </button>
                            <button type="button" class="button button-secondary" onclick="jQuery('#w3opcfe-import-csv-panel').slideToggle();">
                                <span class="dashicons dashicons-upload" style="vertical-align:middle;"></span>
                                <?php esc_html_e( 'Import CSV', 'on-page-checkout-for-elementor' ); ?>
                            </button>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=w3opcfe_fop_export_csv&list_type=' . $current_list ), 'w3opcfe_export_nonce' ) ); ?>" class="button button-secondary">
                                <span class="dashicons dashicons-download" style="vertical-align:middle;"></span>
                                <?php esc_html_e( 'Export CSV', 'on-page-checkout-for-elementor' ); ?>
                            </a>
                        </div>
                    </div>

                    <!-- Add Entry Panel (collapsible) -->
                    <div id="w3opcfe-add-entry-panel" style="display:none; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:16px 20px; margin-bottom:20px;">
                        <h3 style="margin-top:0; margin-bottom:12px; font-size:14px;">
                            <?php
                            /* translators: %s: Target list name. */
                            printf( esc_html__( 'Add New Entry to %s', 'on-page-checkout-for-elementor' ), esc_html( ucfirst( $current_list ) ) );
                            ?>
                        </h3>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <input type="hidden" name="action" value="w3opcfe_fop_add_entry">
                            <input type="hidden" name="list_type" value="<?php echo esc_attr( $current_list ); ?>">
                            <?php wp_nonce_field( 'w3opcfe_add_entry_nonce' ); ?>

                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px; align-items:end;">
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px; text-transform:uppercase; color:#64748b;"><?php esc_html_e( 'Entry Type', 'on-page-checkout-for-elementor' ); ?></label>
                                    <select name="entry_type" style="width:100%;">
                                        <option value="phone"><?php esc_html_e( 'Phone Number (BD)', 'on-page-checkout-for-elementor' ); ?></option>
                                        <option value="device_id"><?php esc_html_e( 'Device UUID', 'on-page-checkout-for-elementor' ); ?></option>
                                        <option value="fingerprint"><?php esc_html_e( 'Fingerprint Hash', 'on-page-checkout-for-elementor' ); ?></option>
                                        <option value="ip"><?php esc_html_e( 'IP Address', 'on-page-checkout-for-elementor' ); ?></option>
                                        <?php if ( 'blocklist' === $current_list ) : ?>
                                            <option value="address_keyword"><?php esc_html_e( 'Address Keyword', 'on-page-checkout-for-elementor' ); ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px; text-transform:uppercase; color:#64748b;"><?php esc_html_e( 'Value', 'on-page-checkout-for-elementor' ); ?></label>
                                    <input type="text" name="entry_value" required placeholder="017XXXXXXXX" style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px; text-transform:uppercase; color:#64748b;"><?php esc_html_e( 'Reason / Note', 'on-page-checkout-for-elementor' ); ?></label>
                                    <input type="text" name="reason" placeholder="e.g. Fraudulent customer" style="width:100%;">
                                </div>
                                <div>
                                    <label style="display:block; font-size:11px; font-weight:600; margin-bottom:4px; text-transform:uppercase; color:#64748b;"><?php esc_html_e( 'Auto-Expires In (Days)', 'on-page-checkout-for-elementor' ); ?></label>
                                    <input type="number" min="0" max="365" name="expires_in_days" value="0" style="width:100%;" placeholder="0 = Permanent">
                                </div>
                                <div>
                                    <button type="submit" class="button button-primary" style="width:100%;">
                                        <?php
                                        /* translators: %s: Target list name. */
                                        printf( esc_html__( 'Add to %s', 'on-page-checkout-for-elementor' ), esc_html( ucfirst( $current_list ) ) );
                                        ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Import CSV Panel (collapsible) -->
                    <div id="w3opcfe-import-csv-panel" style="display:none; background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:16px 20px; margin-bottom:20px;">
                        <h3 style="margin-top:0; margin-bottom:6px; font-size:14px;">
                            <?php
                            /* translators: %s: Target list name. */
                            printf( esc_html__( 'Import CSV into %s', 'on-page-checkout-for-elementor' ), esc_html( ucfirst( $current_list ) ) );
                            ?>
                        </h3>
                        <p class="description" style="margin-bottom:12px;">
                            <?php esc_html_e( 'Upload a CSV file containing columns: entry_type, entry_value, reason. You can also upload a simple single-column CSV with phone numbers only. Phone numbers are automatically normalized and duplicates are skipped.', 'on-page-checkout-for-elementor' ); ?>
                        </p>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                            <input type="hidden" name="action" value="w3opcfe_fop_import_csv">
                            <input type="hidden" name="list_type" value="<?php echo esc_attr( $current_list ); ?>">
                            <?php wp_nonce_field( 'w3opcfe_import_csv_nonce' ); ?>
                            <input type="file" name="csv_file" accept=".csv,text/csv" required>
                            <button type="submit" class="button button-primary">
                                <span class="dashicons dashicons-upload" style="vertical-align:middle;"></span>
                                <?php esc_html_e( 'Upload & Import CSV', 'on-page-checkout-for-elementor' ); ?>
                            </button>
                        </form>
                    </div>

                    <!-- Search & Filter Bar -->
                    <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" style="display:flex; gap:10px; align-items:center; margin-bottom:16px; flex-wrap:wrap;">
                        <input type="hidden" name="page" value="w3opcfe_fake_order_protection">
                        <input type="hidden" name="tab" value="blocklists">
                        <input type="hidden" name="list" value="<?php echo esc_attr( $current_list ); ?>">

                        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search value or reason...', 'on-page-checkout-for-elementor' ); ?>" style="min-width:240px;">
                        <select name="entry_type">
                            <option value=""><?php esc_html_e( 'All Types', 'on-page-checkout-for-elementor' ); ?></option>
                            <option value="phone" <?php selected( 'phone', $type_filter ); ?>><?php esc_html_e( 'Phone', 'on-page-checkout-for-elementor' ); ?></option>
                            <option value="device_id" <?php selected( 'device_id', $type_filter ); ?>><?php esc_html_e( 'Device UUID', 'on-page-checkout-for-elementor' ); ?></option>
                            <option value="fingerprint" <?php selected( 'fingerprint', $type_filter ); ?>><?php esc_html_e( 'Fingerprint', 'on-page-checkout-for-elementor' ); ?></option>
                            <option value="ip" <?php selected( 'ip', $type_filter ); ?>><?php esc_html_e( 'IP Address', 'on-page-checkout-for-elementor' ); ?></option>
                            <?php if ( 'blocklist' === $current_list ) : ?>
                                <option value="address_keyword" <?php selected( 'address_keyword', $type_filter ); ?>><?php esc_html_e( 'Address Keyword', 'on-page-checkout-for-elementor' ); ?></option>
                            <?php endif; ?>
                        </select>
                        <button type="submit" class="button"><?php esc_html_e( 'Filter', 'on-page-checkout-for-elementor' ); ?></button>
                        <?php if ( ! empty( $search ) || ! empty( $type_filter ) ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=w3opcfe_fake_order_protection&tab=blocklists&list=' . $current_list ) ); ?>" class="button-link"><?php esc_html_e( 'Clear Filter', 'on-page-checkout-for-elementor' ); ?></a>
                        <?php endif; ?>
                    </form>

                    <!-- Entries Table -->
                    <table class="wp-list-table widefat fixed striped table-view-list">
                        <thead>
                            <tr>
                                <th scope="col" style="width:120px;"><?php esc_html_e( 'Type', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Entry Value', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Reason / Note', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col" style="width:90px;"><?php esc_html_e( 'Source', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col" style="width:130px;"><?php esc_html_e( 'Added', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col" style="width:120px;"><?php esc_html_e( 'Expires', 'on-page-checkout-for-elementor' ); ?></th>
                                <th scope="col" style="width:70px; text-align:right;"><?php esc_html_e( 'Action', 'on-page-checkout-for-elementor' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( ! empty( $entries ) ) : ?>
                                <?php foreach ( $entries as $entry ) : ?>
                                    <tr>
                                        <td>
                                            <code><?php echo esc_html( $entry['entry_type'] ); ?></code>
                                        </td>
                                        <td>
                                            <strong style="font-family:Consolas, Monaco, monospace; word-break:break-all;"><?php echo esc_html( $entry['entry_value'] ); ?></strong>
                                        </td>
                                        <td style="color:#555;">
                                            <?php echo esc_html( $entry['reason'] ?: '—' ); ?>
                                        </td>
                                        <td>
                                            <span class="w3opcfe-source-badge w3opcfe-source-<?php echo esc_attr( $entry['source'] ); ?>">
                                                <?php echo esc_html( ucfirst( $entry['source'] ) ); ?>
                                            </span>
                                        </td>
                                        <td style="font-size:12px; color:#666;">
                                            <?php echo esc_html( mysql2date( 'M j, Y', $entry['created_at'] ) ); ?>
                                        </td>
                                        <td style="font-size:12px; color:#666;">
                                            <?php echo ! empty( $entry['expires_at'] ) ? esc_html( mysql2date( 'M j, Y', $entry['expires_at'] ) ) : '<span style="color:#137333;">' . esc_html__( 'Permanent', 'on-page-checkout-for-elementor' ) . '</span>'; ?>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=w3opcfe_fop_delete_entry&list_type=' . $current_list . '&id=' . $entry['id'] ), 'w3opcfe_delete_entry_nonce' ) ); ?>" class="button button-small" style="color:#b32d2e;" onclick="return confirm('<?php echo esc_js( __( 'Are you sure you want to delete this entry?', 'on-page-checkout-for-elementor' ) ); ?>');">
                                                <span class="dashicons dashicons-trash" style="font-size:15px; width:15px; height:15px; line-height:22px; vertical-align:middle;"></span>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:30px 15px; color:#64748b;">
                                        <span class="dashicons dashicons-info-outline" style="font-size:28px; width:28px; height:28px; color:#94a3b8; display:block; margin:0 auto 8px;"></span>
                                        <strong>
                                            <?php
                                            /* translators: %s: Target list name. */
                                            printf( esc_html__( 'No %s entries found.', 'on-page-checkout-for-elementor' ), esc_html( $current_list ) );
                                            ?>
                                        </strong>
                                        <p style="margin:4px 0 0 0; font-size:12px;"><?php esc_html_e( 'Add entries manually above, import a CSV list, or let automated rules add them.', 'on-page-checkout-for-elementor' ); ?></p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <?php if ( $total_pages > 1 ) : ?>
                        <div class="tablenav" style="margin-top:12px;">
                            <div class="tablenav-pages">
                                <span class="displaying-num">
                                    <?php
                                    /* translators: %d: Total number of items. */
                                    printf( esc_html__( '%d items', 'on-page-checkout-for-elementor' ), absint( $total_count ) );
                                    ?>
                                </span>
                                <span class="pagination-links">
                                    <?php
                                    echo wp_kses_post( paginate_links( [
                                        'base'      => add_query_arg( 'paged', '%#%' ),
                                        'format'    => '',
                                        'prev_text' => '&laquo;',
                                        'next_text' => '&raquo;',
                                        'total'     => $total_pages,
                                        'current'   => $paged,
                                    ] ) );
                                    ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}

