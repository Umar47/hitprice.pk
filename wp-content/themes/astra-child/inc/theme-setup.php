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

	if ( is_page_template( 'template-homepage.php' ) ) {
		wp_enqueue_style(
			'hitprice-front-page',
			get_stylesheet_directory_uri() . '/assets/css/front-page.css',
			array( 'hitprice-header-footer' ),
			filemtime( get_stylesheet_directory() . '/assets/css/front-page.css' )
		);
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		// Deferred product sections CSS with print/onload pattern.
		wp_enqueue_style(
			'hitprice-product-sections',
			get_stylesheet_directory_uri() . '/assets/css/product-sections.css',
			array( 'hitprice-header-footer' ),
			filemtime( get_stylesheet_directory() . '/assets/css/product-sections.css' ),
			'print'
		);

		wp_enqueue_script(
			'hitprice-single-product',
			get_stylesheet_directory_uri() . '/assets/js/single-product.js',
			array( 'jquery' ),
			filemtime( get_stylesheet_directory() . '/assets/js/single-product.js' ),
			true
		);
	}

	if ( function_exists( 'is_woocommerce' ) && ( is_shop() || is_product_taxonomy() ) ) {
		wp_enqueue_style(
			'hitprice-shop-archive',
			get_stylesheet_directory_uri() . '/assets/css/shop-archive.css',
			array( 'hitprice-header-footer' ),
			filemtime( get_stylesheet_directory() . '/assets/css/shop-archive.css' )
		);

		wp_enqueue_script(
			'hitprice-shop-archive',
			get_stylesheet_directory_uri() . '/assets/js/shop-archive.js',
			array(),
			filemtime( get_stylesheet_directory() . '/assets/js/shop-archive.js' ),
			true
		);

		wp_localize_script(
			'hitprice-shop-archive',
			'hitpriceShopArchive',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'hitprice_shop_archive' ),
				'action'  => 'hitprice_filter_products',
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hitprice_enqueue_child_assets', 20 );

/**
 * Inline critical product CSS in <head>.
 */
function hitprice_inline_product_critical_css() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	$css_file = get_stylesheet_directory() . '/assets/css/product-hero-critical.css';

	if ( ! file_exists( $css_file ) ) {
		return;
	}

	$css = file_get_contents( $css_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( $css ) {
		echo '<style id="hp-product-critical">' . $css . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'hitprice_inline_product_critical_css', 99 );

/**
 * Add onload attribute to deferred product sections stylesheet.
 *
 * @param string $html Link tag HTML.
 * @param string $handle Stylesheet handle.
 * @return string
 */
function hitprice_deferred_product_css_onload( $html, $handle ) {
	if ( 'hitprice-product-sections' === $handle ) {
		$html = str_replace(
			"media='print'",
			"media='print' onload=\"this.media='all'\"",
			$html
		);
	}
	return $html;
}
add_filter( 'style_loader_tag', 'hitprice_deferred_product_css_onload', 10, 2 );

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

/**
 * Register shop widget areas.
 */
function hitprice_register_widget_areas() {
	register_sidebar(
		array(
			'name'          => __( 'Hit Price Shop Filters', 'hitprice' ),
			'id'            => 'hitprice-shop-filters',
			'description'   => __( 'Widgets shown in the WooCommerce shop filter sidebar.', 'hitprice' ),
			'before_widget' => '<section id="%1$s" class="widget hitprice-filter-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title hitprice-filter-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'hitprice_register_widget_areas' );

/**
 * Force full-width, no-sidebar layout on single product pages.
 *
 * Astra's default boxed/sidebar layout constrains the content area,
 * preventing the product grid from using the full viewport width.
 */
function hitprice_force_product_fullwidth_layout( $layout ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'no-sidebar';
	}
	return $layout;
}
add_filter( 'astra_page_layout', 'hitprice_force_product_fullwidth_layout' );

/**
 * Force full-width content style on single product pages.
 *
 * @param string $layout Content layout.
 * @return string
 */
function hitprice_force_product_content_layout( $layout ) {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'page-builder';
	}
	return $layout;
}
add_filter( 'astra_get_content_layout', 'hitprice_force_product_content_layout' );
