<?php
/*
Plugin Name: MandarinPay - WooCommerce Gateway
Plugin URI: http://www.mandarin.com/
Description: Extends WooCommerce by Adding the MandarinPay Gateway.
Version: 1.0
Author: LIF, Look In Future
Author URI: http://www.lif.org.ua/
*/

// Include our Gateway Class and Register Payment Gateway with WooCommerce
add_action( 'plugins_loaded', 'mandarin_pay_init', 0 );
function mandarin_pay_init() {
	// If the parent WC_Payment_Gateway class doesn't exist
	// it means WooCommerce is not installed on the site
	// so do nothing
	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
	
	// If we made it this far, then include our Gateway Class
	include_once( 'woocommerce-mandarin-pay.php' );

	// Now that we have successfully included our class,
	// Lets add it too WooCommerce
	add_filter( 'woocommerce_payment_gateways', 'add_mandarin_pay_gateway' );
	function add_mandarin_pay_gateway( $methods ) {
		$methods[] = 'Mandarin_Pay';
		return $methods;
	}
}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mandarin_pay_action_links' );
function mandarin_pay_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Настроить', 'mandarin-pay' ) . '</a>',
	);

	// Merge our new link with the default ones
	return array_merge( $plugin_links, $links );
}