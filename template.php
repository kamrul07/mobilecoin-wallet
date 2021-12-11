<?php get_header(); ?>
		<?php
		// WooCommerce Order ID
		if (isset($_GET['order_id'])) {
			$order_id =  $_GET['order_id'];
		} else {
			$order_id = '';
		}

		$order = wc_get_order( $order_id );				
		$payment_gateway_id = 'mob';
		$payment_gateways   = WC_Payment_Gateways::instance();
		$payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];									
        $amount_cart = (double)$order->get_total();

		if($order) {
			$unique_id = get_post_meta($order_id, '_public_id', true);
		
			if( empty( $unique_id) ) {

				$url = "http://34.71.112.7:8000/api/payment-intents/";
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
				$headers = array(
				   "Authorization: Api-Key " . $payment_gateway->settings['public_key'],
				   "Content-Type: application/json",
				);
				curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
				$data = <<<DATA
				{
					"fiat_amount_currency": "USD",
					"fiat_amount": $amount_cart,
					"description": "MobileCoin Drop",
					"customer_id": "$order_id"
				}
				DATA;

				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

				$resp = curl_exec($curl);
				curl_close($curl);

				$result = json_decode($resp);

				$rqst_code = $result->b58_payment_request;
				$public_id = $result->public_id;
				$rqst_address = $result->b58_public_address;
				$rqst_url = $result->url;
				// Show data
			
		        $total_amount = $result->picomob_amount;
		         
		        
				// Save data as order custom meta field
				$order->update_meta_data( '_request_id', $result->b58_payment_request );
				$order->update_meta_data( '_public_address', $result->b58_public_address );
				$order->update_meta_data( '_mob_amount', $result->picomob_amount );
				$order->update_meta_data( '_public_id', $result->public_id );
				$order->update_meta_data( '_request_url', $result->url );
				$order->save();
	
			} else {	
				$rqst_code = get_post_meta($order_id, '_request_id', true);
				$public_id = get_post_meta($order_id, '_public_id', true);
				$rqst_address = get_post_meta($order_id, '_public_address', true);
				$total_amount = get_post_meta($order_id, '_mob_amount', true);
				$rqst_url = get_post_meta($order_id, '_request_url', true);
			
			}
	
			// Show QR Code
			//echo '';
	
			// Save unique id
			//$order->update_meta_data( '_unique_id', 'mob' . $order_id );
			//$order->save();

			//$public_id = get_post_meta($order_id, '_public_id', true);
			
		?>
		
		<div class="payment_area_wrapper mob_successfull_payment">
		    <div class="payment_details_area">
		       <div class="payment_qr_code_area">
		          <div class="paymnet_qr_inner">
		            <h2>Payment</h2>
		            <div class="payment_info_area">
		               <div class="paymnet_qr_box">
		                  <div class="qrcode"></div>
		                  <div class="qr_code_display">
		                    <p><span>Payment Request Code: </span><span class="code_area_s" ><?php echo $rqst_code; ?> </span></p>
		                    
		                    <p class=""><span>MOB amount : </span> <?php  echo $total_amount; ?></p>
		                    <p class="payment_note">Please use the <strong>"Payment request code"</strong> or scan the <strong>"QR code"</strong> to make payment.You will be autometically redirected to the thank you page when the payment is done in your wallet.</p>
		                  
		                  <div class="success_notice">
		                    <p>Your payement is successfull .This page will be redirected autometically.</p>
		                  </div>
		                  <div class="error_notice">
		                    <p>Your payement is canceled .This page will be redirected autometically.</p>
		                  </div>
		                  </div>
		               </div>
		            </div>
		          </div>
		       </div>
		       
		       
		       <div class="paymemnt_order_area">
		         <div class="payment_orderder_details_inner">
		          <div class="payment_cart_title">
		             <h2>In your cart</h2>
		          </div>
		           
		           <div class="payment_order_items">
		          <?php $currency_symbol = get_woocommerce_currency_symbol( get_woocommerce_currency() ); ?>
		            <p><span class="cart_detail_itam_name">Sub-total</span><span class="cart_detail_itam_amount"><?php echo $currency_symbol.''.$order->get_subtotal(); ?></span></p>
		            <p><span class="cart_detail_itam_name">Estimated Shipping and delivery</span><span class="cart_detail_itam_amount"><?php echo $currency_symbol.''.$order->get_shipping_total(); ?></span></p>
		            <p><span class="cart_detail_itam_name">Estimated TAX </span><span class="cart_detail_itam_amount"><?php echo $currency_symbol.''.$order->get_total_tax() ?></span></p>
		            <p><span class="cart_detail_itam_name"></span><span class="cart_detail_itam_amount"></span></p>
		            <p><span class="cart_detail_itam_name total_name">Total Amount</span><span class="cart_detail_itam_amount"><?php echo $currency_symbol.''.$order->get_total(); ?></span></p>
		           </div>
		           <div class="order_product_items">
		           <?php $order_items = $order->get_items(); ?>
		           <?php foreach($order_items as $order_item): ?>
		           
		            <div class="order_single_product_items">
		              <div class="product_thumbnail">
		                <img class="cart_p_thumb" src="<?php echo get_the_post_thumbnail_url( $order_item->get_product_id() ); ?>" />
		              </div>
		              <div class="cart_p_details">
		                <h4><?php echo $order_item->get_name(); ?></h4>
		                <p class="p_c_qty">QTY: <?php echo $order_item->get_quantity(); ?></p>
		                <p class="p_c_sub">$ <?php echo $order_item->get_total(); ?></p>
		              </div>
		              
		              
		            </div>
		            <?php endforeach; ?>
		           </div>
		         </div>
		       </div>
		    </div>
		</div>

		<input value="<?php echo $rqst_url; ?>" type="hidden" class="request_url" />
		<input value='Api-Key <?php echo $payment_gateway->settings['secret_key']; ?>' type="hidden" class="apikey" />
		<input value='<?php echo $order_id; ?>' type="hidden" class="order_id" />
		<input value='<?php echo get_permalink( 21 )."?order_id=".$order_id; ?>' type="hidden" class="success_url" />
		<input value='<?php echo get_permalink( 1135 )."?order_id=".$order_id; ?>' type="hidden" class="cancel_url" />
		<script>

		</script>

		<?php 
		} else {
			echo '<h3>Invalid Request!</h3>';
		}
		?>
		<script>
			// QR code generator
			var el = kjua({text: '<?php echo $rqst_code; ?>', render: 'svg',});
			document.querySelector('.qrcode').appendChild(el);
		</script>
<?php get_footer(); ?>