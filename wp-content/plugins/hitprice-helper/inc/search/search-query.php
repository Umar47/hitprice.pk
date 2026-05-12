<?php
/**
 * Multi-pass product search query builder.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the cache key for a normalized term + limit + context.
 *
 * @param string $normalized Normalized term.
 * @param int    $limit      Result limit.
 * @param string $context    'suggest' | 'full'.
 * @return string
 */
function hp_search_cache_key( $normalized, $limit, $context ) {
	return 'hp_search_' . $context . '_' . md5( $normalized . '|' . (int) $limit );
}

/**
 * Run a multi-pass relevancy product search.
 *
 * Passes are unioned in order — earlier passes rank higher.
 *
 *   1. Exact title match
 *   2. Title LIKE (starts with)
 *   3. Title LIKE (contains)
 *   4. SKU exact / partial
 *   5. Tag and category match
 *   6. Split-word intersection (every word appears in title)
 *   7. Content / excerpt fallback
 *
 * @param string $term    Raw search term.
 * @param int    $limit   Max results.
 * @param string $context 'suggest' | 'full'.
 * @return int[] Ranked product IDs.
 */
function hp_search_products( $term, $limit = 6, $context = 'suggest' ) {
	$term  = trim( wp_strip_all_tags( (string) $term ) );
	$limit = max( 1, min( 50, (int) $limit ) );

	if ( mb_strlen( $term ) < 2 ) {
		return array();
	}

	$normalized = hp_search_normalize_term( $term );
	$cache_key  = hp_search_cache_key( $normalized, $limit, $context );

	$cached = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;

	$like      = '%' . $wpdb->esc_like( $term ) . '%'; // contains
	$like_pre  = $wpdb->esc_like( $term ) . '%';       // starts with
	$exact     = $term;
	$ids       = array();
	$max_fetch = $limit * 3; // Pull a buffer; we dedupe and trim.

	// Common visibility filter via post_status only — _visibility taxonomy filter
	// is applied at output time using wc_products_array_filter_visible.
	$post_status = "p.post_status = 'publish'";

	// Pass 1: exact title match.
	$pass1 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			WHERE p.post_type = 'product' AND {$post_status}
				AND p.post_title = %s
			LIMIT %d",
			$exact,
			$max_fetch
		)
	);
	$ids = hp_search_merge_ids( $ids, $pass1, $limit );
	if ( count( $ids ) >= $limit ) {
		return hp_search_finalize_ids( $ids, $limit, $cache_key );
	}

	// Pass 2: title LIKE starts-with.
	$pass2 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			WHERE p.post_type = 'product' AND {$post_status}
				AND p.post_title LIKE %s
			ORDER BY p.menu_order ASC, p.post_date DESC
			LIMIT %d",
			$like_pre,
			$max_fetch
		)
	);
	$ids = hp_search_merge_ids( $ids, $pass2, $limit );
	if ( count( $ids ) >= $limit ) {
		return hp_search_finalize_ids( $ids, $limit, $cache_key );
	}

	// Pass 3: title contains.
	$pass3 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			WHERE p.post_type = 'product' AND {$post_status}
				AND p.post_title LIKE %s
			ORDER BY p.menu_order ASC, p.post_date DESC
			LIMIT %d",
			$like,
			$max_fetch
		)
	);
	$ids = hp_search_merge_ids( $ids, $pass3, $limit );
	if ( count( $ids ) >= $limit ) {
		return hp_search_finalize_ids( $ids, $limit, $cache_key );
	}

	// Pass 4: SKU match.
	$pass4 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID AND pm.meta_key = '_sku'
			WHERE p.post_type IN ('product','product_variation') AND {$post_status}
				AND pm.meta_value LIKE %s
			LIMIT %d",
			$like,
			$max_fetch
		)
	);
	// SKU pass may include variations — map back to parent product IDs.
	$pass4 = hp_search_resolve_parents( $pass4 );
	$ids   = hp_search_merge_ids( $ids, $pass4, $limit );
	if ( count( $ids ) >= $limit ) {
		return hp_search_finalize_ids( $ids, $limit, $cache_key );
	}

	// Pass 5: tag / category match.
	$pass5 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT p.ID FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->term_relationships} tr ON tr.object_id = p.ID
			INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
			INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
			WHERE p.post_type = 'product' AND {$post_status}
				AND tt.taxonomy IN ('product_cat','product_tag')
				AND t.name LIKE %s
			ORDER BY p.menu_order ASC, p.post_date DESC
			LIMIT %d",
			$like,
			$max_fetch
		)
	);
	$ids = hp_search_merge_ids( $ids, $pass5, $limit );
	if ( count( $ids ) >= $limit ) {
		return hp_search_finalize_ids( $ids, $limit, $cache_key );
	}

	// Pass 6: split-word intersection — every word in the query must appear in the title.
	// Handles "s24 ultra" matching "Samsung Galaxy S24 Ultra".
	$words = hp_search_split_words( $normalized );
	if ( count( $words ) >= 2 ) {
		$where_parts = array();
		$args        = array();
		foreach ( $words as $word ) {
			$where_parts[] = 'p.post_title LIKE %s';
			$args[]        = '%' . $wpdb->esc_like( $word ) . '%';
		}
		$args[]   = $max_fetch;
		$where_sql = implode( ' AND ', $where_parts );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$pass6 = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID FROM {$wpdb->posts} p
				WHERE p.post_type = 'product' AND {$post_status}
					AND ({$where_sql})
				ORDER BY p.menu_order ASC, p.post_date DESC
				LIMIT %d",
				$args
			)
		);
		$ids = hp_search_merge_ids( $ids, $pass6, $limit );
		if ( count( $ids ) >= $limit ) {
			return hp_search_finalize_ids( $ids, $limit, $cache_key );
		}
	}

	// Pass 7: content / excerpt fallback.
	$pass7 = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			WHERE p.post_type = 'product' AND {$post_status}
				AND ( p.post_excerpt LIKE %s OR p.post_content LIKE %s )
			ORDER BY p.menu_order ASC, p.post_date DESC
			LIMIT %d",
			$like,
			$like,
			$max_fetch
		)
	);
	$ids = hp_search_merge_ids( $ids, $pass7, $limit );

	return hp_search_finalize_ids( $ids, $limit, $cache_key );
}

