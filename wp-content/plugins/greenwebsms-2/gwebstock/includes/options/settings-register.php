<?php

if ( ! defined( 'ABSPATH' ) ) exit;

require_once(GWEB_PATH . 'includes/options/setting-pages/settings-display.php' );
require_once(GWEB_PATH . 'includes/options/setting-pages/pending-alerts.php' );
require_once(GWEB_PATH . 'includes/options/setting-pages/sent-alerts.php' );



function gweb_register_option_menu_page() {

    $menu_page = add_submenu_page( 'greenweb-sms',
                                'Back in Stock Manager',
                                'Back in Stock Manager',
                                'manage_options',
                              'gweb-stock-notify',
                                'gweb_display_setting_page'
								);



    add_action('admin_print_scripts-' . $menu_page, function() {
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'out-of-stock', GWEB_URL_PATH . 'assets/css/out-of-stock-admin.css');
        // Update / Reset double Message fix
        $custom_css = "#setting-error-settings_updated, #setting-error-success_message { display: none !important; }";
        wp_add_inline_style( 'out-of-stock', $custom_css );

        $display_manager = new displayManager();
        wp_enqueue_script( 'out-of-stock-admin', GWEB_URL_PATH . 'assets/js/out-of-stock-admin.js',  array('jquery', 'wp-color-picker'), 10.0, true);
        wp_localize_script( 'out-of-stock-admin', 'alertDataAdmin', array(
          'api_base_url' => esc_url_raw( rest_url('gweb-stock-notify/v1') ),
          'nonce' => wp_create_nonce( 'wp_rest' ),
          'current_tab' => !empty($_GET['tab']) ? esc_html($_GET['tab']) : ''
        ) );
    });
	}

add_action( 'admin_menu', 'gweb_register_option_menu_page', 20 );



