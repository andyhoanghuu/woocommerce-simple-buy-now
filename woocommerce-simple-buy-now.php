<?php
/**
 * Plugin Name:     WooCommerce Simple Buy Now
 * Plugin URI:      http://ndoublehwp.com/
 * Description:     Add Buy Now button and add to cart/ checkout in the single product page.
 * Author:          Andy Hoang Huu
 * Author URI:      http://ndoublehwp.com/
 * Text Domain:     woocommerce-simple-buy-now
 * Domain Path:     /languages
 * Version:         1.0.6
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

define( 'WOO_SIMPLE_BUY_VERSION', '1.0.6' );
define( 'WOO_SIMPLE_BUY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_SIMPLE_BUY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
/**
 * The code that runs during plugin activation.
 */
function activate_woocommerce_simple_buy_now() {
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_woocommerce_simple_buy_now() {
}

register_activation_hook( __FILE__, 'activate_woocommerce_simple_buy_now' );
register_deactivation_hook( __FILE__, 'deactivate_woocommerce_simple_buy_now' );

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

add_action( 'plugins_loaded', function () {
	if ( class_exists( 'WooCommerce' ) ) {
		$GLOBALS['woocommerce_simple_buy_now'] = WooCommerce_Simple_Buy_Now::get_instance();
	}
	add_action( 'admin_notices', 'woocommerce_simple_buy_now_admin_notice', 4 );
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
	 * Status.
	 *
	 * @var string
	 */
	private $enabled = 'yes';

	/**
	 * Redirect.
	 *
	 * @var string
	 */
	private $redirect = 'popup';

	/**
	 * Position.
	 *
	 * @var string
	 */
	private $position = 'before';

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
		if ( $this->is_enabled() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
			add_action( 'plugins_loaded', [ $this, 'i18n' ], 3 );
			add_action( 'wp_ajax_wsb_add_to_cart_ajax', [ $this, 'add_to_cart_ajax' ] );
			add_action( 'wp_ajax_nopriv_wsb_add_to_cart_ajax', [ $this, 'add_to_cart_ajax' ] );
			add_filter( 'body_class', [ $this, 'body_class' ] );

			if ( ! $this->is_redirect() ) {
				add_action( 'wp_footer', [ $this, 'add_checkout_template' ] );
			}

			if ( $this->is_before_button() ) {
				add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_simple_buy_button' ] );
			} else {
				add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_simple_buy_button' ] );
			}

			add_action( 'wsb_before_add_to_cart', [ $this, 'reset_cart' ], 10 );
		}

		add_filter( 'woocommerce_get_settings_pages', [ $this, 'settings_page' ] );
	}

	/**
	 * Add WC settings.
	 *
	 * @param  array $integrations integrations.
	 *
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
		wp_enqueue_style( 'woocommerce-simple-buy-now', WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/css/woocommerce-simple-buy-now.css', [], WOO_SIMPLE_BUY_VERSION );
		wp_enqueue_script( 'woocommerce-simple-buy-now', WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/js/woocommerce-simple-buy-now.js', [ 'jquery' ], WOO_SIMPLE_BUY_VERSION, true );
		wp_localize_script( 'woocommerce-simple-buy-now', 'woocommerce_simple_buy_now', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		] );

		if ( is_product() ) {
			wp_enqueue_script( 'wc-checkout' );
		}

		/**
		 * Fires enqueue scripts.
		 *
		 * @param WooCommerce_Simple_Buy_Now WooCommerce_Simple_Buy_Now main class.
		 */
		do_action( 'wsb_enqueue_scripts', $this );
	}

	/**
	 * Translations.
	 */
	public function i18n() {
		load_plugin_textdomain( 'woocommerce-simple-buy-now', false, 'woocommerce-simple-buy-now/languages' );
	}

	/**
	 * Add class to body tag with check availability page.
	 *
	 * @param  array $classes classes.
	 *
	 * @return array
	 */
	public function body_class( $classes ) {
		$button_position = get_option( 'woocommerce_simple_buy_single_product_position' );

		if ( is_product() ) {
			$classes[] = 'woocommerce-simple-buy-now';

			if ( $this->is_replace_button() ) {
				$classes[] = 'woocommerce-simple-buy-now--remove_add_to_cart_btn';
			}

			if ( $this->is_remove_quantity() ) {
				$classes[] = 'woocommerce-simple-buy-now--remove_quantity_input';
			}
		}

		return $classes;
	}

	/**
	 * Is enable.
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		$enabled = get_option( 'woocommerce_simple_buy_single_product_enable', $this->enabled );

		if ( $enabled && 'no' !== $enabled ) {
			return true;
		}

		return false;
	}

	/**
	 * Gets redirect.
	 *
	 * @return string
	 */
	public function get_redirect() {
		return get_option( 'woocommerce_simple_buy_redirect', $this->redirect );
	}

	/**
	 * Gets position button.
	 *
	 * @return string
	 */
	public function get_position() {
		return get_option( 'woocommerce_simple_buy_single_product_position', $this->position );
	}

	/**
	 * Gets button title.
	 *
	 * @return string
	 */
	public function get_button_title() {
		$title = esc_html__( 'Buy Now', 'woocommerce-simple-buy-now' );

		if ( get_option( 'woocommerce_simple_buy_single_product_button' ) ) {
			$title = get_option( 'woocommerce_simple_buy_single_product_button' );
		}

		return $title;
	}

	/**
	 * Is use pop-up?
	 *
	 * @return boolean
	 */
	public function is_popup() {
		return ( 'popup' === $this->get_redirect() );
	}

	/**
	 * Is redirect to the checkout page?
	 *
	 * @return boolean
	 */
	public function is_redirect() {
		return ( 'checkout' === $this->get_redirect() );
	}

	/**
	 * If button position is before `add to cart` button.
	 *
	 * @return boolean
	 */
	public function is_before_button() {
		return ( 'before' === $this->get_position() );
	}

	/**
	 * If button position is after `add to cart` button.
	 *
	 * @return boolean
	 */
	public function is_after_button() {
		return ( 'after' === $this->get_position() );
	}

	/**
	 * If `buy now` button replace `add to cart` button
	 *
	 * @return boolean
	 */
	public function is_replace_button() {
		return ( 'replace' === $this->get_position() );
	}

	/**
	 * If remove quantity input.
	 *
	 * @return boolean
	 */
	public function is_remove_quantity() {
		$remove_quantity = get_option( 'woocommerce_simple_buy_single_product_remove_quantity' );

		return ( $remove_quantity && 'no' !== $remove_quantity );
	}

	/**
	 * Add popup to cart form in single product page.
	 */
	public function add_simple_buy_button() {
		global $product;

		$btn_class = apply_filters( 'wsb_single_product_button_classes', [
			'wsb-button',
			'js-wsb-add-to-cart',
		] );

		$args = apply_filters( 'wsb_buy_now_button_args', [
			'type'       => 'submit',
			'class'      => $btn_class,
			'title'      => esc_html( $this->get_button_title() ),
			'attributes' => '',
		], $this->get_redirect(), $this->get_position() );

		$this->button_template( $args );
	}

	/**
	 * Button template.
	 *
	 * @param  array $args arguments.
	 *
	 * @return void
	 */
	public function button_template( $args ) {
		global $product;

		?>
        <button <?php echo isset( $args['type'] ) ? 'type="' . esc_attr( $args['type'] ) . '"' : ''; ?> value="<?php echo esc_attr( $product->get_id() ); ?>" class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $args['class'] ) ) ); ?>" <?php echo isset( $args['attributes'] ) ? $args['attributes'] : ''; // WPCS: xss ok. ?>>
			<?php echo isset( $args['title'] ) ? esc_html( $args['title'] ) : ''; ?>
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
		$product_id   = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$quantity     = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( $_POST['quantity'] );
		$variation_id = isset( $_POST['variation_id'] ) ? $_POST['variation_id'] : '';

		$args = [
			'product_id'   => $product_id,
			'quantity'     => $quantity,
			'variation_id' => $variation_id,
		];

		/**
		 * Filters the array of args product.
		 *
		 * @param array $args Args.
		 */
		$args = apply_filters( 'wsb_cart_args', $args );

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $args['product_id'], $args['quantity'] );
		$product_status    = get_post_status( $args['product_id'] );

		try {

			/**
			 * Fires before add to cart via ajax.
			 *
			 * @param array $args Args.
			 */
			do_action( 'wsb_before_add_to_cart', $args );

			if ( $args['variation_id'] ) {
				$added_to_cart = WC()->cart->add_to_cart( $args['product_id'], $args['quantity'], $args['variation_id'] );
			} else {
				$added_to_cart = WC()->cart->add_to_cart( $args['product_id'], $args['quantity'] );
			}

			/**
			 * Fires after add to cart via ajax.
			 *
			 * @param boolean $added_to_cart added_to_cart.
			 */
			do_action( 'wsb_after_add_to_cart', $added_to_cart );

			if ( $passed_validation && $added_to_cart && 'publish' === $product_status ) {

				do_action( 'woocommerce_ajax_added_to_cart', $args['product_id'] );
				global $woocommerce;
				$items = $woocommerce->cart->get_cart();

				wc_setcookie( 'woocommerce_items_in_cart', count( $items ) );
				wc_setcookie( 'woocommerce_cart_hash', md5( json_encode( $items ) ) );

				do_action( 'woocommerce_set_cart_cookies', true );
				define( 'WOOCOMMERCE_CHECKOUT', true );
			}

			/**
			 * Filters the template of checkout form after add to cart.
			 *
			 * @param array $results results.
			 */
			$results = apply_filters( 'wsb_checkout_template', [
				'element'      => '.wsb-modal-content',
				'redirect'     => $this->is_redirect(),
				'checkout_url' => esc_url( wc_get_checkout_url() ),
				'template'     => do_shortcode( '[woocommerce_checkout]' ),
				'method'       => 'html',
			] );

			return wp_send_json_success( $results, 200 );

		} catch ( \Exception $e ) {
			return wp_send_json_error( [ 'message' => $e->getMessage() ], 400 );
		}
	}

	/**
	 * Reset cart before Buy Now.
	 */
	public function reset_cart() {
		$reset_cart = get_option( 'woocommerce_simple_buy_single_product_reset_cart' );

		if ( $reset_cart && 'no' !== $reset_cart ) {
			// Remove all products in cart.
			WC()->cart->empty_cart();
		}
	}
}
