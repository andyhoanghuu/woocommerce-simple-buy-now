<?php
/**
 * WooCommerce General Settings
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WC_Admin_Settings_General
 */
class WooCommerce_Simple_Buy_Settings_Buy_Now_Settings extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'wc_simple_buy_settings';
		$this->label = esc_html__( 'WC Simple Buy Now', 'woocommerce-simple-buy-now' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_filter( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_filter( 'woocommerce_settings_' . $this->id, array( $this, 'output_settings' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Gets sections
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
		    '' => esc_html__( 'General', 'woocommerce-simple-buy-now' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Gets button positions.
	 *
	 * @return array
	 */
	public function get_positions() {
		return apply_filters( 'woocommerce_simple_buy_get_postitions', array(
			'before'  => esc_html__( 'Before Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'after'   => esc_html__( 'After Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'replace' => esc_html__( 'Replace Add To Cart Button', 'woocommerce-simple-buy-now' ),
		) );
	}

	/**
	 * Gets redirects.
	 *
	 * @return array
	 */
	public function get_redirects() {
		return apply_filters( 'woocommerce_simple_buy_get_redirects', array(
			'popup'    => esc_html__( 'Use pop-up', 'woocommerce-simple-buy-now' ),
			'checkout' => esc_html__( 'Redirect to the checkout page (skip the cart page)', 'woocommerce-simple-buy-now' ),
		) );
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
	 * @param  array $section section.
	 * @return array
	 */
	public function get_settings( $section = null ) {
		$settings = array();

		if ( '' == $section ) {
			return $this->get_general();
		} else {
			$settings = apply_filters( 'woocommerce_simple_buy_section_settings', array() );
		}

		return $settings;
	}

	/**
	 * Gets general settings.
	 *
	 * @return array
	 */
	public function get_general() {
		$settings_array = array();

		$settings_array[] = array(
			'name' => esc_html__( 'General Settings', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'desc' => esc_html__( 'The following options are used to configure WC Simple Buy Now Actions','woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_settings_start',
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Enable Simple Buy Now', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_enable',
			'type'     => 'checkbox',
			'default'  => 'yes',
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Redirect', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Use pop-up or redirect to the checkout page', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_redirect',
			'type'     => 'radio',
			'default'  => 'popup',
			'options'  => $this->get_redirects(),
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Simple Buy Now Button Position', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Where the button need to be added in single page .. before / after / replace', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_position',
			'type'     => 'select',
			'class'    => 'chosen_select',
			'default'  => 'before',
			'options'  => $this->get_positions(),
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'desc_tip' => esc_html__( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_button',
			'type'     => 'text',
			'default'  => esc_html__( 'Buy Now', 'woocommerce-simple-buy-now' ),
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Reset Cart before Buy Now', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_reset_cart',
			'type'     => 'checkbox',
		);

		$settings_array[] = array(
			'name'     => esc_html__( 'Remove Quantity input', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_remove_quantity',
			'type'     => 'checkbox',
		);

		$settings_array[] = array(
			'type' => 'sectionend',
			'id'   => 'woocommerce_simple_buy_settings_end',
		);

		return apply_filters( 'woocommerce_quick_buy_general_settings', $settings_array );
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
