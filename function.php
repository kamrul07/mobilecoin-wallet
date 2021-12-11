<?php
/*
 * Get Woo Payment Options
 */
// HERE define you payment gateway ID
$payment_gateway_id = 'mob';
// Get an instance of the WC_Payment_Gateways object
$payment_gateways   = WC_Payment_Gateways::instance();
// Get the desired WC_Payment_Gateway object
$payment_gateway    = $payment_gateways->payment_gateways()[$payment_gateway_id];

// WooCommerce Order ID
$order_id = $_GET['order_id'];
$order = new WC_Order($order_id);
if (!empty($order)) {
	// Update order status
	$order->update_status('completed');
}
?>
<html>
	<head>
		<title>MOB Function Page | Redirecting...</title>
	</head>
	<body>
		<h2 style="text-align:center;">Redirecting....</h2>
		<script>
			// Redirect to success page
			window.location.replace("<?php echo $payment_gateway->settings['success_page']; ?>");
		</script>
	</body>
</html>