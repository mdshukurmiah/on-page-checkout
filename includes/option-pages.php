<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * One-time migration from legacy `w3_opc_*` option keys to `w3opcfe_*`.
 */
function w3opcfe_migrate_options() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $migrations = [
        'w3_opc_general_options'        => 'w3opcfe_general_options',
        'w3_opc_checkout_fields'        => 'w3opcfe_checkout_fields',
        'w3_opc_checkout_fields_master' => 'w3opcfe_checkout_fields_master',
    ];

    foreach ( $migrations as $old_key => $new_key ) {
        $old_val = get_option( $old_key, false );
        if ( false !== $old_val && false === get_option( $new_key, false ) ) {
            update_option( $new_key, $old_val );
        }
    }

    // Initialize the two new switch options if they do not exist
    if ( false === get_option( 'w3opcfe_checkout_fields_on_page', false ) ) {
        $master_val = get_option( 'w3opcfe_checkout_fields_master', 1 );
        update_option( 'w3opcfe_checkout_fields_on_page', ! empty( $master_val ) ? 1 : 0 );
    }

    if ( false === get_option( 'w3opcfe_checkout_fields_store', false ) ) {
        update_option( 'w3opcfe_checkout_fields_store', 0 );
    }
}

add_action( 'admin_init', 'w3opcfe_migrate_options' );

add_action( 'admin_menu', 'w3opcfe_register_settings_pages' );

