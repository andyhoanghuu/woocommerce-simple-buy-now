<?php

namespace WooCommerce_Simple_Buy_Now\Admin;

/**
 * Settings
 */
class Size_Field {
	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_admin_field_wsb_size', [ $this, 'output' ] );
	}

	/**
	 * Output size field.
	 *
	 * @param string|array $value
	 */
	public function output( $value ) {
		// Description handling.
		$field_description = \WC_Admin_Settings::get_field_description( $value );
		$description       = $field_description['description'];
		$tooltip_html      = $field_description['tooltip_html'];
		$option_value      = $this->parse_option( \WC_Admin_Settings::get_option( $value['id'], $value['default'] ) );
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?><?php echo $tooltip_html; // WPCS: XSS ok. ?></label>
			</th>
			<td class="forminp">
				<input
					name="<?php echo esc_attr( $value['id'] ); ?>[size]"
					id="<?php echo esc_attr( $value['id'] ); ?>_size"
					type="number"
					style="width: 60px;"
					value="<?php echo esc_attr( $option_value['size'] ); ?>"
					class="<?php echo esc_attr( $value['class'] ); ?>"
					placeholder="<?php echo esc_attr( $value['placeholder'] ); ?>"
					step="1"
					min="0"
				/>

				<select name="<?php echo esc_attr( $value['id'] ); ?>[unit]" style="width: auto;">
					<?php
					foreach ( wsb_get_css_units() as $unit_value => $label ) {
						echo '<option value="' . esc_attr( $unit_value ) . '"' . selected( $option_value['unit'], $unit_value, false ) . '>' . esc_html( $label ) . '</option>';
					}
					?>
				</select>

				<?php echo ( $description ) ? '<span class="description">' . $description . '</span>' : ''; // WPCS: XSS ok. ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Parse a size option from the settings API into a standard format.
	 *
	 * @param mixed $raw_value Value stored in DB.
	 *
	 * @return array Nicely formatted array with number and unit values.
	 */
	public function parse_option( $raw_value ) {
		$value = wp_parse_args( (array) $raw_value, [
			'size' => '',
			'unit' => 'px',
		] );

		$value['size'] = isset( $value['size'] ) ? $value['size'] : '';

		if ( ! array_key_exists( $value['unit'], wsb_get_css_units() ) ) {
			$value['unit'] = 'px';
		}

		return $value;
	}
}
