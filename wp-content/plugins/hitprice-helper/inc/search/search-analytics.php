<?php
/**
 * Search analytics logging and query helpers.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a search term for grouping (lowercase, collapse whitespace).
 *
 * @param string $term Raw term.
 * @return string
 */
function hp_search_normalize_term( $term ) {
	$term = strtolower( wp_strip_all_tags( (string) $term ) );
	$term = preg_replace( '/\s+/', ' ', $term );
	return trim( $term );
}

/**
 * Get a privacy-friendly hash of the visitor IP.
 *
 * @return string
 */
function hp_search_ip_hash() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$ip = sanitize_text_field( $ip );
	if ( '' === $ip ) {
		return '';
	}
	return wp_hash( $ip );
}

/**
 * Get a per-session hash so anonymous repeat searches can be grouped without storing PII.
 *
 * @return string
 */
function hp_search_session_hash() {
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
	$ua = sanitize_text_field( $ua );
	return wp_hash( $ua . '|' . hp_search_ip_hash() );
}

/**
 * Check if analytics logging is enabled.
 *
 * @return bool
 */
function hp_search_logging_enabled() {
	return (bool) get_option( 'hp_search_logging_enabled', 1 );
}

/**
 * Log a search event.
 *
 * @param string $term          Raw search term.
 * @param int    $results_count Number of products returned.
 * @return int Inserted row ID, or 0 on failure.
 */
function hp_log_search( $term, $results_count ) {
	if ( ! hp_search_logging_enabled() ) {
		return 0;
	}

	$term = sanitize_text_field( (string) $term );
	if ( '' === $term ) {
		return 0;
	}

	global $wpdb;

	$inserted = $wpdb->insert(
		hp_search_table_name(),
		array(
			'term'            => mb_substr( $term, 0, 191 ),
			'normalized_term' => mb_substr( hp_search_normalize_term( $term ), 0, 191 ),
			'results_count'   => max( 0, (int) $results_count ),
			'user_id'         => get_current_user_id(),
			'session_hash'    => hp_search_session_hash(),
			'ip_hash'         => hp_search_ip_hash(),
			'created_at'      => current_time( 'mysql' ),
		),
		array( '%s', '%s', '%d', '%d', '%s', '%s', '%s' )
	);

	return $inserted ? (int) $wpdb->insert_id : 0;
}

/**
 * Record a click on a search suggestion.
 *
 * @param int    $log_id     Search log row ID returned by hp_log_search.
 * @param int    $product_id Clicked product ID.
 * @return bool
 */
function hp_log_search_click( $log_id, $product_id ) {
	if ( ! hp_search_logging_enabled() ) {
		return false;
	}

	$log_id     = (int) $log_id;
	$product_id = (int) $product_id;
	if ( $log_id <= 0 || $product_id <= 0 ) {
		return false;
	}

	global $wpdb;

	$updated = $wpdb->update(
		hp_search_table_name(),
		array( 'clicked_product_id' => $product_id ),
		array( 'id' => $log_id ),
		array( '%d' ),
		array( '%d' )
	);

	return false !== $updated;
}

/**
 * Get top search terms over the last N days.
 *
 * @param int $days  Lookback window.
 * @param int $limit Max rows.
 * @return array Array of objects with normalized_term, term, hits, avg_results.
 */
function hp_get_top_searches( $days = 7, $limit = 8 ) {
	global $wpdb;

	$days  = max( 1, (int) $days );
	$limit = max( 1, (int) $limit );
	$table = hp_search_table_name();

	$cache_key = "hp_top_searches_{$days}_{$limit}";
	$cached    = wp_cache_get( $cache_key, 'hp_search' );
	if ( false !== $cached ) {
		return $cached;
	}

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT normalized_term, MAX(term) AS term, COUNT(*) AS hits, AVG(results_count) AS avg_results
			FROM {$table}
			WHERE created_at >= DATE_SUB(%s, INTERVAL %d DAY)
				AND normalized_term <> ''
			GROUP BY normalized_term
			ORDER BY hits DESC, normalized_term ASC
			LIMIT %d",
			current_time( 'mysql' ),
			$days,
			$limit
		)
	);

	if ( ! is_array( $rows ) ) {
		$rows = array();
	}

	wp_cache_set( $cache_key, $rows, 'hp_search', 5 * MINUTE_IN_SECONDS );
	return $rows;
}

/**
 * Get zero-result search terms over the last N days.
 *
 * @param int $days  Lookback window.
 * @param int $limit Max rows.
 * @return array
 */
