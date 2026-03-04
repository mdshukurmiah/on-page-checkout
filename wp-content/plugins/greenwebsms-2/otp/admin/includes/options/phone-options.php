<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}


	$phone_codes['BD'] = "+880 (BD)";


$option_name = 'g-web-phone-options';

$settings = array(

	
	array(
		'type' 			=> 'section',
		'callback' 		=> 'section',
		'id' 			=> 'otp-section',
		'title' 		=> 'Woocommerce Customers OTP login/registration Settings',
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'otp-digits',
		'title' 		=> 'OTP Digits',
		'default' 		=> '4',
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'otp-expiry',
		'title' 		=> 'OTP Expiry',
		'default' 		=> '120',
		'desc' 			=> 'In Seconds'
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'otp-resend-limit',
		'title' 		=> 'Resend OTP Limit',
		'default' 		=> '8',
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'otp-resend-wait',
		'title' 		=> 'Resend OTP Wait Time',
		'default' 		=> '30',
		'desc'			=> 'Waiting time to resend a new OTP (In seconds) '
	),
    
	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'gweb-otp-session-timeout',
		'title' 		=> 'Login Page OTP session time (in seconds)',
		'default' 		=> '600',
		'desc'			=> 'Set it too low to prevent OTP abuse and high enough to avoid normal users session time out'
	),    
	
	array(
		'type' 			=> 'setting',
		'callback' 		=> 'number',
		'section' 		=> 'otp-section',
		'option_name' 	=> $option_name,
		'id'			=> 'otp-incorrect-limit',
		'title' 		=> 'Maximum OTP Request Limit Per IP Or Mobile',
		'default' 		=> '10',
		'desc' 			=> 'একদিনে একটি IP or Mobile নাম্বার থেকে সর্বোচ্চ কতবার OTP রিকোয়েস্ট দেওয়া যাবে তা সেট করুন, অনেক বেশি দিলে কেউ বারবার OTP রিকোয়েস্ট দিয়ে Abuse করতে পারবে, অনেক কম দিলে যারা বারবার লগিন/লগআউট করে তাদের সমস্যা হতে পারে । Normally একজন User একদিনে ১০ বারের বেশি লগিন/লগআউট করে না, এজন্য Default ভাবে ১০ দেওয়া আছে ।',
	),
	
	array(
		'type' 			=> 'section',
		'callback' 		=> 'section',
		'id' 			=> 'reg-section',
		'title' 		=> 'Configure Woocommerce Customers Registration Using Mobile (OTP)',
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'r-enable-phone',
		'title' 		=> 'Enable Phone Verification',
		'default' 		=> 'yes',
		'desc' 			=> ''
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'select',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id'			=> 'r-show-country-code-as',
		'title' 		=> 'Display Country Code Field as',
		'default' 		=> 'selectbox',
		'desc' 			=> 'A valid phone number needs a country code. If disabled, the default one selected below is set as country code.',
		'extra'			=> array(
			'options' => array(
				'selectbox' => 'Select Box',
			)	
		)
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'select',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id'			=> 'r-default-country-code-type',
		'title' 		=> 'Country',
		'default' 		=> 'Bangladesh',
		'extra'			=> array(
			'options' => array(
				'custom'   		=> 'Bangladesh',
			)	
		)
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'select',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id'			=> 'r-default-country-code',
		'title' 		=> 'Country Code',
		'default' 		=> 'BD',
		'extra'			=> array(
			'options' => $phone_codes
		)
	),




	array(
		'type' 			=> 'setting',
		'callback' 		=> 'select',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'r-phone-field',
		'title' 		=> 'Phone Field',
		'default' 		=> 'required',
		'extra'			=> array(
			'options' => array(
								'required' 			=> 'Required',
			)
		)
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'textarea',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'r-sms-txt',
		'title' 		=> 'SMS Text',
		'desc' 			=> 'Shortcodes: [otp], Note: SMS Body শুরুতে অবশ্যই ব্রাকেট দিয়ে কোম্পানীর নাম/সাইট এড্রেস/ব্রান্ড নেম দিবেন, অনথ্যায় ওটিপি এসএমএস যাবে না। যেমনে, (Company Name) অথবা (কোম্পানীসাইট.কম)',
		'default' 		=> __("(Company Name)
আপনার ওটিপি: [otp]
@domain.com, #[otp]",'mobile-login-woocommerce')
	),
	
	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'r-disable-emailf',
		'title' 		=> 'Hide Email Field From Registration Page',
		'default' 		=> 'no',
		'desc' 			=> 'যারা রেজিস্ট্রেশন পেজ হতে ইমেইল বাতিল করে শুধু মোবাইল OTP দিয়ে রেজিস্ট্রেশন করার পদ্ধতি ব্যবহার করতে চান তারা এই অপশন Enable করবেন । ইউজার ইমেইল ইনপুট করা ছাড়াই রেজিস্ট্রেশন করতে পারবে । '
	),
	
	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'reg-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'r-auto-submit',
		'title' 		=> 'Auto submit form',
		'desc' 			=> 'Auto submit registration form on otp verification.',
		'default' 		=> 'yes'
	),

	array(
		'type' 			=> 'section',
		'callback' 		=> 'section',
		'id' 			=> 'login-section',
		'title' 		=> 'Configure Woocommerce Customers Login System Via Mobile (OTP)',
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'login-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'l-enable-login-with-otp',
		'title' 		=> 'Enable Login with OTP',
		'default' 		=> 'yes',
		'desc' 			=> ''
	),


	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'login-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'l-login-display',
		'title' 		=> 'Display OTP login form first',
		'default' 		=> 'yes',
		'desc' 			=> ''
	),
	
	array(
		'type' 			=> 'setting',
		'callback' 		=> 'checkbox',
		'section' 		=> 'login-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'l-disble-emailf',
		'title' 		=> 'Remove Login with EMail Button',
		'default' 		=> 'no',
		'desc' 			=> 'এই অপশনটি Enable করলে আপনার লগিন পেজ হতে Login With Email বাটনটি হাইড হবে । ফলে শুধুমাত্র মোবাইল নাম্বার দিয়ে লগিন করা যাবে । তবে যে সকল ব্যবহারকারীর অ্যাকাউন্ট পূর্বে ইমেইল ব্যবহার করে করা হয়েছে  এবং মোবাইল নাম্বার  ADD করা নেই তাদের নতুন করে রেজিস্ট্রেশন/প্রোফাইল Edit করে মোবাইল নাম্বার যুক্ত করে নিতে হবে ।'
	),

	array(
		'type' 			=> 'setting',
		'callback' 		=> 'text',
		'section' 		=> 'login-section',
		'option_name' 	=> $option_name,
		'id' 			=> 'l-redirect',
		'title' 		=> 'Login with OTP Redirect',
		'desc' 			=> 'Leave empty to redirect on the same page',
		'default' 		=> '',
	),

);

return $settings;

?>