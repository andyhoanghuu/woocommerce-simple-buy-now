<?php
/**
 * WooCommerce Simple Buy Now Settings
 *
 * @author      ndoublehwp
 * @version     1.0.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WooCommerce_Simple_Buy_Settings_Buy_Now_Settings
 */
class WooCommerce_Simple_Buy_Settings_Buy_Now_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'wc_simple_buy_settings';
		$this->label = esc_html__( 'WC Simple Buy Now', 'woocommerce-simple-buy-now' );

		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_settings_page' ], 20 );
		add_filter( 'woocommerce_sections_' . $this->id, [ $this, 'output_sections' ] );
		add_filter( 'woocommerce_settings_' . $this->id, [ $this, 'output_settings' ] );
		add_action( 'woocommerce_settings_save_' . $this->id, [ $this, 'save' ] );
	}

	/**
	 * Gets sections
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = [
			'' => esc_html__( 'General', 'woocommerce-simple-buy-now' ),
			// 'customize' => esc_html__( 'Customize', 'woocommerce-simple-buy-now' ),
		];

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Gets button positions.
	 *
	 * @return array
	 */
	public function get_positions() {
		return apply_filters( 'woocommerce_simple_buy_get_postitions', [
			'before'          => esc_html__( 'Before Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'after'           => esc_html__( 'After Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'replace'         => esc_html__( 'Replace Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'before_quantity' => esc_html__( 'Before Quantity Input', 'woocommerce-simple-buy-now' ),
			'after_quantity'  => esc_html__( 'After Quantity Input', 'woocommerce-simple-buy-now' ),
			'shortcode'       => esc_html__( 'Use a Shortcode (for developer)', 'woocommerce-simple-buy-now' ),
		] );
	}

	/**
	 * Gets redirects.
	 *
	 * @return array
	 */
	public function get_redirects() {
		return apply_filters( 'woocommerce_simple_buy_get_redirects', [
			'popup'    => esc_html__( 'Use pop-up', 'woocommerce-simple-buy-now' ),
			'checkout' => esc_html__( 'Redirect to the checkout page (skip the cart page)', 'woocommerce-simple-buy-now' ),
		] );
	}

	/**
	 * Output settings.
	 */
	public function output_settings() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Gets settings.
	 *
	 * @param  array $current_section Current section.
	 *
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		// if ( 'customize' === $current_section ) {
		// 	$settings = $this->get_customize();
		// } else {
		$settings = $this->get_general();

		// }

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	/**
	 * Gets general settings.
	 *
	 * @return array
	 */
	public function get_general() {
		$settings = [];

		$settings[] = [
			'name' => esc_html__( 'General Settings', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'desc' => esc_html__( 'The following options are used to configure WC Simple Buy Now actions.', 'woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_settings_start',
		];

		$settings[] = [
			'name'    => esc_html__( 'Enable Simple Buy Now', 'woocommerce-simple-buy-now' ),
			'id'      => 'woocommerce_simple_buy_single_product_enable',
			'type'    => 'checkbox',
			'default' => 'yes',
		];

		$settings[] = [
			'name'     => esc_html__( 'Redirect', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Use pop-up or redirect to the checkout page', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_redirect',
			'type'     => 'radio',
			'default'  => 'popup',
			'options'  => $this->get_redirects(),
		];

		$settings[] = [
			'name'     => esc_html__( 'Simple Buy Now Button Position', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Where the button need to be added in single page .. before / after / replace', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_position',
			'type'     => 'select',
			'class'    => 'chosen_select',
			'default'  => 'before',
			'options'  => $this->get_positions(),
		];

		$settings[] = [
			'name'     => esc_html__( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_button',
			'type'     => 'text',
			'default'  => esc_html__( 'Buy Now', 'woocommerce-simple-buy-now' ),
		];

		$settings[] = [
			'name' => esc_html__( 'Reset Cart before Buy Now', 'woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_single_product_reset_cart',
			'type' => 'checkbox',
		];

		$settings[] = [
			'name' => esc_html__( 'Remove Quantity input', 'woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_single_product_remove_quantity',
			'type' => 'checkbox',
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'woocommerce_simple_buy_settings_end',
		];

		return apply_filters( 'woocommerce_quick_buy_general_settings', $settings );
	}

	/**
	 * Gets customize settings.
	 *
	 * @return array
	 */
	public function get_customize() {
		$settings = [];

		$settings[] = [
			'name' => esc_html__( 'Customize Settings', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'desc' => esc_html__( 'The following options are used to configure WC Simple Buy Now style.', 'woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_settings_start',
		];

		$settings[] = [
			'name'     => esc_html__( 'Button style', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Use theme style or customize', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_customize',
			'type'     => 'radio',
			'default'  => 'theme',
			'options'  => [
				'theme'     => esc_html__( 'Theme style (default)', 'woocommerce-simple-buy-now' ),
				'customize' => esc_html__( 'Customize', 'woocommerce-simple-buy-now' ),
			],
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'woocommerce_simple_buy_customize_end',
		];

		$settings[] = [
			'name' => esc_html__( 'Normal colors', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'id'   => 'woocommerce_simple_buy_normal_colors',
		];

		$settings[] = [
			'name'     => esc_html__( 'Color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_color',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'name'     => esc_html__( 'Background color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_bgcolor',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'name'     => esc_html__( 'Border color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_border_color',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'woocommerce_simple_buy_colors_end',
		];

		$settings[] = [
			'name' => esc_html__( 'Hover colors', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'id'   => 'woocommerce_simple_buy_hover_colors',
		];

		$settings[] = [
			'name'     => esc_html__( 'Color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_hover_color',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'name'     => esc_html__( 'Background color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_hover_bgcolor',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'name'     => esc_html__( 'Border color', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_button_hover_border_color',
			'type'     => 'color',
			'css'      => 'width:6em;',
			'autoload' => false,
			'desc_tip' => true,
		];

		$settings[] = [
			'type' => 'sectionend',
			'id'   => 'woocommerce_simple_buy_hover_colors_end',
		];

		return apply_filters( 'woocommerce_quick_buy_customize_settings', $settings );
	}

	/**
	 * Save settings
	 */
	public function save() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}
}

return new WooCommerce_Simple_Buy_Settings_Buy_Now_Settings();
