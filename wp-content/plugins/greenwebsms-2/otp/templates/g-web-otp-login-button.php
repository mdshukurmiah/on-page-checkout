<?php
$registration_options = get_option('g-web-phone-options');
if (isset($registration_options['l-disble-emailf'] ) && ($registration_options['l-disble-emailf'] != "no")) {
$login_first = "yes";
} 
?>


<button style="margin-top:5px;margin-bottom:5px;" type="button" class="g-web-open-lwo-btn woocommerce-button button <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php _e( 'Login with OTP', 'mobile-login-woocommerce' ); ?></button>

<div class="g-web-lwo-form-placeholder" <?php if( $login_first !== 'yes' ): ?> style="display: none !important;" <?php endif; ?> >

	<div class="g-web-login-phinput-cont <?php echo esc_attr( implode( ' ', $cont_class ) ); ?>">

		<?php if( $label ): ?>
			<label class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>" for="g-web-login-phone"> <?php echo $label; ?>&nbsp;<span class="required">*</span></label>
		<?php endif; ?>


		<?php if( $is_login_popup ): ?>

			<div class="g-aff-group">
				<div class="g-aff-input-group">
					<span class="g-aff-input-icon fas fa-phone"></span>
					<input type="text" placeholder="<?php _e( '017xxxxxxx', 'mobile-login-woocommerce' ); ?>" name="g-web-phone-login" class="g-web-phone-login g-web-phone-input <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" required autocomplete="tel">
				</div>
			</div>

		<?php else: ?>

			<input type="text" placeholder="<?php _e( '017xxxxxxx', 'mobile-login-woocommerce' ); ?>" name="g-web-phone-login" class="g-web-phone-login g-web-phone-input <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" required  autocomplete="tel" >

		<?php endif; ?>

	</div>
<?php
		if (!isset($randomcsrf)) {
$randomcsrf = substr(md5(rand()), 0, 7);
			delete_expired_transients();
$tv = "ok";
if ((isset($registration_options['gweb-otp-session-timeout'])) AND (is_numeric($registration_options['gweb-otp-session-timeout'])) AND ($registration_options['gweb-otp-session-timeout'] > 0))	{
set_transient($randomcsrf, $tv, $registration_options['gweb-otp-session-timeout']);	
} else {
set_transient($randomcsrf, $tv, 600);
}
		
		}
		?>
	
		<input type="hidden" name="g-web-csrf" value="<?php echo $randomcsrf; ?>">
	<input type="hidden" name="g-web-form-token" value="<?php echo $form_token; ?>">
	<input type="hidden" name="g-web-form-type" value="login_user_with_otp">
	<input type="hidden" name="redirect" value="<?php echo $redirect; ?>">





<button type="submit" class="g-web-login-otp-btn  woocommerce-button button <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php _e( 'Login with OTP', 'mobile-login-woocommerce' ); ?></button></br><p></p>

<?php
		if (isset($registration_options['l-disble-emailf'] ) && ($registration_options['l-disble-emailf'] == "no")) {
	?>

	<button type="button" class="g-web-low-back woocommerce-button button <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php _e( 'Login with Email & Password', 'mobile-login-woocommerce' ); ?></button>

<?php } else {
			echo "<style>.woocommerce-info > .showlogin {
	display:none !important;
	}</style>";
		} 
?>

</div>