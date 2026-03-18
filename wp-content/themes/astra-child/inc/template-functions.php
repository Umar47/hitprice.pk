<?php
/**
 * Template helper functions.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a template part from the child theme.
 *
 * @param string $slug Template slug.
 * @param array  $args Optional arguments.
 */
function hitprice_get_template_part( $slug, $args = array() ) {
	if ( ! empty( $args ) ) {
		set_query_var( 'hitprice_template_args', $args );
	}

	get_template_part( $slug );

	if ( ! empty( $args ) ) {
		set_query_var( 'hitprice_template_args', null );
	}
}

/**
 * Read template arguments in a part file.
 *
 * @return array
 */
function hitprice_get_template_args() {
	$args = get_query_var( 'hitprice_template_args', array() );

	return is_array( $args ) ? $args : array();
}

/**
 * Resolve product page urls safely.
 *
 * @param string $page WooCommerce page key.
 * @return string
 */
function hitprice_get_wc_page_url( $page ) {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( $page );
	}

	return home_url( '/' );
}

/**
 * Resolve cart page url safely.
 *
 * @return string
 */
function hitprice_get_cart_url() {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

/**
 * Return cart count.
 *
 * @return int
 */
function hitprice_get_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}

	return 0;
}

/**
 * Render the assigned header menu.
 */
function hitprice_render_header_menu() {
	wp_nav_menu(
		array(
			'theme_location' => 'hitprice_header_menu',
			'container'      => false,
			'menu_class'     => 'hitprice-menu',
			'fallback_cb'    => false,
		)
	);
}
