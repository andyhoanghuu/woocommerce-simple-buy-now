<?php
/**
 * Gets CSS units.
 *
 * @return array
 */
function wsb_get_css_units() {
	$units = apply_filters( 'wsb_dimensions_units', [
		'px'  => __( 'px', 'woocommerce-simple-buy-now' ),
		'em'  => __( 'em', 'woocommerce-simple-buy-now' ),
		'rem' => __( 'rem', 'woocommerce-simple-buy-now' ),
	] );

	return is_array( $units ) && ! empty( $units ) ? $units : [];
}

