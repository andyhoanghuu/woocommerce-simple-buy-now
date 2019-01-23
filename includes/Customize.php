<?php

namespace WooCommerce_Simple_Buy_Now;

/**
 * Set up and initialize
 */
class Customize {
	/**
	 * Customize constructor.
	 */
	public function __construct() {
		if ( ! $this->is_customize() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_inline_style' ], 25 );
	}

	/**
	 * Is customize?
	 *
	 * @return bool
	 */
	public function is_customize() {
		return 'customize' === $this->get_button_style();
	}

	/**
	 * Gets button style.
	 *
	 * @return string
	 */
	public function get_button_style() {
		return get_option( 'woocommerce_simple_buy_customize', 'theme' );
	}

	/**
	 * Enqueue inline style.
	 */
	public function enqueue_inline_style() {
		if ( ! $styles = apply_filters( 'wsb_inline_style', trim( $this->get_custom_css() ) ) ) {
			return;
		}

		wp_add_inline_style( 'woocommerce-simple-buy-now', $styles );
	}

	/**
	 * Gets custom CSS.
	 *
	 * @return string
	 */
	public function get_custom_css() {
		ob_start();
		if ( $colors = $this->get_color_atts() ) {
			?>
			.wsb-button {<?php echo $colors; ?>}
			<?php
		}

		if ( $hover_colors = $this->get_hover_color_atts() ) {
			?>
			.wsb-button:hover {<?php echo $hover_colors; ?>}
			<?php
		}

		if ( $padding = $this->get_padding_atts() ) {
			?>
			.wsb-button {<?php echo $padding; ?>}
			<?php
		}

		if ( $margin = $this->get_margin_atts() ) {
			?>
			.wsb-button {<?php echo $margin; ?>}
			<?php
		}

		if ( $size = $this->get_size_atts() ) {
			?>
			.wsb-button {<?php echo $size; ?>}
			<?php
		}

		if ( $this->get_additional_css() ) {
			echo $this->get_additional_css();
		}

		$styles = ob_get_clean();

		return $styles;
	}

	/**
	 * Gets color attributes.
	 *
	 * @return string
	 */
	public function get_color_atts() {
		$colors = '';
		$colors .= $this->get_color() ? "color: {$this->get_color()} !important;" : '';
		$colors .= $this->get_background_color() ? "background-color: {$this->get_background_color()} !important;" : '';
		$colors .= $this->get_border_color() ? "border-color: {$this->get_border_color()} !important;" : '';

		return apply_filters( 'wsb_color_atts', $colors );
	}

	/**
	 * Gets hover color attributes.
	 *
	 * @return string
	 */
	public function get_hover_color_atts() {
		$hover_colors = '';
		$hover_colors .= $this->get_hover_color() ? "color: {$this->get_hover_color()} !important;" : '';
		$hover_colors .= $this->get_hover_background_color() ? "background-color: {$this->get_hover_background_color()} !important;" : '';
		$hover_colors .= $this->get_hover_border_color() ? "border-color: {$this->get_hover_border_color()} !important;" : '';

		return apply_filters( 'wsb_hover_color_atts', $hover_colors );
	}

	/**
	 * Gets padding attributes.
	 *
	 * @return string
	 */
	public function get_padding_atts() {
		if ( ! $padding_option = $this->get_padding() ) {
			return '';
		}

		$padding_option = $this->parse_dimensions_option( $padding_option );

		$padding = '';
		$padding .= $this->isset_option( $padding_option['top'] ) ? "padding-top: {$padding_option['top']}{$padding_option['unit']} !important;" : '';
		$padding .= $this->isset_option( $padding_option['right'] ) ? "padding-right: {$padding_option['right']}{$padding_option['unit']} !important;" : '';
		$padding .= $this->isset_option( $padding_option['bottom'] ) ? "padding-bottom: {$padding_option['bottom']}{$padding_option['unit']} !important;" : '';
		$padding .= $this->isset_option( $padding_option['left'] ) ? "padding-left: {$padding_option['left']}{$padding_option['unit']} !important;" : '';

		return apply_filters( 'wsb_padding_atts', $padding );
	}

	/**
	 * Gets margin attributes.
	 *
	 * @return string
	 */
	public function get_margin_atts() {
		if ( ! $margin_option = $this->get_margin() ) {
			return '';
		}

		$margin_option = $this->parse_dimensions_option( $margin_option );

		$margin = '';
		$margin .= $this->isset_option( $margin_option['top'] ) ? "margin-top: {$margin_option['top']}{$margin_option['unit']} !important;" : '';
		$margin .= $this->isset_option( $margin_option['right'] ) ? "margin-right: {$margin_option['right']}{$margin_option['unit']} !important;" : '';
		$margin .= $this->isset_option( $margin_option['bottom'] ) ? "margin-bottom: {$margin_option['bottom']}{$margin_option['unit']} !important;" : '';
		$margin .= $this->isset_option( $margin_option['left'] ) ? "margin-left: {$margin_option['left']}{$margin_option['unit']} !important;" : '';

		return apply_filters( 'wsb_margin_atts', $margin );
	}

	/**
	 * Gets size attributes.
	 *
	 * @return string
	 */
	public function get_size_atts() {
		$width  = $this->parse_sizes_option( $this->get_width() );
		$height = $this->parse_sizes_option( $this->get_height() );

		$size = '';
		if ( $width ) {
			$size .= $this->isset_option( $width['size'] ) ? "width: {$width['size']}{$width['unit']} !important;" : '';
		}

		if ( $height ) {
			$size .= $this->isset_option( $height['size'] ) ? "height: {$height['size']}{$height['unit']} !important;" : '';
		}

		return apply_filters( 'wsb_size_atts', $size );
	}

	/**
	 * Gets normal color.
	 *
	 * @return string
	 */
	public function get_color() {
		return get_option( 'woocommerce_simple_buy_button_color', '' );
	}

	/**
	 * Gets normal background color.
	 *
	 * @return string
	 */
	public function get_background_color() {
		return get_option( 'woocommerce_simple_buy_button_bgcolor', '' );
	}

	/**
	 * Gets border color.
	 *
	 * @return string
	 */
	public function get_border_color() {
		return get_option( 'woocommerce_simple_buy_button_border_color', '' );
	}

	/**
	 * Gets hover color.
	 *
	 * @return string
	 */
	public function get_hover_color() {
		return get_option( 'woocommerce_simple_buy_button_hover_color', '' );
	}

	/**
	 * Gets hover background color.
	 *
	 * @return string
	 */
	public function get_hover_background_color() {
		return get_option( 'woocommerce_simple_buy_button_hover_bgcolor', '' );
	}

	/**
	 * Gets hover border color.
	 *
	 * @return string
	 */
	public function get_hover_border_color() {
		return get_option( 'woocommerce_simple_buy_button_hover_border_color', '' );
	}

	/**
	 * Gets padding.
	 *
	 * @return string
	 */
	public function get_padding() {
		return get_option( 'woocommerce_simple_buy_button_padding', '' );
	}

	/**
	 * Gets margin.
	 *
	 * @return string
	 */
	public function get_margin() {
		return get_option( 'woocommerce_simple_buy_button_margin', '' );
	}

	/**
	 * Gets width.
	 *
	 * @return string
	 */
	public function get_width() {
		return get_option( 'woocommerce_simple_buy_button_width', '' );
	}

	/**
	 * Gets height.
	 *
	 * @return string
	 */
	public function get_height() {
		return get_option( 'woocommerce_simple_buy_button_height', '' );
	}

	/**
	 * Gets additional CSS.
	 *
	 * @return string
	 */
	public function get_additional_css() {
		return apply_filters( 'wsb_additional_css',
			trim( get_option( 'woocommerce_simple_buy_button_additional_css', '' ) ) );
	}

	/**
	 * Parse dimensions option.
	 *
	 * @param array $option Option.
	 *
	 * @return array
	 */
	protected function parse_dimensions_option( $option ) {
		return wp_parse_args( $option, [
			'top'    => '',
			'right'  => '',
			'bottom' => '',
			'left'   => '',
			'unit'   => 'px',
		] );
	}

	/**
	 * Parse size option.
	 *
	 * @param array $option Option.
	 *
	 * @return array
	 */
	protected function parse_sizes_option( $option ) {
		return wp_parse_args( $option, [
			'size' => '',
			'unit' => 'px',
		] );
	}

	/**
	 * Isset option?
	 *
	 * @param string $option Option.
	 *
	 * @return bool
	 */
	protected function isset_option( $option ) {
		return isset( $option ) && ( '' !== $option );
	}
}