/**
 * Split a normalized term into meaningful words (min 2 chars each).
 *
 * @param string $normalized Already-normalized term.
 * @return string[]
 */
function hp_search_split_words( $normalized ) {
	$words = preg_split( '/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY );
	return array_values(
		array_filter(
			$words,
			static function ( $w ) {
				return mb_strlen( $w ) >= 2;
			}
		)
	);
}

/**
 * Merge new IDs into the running ranked list, dedup, cap at limit buffer.
 *
 * @param int[] $existing Current list.
 * @param array $incoming Raw IDs.
 * @param int   $limit    Cap.
 * @return int[]
 */
function hp_search_merge_ids( $existing, $incoming, $limit ) {
	if ( empty( $incoming ) ) {
		return $existing;
	}
	foreach ( $incoming as $raw_id ) {
		$id = (int) $raw_id;
		if ( $id <= 0 ) {
			continue;
		}
		if ( in_array( $id, $existing, true ) ) {
			continue;
		}
		$existing[] = $id;
		if ( count( $existing ) >= $limit * 2 ) {
			break;
		}
	}
	return $existing;
}

/**
 * Resolve product_variation IDs to their parent product IDs.
 *
 * @param int[] $ids Raw IDs (mix of products and variations).
 * @return int[]
 */
function hp_search_resolve_parents( $ids ) {
	$ids = array_filter( array_map( 'intval', (array) $ids ) );
	if ( empty( $ids ) ) {
		return array();
	}
	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_parent, post_type FROM {$wpdb->posts} WHERE ID IN ({$placeholders})",
			$ids
		)
	);
	$out = array();
	foreach ( $rows as $row ) {
		$parent = ( 'product_variation' === $row->post_type && (int) $row->post_parent > 0 ) ? (int) $row->post_parent : (int) $row->ID;
		if ( $parent > 0 ) {
			$out[] = $parent;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Filter visible products, cap, cache, and return final list.
 *
 * @param int[]  $ids       Ranked IDs.
 * @param int    $limit     Cap.
 * @param string $cache_key Transient key.
 * @return int[]
 */
function hp_search_finalize_ids( $ids, $limit, $cache_key ) {
	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );

	// Filter to visible/purchasable products and respect catalog visibility.
	$visible = array();
	foreach ( $ids as $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $id ) : null;
		if ( $product instanceof WC_Product && $product->is_visible() ) {
			$visible[] = $id;
			if ( count( $visible ) >= $limit ) {
				break;
			}
		}
	}

	$visible = array_slice( $visible, 0, $limit );
	set_transient( $cache_key, $visible, 5 * MINUTE_IN_SECONDS );

	return $visible;
}