function w3opcfe_register_settings_pages() {
    // W3 logo as SVG data URI: a filled circle with "W3" cut out through a mask. WordPress recolors every
    // fill to the admin color scheme, so the mask white uses a CSS rule without a trailing ";" (left untouched).
    $menu_icon_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><style>.w3opcfe-mask-bg{fill:#fff}</style><mask id="w3opcfe-w3"><rect class="w3opcfe-mask-bg" width="20" height="20"/><text x="10" y="10.6" text-anchor="middle" dominant-baseline="central" font-family="Arial, Helvetica, sans-serif" font-size="9" font-weight="700">W3</text></mask><circle cx="10" cy="10" r="10" fill="currentColor" mask="url(#w3opcfe-w3)"/></svg>';

    // Top-level menu: W3 OPC
    add_menu_page(
        __( 'W3 OPC', 'on-page-checkout-for-elementor' ),
        __( 'W3 OPC', 'on-page-checkout-for-elementor' ),
        'manage_options',
        'w3opcfe_checkout_fields_edit',
        'w3opcfe_checkout_fields_edit_page',
        'data:image/svg+xml;base64,' . base64_encode( $menu_icon_svg ),
        60
    );

    // 1. Checkout Fields
    add_submenu_page(
        'w3opcfe_checkout_fields_edit',
        __( 'Checkout Fields', 'on-page-checkout-for-elementor' ),
        __( 'Checkout Fields', 'on-page-checkout-for-elementor' ),
        'manage_options',
        'w3opcfe_checkout_fields_edit',
        'w3opcfe_checkout_fields_edit_page'
    );

    // 2. Upgrade to Pro
    add_submenu_page(
        'w3opcfe_checkout_fields_edit',
        __( 'Upgrade to Pro', 'on-page-checkout-for-elementor' ),
        '<span class="w3opcfe-upgrade-menu-item" style="color:#f0b849; font-weight:600;">' . esc_html__( 'Upgrade to Pro', 'on-page-checkout-for-elementor' ) . '</span>',
        'manage_options',
        'w3opcfe_upgrade_pro',
        'w3opcfe_upgrade_pro_page'
    );
}

function w3opcfe_checkout_fields_edit_page() {
    if ( class_exists( 'W3OPCFE_Checkout_Fields_Settings' ) ) {
        W3OPCFE_Checkout_Fields_Settings::render_page();
    }
}

/**
 * Upgrade to Pro page: lists the Pro-only features and links to the store.
 */
function w3opcfe_upgrade_pro_page() {
    $pro_url  = 'https://store.w3relations.com/products/on-page-checkout-pro-for-woocommerce';
    $currency = function_exists( 'get_woocommerce_currency_symbol' ) ? html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ) : '$';

    $sections = [
        [
            'title' => __( 'Order Bump', 'on-page-checkout-for-elementor' ),
            'desc'  => __( 'A one-click add-on offer right above the Place Order button. Customers tick a box, the item joins the order, and your average order value goes up.', 'on-page-checkout-for-elementor' ),
            'items' => [
                __( 'Simple and variable offer products', 'on-page-checkout-for-elementor' ),
                __( 'Percentage or fixed-amount discount', 'on-page-checkout-for-elementor' ),
                __( 'Custom title, description and checkbox label', 'on-page-checkout-for-elementor' ),
                __( 'Style controls in the Elementor editor', 'on-page-checkout-for-elementor' ),
            ],
        ],
        [
            'title'  => __( 'Fake Order Protection', 'on-page-checkout-for-elementor' ),
            'desc'   => __( 'Three layers of checks that run before an order is created, so fake and undeliverable orders never reach your courier.', 'on-page-checkout-for-elementor' ),
            'groups' => [
                [
                    'title' => __( 'Input validation', 'on-page-checkout-for-elementor' ),
                    'items' => [
                        __( 'Bangladesh phone validation and normalization', 'on-page-checkout-for-elementor' ),
                        __( 'Name and address quality checks', 'on-page-checkout-for-elementor' ),
                        __( 'Banned keywords and repeated characters', 'on-page-checkout-for-elementor' ),
                    ],
                ],
                [
                    'title' => __( 'Duplicate & velocity control', 'on-page-checkout-for-elementor' ),
                    'items' => [
                        __( 'Same phone, same product detection', 'on-page-checkout-for-elementor' ),
                        __( 'Merge repeats into the pending order', 'on-page-checkout-for-elementor' ),
                        __( 'Device and IP order limits', 'on-page-checkout-for-elementor' ),
                    ],
                ],
                [
                    'title' => __( 'Blocklists & allowlists', 'on-page-checkout-for-elementor' ),
                    'items' => [
                        __( 'Phone, device, fingerprint, IP and address keyword', 'on-page-checkout-for-elementor' ),
                        __( 'Auto-block after failed or returned orders', 'on-page-checkout-for-elementor' ),
                        __( 'CSV import and export', 'on-page-checkout-for-elementor' ),
                    ],
                ],
            ],
        ],
        [
            'title' => __( 'Order risk tools', 'on-page-checkout-for-elementor' ),
            'desc'  => __( 'Every order gets a risk badge in the WooCommerce orders list, so suspicious ones stand out before you ship.', 'on-page-checkout-for-elementor' ),
            'items' => [
                __( 'See other recent orders from the same phone', 'on-page-checkout-for-elementor' ),
                __( 'Block or allow a customer from the order page', 'on-page-checkout-for-elementor' ),
                __( 'Log-only mode to test rules before enforcing them', 'on-page-checkout-for-elementor' ),
                __( 'Neutral block message that never reveals the rule', 'on-page-checkout-for-elementor' ),
            ],
        ],
        [
            'title' => __( 'Fake order network', 'on-page-checkout-for-elementor' ),
            'desc'  => __( 'Optionally check phone numbers against fraud reports shared by other participating stores.', 'on-page-checkout-for-elementor' ),
            'items' => [
                __( 'Numbers are hashed (HMAC-SHA256), never sent in plain text', 'on-page-checkout-for-elementor' ),
                __( 'Cached for 12 hours with a 2-second timeout', 'on-page-checkout-for-elementor' ),
            ],
        ],
        [
            'title' => __( 'Automatic updates', 'on-page-checkout-for-elementor' ),
            'desc'  => __( 'New features and fixes arrive in your WordPress dashboard like any other plugin update, as long as your license is active.', 'on-page-checkout-for-elementor' ),
            'items' => [],
        ],
    ];

    // [ feature, in free ]
    $compare = [
        [ __( 'On-Page Checkout widget for Elementor', 'on-page-checkout-for-elementor' ), true ],
        [ __( 'Simple and variable products, variation cards', 'on-page-checkout-for-elementor' ), true ],
        [ __( 'Countdown and Scarcity widgets', 'on-page-checkout-for-elementor' ), true ],
        [ __( 'Checkout fields editor', 'on-page-checkout-for-elementor' ), true ],
        [ __( 'Preselect a product from the URL', 'on-page-checkout-for-elementor' ), true ],
        [ __( 'Order bump', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Input validation', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Duplicate and velocity control', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Blocklists and allowlists', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Order risk badges and quick actions', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Fake order network', 'on-page-checkout-for-elementor' ), false ],
        [ __( 'Automatic updates', 'on-page-checkout-for-elementor' ), false ],
    ];

    $check = '<svg class="w3up-mark w3up-mark-yes" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 8.5l3 3 7-7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    $dash  = '<svg class="w3up-mark w3up-mark-no" viewBox="0 0 16 16" aria-hidden="true"><path d="M4 8h8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
    $mark_tags = [
        'svg'  => [ 'class' => true, 'viewbox' => true, 'aria-hidden' => true ],
        'path' => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ],
    ];
    ?>
    <div class="wrap w3up">
        <h1 class="screen-reader-text"><?php esc_html_e( 'Upgrade to Pro', 'on-page-checkout-for-elementor' ); ?></h1>

        <section class="w3up-hero">
            <div class="w3up-hero-copy">
                <p class="w3up-eyebrow"><?php esc_html_e( 'On-Page Checkout Pro', 'on-page-checkout-for-elementor' ); ?></p>
                <h2 class="w3up-title"><?php esc_html_e( 'Fewer fake orders. Bigger baskets.', 'on-page-checkout-for-elementor' ); ?></h2>
                <p class="w3up-lead">
                    <?php esc_html_e( 'Pro keeps everything you already use and adds an order bump at checkout, plus a full set of checks that stop fake and undeliverable orders before they are placed.', 'on-page-checkout-for-elementor' ); ?>
                </p>
                <div class="w3up-actions">
                    <a href="<?php echo esc_url( $pro_url ); ?>" class="w3up-btn" target="_blank" rel="noopener noreferrer">
                        <?php esc_html_e( 'Get Pro', 'on-page-checkout-for-elementor' ); ?>
                        <span class="w3up-btn-arrow" aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="#w3up-compare" class="w3up-link"><?php esc_html_e( 'Compare Free and Pro', 'on-page-checkout-for-elementor' ); ?></a>
                </div>
                <p class="w3up-note"><?php esc_html_e( 'Your widgets and settings carry over when you switch.', 'on-page-checkout-for-elementor' ); ?></p>
            </div>

            <div class="w3up-stage" aria-hidden="true">
                <p class="w3up-stage-label"><?php esc_html_e( 'At checkout', 'on-page-checkout-for-elementor' ); ?></p>
                <div class="w3up-bump">
                    <div class="w3up-bump-head">
                        <span class="w3up-bump-box"><?php echo wp_kses( $check, $mark_tags ); ?></span>
                        <?php esc_html_e( 'Yes! Add this to my order', 'on-page-checkout-for-elementor' ); ?>
                    </div>
                    <div class="w3up-bump-body">
                        <span class="w3up-bump-thumb"></span>
                        <span class="w3up-bump-text">
                            <span class="w3up-bump-title"><?php esc_html_e( 'Special one-time offer!', 'on-page-checkout-for-elementor' ); ?></span>
                            <span class="w3up-bump-product">
                                <?php esc_html_e( 'Leather card holder', 'on-page-checkout-for-elementor' ); ?>
                                <del><?php echo esc_html( $currency . '1,200' ); ?></del>
                                <ins><?php echo esc_html( $currency . '900' ); ?></ins>
                            </span>
                        </span>
                    </div>
                </div>

                <p class="w3up-stage-label"><?php esc_html_e( 'In your orders list', 'on-page-checkout-for-elementor' ); ?></p>
                <div class="w3up-orders">
                    <div class="w3up-order">
                        <span class="w3up-order-id">#2041</span>
                        <span class="w3up-order-meta"><?php esc_html_e( 'New customer, valid phone', 'on-page-checkout-for-elementor' ); ?></span>
                        <span class="w3up-badge w3up-badge-clean"><?php esc_html_e( 'Clean', 'on-page-checkout-for-elementor' ); ?></span>
                    </div>
                    <div class="w3up-order">
                        <span class="w3up-order-id">#2042</span>
                        <span class="w3up-order-meta"><?php esc_html_e( 'Same phone and product as #2039', 'on-page-checkout-for-elementor' ); ?></span>
                        <span class="w3up-badge w3up-badge-duplicate"><?php esc_html_e( 'Duplicate', 'on-page-checkout-for-elementor' ); ?></span>
                    </div>
                    <div class="w3up-order">
                        <span class="w3up-order-id">#2043</span>
                        <span class="w3up-order-meta"><?php esc_html_e( '4 orders from one device in an hour', 'on-page-checkout-for-elementor' ); ?></span>
                        <span class="w3up-badge w3up-badge-flagged"><?php esc_html_e( 'Flagged', 'on-page-checkout-for-elementor' ); ?></span>
                    </div>
                    <div class="w3up-order">
                        <span class="w3up-order-id">#2044</span>
                        <span class="w3up-order-meta"><?php esc_html_e( 'Phone on your blocklist', 'on-page-checkout-for-elementor' ); ?></span>
                        <span class="w3up-badge w3up-badge-blocked"><?php esc_html_e( 'Blocked', 'on-page-checkout-for-elementor' ); ?></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="w3up-features">
            <h2 class="w3up-section-title"><?php esc_html_e( "What's included in Pro", 'on-page-checkout-for-elementor' ); ?></h2>

            <?php foreach ( $sections as $index => $section ) : ?>
                <div class="w3up-row">
                    <div class="w3up-row-head">
                        <span class="w3up-num"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
                        <h3><?php echo esc_html( $section['title'] ); ?></h3>
                    </div>
                    <div class="w3up-row-body">
                        <p><?php echo esc_html( $section['desc'] ); ?></p>

                        <?php if ( ! empty( $section['groups'] ) ) : ?>
                            <div class="w3up-groups">
                                <?php foreach ( $section['groups'] as $group ) : ?>
                                    <div class="w3up-group">
                                        <h4><?php echo esc_html( $group['title'] ); ?></h4>
                                        <ul class="w3up-list">
                                            <?php foreach ( $group['items'] as $item ) : ?>
                                                <li><?php echo wp_kses( $check, $mark_tags ); ?><?php echo esc_html( $item ); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif ( ! empty( $section['items'] ) ) : ?>
                            <ul class="w3up-list w3up-list-cols">
                                <?php foreach ( $section['items'] as $item ) : ?>
                                    <li><?php echo wp_kses( $check, $mark_tags ); ?><?php echo esc_html( $item ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="w3up-compare" id="w3up-compare">
            <h2 class="w3up-section-title"><?php esc_html_e( 'Free and Pro, side by side', 'on-page-checkout-for-elementor' ); ?></h2>
            <table class="w3up-table">
                <thead>
                    <tr>
                        <th scope="col"><?php esc_html_e( 'Feature', 'on-page-checkout-for-elementor' ); ?></th>
                        <th scope="col"><?php esc_html_e( 'Free', 'on-page-checkout-for-elementor' ); ?></th>
                        <th scope="col" class="w3up-col-pro"><?php esc_html_e( 'Pro', 'on-page-checkout-for-elementor' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $compare as $row ) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html( $row[0] ); ?></th>
                            <td>
                                <?php echo wp_kses( $row[1] ? $check : $dash, $mark_tags ); ?>
                                <span class="screen-reader-text"><?php echo $row[1] ? esc_html__( 'Included', 'on-page-checkout-for-elementor' ) : esc_html__( 'Not included', 'on-page-checkout-for-elementor' ); ?></span>
                            </td>
                            <td class="w3up-col-pro">
                                <?php echo wp_kses( $check, $mark_tags ); ?>
                                <span class="screen-reader-text"><?php esc_html_e( 'Included', 'on-page-checkout-for-elementor' ); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="w3up-closing">
            <div>
                <h2><?php esc_html_e( 'Ready to protect your checkout?', 'on-page-checkout-for-elementor' ); ?></h2>
                <p><?php esc_html_e( 'Install Pro, activate your license, and your existing setup keeps working.', 'on-page-checkout-for-elementor' ); ?></p>
            </div>
            <a href="<?php echo esc_url( $pro_url ); ?>" class="w3up-btn w3up-btn-light" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Get Pro', 'on-page-checkout-for-elementor' ); ?>
                <span class="w3up-btn-arrow" aria-hidden="true">&rarr;</span>
            </a>
        </section>
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

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- Read-only query arg set by WordPress Settings API after redirect.
    $settings_updated = isset( $_GET['settings-updated'] ) ? sanitize_text_field( wp_unslash( $_GET['settings-updated'] ) ) : '';

    if ( '1' === $settings_updated || 'true' === $settings_updated ) {
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
        add_action( 'update_option_w3opcfe_checkout_fields_on_page', [ $this, 'sync_master_option' ] );
        add_action( 'update_option_w3opcfe_checkout_fields_store', [ $this, 'sync_master_option' ] );
    }

    public function sync_master_option() {
        $on_page = get_option( 'w3opcfe_checkout_fields_on_page', 1 );
        $store   = get_option( 'w3opcfe_checkout_fields_store', 0 );
        update_option( 'w3opcfe_checkout_fields_master', ( $on_page || $store ) ? 1 : 0 );
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
            'w3opcfe_checkout_fields_on_page',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        register_setting(
            'w3opcfe_checkout_fields_group',
            'w3opcfe_checkout_fields_store',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 0,
            ]
        );

        register_setting(
            'w3opcfe_checkout_fields_group',
            'w3opcfe_checkout_fields_master',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [ $this, 'sanitize_checkbox' ],
                'default'           => 1,
            ]
        );

        add_settings_section(
            'w3opcfe_checkout_fields_master_section',
            __( 'General Field Options', 'on-page-checkout-for-elementor' ),
            function() {
                echo '<p>' . esc_html__( 'Configure where your custom checkout field settings should be applied.', 'on-page-checkout-for-elementor' ) . '</p>';
            },
            'w3opcfe-checkout-fields'
        );

        add_settings_field(
            'w3opcfe_checkout_fields_on_page_field',
            __( 'Enable custom field for on-page checkout', 'on-page-checkout-for-elementor' ),
            [ $this, 'render_on_page_switch' ],
            'w3opcfe-checkout-fields',
            'w3opcfe_checkout_fields_master_section'
        );

        add_settings_field(
            'w3opcfe_checkout_fields_store_field',
            __( 'Enable custom field for store checkout page', 'on-page-checkout-for-elementor' ),
            [ $this, 'render_store_switch' ],
            'w3opcfe-checkout-fields',
            'w3opcfe_checkout_fields_master_section'
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

    /**
     * Render modern Checkout Fields Editor page.
     */
    public static function render_page() {
        $option_name = 'w3opcfe_checkout_fields';
        $options     = get_option( $option_name, [] );
        $on_page_val = get_option( 'w3opcfe_checkout_fields_on_page', 1 );
        $store_val   = get_option( 'w3opcfe_checkout_fields_store', 0 );

        $checkout        = WC()->checkout();
        $billing_fields  = $checkout ? $checkout->get_checkout_fields( 'billing' ) : [];
        $shipping_fields = $checkout ? $checkout->get_checkout_fields( 'shipping' ) : [];
        ?>
        <div class="wrap w3opcfe-admin-wrap">
            <h1>
                <span class="dashicons dashicons-forms" style="font-size:32px; width:32px; height:32px; vertical-align:middle; margin-right:8px; color:#2271b1;"></span>
                <?php esc_html_e( 'Checkout Fields Editor', 'on-page-checkout-for-elementor' ); ?>
            </h1>
            <p class="description">
                <?php esc_html_e( 'Customize field labels, set mandatory requirements, or hide unwanted checkout fields for both On-Page Checkout and Store Classic Checkout.', 'on-page-checkout-for-elementor' ); ?>
            </p>

            <?php settings_errors( 'w3opcfe_checkout_fields_group' ); ?>

            <form method="post" action="options.php">
                <?php settings_fields( 'w3opcfe_checkout_fields_group' ); ?>

                <!-- Master Scope Control Card -->
                <div class="card" style="max-width: 960px; margin-bottom: 24px; padding: 18px 24px; background:#fff; border-left:4px solid #2271b1; box-shadow:0 1px 3px rgba(0,0,0,0.05); border-radius:6px;">
                    <div style="display:flex; flex-wrap:wrap; gap:36px; align-items:center;">
                        <div class="w3opcfe-switch-wrapper">
                            <label class="w3opcfe-switch">
                                <input type="hidden" name="w3opcfe_checkout_fields_on_page" value="0">
                                <input type="checkbox" name="w3opcfe_checkout_fields_on_page" value="1" <?php checked( 1, $on_page_val ); ?>>
                                <span class="w3opcfe-slider"></span>
                            </label>
                            <div>
                                <strong style="display:block; font-size:14px; margin-bottom:2px;"><?php esc_html_e( 'Enable custom field for on-page checkout', 'on-page-checkout-for-elementor' ); ?></strong>
                                <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Apply custom field labels, requirements, and visibility to On-Page Checkout widget.', 'on-page-checkout-for-elementor' ); ?></span>
                            </div>
                        </div>

                        <div class="w3opcfe-switch-wrapper">
                            <label class="w3opcfe-switch">
                                <input type="hidden" name="w3opcfe_checkout_fields_store" value="0">
                                <input type="checkbox" name="w3opcfe_checkout_fields_store" value="1" <?php checked( 1, $store_val ); ?>>
                                <span class="w3opcfe-slider"></span>
                            </label>
                            <div>
                                <strong style="display:block; font-size:14px; margin-bottom:2px;"><?php esc_html_e( 'Enable custom field for store checkout page', 'on-page-checkout-for-elementor' ); ?></strong>
                                <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Apply custom field labels, requirements, and visibility to WooCommerce classic store checkout.', 'on-page-checkout-for-elementor' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <nav class="nav-tab-wrapper w3opcfe-tabs" style="margin-bottom: 20px; max-width:960px;">
                    <a href="#billing" class="nav-tab nav-tab-active" data-tab="billing">
                        <span class="dashicons dashicons-id-alt" style="vertical-align:text-top; margin-right:4px;"></span>
                        <?php esc_html_e( 'Billing Fields', 'on-page-checkout-for-elementor' ); ?>
                        <span class="update-plugins count-<?php echo count( $billing_fields ); ?>" style="margin-left:6px; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 6px; border-radius:10px;"><?php echo count( $billing_fields ); ?></span>
                    </a>
                    <a href="#shipping" class="nav-tab" data-tab="shipping">
                        <span class="dashicons dashicons-car" style="vertical-align:text-top; margin-right:4px;"></span>
                        <?php esc_html_e( 'Shipping Fields', 'on-page-checkout-for-elementor' ); ?>
                        <span class="update-plugins count-<?php echo count( $shipping_fields ); ?>" style="margin-left:6px; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 6px; border-radius:10px;"><?php echo count( $shipping_fields ); ?></span>
                    </a>
                    <a href="#other" class="nav-tab" data-tab="other">
                        <span class="dashicons dashicons-clipboard" style="vertical-align:text-top; margin-right:4px;"></span>
                        <?php esc_html_e( 'Other Settings', 'on-page-checkout-for-elementor' ); ?>
                    </a>
                </nav>

                <!-- TAB 1: Billing Fields -->
                <div class="w3opcfe-tab-pane" id="w3opcfe-tab-billing">
                    <div class="card" style="max-width: 960px; padding: 20px 24px;">
                        <h2 style="margin-top:0; margin-bottom:4px;"><?php esc_html_e( 'Billing Fields', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description" style="margin-bottom:20px;"><?php esc_html_e( 'Manage visibility, custom labels, and required flags for customer billing fields.', 'on-page-checkout-for-elementor' ); ?></p>

                        <?php foreach ( $billing_fields as $wc_key => $field ) :
                            $field_key = "billing_{$wc_key}";
                            $saved     = $options[ $field_key ] ?? [];
                            $enabled   = isset( $saved['enabled'] ) ? $saved['enabled'] : 1;
                            $label     = isset( $saved['label'] ) ? $saved['label'] : ( $field['label'] ?? '' );
                            $required  = isset( $saved['required'] ) ? $saved['required'] : ( ! empty( $field['required'] ) ? 1 : 0 );
                            $title     = ! empty( $field['label'] ) ? $field['label'] : $wc_key;
                            ?>
                            <div class="w3opcfe-field-card <?php echo empty( $enabled ) ? 'is-disabled' : ''; ?>">
                                <div class="w3opcfe-field-main">
                                    <label class="w3opcfe-switch">
                                        <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]" value="0">
                                        <input class="w3opcfe-enable-toggle" type="checkbox"
                                            name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]"
                                            value="1" <?php checked( 1, $enabled ); ?>>
                                        <span class="w3opcfe-slider"></span>
                                    </label>
                                    <div class="w3opcfe-field-title-wrap">
                                        <span class="w3opcfe-field-title"><?php echo esc_html( $title ); ?></span>
                                        <span class="w3opcfe-field-key"><?php echo esc_html( $field_key ); ?></span>
                                    </div>
                                </div>

                                <div class="w3opcfe-dependent-options" style="<?php echo empty( $enabled ) ? 'display:none;' : ''; ?>">
                                    <div class="w3opcfe-opt-group">
                                        <label class="w3opcfe-input-label"><?php esc_html_e( 'Display Label', 'on-page-checkout-for-elementor' ); ?></label>
                                        <input type="text"
                                            name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][label]"
                                            value="<?php echo esc_attr( $label ); ?>"
                                            placeholder="<?php echo esc_attr( $field['label'] ?? '' ); ?>"
                                            class="regular-text w3opcfe-label-input">
                                    </div>

                                    <div class="w3opcfe-opt-group w3opcfe-required-group">
                                        <label class="w3opcfe-required-pill">
                                            <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]" value="0">
                                            <input type="checkbox"
                                                name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]"
                                                value="1" <?php checked( 1, $required ); ?>>
                                            <span class="w3opcfe-pill-label"><?php esc_html_e( 'Required', 'on-page-checkout-for-elementor' ); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TAB 2: Shipping Fields -->
                <div class="w3opcfe-tab-pane" id="w3opcfe-tab-shipping" style="display:none;">
                    <div class="card" style="max-width: 960px; padding: 20px 24px;">
                        <h2 style="margin-top:0; margin-bottom:4px;"><?php esc_html_e( 'Shipping Fields', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description" style="margin-bottom:20px;"><?php esc_html_e( 'Manage visibility, custom labels, and required flags for customer shipping fields.', 'on-page-checkout-for-elementor' ); ?></p>

                        <?php foreach ( $shipping_fields as $wc_key => $field ) :
                            $field_key = "shipping_{$wc_key}";
                            $saved     = $options[ $field_key ] ?? [];
                            $enabled   = isset( $saved['enabled'] ) ? $saved['enabled'] : 1;
                            $label     = isset( $saved['label'] ) ? $saved['label'] : ( $field['label'] ?? '' );
                            $required  = isset( $saved['required'] ) ? $saved['required'] : ( ! empty( $field['required'] ) ? 1 : 0 );
                            $title     = ! empty( $field['label'] ) ? $field['label'] : $wc_key;
                            ?>
                            <div class="w3opcfe-field-card <?php echo empty( $enabled ) ? 'is-disabled' : ''; ?>">
                                <div class="w3opcfe-field-main">
                                    <label class="w3opcfe-switch">
                                        <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]" value="0">
                                        <input class="w3opcfe-enable-toggle" type="checkbox"
                                            name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]"
                                            value="1" <?php checked( 1, $enabled ); ?>>
                                        <span class="w3opcfe-slider"></span>
                                    </label>
                                    <div class="w3opcfe-field-title-wrap">
                                        <span class="w3opcfe-field-title"><?php echo esc_html( $title ); ?></span>
                                        <span class="w3opcfe-field-key"><?php echo esc_html( $field_key ); ?></span>
                                    </div>
                                </div>

                                <div class="w3opcfe-dependent-options" style="<?php echo empty( $enabled ) ? 'display:none;' : ''; ?>">
                                    <div class="w3opcfe-opt-group">
                                        <label class="w3opcfe-input-label"><?php esc_html_e( 'Display Label', 'on-page-checkout-for-elementor' ); ?></label>
                                        <input type="text"
                                            name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][label]"
                                            value="<?php echo esc_attr( $label ); ?>"
                                            placeholder="<?php echo esc_attr( $field['label'] ?? '' ); ?>"
                                            class="regular-text w3opcfe-label-input">
                                    </div>

                                    <div class="w3opcfe-opt-group w3opcfe-required-group">
                                        <label class="w3opcfe-required-pill">
                                            <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]" value="0">
                                            <input type="checkbox"
                                                name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][required]"
                                                value="1" <?php checked( 1, $required ); ?>>
                                            <span class="w3opcfe-pill-label"><?php esc_html_e( 'Required', 'on-page-checkout-for-elementor' ); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TAB 3: Other Fields (Order Notes) -->
                <div class="w3opcfe-tab-pane" id="w3opcfe-tab-other" style="display:none;">
                    <div class="card" style="max-width: 960px; padding: 20px 24px;">
                        <h2 style="margin-top:0; margin-bottom:4px;"><?php esc_html_e( 'Other Settings', 'on-page-checkout-for-elementor' ); ?></h2>
                        <p class="description" style="margin-bottom:20px;"><?php esc_html_e( 'Customize additional checkout options such as the order comments note field.', 'on-page-checkout-for-elementor' ); ?></p>

                        <?php
                        $field_key = 'other_order_notes';
                        $saved     = $options[ $field_key ] ?? [];
                        $enabled   = isset( $saved['enabled'] ) ? $saved['enabled'] : 1;
                        $label     = isset( $saved['label'] ) ? $saved['label'] : __( 'Order note', 'on-page-checkout-for-elementor' );
                        ?>
                        <div class="w3opcfe-field-card <?php echo empty( $enabled ) ? 'is-disabled' : ''; ?>">
                            <div class="w3opcfe-field-main">
                                <label class="w3opcfe-switch">
                                    <input type="hidden" name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]" value="0">
                                    <input class="w3opcfe-enable-toggle" type="checkbox"
                                        name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][enabled]"
                                        value="1" <?php checked( 1, $enabled ); ?>>
                                    <span class="w3opcfe-slider"></span>
                                </label>
                                <div class="w3opcfe-field-title-wrap">
                                    <span class="w3opcfe-field-title"><?php esc_html_e( 'Order Notes / Comments', 'on-page-checkout-for-elementor' ); ?></span>
                                    <span class="w3opcfe-field-key"><?php echo esc_html( $field_key ); ?></span>
                                </div>
                            </div>

                            <div class="w3opcfe-dependent-options" style="<?php echo empty( $enabled ) ? 'display:none;' : ''; ?>">
                                <div class="w3opcfe-opt-group">
                                    <label class="w3opcfe-input-label"><?php esc_html_e( 'Display Label', 'on-page-checkout-for-elementor' ); ?></label>
                                    <input type="text"
                                        name="<?php echo esc_attr( $option_name ); ?>[<?php echo esc_attr( $field_key ); ?>][label]"
                                        value="<?php echo esc_attr( $label ); ?>"
                                        placeholder="<?php esc_attr_e( 'Notes about your order...', 'on-page-checkout-for-elementor' ); ?>"
                                        class="regular-text w3opcfe-label-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top:20px;">
                    <?php submit_button(); ?>
                </div>
            </form>
        </div>
        <?php
    }

    public function render_on_page_switch() {
        $val = get_option( 'w3opcfe_checkout_fields_on_page', 1 );
        ?>
        <div class="w3opcfe-switch-wrapper">
            <label class="w3opcfe-switch">
                <input type="hidden" name="w3opcfe_checkout_fields_on_page" value="0">
                <input type="checkbox" name="w3opcfe_checkout_fields_on_page" value="1" <?php checked( 1, $val ); ?>>
                <span class="w3opcfe-slider"></span>
            </label>
            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Apply custom field labels, requirements, and visibility to On-Page Checkout widget.', 'on-page-checkout-for-elementor' ); ?></span>
        </div>
        <?php
    }

    public function render_store_switch() {
        $val = get_option( 'w3opcfe_checkout_fields_store', 0 );
        ?>
        <div class="w3opcfe-switch-wrapper">
            <label class="w3opcfe-switch">
                <input type="hidden" name="w3opcfe_checkout_fields_store" value="0">
                <input type="checkbox" name="w3opcfe_checkout_fields_store" value="1" <?php checked( 1, $val ); ?>>
                <span class="w3opcfe-slider"></span>
            </label>
            <span class="w3opcfe-switch-desc"><?php esc_html_e( 'Apply custom field labels, requirements, and visibility to WooCommerce store classic checkout page.', 'on-page-checkout-for-elementor' ); ?></span>
        </div>
        <?php
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

    public function sanitize_checkbox( $val ) {
        return ! empty( $val ) ? 1 : 0;
    }

    /**
     * Determine if current request is for the On-Page Checkout widget.
     *
     * @return bool
     */
    public function is_on_page_checkout() {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing -- Read-only check for current AJAX action name; actual actions verify nonces.
            $action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
            if ( in_array( $action, [ 'w3opcfe_load_checkout_fields', 'w3opcfe_process_checkout' ], true ) ) {
                return true;
            }
        }
        return (bool) apply_filters( 'w3opcfe_is_on_page_checkout', false );
    }

    public function modify_checkout_fields( $fields ) {
        // Do not modify fields in admin area unless it is our On-Page Checkout AJAX request
        if ( is_admin() && ! $this->is_on_page_checkout() ) {
            return $fields;
        }

        $is_on_page = $this->is_on_page_checkout();

        if ( $is_on_page ) {
            $enabled = get_option( 'w3opcfe_checkout_fields_on_page', get_option( 'w3opcfe_checkout_fields_master', 1 ) );
        } else {
            $enabled = get_option( 'w3opcfe_checkout_fields_store', 0 );
        }

        if ( empty( $enabled ) ) {
            return $fields;
        }

        $options = get_option( $this->option_name, [] );
        if ( empty( $options ) || ! is_array( $options ) ) {
            return $fields;
        }

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
