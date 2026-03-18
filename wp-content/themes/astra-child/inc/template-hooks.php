<?php
/**
 * Hook integration with Astra.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace Astra header/footer markup with child theme parts.
 */
function hitprice_register_template_hooks() {
	if ( function_exists( 'astra_header_markup' ) ) {
		remove_action( 'astra_header', 'astra_header_markup' );
		add_action( 'astra_header', 'hitprice_render_site_header' );
	}

	if ( function_exists( 'astra_footer_markup' ) ) {
		remove_action( 'astra_footer', 'astra_footer_markup' );
		add_action( 'astra_footer', 'hitprice_render_site_footer' );
	}
}
add_action( 'wp', 'hitprice_register_template_hooks', 20 );

/**
 * Render child theme header.
 */
function hitprice_render_site_header() {
	hitprice_get_template_part( 'template-parts/header/header' );
}

/**
 * Render child theme footer.
 */
function hitprice_render_site_footer() {
	hitprice_get_template_part( 'template-parts/footer/footer' );
}