/**
 * Build a lightweight payload for the suggestion overlay.
 *
 * @param int[] $product_ids Product IDs.
 * @return array
 */
function hp_search_format_products( $product_ids ) {
	$out = array();
	foreach ( (array) $product_ids as $id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( (int) $id ) : null;
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$image_id  = (int) $product->get_image_id();
		$thumb_url = $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_gallery_thumbnail' ) : '';
		if ( ! $thumb_url ) {
			$thumb_url = function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) : '';
		}

		// Primary category name (cheapest: already loaded via term cache).
		$category = '';
		$cat_ids  = $product->get_category_ids();
		if ( ! empty( $cat_ids ) ) {
			$term = get_term( (int) $cat_ids[0], 'product_cat' );
			if ( $term instanceof WP_Term ) {
				$category = $term->name;
			}
		}

		$out[] = array(
			'id'       => (int) $product->get_id(),
			'title'    => wp_strip_all_tags( $product->get_name() ),
			'url'      => get_permalink( $product->get_id() ),
			'price'    => wp_kses(
				$product->get_price_html(),
				array(
					'del'  => array( 'aria-hidden' => array() ),
					'ins'  => array(),
					'span' => array( 'class' => array() ),
					'bdi'  => array(),
				)
			),
			'image'    => esc_url_raw( $thumb_url ),
			'sku'      => (string) $product->get_sku(),
			'category' => $category,
			'in_stock' => $product->is_in_stock(),
		);
	}
	return $out;
}

/**
 * Get related search-term suggestions based on past searches matching a prefix.
 *
 * @param string $term  Raw term.
 * @param int    $limit Max suggestions.
 * @return string[]
 */
function hp_search_term_suggestions( $term, $limit = 4 ) {
	$normalized = hp_search_normalize_term( $term );
	$limit      = max( 1, (int) $limit );
	if ( mb_strlen( $normalized ) < 2 ) {
		return array();
	}

	global $wpdb;
	$table = hp_search_table_name();
	$like  = $wpdb->esc_like( $normalized ) . '%';

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT MAX(term) AS term, COUNT(*) AS hits
			FROM {$table}
			WHERE normalized_term LIKE %s
				AND normalized_term <> %s
				AND results_count > 0
			GROUP BY normalized_term
			ORDER BY hits DESC
			LIMIT %d",
			$like,
			$normalized,
			$limit
		)
	);

	$out = array();
	foreach ( (array) $rows as $row ) {
		$value = isset( $row->term ) ? trim( (string) $row->term ) : '';
		if ( '' !== $value ) {
			$out[] = $value;
		}
	}
	return $out;
}
