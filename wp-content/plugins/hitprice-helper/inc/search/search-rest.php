<?php
/**
 * REST endpoints for live search.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const HP_SEARCH_REST_NS         = 'hp/v1';
const HP_SEARCH_RATE_LIMIT_MAX  = 60;  // Requests per window per IP.
const HP_SEARCH_RATE_LIMIT_WIN  = 60;  // Window in seconds.
const HP_SEARCH_MIN_QUERY_LEN   = 2;
const HP_SEARCH_MAX_QUERY_LEN   = 100;
const HP_SEARCH_PRODUCT_LIMIT   = 6;
const HP_SEARCH_TERM_LIMIT      = 4;
const HP_SEARCH_TRENDING_LIMIT  = 8;

/**
 * Register REST routes.
 */
function hp_search_register_rest_routes() {
	register_rest_route(
		HP_SEARCH_REST_NS,
		'/search/suggest',
		array(
			'methods'             => 'GET',
			'callback'            => 'hp_search_rest_suggest',
			'permission_callback' => '__return_true',
			'args'                => array(
				'q' => array(
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		HP_SEARCH_REST_NS,
		'/search/click',
		array(
			'methods'             => 'POST',
			'callback'            => 'hp_search_rest_click',
			'permission_callback' => '__return_true',
			'args'                => array(
				'log_id'     => array(
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
				'product_id' => array(
					'required'          => true,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		HP_SEARCH_REST_NS,
		'/search/trending',
		array(
			'methods'             => 'GET',
			'callback'            => 'hp_search_rest_trending',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'hp_search_register_rest_routes' );

/**
 * Per-IP rate limiter using a transient counter.
 *
 * @return bool True when within limits.
 */
function hp_search_rate_limit_ok() {
	$hash = hp_search_ip_hash();
	if ( '' === $hash ) {
		return true;
	}
	$key   = 'hp_search_rl_' . substr( $hash, 0, 32 );
	$count = (int) get_transient( $key );
	if ( $count >= HP_SEARCH_RATE_LIMIT_MAX ) {
		return false;
	}
	set_transient( $key, $count + 1, HP_SEARCH_RATE_LIMIT_WIN );
	return true;
}

/**
 * Validate and clean an incoming query string.
 *
 * @param string $raw Raw query.
 * @return string Cleaned (may be empty if invalid).
 */
function hp_search_clean_query( $raw ) {
	$value = sanitize_text_field( (string) $raw );
	$value = preg_replace( '/[^\PC\s]+/u', '', $value ); // Strip non-printable.
	$value = trim( (string) $value );
	if ( mb_strlen( $value ) > HP_SEARCH_MAX_QUERY_LEN ) {
		$value = mb_substr( $value, 0, HP_SEARCH_MAX_QUERY_LEN );
	}
	if ( mb_strlen( $value ) < HP_SEARCH_MIN_QUERY_LEN ) {
		return '';
	}
	return $value;
}

/**
 * REST: live suggestions.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function hp_search_rest_suggest( WP_REST_Request $request ) {
	if ( ! hp_search_rate_limit_ok() ) {
		return new WP_Error( 'hp_rate_limited', __( 'Too many requests', 'hitprice' ), array( 'status' => 429 ) );
	}

	$query = hp_search_clean_query( $request->get_param( 'q' ) );
	if ( '' === $query ) {
		return rest_ensure_response(
			array(
				'query'    => '',
				'log_id'   => 0,
				'products' => array(),
				'terms'    => array(),
			)
		);
	}

	$ids       = hp_search_products( $query, HP_SEARCH_PRODUCT_LIMIT, 'suggest' );
	$products  = hp_search_format_products( $ids );
	$terms     = hp_search_term_suggestions( $query, HP_SEARCH_TERM_LIMIT );
	$log_id    = hp_log_search( $query, count( $ids ) );

	$response = rest_ensure_response(
		array(
			'query'    => $query,
			'log_id'   => (int) $log_id,
			'products' => $products,
			'terms'    => $terms,
		)
	);
	// Allow the browser to reuse identical queries for 30 s (private = no CDN).
	// The transient cache on the server side means the DB is only hit once per 5 min anyway.
	$response->header( 'Cache-Control', 'private, max-age=30' );
	return $response;
}

/**
 * REST: track click on a suggestion.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function hp_search_rest_click( WP_REST_Request $request ) {
	if ( ! hp_search_rate_limit_ok() ) {
		return new WP_Error( 'hp_rate_limited', __( 'Too many requests', 'hitprice' ), array( 'status' => 429 ) );
	}

	$log_id     = (int) $request->get_param( 'log_id' );
	$product_id = (int) $request->get_param( 'product_id' );
	if ( $log_id <= 0 || $product_id <= 0 ) {
		return new WP_Error( 'hp_bad_request', __( 'Invalid parameters', 'hitprice' ), array( 'status' => 400 ) );
	}

	$ok = hp_log_search_click( $log_id, $product_id );
	return rest_ensure_response( array( 'ok' => (bool) $ok ) );
}

/**
 * REST: trending terms for empty-state.
 *
 * @return WP_REST_Response
 */
function hp_search_rest_trending() {
	if ( ! hp_search_rate_limit_ok() ) {
		return new WP_Error( 'hp_rate_limited', __( 'Too many requests', 'hitprice' ), array( 'status' => 429 ) );
	}
	return rest_ensure_response(
		array(
			'terms' => hp_get_trending_terms_for_overlay( HP_SEARCH_TRENDING_LIMIT ),
		)
	);
}
