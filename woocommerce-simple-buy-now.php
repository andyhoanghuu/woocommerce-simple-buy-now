<?php
/**
 * Plugin Name:     Woocommerce Simple Buy Now
 * Plugin URI:      ndoublehwp.com
 * Description:     Add Buy Now button and add to cart/ checkout in the single product page.
 * Author:          ndoublehwp
 * Author URI:      https://twitter.com/NDoubleHWP
 * Text Domain:     woocommerce-simple-buy-now
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Woocommerce_Simple_Buy_Now
 */
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( defined( 'WOO_SIMPLE_BUY_VERSION' ) ) {
	return;
}

define( 'WOO_SIMPLE_BUY_VERSION', '1.0.0' );
define( 'WOO_SIMPLE_BUY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_SIMPLE_BUY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
 * The code that runs during plugin activation.
 */
function activate_woocommerce_simple_buy() {
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_woocommerce_simple_buy() {
}

register_activation_hook( __FILE__, 'activate_woocommerce_simple_buy' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_simple_buy' );

/**
 * Admin notice: Require WooCommerce.
 */
function woocommerce_simple_buy_admin_notice() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="error">';
		echo  '<p>' . __( 'Please note that the <strong>WooCommerce Simple Buy</strong> plugin is meant to be used only with the <strong>WooCommerce</strong> plugin.</p>', 'woocommerce-simple-buy-now' );
		echo '</div>';
	}
}

add_action( 'plugins_loaded', function() {
	if ( class_exists( 'WooCommerce' ) ) {
		WooCommerce_Simple_Buy_Now::get_instance();
	}
	add_action( 'admin_notices', 'woocommerce_simple_buy_admin_notice', 4 );
} );

/**
 * Set up and initialize
 */
class WooCommerce_Simple_Buy_Now {
	/**
	 *  The instance.
	 *
	 * @var void
	 */
	private static $instance;

	/**
	 * Returns the instance.
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Actions setup
	 */
	public function __construct() {
		$enable          = get_option( 'woocommerce_simple_buy_single_product_enable' );
		$button_position = get_option( 'woocommerce_simple_buy_single_product_position' );

		if ( $enable && 'no' !== $enable ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
			add_action( 'plugins_loaded', array( $this, 'i18n' ), 3 );
			add_action( 'wp_ajax_wsb_add_to_cart_ajax', array( $this, 'add_to_cart_ajax' ) );
			add_action( 'wp_ajax_nopriv_wsb_add_to_cart_ajax', array( $this, 'add_to_cart_ajax' ) );
			add_action( 'wp_footer', array( $this, 'add_checkout_template' ) );
			add_filter( 'body_class', array( $this, 'wsb_body_class' ) );

			if ( 'before' === $button_position ) {
				add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'add_simple_buy_button' ) );
			} else {
				add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_simple_buy_button' ) );
			}
		}

		add_filter( 'woocommerce_get_settings_pages', array( $this, 'settings_page' ) );
	}

	/**
	 * Add WC settings.
	 *
	 * @param  array $integrations integrations.
	 * @return array integrations
	 */
	public function settings_page( $integrations ) {
		foreach ( glob( WOO_SIMPLE_BUY_PLUGIN_PATH . '/includes/woocommerce-settings.php*' ) as $file ) {
			$integrations[] = require_once( $file );
		}
		return $integrations;
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'woocommerce-simple-buy-now', WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/css/woocommerce-simple-buy-now.css', array(), WOO_SIMPLE_BUY_VERSION );
		wp_enqueue_script( 'woocommerce-simple-buy-now', WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/js/woocommerce-simple-buy-now.js', array( 'jquery' ), WOO_SIMPLE_BUY_VERSION, true );
		wp_localize_script( 'woocommerce-simple-buy-now', 'woocommerce_simple_buy_now', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		) );
	}

	/**
	 * Translations
	 */
	public function i18n() {
		load_plugin_textdomain( 'woocommerce-simple-buy-now', false, 'woocommerce-simple-buy-now/languages' );
	}

	/**
	 * Add popup to cart form in single product page.
	 */
	public function add_simple_buy_button() {
		global $product;
		?>
	    	<button type="submit" value="<?php echo esc_attr( $product->get_id() ); ?>" class="js-wsb-add-to-cart wsb-add-to-cart">
	    		<?php echo esc_html( get_option( 'woocommerce_simple_buy_single_product_button', 'Buy Now' ) ); ?>
	    	</button>
		<?php
	}

	/**
	 * Add checkout template.
	 */
	public function add_checkout_template() {
		if ( ! is_product() ) {
			return;
		}
		?>
			<div class="wsb-modal">
			    <div class="wsb-modal-overlay wsb-modal-toggle"></div>
			    <div class="wsb-modal-wrapper wsb-modal-transition">

			    	<?php do_action( 'wsb_modal_header_content' ); ?>

			      	<div class="wsb-modal-header">
			        	<button class="wsb-modal-close wsb-modal-toggle">
			        		<span aria-hidden="true">×</span>
			        	</button>
			      	</div>
			      	<div class="wsb-modal-body">
			      		<?php do_action( 'wsb_before_modal_body_content' ); ?>

				        <div class="wsb-modal-content"></div>

				        <?php do_action( 'wsb_after_modal_body_content' ); ?>

			      	</div>
			    </div>
		 	</div>
		<?php
	}

	/**
	 * Add product to cart via ajax function.
	 */
	public function add_to_cart_ajax() {
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );

		$variation_id      = $_POST['variation_id'];

		try {
		  	if ( $variation_id ) {
		    	$added_to_cart = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
		  	} else {
		   		$added_to_cart = WC()->cart->add_to_cart( $product_id, $quantity );
		  	}

		   	if ( $passed_validation && $added_to_cart && 'publish' === $product_status ) {

	            do_action( 'woocommerce_ajax_added_to_cart', $product_id );
	            global $woocommerce;
	            $items = $woocommerce->cart->get_cart();

	            wc_setcookie( 'woocommerce_items_in_cart', count( $items ) );
	            wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( $items ) ) );

	            do_action( 'woocommerce_set_cart_cookies', true );
	            define( 'WOOCOMMERCE_CHECKOUT', true );
	        }

			return wp_send_json_success( array(
			  	'checkout' => do_shortcode( '[woocommerce_checkout]' ),
			), 200 );

		} catch ( \Exception $e ) {
			return wp_send_json_error( array( 'message' => $e->getMessage() ), 400 );
		}
	}

	/**
	 * Add class to body tag with check availability page.
	 *
	 * @param  array $classes classes.
	 * @return array
	 */
	public function wsb_body_class( $classes ) {
		$button_position = get_option( 'woocommerce_simple_buy_single_product_position' );

		if ( is_product() ) {
			$classes[] = 'woocommerce-simple-buy-now';

			if ( 'replace' == get_option( 'woocommerce_simple_buy_single_product_position' ) ) {
				$classes[] = 'woocommerce-simple-buy-now--remove_add_to_cart_btn';
			}
		}

		return $classes;
	}
}
