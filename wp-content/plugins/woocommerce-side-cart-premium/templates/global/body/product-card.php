<?php
/**
 * Product
 *
 * This template can be overridden by copying it to yourtheme/templates/side-cart-woocommerce/global/body/product-card.php.
 *
 * HOWEVER, on occasion we will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen.
 * @see     https://docs.xootix.com/side-cart-woocommerce/
 * @version 4.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$productClasses = apply_filters( 'xoo_wsc_product_class', $productClasses );

$visible 		= xoo_wsc_helper()->get_style_option('scbp-card-visible');
$details 		= xoo_wsc_helper()->get_style_option('scbp-card-back'); 
$hasBack 		= $visible !== 'all_on_front' && !empty( $details );
$allFront 		= $visible === 'all_on_front';

$delHTML = $qtyHTML = $totalHTML = $nameHTML = $metaHTML = $imageHTML = $priceHTML = '';

$imageHTML 		= $showPimage ? $thumbnail : '';
$nameHTML 		= $showPname ? sprintf( '<span class="xoo-wsc-pname">%1$s</span>', $product_name ) : '';
$totalHTML 		= $showPtotal ? sprintf( '<span class="xoo-wsc-card-ptotal">%1$s</span>', $product_subtotal ) : '';
$metaHTML 		= $showPmeta ? $product_meta : '';
$viewLinkHTML 	= sprintf( '<a class="xoo-wsc-smr-link" href="%1$s">%2$s</a>', $product_permalink, '<i class="xoo-wsc-icon-external-link"></i>'. __( 'View', 'side-cart-woocommerce' ) );
$priceHTML 		= $showPprice && $updateQty ? sprintf( '<span class="xoo-wsc-card-price">%1$s</span>', __( 'Price: ', 'side-cart-woocommerce' ) . $product_price ) : '';

?>


<?php ob_start(); //Delete HTML ?>

<?php if( $showPdel ): ?>

	<?php if( $deleteType === 'icon' ): ?>
		<span class="xoo-wsc-smr-del <?php echo $delete_icon ?>"></span>
	<?php else: ?>
		<span class="xoo-wsc-smr-del xoo-wsc-del-txt"><?php echo $deleteText ?></span>
	<?php endif; ?>

<?php endif; ?>

<?php $delHTML = ob_get_clean(); ?>


<?php ob_start(); // Quantity & Price HTML ?>

<div class="xoo-wsc-qty-price">

	<?php if( $updateQty ): ?>

		<?php

		xoo_wsc_quantity_input(
			array(
				'input_value'  	=> $cart_item['quantity'],
				'quantity'  	=> $cart_item['quantity'],
				'max_value'    	=> $_product->get_max_purchase_quantity(),
				'min_value'    	=> '0',
				'product_name' 	=> $_product->get_name(),
			),
			$_product
		);

		?>

		<?php echo $totalHTML ?>
		
	<?php else: ?>

		<?php if( $showPprice ): ?>
			<span><?php echo $cart_item['quantity']; ?></span> X <span><?php echo $product_price; ?></span>
			<?php if( $showPtotal ): ?>
				<span> = <?php echo $product_subtotal ?></span>
			<?php endif; ?>

		<?php else: ?>
			<span><?php _e( 'Qty:', 'side-cart-woocommerce' ) ?></span> <span><?php echo $cart_item['quantity']; ?></span>
		<?php endif; ?>
	<?php endif; ?>

</div>

<?php $qtyHTML = ob_get_clean(); ?>


<div data-key="<?php echo $cart_item_key ?>" data-product_id="<?php echo $product_id ?>" class="<?php echo implode( ' ', $productClasses ) ?>">

	<?php do_action( 'xoo_wsc_product_start', $_product, $cart_item_key ); ?>

	<div class="xoo-wsc-card-cont">

		<?php echo $delHTML ?>

		<div class="xoo-wsc-img-col magictime">

			<?php echo $imageHTML ?>

			<?php do_action( 'xoo_wsc_product_image_col', $_product, $cart_item_key ); ?>

		</div>


		<?php if( $hasBack ): ?>

		<div class="xoo-wsc-sm-back-cont">

			<div class="xoo-wsc-sm-back">

				<?php echo in_array( 'name', $details ) ? $nameHTML : '' ?>
				<?php echo in_array( 'meta', $details ) ? $metaHTML : '' ?>
				<?php echo in_array( 'total', $details ) && !$updateQty ? $totalHTML : '' ?>
				<?php echo in_array( 'link', $details ) ? $viewLinkHTML : '' ?>
				<?php echo in_array( 'price', $details ) ? $priceHTML : '' ?>
				<?php echo in_array( 'qty', $details ) ? $qtyHTML : '' ?>

				<?php do_action( 'xoo_wsc_product_card_back', $_product, $cart_item_key ); ?>
			</div>

		</div>

		<?php endif; ?>
		
	</div>


	<div class="xoo-wsc-sm-front">

		<span class="xoo-wsc-sm-emp"></span>

		<?php echo $allFront || !in_array( 'name', $details ) ? $nameHTML : '' ?>
		<?php echo $allFront || !in_array( 'price', $details ) ? $priceHTML : '' ?>
		<?php echo $allFront || !in_array( 'meta', $details ) ? $metaHTML : '' ?>
		<?php echo $allFront || !in_array( 'qty', $details ) ? $qtyHTML : '' ?>
		<?php echo ( $allFront || !in_array( 'total', $details ) ) && !$updateQty ? $totalHTML : '' ?>

		<?php do_action( 'xoo_wsc_product_card_front', $_product, $cart_item_key ); ?>
		
	</div>



	<?php do_action( 'xoo_wsc_product_end', $_product, $cart_item_key ); ?>

</div>