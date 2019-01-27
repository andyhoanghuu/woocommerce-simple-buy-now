<?php
/**
 * Plugin Name:     WooCommerce Simple Buy Now
 * Plugin URI:      http://ndoublehwp.com/
 * Description:     Add Buy Now button and add to cart/ checkout in the single product page.
 * Author:          Andy Hoang Huu
 * Author URI:      http://ndoublehwp.com/
 * Text Domain:     woocommerce-simple-buy-now
 * Domain Path:     /languages
 * Version:         2.0.0
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.4
 *
 * @package         Woocommerce_Simple_Buy_Now
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * WooCommerce Simple Buy Now only works with WordPress 4.6 or later.
 */
if ( version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {
	/**
	 * Prints an update nag after an unsuccessful attempt to active
	 * WooCommerce Simple Buy Now on WordPress versions prior to 4.6.
	 *
	 * @global string $wp_version WordPress version.
	 */
	function woocommerce_simple_buy_now_wordpress_upgrade_notice() {
		$message = sprintf( esc_html__( 'WooCommerce Simple Buy Now requires at least WordPress version 4.6, you are running version %s. Please upgrade and try again!', 'woocommerce-simple-buy-now' ), $GLOBALS['wp_version'] );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'woocommerce_simple_buy_now/woocommerce_simple_buy_now.php' ] );
	}

	add_action( 'admin_notices', 'woocommerce_simple_buy_now_wordpress_upgrade_notice' );

	return;
}

/**
 * And only works with PHP 5.4 or later.
 */
if ( version_compare( phpversion(), '5.4', '<' ) ) {
	/**
	 * Adds a message for outdate PHP version.
	 */
	function woocommerce_simple_buy_now_php_upgrade_notice() {
		$message = sprintf( esc_html__( 'WooCommerce Simple Buy Now requires at least PHP version 5.4 to work, you are running version %s. Please contact to your administrator to upgrade PHP version!', 'woocommerce-simple-buy-now' ), phpversion() );
		printf( '<div class="error"><p>%s</p></div>', $message ); // WPCS: XSS OK.

		deactivate_plugins( [ 'woocommerce_simple_buy_now/woocommerce_simple_buy_now.php' ] );
	}

	add_action( 'admin_notices', 'woocommerce_simple_buy_now_php_upgrade_notice' );

	return;
}

if ( defined( 'WOO_SIMPLE_BUY_VERSION' ) ) {
	return;
}

define( 'WOO_SIMPLE_BUY_VERSION', '2.0.0' );
define( 'WOO_SIMPLE_BUY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_SIMPLE_BUY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Admin notice: Require WooCommerce.
 */
function woocommerce_simple_buy_now_admin_notice() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="error">';
		echo '<p>' . __( 'Please note that the <strong>WooCommerce Simple Buy Now</strong> plugin is meant to be used only with the <strong>WooCommerce</strong> plugin.</p>', 'woocommerce-simple-buy-now' );
		echo '</div>';
	}
}

// Include the loader.
require_once dirname( __FILE__ ) . '/loader.php';

add_action( 'plugins_loaded', function () {
	if ( class_exists( 'WooCommerce' ) ) {
		$GLOBALS['woocommerce_simple_buy_now'] = WooCommerce_Simple_Buy_Now::get_instance();
	}
	add_action( 'admin_notices', 'woocommerce_simple_buy_now_admin_notice', 4 );
} );
