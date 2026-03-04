<?php

if( !function_exists( 'xoo_framework_includes' ) ){

	if( !defined( 'XOO_FW_DIR' ) ){
		define( 'XOO_FW_DIR' , __DIR__ );
	}

	function xoo_framework_includes(){
		require_once __DIR__.'/class-xoo-helper.php';
		require_once __DIR__.'/class-xoo-exception.php';
	}

	xoo_framework_includes();

}

if( !function_exists( 'xoo_elext' ) ){
	function xoo_elext(){

	$defaults = wp_kses_allowed_html( 'post' );

	$allowed = array(
		'input' 		=> array(
			'class' 		=> array(),
			'name' 			=> array(),
			'placeholder' 	=> array(),
			'type' 			=> array(),
			'id' 			=> array(),
			'value' 		=> array(),
			'disabled' 		=> array(),
			'minlength' 	=> array(),
			'maxlength' 	=> array(),
			'checked' 		=> array(),
			'min' 			=> array(),
			'max' 			=> array()
		),
		'select' 		=> array(
			'class' 		=> array(),
			'name' 			=> array(),
			'type' 			=> array(),
			'id' 			=> array(),
			'disabled' 		=> array(),
		),
		'option' => array(
			'value' 	=> array(),
			'selected' 	=> array(),
		),
	);

	return array_merge( $defaults, $allowed );

}
}