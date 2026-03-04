jQuery(document).ready(function ($) {
	
    // Get the current User infos
    const verified = $("input[name=wps_user_verified]");
    const bdbulksms_cod_payment_data = "0";
   const user_meta_filed = $("input[name=wps_user_meta_field]").val();
    const mobile = $("input[name=" + user_meta_filed + "]");
	 const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
    const intel_input = parseInt(wp_sms_woocommerce_otp.intel_input);
    const checkoutForm = $('form.woocommerce-checkout');
    let intel_class = "";

    if (intel_input === 1) {
        intel_class = " wp-sms-input-mobile-otp";
    }

    const dialog = $.dialog({
        columnClass: 'wps-otp-box',
        title: "",
        rtl: parseInt(wp_sms_woocommerce_otp.is_rtl),
        lazyOpen: true,
        content: `<div id="greenweb-otp-model-check">
            <div id="wps-otp">
            <div data-otp-alert style="position: relative;height: 50px;display:none;"></div>
            <div id="greenwebsmsotpbox" class="otp-mobile-verification">
            ` + wp_sms_woocommerce_otp.lang.title + `
            <input inputmode="numeric" pattern="[0-9]*" style="min-height: 35px;width: 90%;margin: 0 auto;margin-top: 15px;margin-bottom: 15px;" type="text" name="wp_sms_otp_number" class="woocommerce-Input input input-text woocommerce-Input--text ` + intel_class + `" placeholder="` + wp_sms_woocommerce_otp.lang.number + `" value="` + mobile.val() + `"/>
<input type="hidden" name="gwebreqverifyc" value="` + gwebreqverifyc + `"/>
<input type="hidden" name="bdbulksms_cod_payment_data" value="` + bdbulksms_cod_payment_data + `"/>
            <input type="submit" name="otp-submit-number" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button" id="otp-submit-button" value="` + wp_sms_woocommerce_otp.lang.submit + `"/>
            </div>
            <div class="loading_otp"><img src="https://media.bdsms.net/bdbulksms.net.gif"></img>` + wp_sms_woocommerce_otp.lang.wait + `</div></div>
            `,
        buttons: {},
        closeIcon: true,
        fluid: true,
        width: 'auto'
    });

    const dialognew = $.dialog({
        columnClass: 'wps-otp-box',
        title: "",
        rtl: parseInt(wp_sms_woocommerce_otp.is_rtl),
        lazyOpen: true,
        content: `
            <div id="wps-otp">
            <div data-otp-alert style="position: relative;height: 50px;display:none;"></div>
            <div id="greenwebsmsotpbox" class="otp-mobile-verification">
            ` + wp_sms_woocommerce_otp.lang.title + `
            <input inputmode="numeric" pattern="[0-9]*" style="min-height: 35px;width: 90%;margin: 0 auto;margin-top: 15px;margin-bottom: 15px;" type="text" name="wp_sms_otp_number" class="woocommerce-Input input input-text woocommerce-Input--text ` + intel_class + `" placeholder="` + wp_sms_woocommerce_otp.lang.number + `" value="` + mobile.val() + `"/>
<input type="hidden" name="gwebreqverifyc" value="` + gwebreqverifyc + `"/>
<input type="hidden" name="bdbulksms_cod_payment_data" value="` + bdbulksms_cod_payment_data + `"/>
            <input type="submit"  name="otp-submit-number" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button" id="otp-submit-button" value="` + wp_sms_woocommerce_otp.lang.submit + `"/>
            </div>
            <div class="loading_otp"><img src="https://media.bdsms.net/bdbulksms.net.gif"></img>` + wp_sms_woocommerce_otp.lang.wait + `</div>
            `,
        buttons: {},
        closeIcon: true,
        fluid: true,
        width: 'auto'
    });	
	
	$(document).on("click", "a[name=otp-change-number]", function (e) {
		

 $("#greenwebsmsotpbox").show();

        e.preventDefault();
		 /* Show Loading Box */
		var modelnocheck = document.getElementById("greenweb-otp-model-check");

if(modelnocheck) {
    dialog.close();
} else {
	dialognew.close();
}
		   
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
        const verified = $(checkoutForm).find("input[name=wps_user_verified]");
        const mobile_number = $(checkoutForm).find("input[name=wps_user_mobile_number]");
        const mobile = $(checkoutForm).find("input[name=" + user_meta_filed + "]");
        const whitelist_countries = wp_sms_woocommerce_otp.countries_whitelist;

        let billing_country = false;
        const billing_country_select = $(checkoutForm).find("select[name=billing_country]");
        const billing_country_input = $(checkoutForm).find("input[name=billing_country]");

        if (billing_country_select.length) billing_country = billing_country_select.val();
        if (billing_country_input.length) billing_country = billing_country_input.val();

        if (whitelist_countries) {
            if (!whitelist_countries.includes(billing_country)) {
                return;
            }
        }

            /* Show Loading Box */
if(modelnocheck) {
    dialognew.open();
} else {
	dialog.open();
}

			var mobileforotp = mobile.val();
mobileforotp = mobileforotp.replace(/[^0-9+]/g, "");			
if (mobileforotp.startsWith("+88")) {
mobileforotp = mobileforotp.slice(3);
}

if (mobileforotp.startsWith("88")) {
mobileforotp = mobileforotp.slice(2);
}	

if (mobileforotp.startsWith("1")) {
mobileforotp = "0"+mobileforotp;
}
            $('input[name="wp_sms_otp_number"]').val(mobileforotp);
            const input = document.querySelector(".wp-sms-input-mobile-otp");

            if (input) {
                window.intlTelInput(input, {
                    onlyCountries: wp_sms_intel_tel_input.only_countries,
                    preferredCountries: wp_sms_intel_tel_input.preferred_countries,
                    autoHideDialCode: wp_sms_intel_tel_input.auto_hide,
                    nationalMode: wp_sms_intel_tel_input.national_mode,
                    separateDialCode: wp_sms_intel_tel_input.separate_dial,
                    utilsScript: wp_sms_intel_tel_input.util_js,
                    customContainer: 'intel-otp'
                });
                $(".intel-otp #country-listbox").attr('style', 'position: fixed !important;');
            }


	} );
    /**
     * Show verification box
     */
    $(document).on("click", "[name=woocommerce_checkout_place_order]", function (e) {
		 // $(document.body).on('checkout_error', function (e) {	
 
  var wooErrorCounts = $('.woocommerce-error li').length;
			  if (wooErrorCounts < "1") {
				  var wooErrorCounts = $('.wc-block-components-notice-banner__content').length;	
				  }
			  
        var wpsmsproValidationData = $('.woocommerce-error').find('li[data-wpsmspro]').data('wpsmspro');
			  
		  if (wpsmsproValidationData === undefined) {
		var ex = $(".is-error");
if (ex.text().indexOf("Please verify your mobile number") !== -1) {
				 var wpsmsproValidationData = 'otp-required';
}
		  }
			  
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
        const verified = $(checkoutForm).find("input[name=wps_user_verified]");
        const mobile_number = $(checkoutForm).find("input[name=wps_user_mobile_number]");
        const mobile = $(checkoutForm).find("input[name=" + user_meta_filed + "]");
        const whitelist_countries = wp_sms_woocommerce_otp.countries_whitelist;
		
   const selectedPaymentMethod = $( '.woocommerce-checkout input[name="payment_method"]:checked' ).attr( 'value' );
		
        let billing_country = false;
        const billing_country_select = $(checkoutForm).find("select[name=billing_country]");
        const billing_country_input = $(checkoutForm).find("input[name=billing_country]");

        if (billing_country_select.length) billing_country = billing_country_select.val();
        if (billing_country_input.length) billing_country = billing_country_input.val();

        if (whitelist_countries) {
            if (!whitelist_countries.includes(billing_country)) {
                return;
            }
        }

        if (verified.val() === '0' || mobile.val() === "" || mobile_number.val() !== mobile.val() || (wooErrorCounts > 0 && wpsmsproValidationData == 'otp-required')) {
       // if (wooErrorCounts > 0 && wpsmsproValidationData == 'otp-required') {
       
            e.preventDefault();

            /* Show Loading Box */
            dialog.open();

			var mobileforotp = mobile.val();
mobileforotp = mobileforotp.replace(/[^0-9+]/g, "");			
if (mobileforotp.startsWith("+88")) {
mobileforotp = mobileforotp.slice(3);
}

if (mobileforotp.startsWith("88")) {
mobileforotp = mobileforotp.slice(2);
}	

if (mobileforotp.startsWith("1")) {
mobileforotp = "0"+mobileforotp;
}
            $('input[name="wp_sms_otp_number"]').val(mobileforotp);
			 $('input[name="bdbulksms_cod_payment_data"]').val(selectedPaymentMethod);
            const input = document.querySelector(".wp-sms-input-mobile-otp");

            if (input) {
                window.intlTelInput(input, {
                    onlyCountries: wp_sms_intel_tel_input.only_countries,
                    preferredCountries: wp_sms_intel_tel_input.preferred_countries,
                    autoHideDialCode: wp_sms_intel_tel_input.auto_hide,
                    nationalMode: wp_sms_intel_tel_input.national_mode,
                    separateDialCode: wp_sms_intel_tel_input.separate_dial,
                    utilsScript: wp_sms_intel_tel_input.util_js,
                    customContainer: 'intel-otp'
                });
                $(".intel-otp #country-listbox").attr('style', 'position: fixed !important;');
            }

if ((mobileforotp.length) && (mobileforotp.startsWith("01"))) {
	document.getElementById('otp-submit-button').click();
	}			
			
        }
    });
	
	
	
    /**
     * Send User name Login with Mobile Step 1
     */
    $(document).on("click", "input[name=otp-submit-number]", function (e) {
        e.preventDefault();

        /* Show Loading */
        $(".loading_otp").show();
        $("[data-otp-alert]").html("");

   $("#greenwebsmsotpbox").hide();
		
        const wp_sms_otp_number = $("input[name=wp_sms_otp_number]").val();
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
const bdbulksmscodpaymentdata = $("input[name=bdbulksms_cod_payment_data]").val();

        $.ajax({
            url: wp_sms_woocommerce_otp.ajax,
            type: 'GET',
            dataType: "json",
            data: {
                action: 'wp_sms_woocommerce_otp',
                step: 1,
                wp_sms_otp_number: wp_sms_otp_number,
				gwebreqverifyc: gwebreqverifyc,
				bdbulksms_payment_method: bdbulksmscodpaymentdata,
            },
            success: function (data) {
                if (data.error === "yes") {
                    $("[data-otp-alert]").html(`<div class="alert-box error"><span>` + wp_sms_woocommerce_otp.lang.error + ` : </span>` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
                } else if (data.error === "yes-limit") {
                    $("[data-otp-alert]").html(`<div class="alert-box error"><span>` + wp_sms_woocommerce_otp.lang.error + ` : </span>` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
					
                    $(".otp-mobile-verification").html(`
<input class="gwebotpcodeone" style="display: inline-block; width: 20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codeone" autocomplete="one-time-code"/>
<input class="gwebotpcodeone" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1" name="wp_sms_otp_codetwo" autocomplete="one-time-code"/>
<input class="gwebotpcodeone" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codethree" autocomplete="one-time-code"/>
<input class="gwebotpcodeone" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codefour" autocomplete="one-time-code"/>


                    <input type="hidden" name="wp_sms_otp_number" value="` + wp_sms_otp_number + `"/>
 <input type="hidden" name="gwebreqverifyc" value="` + gwebreqverifyc + `"/>
                    <input type="submit" style="font-size: 19px;padding: 10px;height: 46px;width: 130px; margin-top:10px;" name="otp-submit-code" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button" value="${wp_sms_woocommerce_otp.lang.submit_code}"/>
                    `);
					
					const otpInputsone = document.querySelectorAll('.gwebotpcodeone');				
otpInputsone.forEach((input, index) => {
  input.addEventListener('input', (e) => {
    const value = e.target.value;
    if (isNaN(value)) {
      input.value = ''; 
      return;
    }

    if (value.length === 1) {
      if (index < otpInputsone.length - 1) {
        otpInputsone[index + 1].focus(); 
      }
    }
  });

  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const pastedValue = (e.clipboardData || window.clipboardData).getData('text');
    if (/^\d{4}$/.test(pastedValue)) {
      for (let i = 0; i < otpInputsone.length; i++) {
        otpInputsone[i].value = pastedValue[i] || '';
      }
    }
  });	
	
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace') {
      if (input.value.length === 0 && index > 0) {
        otpInputsone[index - 1].focus(); 
      }
    }
  });
});
					
                    $(".otp-mobile-verification").append(`
</br>
</br>
</br>

<a style="border: 1px solid #720eec;float: left; margin: 5px; padding: 5px; margin-left: 15px;" href="#" class="${wp_sms_woocommerce_otp.lang.submit_code} woocommerce-Button ${wp_sms_woocommerce_otp.gwebbutton}" name="otp-change-number">🛠 Change Number</a>
<a style="border: 1px solid #720eec; float: right; margin: 5px; padding: 5px; margin-right: 15px; " href="#" class="${wp_sms_woocommerce_otp.lang.submit_code} woocommerce-Button ${wp_sms_woocommerce_otp.gwebbutton}" name="otp-retry-number">🔄 Resend OTP</a>` );
				} else if (data.error === "greenwebsms-verified") {
					const verified = $("input[name=wps_user_verified]");
        const wp_sms_otp_number = $("input[name=wp_sms_otp_number]").val();
        const mobile_number = $("input[name=wps_user_mobile_number]");
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
					$("[data-otp-alert]").html(`<div class="alert-box success">` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
					mobile.val(wp_sms_otp_number);
                    mobile.attr('readonly', true);
                    verified.val('1');
                    mobile_number.val(wp_sms_otp_number);
					       $('.woocommerce-error li').hide()
var modelnocheck = document.getElementById("greenweb-otp-model-check");
if(modelnocheck) {
    dialog.close();
} else {
	dialognew.close();
}		
					
$(".loading_otp").hide();
                   $("#place_order").click();
					
					
				} else {
                    $("[data-otp-alert]").html(`<div class="alert-box success">` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
					
                    $(".otp-mobile-verification").html(`
<input class="gwebotpcodetwo" style="display: inline-block; width: 20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codeone" autocomplete="one-time-code"/>
<input class="gwebotpcodetwo" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1" name="wp_sms_otp_codetwo" autocomplete="one-time-code"/>
<input class="gwebotpcodetwo" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codethree" autocomplete="one-time-code"/>
<input class="gwebotpcodetwo" style="display: inline-block; width:  20%; font-size: 30px; padding: 3px; vertical-align: middle; text-align: center; border-top: none; border-left: none; border-right: none; border-bottom: 3px dashed #5d605b; border-style: dashed;" type="text" inputmode="numeric" pattern="[+,0-9]*" maxlength="1"  name="wp_sms_otp_codefour" autocomplete="one-time-code"/>

                    <input type="hidden" name="wp_sms_otp_number" value="` + wp_sms_otp_number + `"/>
<input type="hidden" name="gwebreqverifyc" value="` + gwebreqverifyc + `"/>
                    <input style="font-size: 19px;padding: 10px;height: 46px;width: 130px; margin-top:10px;" type="submit" name="otp-submit-code" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button" value="${wp_sms_woocommerce_otp.lang.submit_code}"/>
                    `);
					
		const otpInputstwo = document.querySelectorAll('.gwebotpcodetwo');				
otpInputstwo.forEach((input, index) => {
  input.addEventListener('input', (e) => {
    const value = e.target.value;
    if (isNaN(value)) {
      input.value = ''; 
      return;
    }

    if (value.length === 1) {
      if (index < otpInputstwo.length - 1) {
        otpInputstwo[index + 1].focus(); 
      }
    }
  });
  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const pastedValue = (e.clipboardData || window.clipboardData).getData('text');
    if (/^\d{4}$/.test(pastedValue)) {
      for (let i = 0; i < otpInputstwo.length; i++) {
        otpInputstwo[i].value = pastedValue[i] || '';
      }
    }
  });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace') {
      if (input.value.length === 0 && index > 0) {
        otpInputstwo[index - 1].focus(); 
      }
    }
  });
});				
					
                    $(".otp-mobile-verification").append(`
</br>   
</br> 
</br> 

<a  style="border: 1px solid #720eec;float: left; margin: 5px; padding: 5px; margin-left: 15px;" href="#" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button " name="otp-change-number">🛠 Change Number</a>
<a style="border: 1px solid #720eec; float: right; margin: 5px; padding: 5px; margin-right: 15px; " href="#" class="${wp_sms_woocommerce_otp.gwebbutton} woocommerce-Button" name="otp-retry-number">🔄 Resend OTP</a>`);
                }
                $(".loading_otp").hide();
                  $("#greenwebsmsotpbox").show();
            },
            error: function (data) {
                alert(wp_sms_woocommerce_otp.lang.ajax_error);
            }
        });
    });

    /**
     * Check OTP Code step 2
     */
    $(document).on("click", "input[name=otp-submit-code]", function (e) {
        e.preventDefault();

        /* Show Loading */
        $(".loading_otp").show();
        $("[data-otp-alert]").html("");

        const verified = $("input[name=wps_user_verified]");
        const wp_sms_otp_number = $("input[name=wp_sms_otp_number]").val();
        const mobile_number = $("input[name=wps_user_mobile_number]");
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();
        $.ajax({
            url: wp_sms_woocommerce_otp.ajax,
            type: 'GET',
            dataType: "json",
            data: {
                action: 'wp_sms_woocommerce_otp',
                step: 2,
                wp_sms_otp_number: wp_sms_otp_number,
                wp_sms_otp_codeone: $("input[name=wp_sms_otp_codeone]").val(),
				wp_sms_otp_codetwo: $("input[name=wp_sms_otp_codetwo]").val(),
				wp_sms_otp_codethree: $("input[name=wp_sms_otp_codethree]").val(),
				wp_sms_otp_codefour: $("input[name=wp_sms_otp_codefour]").val(),
				gwebreqverifyc: gwebreqverifyc,
            },
            success: function (data) {
                if (data.error === "yes") {
                    $("[data-otp-alert]").html(`<div class="alert-box error"><span>` + wp_sms_woocommerce_otp.lang.error + ` : </span>` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
                    $(".loading_otp").hide();
                } else {
                    $("[data-otp-alert]").html(`<div class="alert-box success">` + data.text + `</div>`);
                    $("[data-otp-alert]").show();

                    mobile.val(wp_sms_otp_number);
                    mobile.attr('readonly', true);
                    verified.val('1');
                    mobile_number.val(wp_sms_otp_number);

                    $('.woocommerce-error li').hide()
var modelnocheck = document.getElementById("greenweb-otp-model-check");

if(modelnocheck) {
    dialog.close();
} else {
	dialognew.close();
}		
					
$(".loading_otp").hide();
                   $("#place_order").click();
					
					
					
                }
                
            },
            error: function () {
                alert(wp_sms_woocommerce_otp.ajax_error);
            }
        });
    });

    /**
     * Retry step 3 - Only resend the SMS
     */
    $(document).on("click", "a[name=otp-retry-number]", function (e) {
        e.preventDefault();

        /* Show Loading */
        $(".loading_otp").show();
        $("[data-otp-alert]").html("");

        const wp_sms_otp_number = $("input[name=wp_sms_otp_number]").val();
const gwebreqverifyc = $("input[name=gwebreqverifyc]").val();

        $.ajax({
            url: wp_sms_woocommerce_otp.ajax,
            type: 'GET',
            dataType: "json",
            data: {
                action: 'wp_sms_woocommerce_otp',
                step: 3,
                wp_sms_otp_number: wp_sms_otp_number,
				gwebreqverifyc: gwebreqverifyc,
            },
            success: function (data) {
                if (data.error === "yes") {
                    $("[data-otp-alert]").html(`<div class="alert-box error"><span>` + wp_sms_woocommerce_otp.lang.error + ` : </span>` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
                } else {
                    $("[data-otp-alert]").html(`<div class="alert-box success">` + data.text + `</div>`);
                    $("[data-otp-alert]").show();
                }
                $(".loading_otp").hide();
                 
            },
            error: function () {
                alert(wp_sms_woocommerce_otp.ajax_error);
            }
        });
    });


});