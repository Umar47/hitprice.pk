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
	}

	// Remove Astra builder footer when the header/footer builder is active.
	if ( class_exists( 'Astra_Builder_Footer' ) ) {
		remove_action( 'astra_footer', array( Astra_Builder_Footer::get_instance(), 'footer_markup' ), 10 );
	}

	add_action( 'astra_footer', 'hitprice_render_site_footer' );
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

/**
 * Register single product page hooks.
 */
function hitprice_register_product_hooks() {
	if ( ! is_product() ) {
		return;
	}

	// Wrap gallery + summary in grid layout.
	// Opens inside <div class="product">, before gallery renders.
	// Closes after summary, before tabs/related.
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_product_layout_open', 1 );
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_product_layout_close', 1 );

	// Remove unwanted summary hooks.
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	// Add custom summary sections.
	add_action( 'woocommerce_single_product_summary', 'hp_render_product_addons_ui', 30 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_product_tradein_block', 35 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_product_payment_options', 40 );

	// Remove default related products and upsells from after summary.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );

	// Add custom after-summary sections.
	add_action( 'woocommerce_after_single_product', 'hp_render_product_compare', 10 );
	add_action( 'woocommerce_after_single_product', 'hp_render_product_features', 20 );
	add_action( 'woocommerce_after_single_product', 'hp_render_product_accordions', 30 );
	add_action( 'woocommerce_after_single_product', 'hp_render_product_detail_specs', 40 );

	// Replace tab UI with accordions — keep data, change rendering.
	add_filter( 'woocommerce_product_tabs', 'hp_capture_product_tabs_for_accordions', 98 );

	// Sticky bar in footer.
	add_action( 'wp_footer', 'hp_render_product_sticky_bar' );
}
add_action( 'wp', 'hitprice_register_product_hooks', 20 );

/**
 * Capture product tabs data for accordion rendering, then suppress default tab output.
 *
 * @param array $tabs Product tabs.
 * @return array Empty array to suppress default tab UI.
 */
function hp_capture_product_tabs_for_accordions( $tabs ) {
	global $hp_product_tabs;
	$hp_product_tabs = $tabs;

	return array();
}
