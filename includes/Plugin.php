<?php

namespace WooCommerce_Simple_Buy_Now;

use WooCommerce_Simple_Buy_Now\Admin\Settings;

/**
 * Set up and initialize
 */
class Plugin {
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
			add_action( 'plugins_loaded', [ $this, 'i18n' ], 3 );
			add_action( 'wp_ajax_wsb_add_to_cart_ajax', [ $this, 'add_to_cart_ajax' ] );
			add_action( 'wp_ajax_nopriv_wsb_add_to_cart_ajax', [ $this, 'add_to_cart_ajax' ] );
			add_filter( 'body_class', [ $this, 'body_class' ] );

			if ( ! $this->is_redirect() ) {
				add_action( 'wp_footer', [ $this, 'add_checkout_template' ] );
			}

			if ( $this->is_before_button() ) {
				add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_simple_buy_button' ] );
			} elseif ( $this->is_after_button() ) {
				add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_simple_buy_button' ], 5 );
			} elseif ( $this->is_before_quantity_input() ) {
				add_action( 'woocommerce_before_add_to_cart_quantity', [ $this, 'add_simple_buy_button' ] );
			} elseif ( $this->is_after_quantity_input() ) {
				add_action( 'woocommerce_after_add_to_cart_quantity', [ $this, 'add_simple_buy_button' ], 5 );
			} elseif ( $this->is_replace_button() ) {
				add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'add_simple_buy_button' ], 5 );
			}

			add_action( 'wsb_before_add_to_cart', [ $this, 'reset_cart' ], 10 );
			add_filter( 'woocommerce_is_checkout', [ $this, 'woocommerce_is_checkout' ] );
			add_shortcode( 'woocommerce_simple_buy_now_button', [ $this, 'add_shortcode_button' ] );

			$this->handle_customize();

			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );
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
		$integrations[] = new Settings;

		return $integrations;
	}

	/**
	 * Handle customize.
	 */
	public function handle_customize() {
		new Customize();
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		wp_register_style( 'woocommerce-simple-buy-now',
			WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/css/woocommerce-simple-buy-now.css', [], WOO_SIMPLE_BUY_VERSION );
		wp_register_script( 'woocommerce-simple-buy-now',
			WOO_SIMPLE_BUY_PLUGIN_URL . 'assets/js/woocommerce-simple-buy-now.js', [ 'jquery' ], WOO_SIMPLE_BUY_VERSION,
			true );

		if ( is_product() ) {
			wp_enqueue_style( 'woocommerce-simple-buy-now' );
			wp_enqueue_script( 'woocommerce-simple-buy-now' );

			wp_localize_script( 'woocommerce-simple-buy-now', 'woocommerce_simple_buy_now', [
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			] );
		}

		/**
		 * Fires enqueue scripts.
		 *
		 * @param Plugin Plugin main class.
		 */
		do_action( 'wsb_enqueue_scripts', $this );
	}

	/**
	 * Fake woocommerce checkout page.
	 *
	 * @param bool $is_checkout Is checkout page?.
	 *
	 * @return bool
	 */
	public function woocommerce_is_checkout( $is_checkout ) {
		if ( is_product() ) {
			return true;
		}

		return $is_checkout;
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
		if ( is_product() ) {
			$button_position = get_option( 'woocommerce_simple_buy_single_product_position' );
			$classes[]       = 'woocommerce-simple-buy-now';
			$classes[]       = 'woocommerce-simple-buy-now--button-' . esc_attr( $button_position ) . '-cart';

			if ( $this->is_remove_quantity() ) {
				$classes[] = 'woocommerce-simple-buy-now--remove-quantity';
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

		return $enabled && 'no' !== $enabled;
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
	 * If button position is before `quantity` input.
	 *
	 * @return boolean
	 */
	public function is_before_quantity_input() {
		return ( 'before_quantity' === $this->get_position() );
	}

	/**
	 * If button position is after `quantity` input.
	 *
	 * @return boolean
	 */
	public function is_after_quantity_input() {
		return ( 'after_quantity' === $this->get_position() );
	}

	/**
	 * If button position is after `quantity` input.
	 *
	 * @return boolean
	 */
	public function is_shortcode() {
		return ( 'shortcode' === $this->get_position() );
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
		$args = $this->get_button_default_args();

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
		<button <?php echo isset( $args['type'] ) ? 'type="' . esc_attr( $args['type'] ) . '"' : ''; ?>
			value="<?php echo esc_attr( $product->get_id() ); ?>" class="<?php echo esc_attr( implode( ' ',
			array_map( 'sanitize_html_class',
				$args['class'] ) ) ); ?>" <?php echo isset( $args['attributes'] ) ? $args['attributes'] : ''; // WPCS: xss ok. ?>>
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
		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$was_added_to_cart = false;
		$product           = wc_get_product( $product_id );

		$product_type = $product->get_type();

		/**
		 * Fires before add to cart via ajax.
		 *
		 * @param int $product_id Product ID.
		 */
		do_action( 'wsb_before_add_to_cart', $product_id );

		if ( 'variable' === $product_type || 'variation' === $product_type ) {
			$was_added_to_cart = $this->add_to_cart_handler_variable( $product_id );
		} else {
			$was_added_to_cart = $this->add_to_cart_handler_simple( $product_id );
		}

		try {
			if ( $was_added_to_cart ) {

				do_action( 'woocommerce_ajax_added_to_cart', $product_id );

				$items = WC()->cart->get_cart();

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
	 * Handle adding variable products to the cart.
	 *
	 * @param int $product_id Product ID to add to the cart.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_variable( $product_id ) {
		try {
			$variation_id       = empty( $_REQUEST['variation_id'] ) ? '' : absint( wp_unslash( $_REQUEST['variation_id'] ) );
			$quantity           = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_REQUEST['quantity'] ) ); // WPCS: sanitization ok.
			$missing_attributes = [];
			$variations         = [];
			$adding_to_cart     = wc_get_product( $product_id );

			// If the $product_id was in fact a variation ID, update the variables.
			if ( $adding_to_cart->is_type( 'variation' ) ) {
				$variation_id   = $product_id;
				$product_id     = $adding_to_cart->get_parent_id();
				$adding_to_cart = wc_get_product( $product_id );

				if ( ! $adding_to_cart ) {
					return false;
				}
			}

			// Gather posted attributes.
			$posted_attributes = [];

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

				if ( isset( $_REQUEST[ $attribute_key ] ) ) {
					if ( $attribute['is_taxonomy'] ) {
						// Don't use wc_clean as it destroys sanitized characters.
						$value = sanitize_title( wp_unslash( $_REQUEST[ $attribute_key ] ) );
					} else {
						$value = html_entity_decode( wc_clean( wp_unslash( $_REQUEST[ $attribute_key ] ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ); // WPCS: sanitization ok.
					}

					$posted_attributes[ $attribute_key ] = $value;
				}
			}

			// If no variation ID is set, attempt to get a variation ID from posted attributes.
			if ( empty( $variation_id ) ) {
				$data_store   = \WC_Data_Store::load( 'product' );
				$variation_id = $data_store->find_matching_product_variation( $adding_to_cart, $posted_attributes );
			}

			// Check the data we have is valid.
			$variation_data = wc_get_product_variation_attributes( $variation_id );

			foreach ( $adding_to_cart->get_attributes() as $attribute ) {
				if ( ! $attribute['is_variation'] ) {
					continue;
				}

				// Get valid value from variation data.
				$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );
				$valid_value   = isset( $variation_data[ $attribute_key ] ) ? $variation_data[ $attribute_key ] : '';

				/**
				 * If the attribute value was posted, check if it's valid.
				 *
				 * If no attribute was posted, only error if the variation has an 'any' attribute which requires a value.
				 */
				if ( isset( $posted_attributes[ $attribute_key ] ) ) {
					$value = $posted_attributes[ $attribute_key ];

					// Allow if valid or show error.
					if ( $valid_value === $value ) {
						$variations[ $attribute_key ] = $value;
					} elseif ( '' === $valid_value && in_array( $value, $attribute->get_slugs() ) ) {
						// If valid values are empty, this is an 'any' variation so get all possible values.
						$variations[ $attribute_key ] = $value;
					}
				} elseif ( '' === $valid_value ) {
					$missing_attributes[] = wc_attribute_label( $attribute['name'] );
				}
			}
		} catch ( \Exception $e ) {
			return false;
		}

		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handle adding simple products to the cart.
	 *
	 * @param int $product_id Product ID to add to the cart.
	 *
	 * @return bool success or not
	 */
	public function add_to_cart_handler_simple( $product_id ) {
		$quantity          = empty( $_REQUEST['quantity'] ) ? 1 : wc_stock_amount( $_REQUEST['quantity'] );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity ) ) {
			return true;
		}

		return false;
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

	/**
	 * Register shortcode button
	 *
	 * @param array $atts Attributes.
	 */
	public function add_shortcode_button( $atts ) {
		$atts = shortcode_atts( $this->get_button_default_args(), $atts, 'woocommerce_simple_buy_now_button' );

		ob_start();

		$this->button_template( $atts );

		return ob_get_clean();
	}

	/**
	 * Gets button default args
	 *
	 * @return array
	 */
	public function get_button_default_args() {
		$btn_class = apply_filters( 'wsb_single_product_button_classes', [
			'wsb-button',
			'js-wsb-add-to-cart',
		] );

		return apply_filters( 'wsb_buy_now_button_args', [
			'type'       => 'submit',
			'class'      => $btn_class,
			'title'      => esc_html( $this->get_button_title() ),
			'attributes' => '',
		], $this->get_redirect(), $this->get_position() );
	}
}