function gweb_register_settings() {

  register_setting(
          'gweb_stock_alert_button_options',
          'gweb_stock_alert_button_options',
          'gweb_validate_button_options' );

  register_setting(
          'gweb_stock_alert_mobile_options',
          'gweb_stock_alert_mobile_options',
          'gweb_validate_mobile_options' );

      add_settings_section(
              'gweb_button_settings',
              'Alert Button Settings',
              'gweb_description',
              'gweb-stock-notify' );

      add_settings_section(
              'gweb_mobile_settings',
              'Notification Settings',
              'gweb_description',
              'gweb-stock-notify' );

      // Button Settings
      add_settings_field(
              'gweb_button_text',
              '',
              'gweb_callback_field_text',
              'gweb-stock-notify',
              'gweb_button_settings',
              ['id'=>'gweb_button_text',
               'option' => 'gweb_stock_alert_button_options',
               'label'=> 'Notification Text',
               'description'=> 'This will appear on the "notify when available" button.'] );

      add_settings_field(
             'gweb_button_text_cancel',
             '',
             'gweb_callback_field_text',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_text_cancel',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Signed up Text',
              'description'=> 'This will appear on the button on success.'] );

      add_settings_field(
             'gweb_button_color',
             '',
             'gweb_callback_color_picker_w_image',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Button Background',
              'description'=> 'Alert button background color.'] );

      add_settings_field(
             'gweb_button_text_color',
             '',
             'gweb_callback_color_picker',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_text_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Button Text',
              'description'=> 'Alert button text color.'] );

      add_settings_field(
             'gweb_submit_mobile_button_color',
             '',
             'gweb_callback_color_picker_w_image',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_submit_mobile_button_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Submit Form Background',

              'description'=> 'Submit Form Background Color. By default, your <u>Theme original colors</u> will be used. Change them according to your preferences. This option is for the guest user'] );

      add_settings_field(
             'gweb_mobile_text_color',
             '',
             'gweb_callback_color_picker',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_mobile_text_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Submit Text',
              'description'=> 'Mobile Number Submit Text Color. This option is for the guest user'] );

      add_settings_field(
             'gweb_mobile_field_back_color',
             '',
             'gweb_callback_color_picker',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_mobile_field_back_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Field Background',
              'description'=> 'Mobile Number Fields Background Color. This option is for the guest user'] );

      add_settings_field(
             'gweb_mobile_field_color',
             '',
             'gweb_callback_color_picker',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_mobile_field_color',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Field Text',
              'description'=> 'Mobile Number Writing Field Text Color. For guest users'] );

      add_settings_field(
             'gweb_button_size',
             '',
             'gweb_callback_radio_field',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_size',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Button Size',
              'options'=> ['normal'=> esc_html__('normal', GWEB_DOMAIN),
                           'wide'=> esc_html__('wide', GWEB_DOMAIN)],
              'description'=> 'How wide the button will display.'] );

      add_settings_field(
             'gweb_fontawesome',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_fontawesome',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Enable FontAwesome',
              'description'=> 'When enabled, FontAwesome icons will be displayed inside the add to waitlist button'] );

      add_settings_field(
             'gweb_enable_backorder_alert',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_enable_backorder_alert',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Enable On Backorder',
              'description'=> 'If your shop has backorder system you can turn this on to send notification after the product back in stock.'] );

      add_settings_field(
             'gweb_button_individual_variation',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_individual_variation',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Enable On Individual Variation',
              'description'=> 'Allows nested alerts on each OUT-OF-STOCK product variation. Customers can pick a specific alert request on a specific product that is out of stock.<br>
              <b>IMP: Some Themes/Plugins/Settings hide out of stock variations. If you can\'t see out of stock variations on the page disable this option!</b>'] );

      add_settings_field(
             'gweb_button_cancel_active',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_button_settings',
             ['id'=>'gweb_button_cancel_active',
              'option' => 'gweb_stock_alert_button_options',
              'label'=> 'Enable Cancel Alert',
              'description'=> 'When enabled the button will display a cancel option after the notification is requested.'] );

 add_settings_field(
             'gweb_stocknotify',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_mobile_settings',
             ['id'=>'gweb_stocknotify',
              'option' => 'gweb_stock_alert_mobile_options',
              'label'=> 'Enable/Disable Stock Notification System',
              'description'=> 'If you turn this off stock management & Notification system will be disabled, Turn on to active again'] );


      add_settings_field(
             'gweb_adminnotify',
             '',
             'gweb_callback_switch',
             'gweb-stock-notify',
             'gweb_mobile_settings',
             ['id'=>'gweb_adminnotify',
              'option' => 'gweb_stock_alert_mobile_options',
              'label'=> 'Enable Admin Notification',
              'description'=> 'When enabled, Admin will get a sms notification whenever anyone request for an Out Of Stock Product'] );

	     add_settings_field(
               'gweb_usernotify',
               '',
               'gweb_callback_switch',
               'gweb-stock-notify',
               'gweb_mobile_settings',
               ['id'=>'gweb_usernotify',
			   'option' => 'gweb_stock_alert_mobile_options',
                'label'=> 'Enable User Notification',
                'description'=> 'When enabled, User will get a sms notification whenever request for an Out Of Stock Product'] );


        add_settings_field(
               'gweb_mobile_subject',
               '',
               'gweb_callback_field_textarea',
               'gweb-stock-notify',
               'gweb_mobile_settings',
               ['id'=>'gweb_mobile_subject',
			   'option' => 'gweb_stock_alert_mobile_options',
                'label'=> 'Admin/Customers Product Request Notification:',
                'description'=> 'When enabled admin/customers will get a confirmation sms notification whenever request for an Out Of Stock Product <br><br>
				Available Shortcodes: %gweb_shopurl%, %gweb_mobile%, %gweb_shopname%, %gweb_productname%, %gweb_producturl% <br><br>
			  Example: %gweb_mobile%, is successfully requested for the %gweb_productname% products stock update. We will let you know once the product back in stock.
			  %gweb_shopname%
			  
			  <br>
              No links are allowed in this field.'] );
  
      add_settings_field(
             'gweb_mobile_body',
             '',
             'gweb_callback_field_textarea',
             'gweb-stock-notify',
             'gweb_mobile_settings',
             ['id'=>'gweb_mobile_body',
              'option' => 'gweb_stock_alert_mobile_options',
              'label'=> 'Back In Stock Notification',
              'description'=> ' Customers will receive a sms notification if their requested product back in stock.
			  <br><br>
			  Available Shortcodes: ShopURL: %gweb_shopurl%, ShopName: %gweb_shopname%, Product Name (Individual): %gweb_productname%, Product URL (Individual): %gweb_producturl%, Product Name And URL( Name: Product URL format): %gweb_productname:url%<br><br>
			  Example: Hello, Your requested %gweb_productname:url% products are available now. Please order before the stock out.
			  
			  %gweb_shopname%
			  <br>
              No links are allowed in this field.'] );

}
add_action( 'admin_init', 'gweb_register_settings' );

function gweb_add_settings_link( array $links ) {
    $url = get_admin_url() . "options-general.php?page=gweb-stock-notify&amp;tab=settings";
    $settings_link = '<a href="' . $url . '">' . __('Settings', GWEB_DOMAIN) . '</a>';
    $links[] = $settings_link;
    return $links;
  }
add_filter( 'plugin_action_links_' . plugin_basename( GWEB_FILE_PATH ), 'gweb_add_settings_link' );