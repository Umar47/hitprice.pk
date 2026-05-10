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
 * Returns whether the PTA Approved badge should show for a product.
 *
 * @param int $product_id
 * @return bool
 */
function hp_is_pta_approved( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}
	return (bool) get_field( 'hp_pta_approved', $product_id );
}

/**
 * Returns the Key Highlights WYSIWYG content (left column).
 *
 * @param int $product_id
 * @return string  Sanitized HTML or empty string.
 */
function hp_get_key_highlights_content( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	$content = get_field( 'hp_key_highlights_content', $product_id );
	return $content ? wp_kses_post( $content ) : '';
}

/**
 * Returns the Key Highlights infographic image array (right column).
 *
 * @param int $product_id
 * @return array|null  ACF image array or null when not set.
 */
function hp_get_key_highlights_image( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}
	$image = get_field( 'hp_key_highlights_image', $product_id );
	return is_array( $image ) && ! empty( $image['url'] ) ? $image : null;
}

/**
 * Returns the Overview Specs repeater rows (max 8).
 * Each row: [ 'icon' => array|null, 'title' => string, 'value' => string ]
 *
 * @param int $product_id
 * @return array
 */
function hp_get_overview_specs( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}
	$rows = get_field( 'hp_overview_specs', $product_id );
	if ( ! is_array( $rows ) ) {
		return array();
	}
	return array_slice( $rows, 0, 8 );
}

/**
 * Returns up to 4 bottom trust strip items for the single product page.
 * Falls back to sensible defaults when nothing is configured.
 *
 * @return array<int, array{ icon_class: string, title: string, subtitle: string }>
 */
function hp_get_product_trust_strip_items() {
	$settings = get_option( 'hp_global_settings', [] );
	$items    = isset( $settings['trust_strip'] ) && is_array( $settings['trust_strip'] )
		? $settings['trust_strip']
		: [];

	if ( empty( $items ) ) {
		$items = [
			[ 'icon_class' => 'fa-solid fa-shield-halved', 'title' => 'Safe & Secure Payments',  'subtitle' => 'Your payment information is 100% secure.' ],
			[ 'icon_class' => 'fa-solid fa-rotate-left',   'title' => 'Easy Returns',             'subtitle' => '7 days easy return & refund policy.' ],
			[ 'icon_class' => 'fa-solid fa-headset',       'title' => 'Customer Support 24/7',    'subtitle' => 'We are here to help you anytime.' ],
			[ 'icon_class' => 'fa-solid fa-medal',         'title' => '100% Satisfaction',        'subtitle' => 'We are committed to provide best quality products.' ],
		];
	}

	return array_slice( $items, 0, 4 );
}

/**
 * Returns payment methods strip items (up to 3) from global settings,
 * falling back to the default hardcoded set.
 *
 * @return array<int, array{ image_id: int, image_url: string, title: string, subtitle: string }>
 */
function hp_get_payment_methods() {
	$settings = get_option( 'hp_global_settings', [] );
	$items    = isset( $settings['payment_methods'] ) && is_array( $settings['payment_methods'] )
		? $settings['payment_methods']
		: [];

	if ( empty( $items ) ) {
		return [
			[ 'icon_class' => 'fa-regular fa-credit-card', 'title' => 'Cash on Delivery',    'subtitle' => 'Pay when you receive' ],
			[ 'icon_class' => 'fa-solid fa-box-open',       'title' => 'Open Parcel',          'subtitle' => 'Check before you pay' ],
			[ 'icon_class' => 'fa-solid fa-shield-halved',  'title' => '7-Day Check Warranty', 'subtitle' => 'Free return & replacement' ],
		];
	}

	return array_slice( $items, 0, 3 );
}

/**
 * Returns the ordered badge key → default label map.
 *
 * Moved from global-settings.php so these data helpers are available on
 * the front end (global-settings.php is admin-only).
 *
 * @return array<string, string>
 */
function hp_get_badge_keys() {
	return [
		'pta_approved'     => 'PTA Approved',
		'genuine'          => '100% Genuine',
		'best_price'       => 'Best Price Guarantee',
		'weekly_deals'     => 'Weekly Deals',
		'fast_delivery'    => 'Fast & Reliable Delivery',
		'secure_packaging' => 'Secure Packaging',
		'easy_returns'     => 'Easy Returns',
		'safe_payments'    => 'Safe & Secure Payments',
		'customer_support' => 'Customer Support 24/7',
		'satisfaction'     => '100% Satisfaction',
	];
}

/**
 * Retrieves a global setting value using dot-notation.
 * e.g. hp_get_global_setting( 'viewers_min' )
 *      hp_get_global_setting( 'badges.pta_approved.label' )
 *
 * @param string $key     Dot-notation path into hp_global_settings option.
 * @param mixed  $default Returned when the key is absent.
 * @return mixed
 */
function hp_get_global_setting( $key, $default = '' ) {
	static $settings = null;
	if ( null === $settings ) {
		$settings = (array) get_option( 'hp_global_settings', [] );
	}
	$parts = explode( '.', $key );
	$value = $settings;
	foreach ( $parts as $part ) {
		if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
			return $default;
		}
		$value = $value[ $part ];
	}
	return $value;
}

/**
 * Retrieves a complete badge array by key.
 *
 * @param string $badge_key One of the keys from hp_get_badge_keys().
 * @return array{ image_url: string, label: string, description: string }
 */
