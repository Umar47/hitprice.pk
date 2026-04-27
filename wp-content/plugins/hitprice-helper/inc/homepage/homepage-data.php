<?php
/**
 * Homepage data helpers.
 *
 * Provides typed, sanitized accessors for each homepage section
 * defined by the ACF field group registered in inc/acf/homepage-fields.php.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve homepage post id for field lookups.
 *
 * @param int $post_id Optional explicit post id.
 * @return int
 */
function hp_home_post_id( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : (int) get_the_ID();

	return $post_id > 0 ? $post_id : 0;
}

/**
 * Safe ACF field accessor.
 *
 * @param string $name    Field name.
 * @param int    $post_id Post id.
 * @param mixed  $default Default value.
 * @return mixed
 */
function hp_home_field( $name, $post_id = 0, $default = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $name, hp_home_post_id( $post_id ) );

	return ( null === $value || false === $value ) ? $default : $value;
}

/**
 * Clamp autoplay speed (seconds) to a sane range.
 *
 * @param mixed $value Raw field value.
 * @param int   $default Fallback in seconds.
 * @return int
 */
function hp_normalize_autoplay_speed( $value, $default = 7 ) {
	$seconds = (int) $value;

	if ( $seconds < 2 ) {
		$seconds = $default;
	}
	if ( $seconds > 30 ) {
		$seconds = 30;
	}

	return $seconds;
}

/**
 * Get hero slides.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_hero_slides( $post_id = 0 ) {
	$slides = hp_home_field( 'hero_slides', $post_id, array() );

	if ( ! is_array( $slides ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $slides as $slide ) {
		if ( empty( $slide['background_image'] ) ) {
			continue;
		}

		$mobile_image = isset( $slide['background_image_mobile'] ) && is_array( $slide['background_image_mobile'] ) && ! empty( $slide['background_image_mobile']['url'] )
			? $slide['background_image_mobile']
			: null;

		$normalized[] = array(
			'background_image'        => $slide['background_image'],
			'background_image_mobile' => $mobile_image,
			'heading'                 => isset( $slide['heading'] ) ? (string) $slide['heading'] : '',
			'subheading'              => isset( $slide['subheading'] ) ? (string) $slide['subheading'] : '',
			'offer_text'              => isset( $slide['offer_text'] ) ? (string) $slide['offer_text'] : '',
			'cta1_label'              => isset( $slide['cta1_label'] ) ? (string) $slide['cta1_label'] : '',
			'cta1_url'                => isset( $slide['cta1_url'] ) ? (string) $slide['cta1_url'] : '',
			'cta2_label'              => isset( $slide['cta2_label'] ) ? (string) $slide['cta2_label'] : '',
			'cta2_url'                => isset( $slide['cta2_url'] ) ? (string) $slide['cta2_url'] : '',
		);
	}

	return $normalized;
}

/**
 * Get hero section data including autoplay settings.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_hero_section_data( $post_id = 0 ) {
	return array(
		'slides'         => hp_get_hero_slides( $post_id ),
		'autoplay'       => (bool) hp_home_field( 'hero_autoplay_enabled', $post_id, true ),
		'autoplay_speed' => hp_normalize_autoplay_speed( hp_home_field( 'hero_autoplay_speed', $post_id, 7 ), 7 ),
	);
}

/**
 * Get trust strip items.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_trust_strip_items( $post_id = 0 ) {
	$items = hp_home_field( 'trust_items', $post_id, array() );

	if ( ! is_array( $items ) ) {
		return array();
	}

	$normalized = array();

	foreach ( $items as $item ) {
		if ( empty( $item['image'] ) ) {
			continue;
		}

		$normalized[] = array(
			'image' => $item['image'],
			'url'   => isset( $item['url'] ) ? (string) $item['url'] : '',
		);
	}

	return $normalized;
}

/**
 * Get product slider data (shared by Hot Deals and Latest Phones).
 *
 * @param string $prefix  Field prefix (hot_deals or latest_phones).
 * @param int    $post_id Post id.
 * @return array
 */
function hp_get_product_slider_data( $prefix, $post_id = 0 ) {
	$prefix = sanitize_key( $prefix );

	$title       = hp_home_field( $prefix . '_title', $post_id, '' );
	$subtitle    = hp_home_field( $prefix . '_subtitle', $post_id, '' );
	$product_ids = hp_home_field( $prefix . '_products', $post_id, array() );
	$cta_label   = hp_home_field( $prefix . '_cta_label', $post_id, '' );
	$cta_url     = hp_home_field( $prefix . '_cta_url', $post_id, '' );
	$autoplay    = (bool) hp_home_field( $prefix . '_autoplay_enabled', $post_id, false );
	$speed       = hp_normalize_autoplay_speed( hp_home_field( $prefix . '_autoplay_speed', $post_id, 5 ), 5 );

	$product_ids = is_array( $product_ids ) ? array_filter( array_map( 'absint', $product_ids ) ) : array();
	$products    = array();

	if ( ! empty( $product_ids ) && function_exists( 'wc_get_product' ) ) {
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );

			if ( $product instanceof WC_Product && $product->is_visible() ) {
				$products[] = $product;
			}
		}
	}

	return array(
		'title'          => (string) $title,
		'subtitle'       => (string) $subtitle,
		'products'       => $products,
		'cta_label'      => (string) $cta_label,
		'cta_url'        => (string) $cta_url,
		'autoplay'       => $autoplay,
		'autoplay_speed' => $speed,
	);
}

/**
 * Get hot deals section data.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_hot_deals_data( $post_id = 0 ) {
	return hp_get_product_slider_data( 'hot_deals', $post_id );
}

/**
 * Get latest phones section data.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_latest_phones_data( $post_id = 0 ) {
	return hp_get_product_slider_data( 'latest_phones', $post_id );
}

/**
 * Get shop by category section data.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_shop_categories_data( $post_id = 0 ) {
	$cards = hp_home_field( 'shop_category_cards', $post_id, array() );
	$cards = is_array( $cards ) ? $cards : array();

	$normalized = array();

	foreach ( $cards as $card ) {
		if ( empty( $card['background_image'] ) || empty( $card['title'] ) ) {
			continue;
		}

		$normalized[] = array(
			'background_image' => $card['background_image'],
			'title'            => (string) $card['title'],
			'text'             => isset( $card['text'] ) ? (string) $card['text'] : '',
			'cta_label'        => isset( $card['cta_label'] ) ? (string) $card['cta_label'] : '',
			'cta_url'          => isset( $card['cta_url'] ) ? (string) $card['cta_url'] : '',
		);
	}

	return array(
		'title'    => (string) hp_home_field( 'shop_categories_title', $post_id, '' ),
		'subtitle' => (string) hp_home_field( 'shop_categories_subtitle', $post_id, '' ),
		'cards'    => $normalized,
	);
}

/**
 * Get "why buy from us" section data.
 *
 * @param int $post_id Post id.
 * @return array
 */
function hp_get_why_buy_data( $post_id = 0 ) {
	$items = hp_home_field( 'why_buy_items', $post_id, array() );
	$items = is_array( $items ) ? $items : array();

	$normalized = array();

	foreach ( $items as $item ) {
		if ( empty( $item['title'] ) ) {
			continue;
		}

		$normalized[] = array(
			'title'       => (string) $item['title'],
			'description' => isset( $item['description'] ) ? (string) $item['description'] : '',
		);
	}

	return array(
		'title'    => (string) hp_home_field( 'why_buy_title', $post_id, '' ),
		'subtitle' => (string) hp_home_field( 'why_buy_subtitle', $post_id, '' ),
		'items'    => $normalized,
	);
}
