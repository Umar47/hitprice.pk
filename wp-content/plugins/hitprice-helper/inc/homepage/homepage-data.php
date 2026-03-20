<?php
/**
 * Homepage data helpers.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return homepage flexible sections.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function hp_get_homepage_sections( $post_id = 0 ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$post_id  = $post_id ? absint( $post_id ) : get_the_ID();
	$sections = get_field( 'home_sections', $post_id );

	return is_array( $sections ) ? $sections : array();
}

/**
 * Resolve homepage flexible template path.
 *
 * @param string $layout Layout name.
 * @return string
 */
function hp_get_homepage_section_template( $layout ) {
	$templates = array(
		'hero_section'                => 'template-parts/home/flexible/hero',
		'featured_categories_section' => 'template-parts/home/flexible/featured-categories',
		'product_block_section'       => 'template-parts/home/flexible/product-block',
		'promo_banner_section'        => 'template-parts/home/flexible/promo-banner',
		'usp_section'                 => 'template-parts/home/flexible/usp',
		'preview_tiles_section'       => 'template-parts/home/flexible/preview-tiles',
		'campaign_tiles_section'      => 'template-parts/home/flexible/campaign-tiles',
		'trust_section'               => 'template-parts/home/flexible/trust',
	);

	return isset( $templates[ $layout ] ) ? $templates[ $layout ] : '';
}

/**
 * Get homepage products for a product block section.
 *
 * @param array $section Section data.
 * @return array
 */
function hp_get_homepage_product_block_products( $section ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$source_type = isset( $section['source_type'] ) ? sanitize_key( $section['source_type'] ) : 'latest';
	$limit       = isset( $section['products_limit'] ) ? max( 1, min( 12, absint( $section['products_limit'] ) ) ) : 8;

	if ( 'manual' === $source_type ) {
		$product_ids = isset( $section['manual_products'] ) ? array_filter( array_map( 'absint', (array) $section['manual_products'] ) ) : array();

		if ( empty( $product_ids ) ) {
			return array();
		}

		return wc_get_products(
			array(
				'include' => $product_ids,
				'status'  => 'publish',
				'limit'   => count( $product_ids ),
				'orderby' => 'post__in',
				'return'  => 'objects',
			)
		);
	}

	$cache_key = 'hp_home_products_' . md5( wp_json_encode( array( $source_type, $limit, $section ) ) );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached && is_array( $cached ) ) {
		return array_map( 'wc_get_product', $cached );
	}

	$args = array(
		'status' => 'publish',
		'limit'  => $limit,
		'return' => 'objects',
	);

	if ( 'featured' === $source_type ) {
		$args['featured'] = true;
	} elseif ( 'latest' === $source_type ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	} elseif ( 'category' === $source_type ) {
		$category_id = isset( $section['query_category'] ) ? absint( $section['query_category'] ) : 0;

		if ( $category_id > 0 ) {
			$term = get_term( $category_id, 'product_cat' );

			if ( $term instanceof WP_Term ) {
				$args['category'] = array( $term->slug );
			}
		}

		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	$products = wc_get_products( $args );
	$product_ids = array();

	foreach ( $products as $product ) {
		if ( $product instanceof WC_Product ) {
			$product_ids[] = $product->get_id();
		}
	}

	set_transient( $cache_key, $product_ids, HOUR_IN_SECONDS );

	return $products;
}

/**
 * Get homepage product block CTA URL.
 *
 * @param array $section Section data.
 * @return string
 */
function hp_get_homepage_product_block_cta_url( $section ) {
	if ( ! empty( $section['cta_url'] ) ) {
		return esc_url_raw( $section['cta_url'] );
	}

	if ( 'category' === ( $section['source_type'] ?? '' ) && ! empty( $section['query_category'] ) ) {
		$term_link = get_term_link( (int) $section['query_category'], 'product_cat' );

		if ( ! is_wp_error( $term_link ) ) {
			return $term_link;
		}
	}

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/shop/' );
}
