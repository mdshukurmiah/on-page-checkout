<div class="g-web-reg-phinput-cont <?php echo esc_attr( implode( ' ', $cont_class ) ); ?>">

	<?php if( $label ): ?>
		<label class="<?php echo esc_attr( implode( ' ', $label_class ) ); ?>" for="g-web-reg-phone"> <?php echo $label; ?><?php if( $show_phone === 'required' ): ?>&nbsp;<span class="required">*</span><?php endif; ?></label>
	<?php endif; ?>

	<div class="g-web-reg-has-cc">

		
				<select class="g-web-phone-cc g-web-reg-phone-cc-select <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" name="g-web-reg-phone-cc" id="g-web-reg-phone-cc">
					
					
						<option value="+88" selected>+88</option>
				</select>
		

		<div class="g-web-regphin">
			<input placeholder="017xxxxxxxx" type="text" class="g-web-phone-input g-web-reg-phone <?php echo esc_attr( implode( ' ', $input_class ) ); ?>" name="g-web-reg-phone" id="g-web-reg-phone" autocomplete="tel" value="<?php echo $default_phone; ?>" <?php echo $show_phone === 'required' ? 'required' : ''; ?>/>
			
		</div>
		<?php
		$registration_options = get_option('g-web-phone-options');
		if (isset($registration_options['r-disable-emailf'] ) && ($registration_options['r-disable-emailf'] == "yes")) {
function fixDomainName($url='')
{
    $strToLower = strtolower(trim($url));
    $httpPregReplace = preg_replace('/^http:\/\//i', '', $strToLower);
    $httpsPregReplace = preg_replace('/^https:\/\//i', '', $httpPregReplace);
    $wwwPregReplace = preg_replace('/^www\./i', '', $httpsPregReplace);
    $explodeToArray = explode('/', $wwwPregReplace);
    $finalDomainName = trim($explodeToArray[0]);
    return $finalDomainName;
}

   
            
$siteaddress = get_bloginfo('url');
$siteaddress = fixDomainName(parse_url($siteaddress, PHP_URL_HOST));

         if ($siteaddress == "") {
		$urlparts = wp_parse_url(home_url());
$siteaddress = fixDomainName($urlparts['host']);
         }

 if ($siteaddress == "") {
if (isset($_SERVER['HTTP_HOST'])) {
$siteaddress = $_SERVER['HTTP_HOST'];
}
$siteaddress = fixDomainName($siteaddress);      
         }

 if ($siteaddress == "") {
  $siteaddress = "example.com";   
 }
		?>
	<script type="module">
    jQuery('input#g-web-reg-phone').on('mouseout focusout',function(){ 
    var mobilenumber = jQuery('#g-web-reg-phone').val();
    
    if (mobilenumber.startsWith("+88")) {
mobilenumber = mobilenumber.slice(3);
document.getElementById("g-web-reg-phone").value = mobilenumber;
}

if (mobilenumber.startsWith("88")) {
mobilenumber = mobilenumber.slice(2);
document.getElementById("g-web-reg-phone").value = mobilenumber;
}

if (mobilenumber.startsWith("1")) {
mobilenumber = "0"+mobilenumber;
document.getElementById("g-web-reg-phone").value = mobilenumber;
}

if (mobilenumber.includes('-')) {
  mobilenumber = mobilenumber.replace(/[^0-9]/g, "");  
  document.getElementById("g-web-reg-phone").value = mobilenumber;
}


    });

    jQuery('input#g-web-reg-phone').on('change paste keyup mouseout input',function(){
       	    var mobilenumber = jQuery('#g-web-reg-phone').val();
                        
            
   if (mobilenumber)  {
        document.getElementById("reg_email").value = mobilenumber+"@<?php	echo $siteaddress; ?>";
    } else {
       	    document.getElementById("reg_email").value = Date.now()+"gw"+Math.floor(Math.random() * 1000000)+"@<?php	echo $siteaddress ?? 'example.com'; ?>";
    }
            
      //  $('#total').text(costs);
       	});
	
	document.getElementById("reg_email").style.display = "none";
var divs = document.querySelectorAll('form p,form label[for=reg_email]');
for (let x = 0; x < divs.length; x++) {
    const div = divs[x];
    const content = div.textContent.trim();
  
    if (content == 'A link to set a new password will be sent to your email address.' || content == 'Email address *') {
        div.style.display = 'none';
		 div.style.opacity = '0';
    }
}
		</script>	
	<style>
	.reg_email{
	display:none !important;	
	}
	label[for="reg_email"]
{
    display:none !important;
}
	</style>
		<?php } ?>
		

		<input type="hidden" name="g-web-form-token" value="<?php echo $form_token; ?>">
		
<?php
if (!isset($randomcsrf)) {
$randomcsrf = substr(md5(rand()), 0, 7);
			delete_expired_transients();
$tv = "ok";
if ((isset($registration_options['gweb-otp-session-timeout'])) AND (is_numeric($registration_options['gweb-otp-session-timeout'])) AND ($registration_options['gweb-otp-session-timeout'] > 0))	{
set_transient($randomcsrf, $tv, $registration_options['gweb-otp-session-timeout']);	
} else {
set_transient($randomcsrf, $tv, 900);
}
		
		}
		?>

		<input type="hidden" name="g-web-csrf" value="<?php echo $randomcsrf; ?>">		
		<input type="hidden" name="g-web-form-type" value="<?php echo $form_type; ?>">

	</div>

</div>