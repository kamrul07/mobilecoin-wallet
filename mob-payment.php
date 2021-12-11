<?php
/**
 * Plugin Name: MOB Payment
 * Plugin URI: 
 * Description: 
 * Version: 1.0
 * Author: Fida Al Hasan
 * Author URI: 
 * License: GNU General Public License v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
*/

// don't load directly
defined( 'ABSPATH' ) || exit;

/**
 * Create MOB Pages
 */
 
 
function mob_enqueue_scripts()
{
    wp_enqueue_script( 'mob-qr-code', plugin_dir_url( __FILE__ ) . 'js/kjua.min.js' );
   
 
    wp_enqueue_script( 'mob-main-scripts', plugin_dir_url( __FILE__ ) . 'js/main.js');
    
    	wp_localize_script( 'mob-main-scripts', 'mob_ajax_params', array(
        'mob_ajax_nonce' => wp_create_nonce( 'mob_ajax_nonce' ),
        'mob_ajax_url' => admin_url( 'admin-ajax.php' ),
    ));
}
add_action( 'wp_enqueue_scripts', 'mob_enqueue_scripts' );


add_action('wp_ajax_mob_payment_check', 'mob_after_payment_function');
add_action('wp_ajax_nopriv_mob_payment_check', 'mob_after_payment_function');

function mob_after_payment_function(){
	// Verify nonce
    $order_id = $_POST['order_id'];
    $order_status = $_POST['status'];
    $order_made = new WC_Order($order_id);
    if (!empty($order_made)) {
        
        if($order_status == "succeeded"){
            	$order_modified = $order_made->update_status('completed');
            	if($order_modified){
            	    
            	    
            	    $redirected_url = get_permalink( 21 );
            	    echo "succeeded";
            	    // wp_redirect( $redirected_url );
                     // die;
            	    }
            	
            }else if($order_status == "canceled"){
                
              $order_modified = $order_made->update_status('canceled');
            	if($order_modified){
                    echo "canceled";
            	    }

            }
        
    	// Update order status
    
    }

	die();
}





add_action( 'init', 'mob_page' );
function mob_page(){
    if( get_page_by_title( 'MOB Payment' ) == NULL ) {
        $createMobPaymentPage = array(
          'post_title'    => 'MOB Payment',
          'post_content'  => "",
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'post_name'     => 'mob-payment'
        );
        // Insert the post into the database
        wp_insert_post( $createMobPaymentPage );
    }
	
	if( get_page_by_title( 'MOB Function' ) == NULL ) {
        $createMobFunctionPage = array(
          'post_title'    => 'MOB Function',
          'post_content'  => "",
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'post_name'     => 'mob-function'
        );
        // Insert the post into the database
        wp_insert_post( $createMobFunctionPage );
    }
}

/**
 * Assign MOB Templates to MOB pages
 */
add_filter( 'page_template', 'mob_template' );
function mob_template( $page_template ){
    if ( is_page( 'mob-payment' ) ) {
        $page_template = dirname( __FILE__ ) . '/template.php';
    }
	
	if ( is_page( 'mob-function' ) ) {
        $page_template = dirname( __FILE__ ) . '/function.php';
    }
    return $page_template;
}

/**
 * Assign MOB post states to MOB pages
 */
add_filter( 'display_post_states', 'mob_post_state', 10, 2 );
function mob_post_state( $post_states, $post ) {
	if( $post->post_name == 'mob-payment' ) {
		$post_states[] = 'MOB Payment Page';
	}
	
	if( $post->post_name == 'mob-function' ) {
		$post_states[] = 'MOB Payment Page';
	}
	return $post_states;
}


/*
 * This action hook registers MOB PHP class as a WooCommerce payment gateway
 */
add_filter( 'woocommerce_payment_gateways', 'mob_add_gateway_class' );
function mob_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Mob_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'mob_init_gateway_class' );
function mob_init_gateway_class() {

	class WC_Mob_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor
 		 */
 		public function __construct() {

			$this->id = 'mob'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = true; 
			$this->method_title = 'MOB Gateway';
			$this->method_description = 'Mobile Coin payment gateway'; // will be displayed on the options page

			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products'
			);

			// Method with all the options fields
			$this->init_form_fields();

			// Load the settings. 
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->enabled = $this->get_option( 'enabled' );
			$this->private_key = $this->get_option( 'public_key' );
			$this->publishable_key = $this->get_option( 'secret_key' );
			$this->success_page = $this->get_option( 'success_page' );
			$this->error_page = $this->get_option( 'error_page' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

 		}

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
 		public function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable MOB Gateway',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'title' => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'MOB Payment',
					'desc_tip'    => true,
				),
				'description' => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => 'Pay with your mobile coin',
				),
				'public_key' => array(
					'title'       => 'Public API Key',
					'type'        => 'text'
				),
				'secret_key' => array(
					'title'       => 'Secret API Key',
					'type'        => 'text'
				),
				'success_page' => array(
					'title'       => 'Success Page URL',
					'type'        => 'url',
					'description' => 'Full url',
				),
				'error_page' => array(
					'title'       => 'Error Page URL',
					'type'        => 'url',
					'description' => 'Full url',
				),
			);	
	 	}

		/**
	 * Process the payment and return the result.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( $order->get_total() > 0 ) {
			// Mark as processing or on-hold (payment won't be taken until delivery).
			$order->update_status( 'pending' );
		} else {
			$order->update_status( 'pending' );
		}

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => get_site_url() . '/mob-payment/?order_id=' . $order_id,
		);
	}

		
 	}
}

?>