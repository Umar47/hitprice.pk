<?php
/**
 * Theme setup and asset loading.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue child theme assets.
 */
function hitprice_enqueue_child_assets() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'hitprice-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		$theme->get( 'Version' )
	);

	wp_enqueue_style(
		'hitprice-header-footer',
		get_stylesheet_directory_uri() . '/assets/css/header-footer.css',
		array( 'hitprice-child-style' ),
		filemtime( get_stylesheet_directory() . '/assets/css/header-footer.css' )
	);

	wp_enqueue_script(
		'hitprice-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/js/header.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'hitprice_enqueue_child_assets', 20 );

/**
 * Register theme menu locations.
 */
function hitprice_register_theme_menus() {
	register_nav_menus(
		array(
			'hitprice_header_menu' => __( 'Hit Price Header Menu', 'hitprice' ),
			'hitprice_footer_menu' => __( 'Hit Price Footer Menu', 'hitprice' ),
		)
	);
}
add_action( 'after_setup_theme', 'hitprice_register_theme_menus' );
