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
		$this->show_position = array(
			'before'  => __( 'Before Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'after'   => __( 'After Add To Cart Button', 'woocommerce-simple-buy-now' ),
			'replace' => __( 'Replace Add To Cart Button', 'woocommerce-simple-buy-now' ),
		);

		$this->id    = 'wc_simple_buy_settings';
		$this->label = __( 'WC Simple Buy Now', 'woocommerce-simple-buy-now' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_filter( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_filter( 'woocommerce_settings_' . $this->id, array( $this, 'output_settings' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
	}

	/**
	 * Get sections
	 */
	public function get_sections() {
		$sections = array(
		    ''            => __( 'General', 'woocommerce-simple-buy-now' ),
		);
		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
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
	 * Get settings.
	 * @param  array $section section
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


	public function get_general(){
		$settings_array = array();

		$settings_array[] = array(
			'name' => __( 'General Settings', 'woocommerce-simple-buy-now' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure WC Simple Buy Now Actions','woocommerce-simple-buy-now' ),
			'id'   => 'woocommerce_simple_buy_settings_start',
		);

		$settings_array[] = array(
			'name'     => __( 'Enable Simple Buy Now', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_enable',
			'type'     => 'checkbox',
		);

		$settings_array[] = array(
			'name'     => __( 'Simple Buy Now Button Position', 'woocommerce-simple-buy-now' ),
			'desc_tip' => __( 'Where the button need to be added in single page .. before / after / replace', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_position',
			'type'     => 'select',
			'class'    => 'chosen_select',
			'options'  => $this->show_position,
		);

		$settings_array[] = array(
			'name'     => __( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'desc_tip' => __( 'Simple Buy Button Title', 'woocommerce-simple-buy-now' ),
			'id'       => 'woocommerce_simple_buy_single_product_button',
			'type'     => 'text',
			'default'  => __( 'Buy Now', 'woocommerce-simple-buy-now' ),
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