function hp_get_zero_result_searches( $days = 30, $limit = 50 ) {
	global $wpdb;

	$days  = max( 1, (int) $days );
	$limit = max( 1, (int) $limit );
	$table = hp_search_table_name();

	return (array) $wpdb->get_results(
		$wpdb->prepare(
			"SELECT normalized_term, MAX(term) AS term, COUNT(*) AS hits, MAX(created_at) AS last_seen
			FROM {$table}
			WHERE created_at >= DATE_SUB(%s, INTERVAL %d DAY)
				AND results_count = 0
				AND normalized_term <> ''
			GROUP BY normalized_term
			ORDER BY hits DESC, last_seen DESC
			LIMIT %d",
			current_time( 'mysql' ),
			$days,
			$limit
		)
	);
}

/**
 * Get daily search volume for the last N days.
 *
 * @param int $days Lookback window.
 * @return array
 */
function hp_get_search_volume_daily( $days = 14 ) {
	global $wpdb;

	$days  = max( 1, (int) $days );
	$table = hp_search_table_name();

	return (array) $wpdb->get_results(
		$wpdb->prepare(
			"SELECT DATE(created_at) AS day, COUNT(*) AS hits
			FROM {$table}
			WHERE created_at >= DATE_SUB(%s, INTERVAL %d DAY)
			GROUP BY day
			ORDER BY day ASC",
			current_time( 'mysql' ),
			$days
		)
	);
}

/**
 * Get top clicked products from search.
 *
 * @param int $days  Lookback window.
 * @param int $limit Max rows.
 * @return array
 */
function hp_get_top_clicked_products( $days = 30, $limit = 10 ) {
	global $wpdb;

	$days  = max( 1, (int) $days );
	$limit = max( 1, (int) $limit );
	$table = hp_search_table_name();

	return (array) $wpdb->get_results(
		$wpdb->prepare(
			"SELECT clicked_product_id, COUNT(*) AS clicks
			FROM {$table}
			WHERE created_at >= DATE_SUB(%s, INTERVAL %d DAY)
				AND clicked_product_id IS NOT NULL
			GROUP BY clicked_product_id
			ORDER BY clicks DESC
			LIMIT %d",
			current_time( 'mysql' ),
			$days,
			$limit
		)
	);
}

/**
 * Get high-level metrics summary.
 *
 * @param int $days Lookback window.
 * @return array
 */
function hp_get_search_summary( $days = 7 ) {
	global $wpdb;

	$days  = max( 1, (int) $days );
	$table = hp_search_table_name();

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT
				COUNT(*) AS total_searches,
				COUNT(DISTINCT normalized_term) AS unique_terms,
				SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) AS zero_result_searches,
				SUM(CASE WHEN clicked_product_id IS NOT NULL THEN 1 ELSE 0 END) AS clicks
			FROM {$table}
			WHERE created_at >= DATE_SUB(%s, INTERVAL %d DAY)",
			current_time( 'mysql' ),
			$days
		),
		ARRAY_A
	);

	if ( ! is_array( $row ) ) {
		$row = array(
			'total_searches'       => 0,
			'unique_terms'         => 0,
			'zero_result_searches' => 0,
			'clicks'               => 0,
		);
	}

	return array_map( 'intval', $row );
}

/**
 * Get configured trending fallback terms (admin-set, comma-separated).
 *
 * @return array Sanitized non-empty term strings.
 */
function hp_get_trending_fallback_terms() {
	$raw = (string) get_option( 'hp_search_trending_fallback', '' );
	if ( '' === trim( $raw ) ) {
		return array();
	}
	$parts = array_map( 'trim', explode( ',', $raw ) );
	$parts = array_filter(
		array_map( 'sanitize_text_field', $parts ),
		static function ( $value ) {
			return '' !== $value;
		}
	);
	return array_values( array_unique( $parts ) );
}

/**
 * Resolve trending terms shown in the overlay empty state.
 *
 * Real top searches over the last 7 days first; fallback terms top up
 * the list when there are fewer than the requested limit.
 *
 * @param int $limit Max terms.
 * @return array Term strings.
 */
function hp_get_trending_terms_for_overlay( $limit = 8 ) {
	$limit = max( 1, (int) $limit );
	$out   = array();

	$top = hp_get_top_searches( 7, $limit );
	foreach ( $top as $row ) {
		if ( count( $out ) >= $limit ) {
			break;
		}
		$value = isset( $row->term ) ? trim( (string) $row->term ) : '';
		if ( '' !== $value && ! in_array( $value, $out, true ) ) {
			$out[] = $value;
		}
	}

	if ( count( $out ) < $limit ) {
		foreach ( hp_get_trending_fallback_terms() as $value ) {
			if ( count( $out ) >= $limit ) {
				break;
			}
			if ( ! in_array( $value, $out, true ) ) {
				$out[] = $value;
			}
		}
	}

	return $out;
}
