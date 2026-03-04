<?php

$delHTML = $qtyHTML = $totalHTML = $nameHTML = $metaHTML = $imageHTML = $priceHTML = '';
$product_permalink = '';

$viewLinkHTML 	= sprintf( '<a class="xoo-wsc-smr-link" href="%1$s">%2$s</a>', $product_permalink, '<i class="xoo-wsc-icon-external-link"></i>'. __( 'View', 'side-cart-woocommerce' ) );


?>

<?php ob_start(); ?>
<# if( data.product.showPImage ){ #>
	<?php echo $product_thumbnail ?>
<# } #>
<?php $imageHTML = ob_get_clean(); ?>


<?php ob_start(); ?>
<# if( data.product.showPmeta ){ #>
	<?php echo $product_meta ?>
<# } #>
<?php $metaHTML = ob_get_clean(); ?>

<?php ob_start(); ?>
<# if( data.product.showPname ){ #>
	 <?php echo sprintf( '<span class="xoo-wsc-pname">%1$s</span>', $product_name ) ?>
<# } #>
<?php $nameHTML = ob_get_clean(); ?>



<?php ob_start(); ?>
<# if(  data.product.showPtotal ){ #>
	<?php echo sprintf( '<span class="xoo-wsc-card-ptotal">%1$s</span>', $product_subtotal ) ?> 
<# } #>
<?php $totalHTML = ob_get_clean(); ?>



<?php ob_start(); ?>
<# if( data.product.showPprice && data.product.updateQty ){ #>
	<?php echo sprintf( '<span class="xoo-wsc-card-price">%1$s</span>', __( 'Price: ', 'side-cart-woocommerce' ) . $product_price ) ?> 
<# } #>
<?php $priceHTML = ob_get_clean(); ?>



<?php ob_start(); //Delete HTML ?>


<# if ( data.product.showPdel ) { #>

	<#  if( data.product.deleteType === 'icon' ){ #>
		<span class="xoo-wsc-smr-del {{{data.product.deleteIcon}}}"></span>
	<# }else{ #>
		<span class="xoo-wsc-smr-del xoo-wsc-del-txt">{{{data.product.deleteText}}}</span>
	<# } #>

<# } #>


<?php $delHTML = ob_get_clean(); ?>


<?php ob_start(); // Quantity & Price HTML ?>

<div class="xoo-wsc-qty-price">

	<# if ( data.product.updateQty ){ #>

		<div class="xoo-wsc-qty-box xoo-wsc-qtb-{{{data.card.qtyDesign}}}">

			<span class="xoo-wsc-minus xoo-wsc-chng">-</span>

			<input type="number" class="xoo-wsc-qty" value="<?php echo esc_attr( $product_quantity ); ?>" />

			<span class="xoo-wsc-plus xoo-wsc-chng">+</span>

		</div>

		<?php echo $totalHTML ?>
		
	<# }else{ #>

		<# if( data.product.showPprice ){ #>

			<span><?php echo $product_quantity; ?></span> X <span><?php echo $product_price; ?></span>

			<# if ( data.product.showPtotal ) { #>
				<span> = <?php echo $product_subtotal ?></span>
			<# } #>

		<# }else{ #>

			<span><?php _e( 'Qty:', 'side-cart-woocommerce' ) ?></span> <span><?php echo $product_quantity; ?></span>

		<# } #>

	<# } #>

</div>

<?php $qtyHTML = ob_get_clean(); ?>


<div class="xoo-wsc-product">

	<div class="xoo-wsc-card-cont">

		<?php echo $delHTML ?>

		<div class="xoo-wsc-img-col magictime">

			<?php echo $imageHTML ?>

		</div>


		<# if( data.card.hasBack ){ #>

		<div class="xoo-wsc-sm-back-cont">

			<div class="xoo-wsc-sm-back">

				<# if( _.contains( data.card.details, 'name' ) ) { #> <?php echo $nameHTML ?> <# } #>
				<# if( _.contains( data.card.details, 'meta' ) ) { #> <?php echo $metaHTML ?> <# } #>
				<# if( _.contains( data.card.details, 'total' ) ) { #> <?php echo $totalHTML ?> <# } #>
				<# if( _.contains( data.card.details, 'link' ) ) { #> <?php echo $viewLinkHTML ?> <# } #>
				<# if( _.contains( data.card.details, 'price' ) ) { #> <?php echo $priceHTML ?> <# } #>
				<# if( _.contains( data.card.details, 'qty' ) ) { #> <?php echo $qtyHTML ?> <# } #>
				

			</div>

		</div>

		<# } #>
		
	</div>


	<div class="xoo-wsc-sm-front">

		<span class="xoo-wsc-sm-emp"></span>

		<# if( data.card.allFront || !_.contains( data.card.details, 'name' ) ){ #> <?php echo $nameHTML ?> <# } #>
		<# if( data.card.allFront || !_.contains( data.card.details, 'price' ) ){ #> <?php echo $priceHTML ?> <# } #>
		<# if( data.card.allFront || !_.contains( data.card.details, 'meta' ) ){ #> <?php echo $metaHTML ?> <# } #>
		<# if( data.card.allFront || !_.contains( data.card.details, 'qty' ) ){ #> <?php echo $qtyHTML ?> <# } #>
		<# if( ( data.card.allFront || !_.contains( 'total' ) ) && !data.product.updateQty ){ #> <?php echo $totalHTML ?> <# } #>
		
	</div>

</div>