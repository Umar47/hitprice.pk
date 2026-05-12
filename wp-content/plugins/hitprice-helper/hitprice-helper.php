<?php
/**
 * Plugin Name: Hit Price Helper
 * Description: Helper plugin for Hit Price custom logic and ACF registration. v1.1.0 adds: dynamic payment methods strip (FA icons, admin-controlled), purchase action row wrapper hooks, product trust strip (FA icons), admin settings UI redesign (card-based layout, Single Product Page submenu), payment methods admin settings group, and removal of unused Product Features ACF field group.
 * Version: 1.1.0
 * Author: Hit Price
 *
 * @package HitPriceHelper
 *
 * Changelog:
 * 1.1.0 - 2026-05-11
 *   - Added hp_open_product_actions_row / hp_close_product_actions_row hooks to wrap
 *     qty + Add to Cart + Buy Now in a single .hp-product-actions-row flex container.
 *   - Added hp_get_payment_methods() — reads payment methods from global settings with
 *     Font Awesome icon class support; falls back to hardcoded defaults.
 *   - Added "After add to cart button — Icons Row" section to admin settings page
 *     (icon class + title + subtitle repeater, max 3 items).
 *   - Added hp_get_product_trust_strip_items() for dynamic bottom trust strip.
 *   - Renamed admin submenu label to "Single Product Page" and restructured settings
 *     page into card-based sections (hp-card layout system).
 *   - Added trust strip save/render logic with Font Awesome icon class fields.
 *   - Removed unused "Product Features" ACF field group (group_hp_product_features),
 *     helper function hp_get_product_features(), and related frontend template.
 *
 * 1.0.0 - Initial release
 *   - ACF field group registration for product overview specs, gallery badges,
 *     price icons, gallery trust strip, why-buy strip, and detail specs.
 *   - Global settings page (sale banner, viewers range, shipping policy).
 *   - Product data helpers: hp_get_compare_products(), hp_get_product_trust_strip_items().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HITPRICE_HELPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'HITPRICE_HELPER_URL', plugin_dir_url( __FILE__ ) );

require_once HITPRICE_HELPER_PATH . 'inc/acf/homepage-fields.php';
require_once HITPRICE_HELPER_PATH . 'inc/acf/product-fields.php';
require_once HITPRICE_HELPER_PATH . 'inc/homepage/homepage-data.php';
require_once HITPRICE_HELPER_PATH . 'inc/product/product-data.php';
require_once HITPRICE_HELPER_PATH . 'inc/product/review-images.php';
require_once HITPRICE_HELPER_PATH . 'inc/checkout/checkout-fields.php';

require_once HITPRICE_HELPER_PATH . 'inc/search/search-install.php';
require_once HITPRICE_HELPER_PATH . 'inc/search/search-analytics.php';
require_once HITPRICE_HELPER_PATH . 'inc/search/search-query.php';
require_once HITPRICE_HELPER_PATH . 'inc/search/search-rest.php';

register_activation_hook( __FILE__, 'hp_search_activate' );

if ( is_admin() ) {
	require_once HITPRICE_HELPER_PATH . 'inc/admin/global-settings.php';
	require_once HITPRICE_HELPER_PATH . 'inc/admin/bulk-specs-importer.php';
	require_once HITPRICE_HELPER_PATH . 'inc/admin/search-admin.php';
}
