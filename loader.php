<?php
/**
 * The loader file.
 *
 * @package WooCommerce_Simple_Buy_Now
 */

/**
 * First, we need autoload via Composer to make everything works.
 */
require_once trailingslashit( __DIR__ ) . 'vendor/autoload.php';

/**
 * Then, require the main class.
 */
require_once trailingslashit( __DIR__ ) . 'includes/functions.php';
require_once trailingslashit( __DIR__ ) . 'includes/Plugin.php';

/**
 * Alias the class "WooCommerce_Simple_Buy_Now\Plugin" to "WooCommerce_Simple_Buy_Now".
 */
class_alias( \WooCommerce_Simple_Buy_Now\Plugin::class, 'WooCommerce_Simple_Buy_Now', false );
