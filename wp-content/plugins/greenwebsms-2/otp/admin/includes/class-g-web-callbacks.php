<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class G_Web_Callbacks {

	public function __construct(){
		//Stop calling parent constructor
	}

	public function simplify_args( $args ){

		if( !isset( $args['id'] ) || ( !isset( $args['option_name'] )  && $args['type'] === 'setting' )  ){
			return;
		}

		$data = array(); //Data to return from this function

		$value = get_option($args['option_name']);


		if( is_array( $value ) ){
			$value = isset( $value[$args['id']] ) ? $value[$args['id']] : ( isset( $args['default'] ) ? $args['default'] : null );
		}

		//Check for extra arguments
		if( isset( $args['extra'] ) ){

			//If options
			if( isset( $args['extra']['options'] ) ){
				$data['options'] = $args['extra']['options'];
			}

		}

		$description = isset( $args['desc'] ) ? esc_attr( $args['desc'] ) : null;

		//Merging all data
		$data = array_merge($data,
			array(
				'id' 			=> $args['option_name'].'['.$args['id'].']',
				'value' 		=> $value,
				'description' 	=> $description, 
			)
		);

		return $data;

	}

	public function section($args){
		extract( $args );
		?>
		<span class="section-title"><?php echo esc_attr($title); ?></span>
		<?php
	}

	public function checkbox( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="no">
		<input type="checkbox" class="g-input-checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="yes" <?php checked($value, "yes"); ?> />
		<?php
		$this->description($description);
	}


	public function color( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<input type="text" class="color-field g-input-text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $value; ?>" />
		<?php
		$this->description($description);
	}


	public function text( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<input type="text" class="g-input-text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $value; ?>" />
		<?php
		$this->description($description);
	}


	public function textarea( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<textarea rows="4" cols="50" class="g-input-text" id="<?php echo $id; ?>" name="<?php echo $id; ?>"><?php echo $value; ?></textarea>
		<?php
		$this->description($description);
	}


	public function number( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<input type="number" class="g-input-number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $value; ?>" />
		<?php
		$this->description($description);
	}

	public function upload( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<a class="button-primary g-upload-icon">Select</a>
		<input type="hidden" id="<?php echo $id; ?>" name="<?php echo $id; ?>" class="g-upload-url" value="<?php echo $value; ?>">
		<a class="button g-remove-media">Remove</a>
		<span class="g-upload-title"></span>
		<p class="description">Supported format: JPEG,PNG </p>
		<?php
	}

	public function select( $args ){
		extract( $this->simplify_args( $args ) );
		?>
		<select name="<?php echo $id; ?>">
			<?php foreach ($options as $option_value => $option_label ): ?>
				<option value="<?php echo $option_value; ?>" <?php selected( $value, $option_value ); ?> > <?php echo $option_label; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		$this->description($description);
	}


	public function description($description){
		if( !isset( $description ) ) return;
		?>
		<p class="description"><?php echo $description; ?></p>
		<?php
	}
}

return new G_Web_Callbacks(); 

?>
