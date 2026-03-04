<div class="xoo-wsc-product">

	<div class="xoo-wsc-img-col">

		<# if ( data.product.showPImage ) { #>
			<?php echo $product_thumbnail; ?>
		<# } #>

		<# if ( data.product.showPdel && "image" === data.product.deletePosition ) { #>

			<# if ( "icon" === data.product.deleteType ) { #>
				<span class="xoo-wsc-smr-del {{data.product.deleteIcon}}"></span>
			<# }else{ #>
				<span class="xoo-wsc-smr-del xoo-wsc-del-txt">{{data.product.deleteText}}</span>
			<# } #>

		<# } #>

	</div>



	<div class="xoo-wsc-sum-col">

		<# if( data.product.showSalesCount && <?php echo $sales_count > 0  ?>){ #>
			<div class="xoo-wsc-sm-sales">
				<?php echo $sales_count.'+ ' ?><?php _e('shoppers have bought this','side-cart-woocommerce'); ?>
			</div>
		<# } #>

		<div class="xoo-wsc-sm-info">

			<div class="xoo-wsc-sm-left">

				<# if ( data.product.showPname ) { #>
					<span class="xoo-wsc-pname"><?php echo $product_name; ?></span>
				<# } #>
				
				<# if ( data.product.showPmeta ) { #>
					<?php echo $product_meta ?>
				<# } #>

				<# if ( data.product.showPprice && ( "separate" === data.product.qtyPriceDisplay || data.product.updateQty ) ) { #>
					<div class="xoo-wsc-pprice">
						<?php echo __( 'Price: ', 'side-cart-woocommerce' ) ?>
						<# if( data.product.priceType === "actual" ){ #>
							<?php echo $product_price; ?>
						<# }else{ #>
							<?php echo $product_sale_price ?>
						<# } #>
					</div>
				<# } #>

				<!-- Quantity -->

				<# if ( !data.product.updateQty ) { #>

					<div class="xoo-wsc-qty-price">

						<# if ( data.product.showPprice && "one_liner" === data.product.qtyPriceDisplay ) { #>

							<span><?php echo $product_quantity; ?></span> X 
						<span>
							<# if( data.product.priceType === "actual" ){ #>
								<?php echo $product_price; ?>
							<# }else{ #>
								<?php echo $product_sale_price ?>
							<# } #>
							
						</span>

							<# if ( data.product.showPtotal ) { #>
								<span> = <?php echo $product_subtotal ?></span>
							<# } #>

						<# }else{ #>

							<span><?php _e( 'Qty:', 'side-cart-woocommerce' ) ?></span> <span><?php echo $product_quantity; ?></span>

						<# } #>


					</div>

				<# }else{ #>

			

					<div class="xoo-wsc-qty-box xoo-wsc-qtb-{{{data.product.qtyDesign}}}">

						<span class="xoo-wsc-minus xoo-wsc-chng">-</span>

						<input type="number" class="xoo-wsc-qty" value="<?php echo esc_attr( $product_quantity ); ?>" />

						<span class="xoo-wsc-plus xoo-wsc-chng">+</span>

					</div>

				<# } #>

			</div>

			<!-- End Quantity -->


			<div class="xoo-wsc-sm-right">

				<# if ( data.product.showPdel && "default" === data.product.deletePosition ) { #>

					<# if ( "icon" === data.product.deleteType  ) { #>
						<span class="xoo-wsc-smr-del {{data.product.deleteIcon}}"></span>
					<# }else{ #>
						<span class="xoo-wsc-smr-del xoo-wsc-del-txt">{{data.product.deleteText}}</span>
					<# } #>

				<# } #>

				<# if ( data.product.showPtotal && ( data.product.updateQty || "separate" === data.product.qtyPriceDisplay ) ) { #>
					<span class="xoo-wsc-smr-ptotal"><?php echo $product_subtotal ?></span>
				<# } #>


			</div>

		</div>

	</div>

</div>