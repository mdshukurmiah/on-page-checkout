<div class="g-web-form-placeholder">
	<form class="g-web-otp-form">

		<div class="g-web-otp-sent-txt">
			<span class="g-web-otp-no-txt"></span>
			<span class="g-web-otp-no-change"> <?php _e( "🛠️ Change", 'mobile-login-woocommerce' ); ?></span>
		</div>

		<div class="g-web-otp-notice-cont">
			<div class="g-web-notice"></div>
		</div>

		<div class="g-web-otp-input-cont">
			<?php for ( $i= 0; $i < $otp_length; $i++ ): ?>
				<input type="number" maxlength="1" autocomplete="off" name="g-web-otp[]" class="g-web-otp-input">
			<?php endfor; ?>
		</div>

		<input type="hidden" name="g-web-otp-phone-no" >
		<input type="hidden" name="g-web-otp-phone-code" >

		<button type="submit" class="button btn g-web-otp-verify-btn  <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php _e( 'Submit OTP', 'mobile-login-woocommerce' ); ?> </button>

		<div class="g-web-otp-resend">
			<a class="g-web-otp-resend-link"><?php _e( '🔄 Resend OTP', 'mobile-login-woocommerce' ); ?></a>
			<span class="g-web-otp-resend-timer"></span>
		</div>

		<input type="hidden" name="g-web-form-token" value="">

	</form>

</div>