function hp_get_global_badge( $badge_key ) {
	$defaults = hp_get_badge_keys();
	return [
		'image_url'   => (string) hp_get_global_setting( "badges.{$badge_key}.image_url", '' ),
		'label'       => (string) hp_get_global_setting( "badges.{$badge_key}.label", $defaults[ $badge_key ] ?? '' ),
		'description' => (string) hp_get_global_setting( "badges.{$badge_key}.description", '' ),
	];
}

/**
 * Returns sale banner data for a product using native WC sale dates.
 * Returns null when the product is not currently on sale.
 *
 * @param WC_Product $product
 * @return array{ label: string, valid_until: string }|null
 */
function hp_get_sale_banner_data( $product ) {
	if ( ! $product->is_on_sale() ) {
		return null;
	}

	$fallback  = (string) hp_get_global_setting( 'sale_banner_label', 'Weekly Sale Offer' );
	$sale_to   = $product->get_date_on_sale_to();

	if ( ! $sale_to ) {
		return array(
			'label'       => $fallback,
			'valid_until' => '',
		);
	}

	$sale_ts     = $sale_to->getTimestamp();
	$now         = current_time( 'timestamp' );
	$day_of_week = (int) gmdate( 'N', $now ); // 1 = Mon … 7 = Sun
	$days_to_sun = 7 - $day_of_week;          // days remaining until Sunday
	$end_of_week = strtotime( '+' . $days_to_sun . ' days', strtotime( gmdate( 'Y-m-d', $now ) ) ) + 86399;

	if ( $sale_ts <= $end_of_week ) {
		$day_name = date_i18n( 'l', $sale_ts ); // localized day name e.g. "Sunday"
		return array(
			'label'       => $fallback,
			'valid_until' => $day_name,
		);
	}

	return array(
		'label'       => $fallback,
		'valid_until' => '',
	);
}

/**
 * Returns the price icons repeater for the after-price row.
 * Data stored in hp_global_settings['price_icons'].
 *
 * @return array Each item: { image_url: string, title: string, subtitle: string }
 */
function hp_get_price_icons() {
	$items = hp_get_global_setting( 'price_icons', array() );
	if ( ! is_array( $items ) ) {
		return array();
	}
	$out = array();
	foreach ( $items as $item ) {
		if ( empty( $item['title'] ) ) {
			continue;
		}
		$out[] = array(
			'image_url' => esc_url( $item['image_url'] ?? '' ),
			'title'     => sanitize_text_field( $item['title'] ),
			'subtitle'  => sanitize_text_field( $item['subtitle'] ?? '' ),
		);
	}
	return array_slice( $out, 0, 6 );
}

/**
 * Returns sale banner settings from the admin settings page.
 *
 * @return array{ enabled: bool, text: string, subtext: string }
 */
function hp_get_sale_banner_settings() {
	return array(
		'enabled' => (bool) hp_get_global_setting( 'sale_banner_enabled', false ),
		'text'    => (string) hp_get_global_setting( 'sale_banner_text', '' ),
	);
}

/**
 * Returns an inline SVG icon string for a given badge key.
 * Used as a fallback when no icon image has been uploaded in Global Settings.
 *
 * @param string $key Badge key from hp_get_badge_keys().
 * @return string Safe inline SVG markup.
 */
function hp_get_badge_svg_icon( $key ) {
	$icons = array(
		'pta_approved'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
		'genuine'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"/></svg>',
		'best_price'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
		'fast_delivery'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>',
		'secure_packaging' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
		'easy_returns'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.85"/></svg>',
		'weekly_deals'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>',
		'safe_payments'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
		'customer_support' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.61 1.2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9a16 16 0 0 0 6.06 6.06l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
		'satisfaction'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
	);

	return isset( $icons[ $key ] )
		? $icons[ $key ]
		: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>';
}

/**
 * Returns a gallery top icon (PTA or Best Price) for the badge overlays.
 * Data stored in hp_global_settings['gallery_top'][$key].
 *
 * @param string $key 'pta' or 'best_price'.
 * @return array{ image_url: string, label: string }
 */
function hp_get_gallery_top_icon( $key ) {
	return array(
		'image_url' => (string) hp_get_global_setting( "gallery_top.{$key}.image_url", '' ),
		'label'     => (string) hp_get_global_setting( "gallery_top.{$key}.label", '' ),
	);
}

/**
 * Returns the gallery bottom icons repeater (max 4).
 * Data stored in hp_global_settings['gallery_bottom_icons'].
 *
 * @return array Each item: { image_url: string, title: string, subtitle: string }
 */
function hp_get_gallery_bottom_icons() {
	$items = hp_get_global_setting( 'gallery_bottom_icons', array() );
	if ( ! is_array( $items ) ) {
		return array();
	}
	$out = array();
	foreach ( $items as $item ) {
		if ( empty( $item['title'] ) ) {
			continue;
		}
		$out[] = array(
			'image_url' => esc_url( $item['image_url'] ?? '' ),
			'title'     => sanitize_text_field( $item['title'] ),
			'subtitle'  => sanitize_text_field( $item['subtitle'] ?? '' ),
		);
	}
	return array_slice( $out, 0, 4 );
}

/**
 * Returns the "Why Buy" section data.
 *
 * @return array{ enabled: bool, title: string, items: array }
 */
function hp_get_why_buy_section() {
	return array(
		'enabled' => (bool) hp_get_global_setting( 'why_buy_enabled', false ),
		'title'   => (string) hp_get_global_setting( 'why_buy_title', 'Why buy from Hitprice.pk?' ),
		'items'   => (array) hp_get_global_setting( 'why_buy_items', array() ),
	);
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
