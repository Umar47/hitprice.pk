<?php
/**
 * Product data helper functions.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get compare products for a given product.
 *
 * Fallback chain: same category → cross-sells → latest products.
 * Results are transient cached for 12 hours per product.
 *
 * @param int $product_id Product ID.
 * @param int $limit      Number of products to return.
 * @return WC_Product[]
 */
function hp_get_compare_products( $product_id, $limit = 4 ) {
	$transient_key = 'hp_compare_' . $product_id . '_' . $limit;
	$cached        = get_transient( $transient_key );

	if ( false !== $cached ) {
		return $cached;
	}

	$products = array();

	// Try same category products first.
	$terms = wc_get_product_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

	if ( ! empty( $terms ) ) {
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $product_id ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $terms,
				),
			),
			'orderby'        => 'rand',
			'fields'         => 'ids',
		);

		$product_ids = get_posts( $args );

		if ( ! empty( $product_ids ) ) {
			foreach ( $product_ids as $pid ) {
				$product = wc_get_product( $pid );
				if ( $product ) {
					$products[] = $product;
				}
			}
		}
	}

	// Fallback to cross-sells.
	if ( count( $products ) < $limit ) {
		$product_obj  = wc_get_product( $product_id );
		$cross_sells  = $product_obj ? $product_obj->get_cross_sell_ids() : array();
		$existing_ids = wp_list_pluck( $products, 'id' );
		$existing_ids = array_map( 'intval', $existing_ids );

		foreach ( $cross_sells as $cs_id ) {
			if ( count( $products ) >= $limit ) {
				break;
			}
			if ( in_array( $cs_id, $existing_ids, true ) || $cs_id === $product_id ) {
				continue;
			}
			$product = wc_get_product( $cs_id );
			if ( $product && $product->is_visible() ) {
				$products[] = $product;
			}
		}
	}

	// Fallback to latest products.
	if ( count( $products ) < $limit ) {
		$existing_ids   = wp_list_pluck( $products, 'id' );
		$existing_ids   = array_map( 'intval', $existing_ids );
		$existing_ids[] = $product_id;
		$needed         = $limit - count( $products );

		$latest_ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => $needed,
				'post__not_in'   => $existing_ids,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		foreach ( $latest_ids as $lid ) {
			$product = wc_get_product( $lid );
			if ( $product ) {
				$products[] = $product;
			}
		}
	}

	set_transient( $transient_key, $products, 12 * HOUR_IN_SECONDS );

	return $products;
}

/**
 * Get product feature cards from ACF repeater.
 *
 * @param int $product_id Product ID.
 * @return array
 */
function hp_get_product_features( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$features = get_field( 'hp_feature_cards', $product_id );

	if ( ! is_array( $features ) ) {
		return array();
	}

	return array_slice( $features, 0, 3 );
}

/**
 * Get product detail specs from ACF flexible content.
 *
 * @param int $product_id Product ID.
 * @return array
 */
function hp_get_product_detail_specs( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$specs = get_field( 'hp_detail_specs', $product_id );

	if ( ! is_array( $specs ) ) {
		return array();
	}

	return array_slice( $specs, 0, 10 );
}

/**
 * Clear product compare transients on save.
 *
 * @param int $product_id Product ID.
 */
function hp_clear_product_transients( $product_id ) {
	if ( 'product' !== get_post_type( $product_id ) ) {
		return;
	}

	// Clear common limit values.
	delete_transient( 'hp_compare_' . $product_id . '_4' );
	delete_transient( 'hp_compare_' . $product_id . '_3' );
}
add_action( 'save_post_product', 'hp_clear_product_transients' );
