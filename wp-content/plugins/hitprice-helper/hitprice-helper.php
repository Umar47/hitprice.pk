<?php
/**
 * Plugin Name: Hit Price Helper
 * Description: Helper plugin for Hit Price custom logic and ACF registration.
 * Version: 1.0.0
 * Author: Hit Price
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HITPRICE_HELPER_PATH', plugin_dir_path( __FILE__ ) );
define( 'HITPRICE_HELPER_URL', plugin_dir_url( __FILE__ ) );

require_once HITPRICE_HELPER_PATH . 'inc/acf/homepage-fields.php';
require_once HITPRICE_HELPER_PATH . 'inc/acf/product-fields.php';
require_once HITPRICE_HELPER_PATH . 'inc/acf/swatch-fields.php';
require_once HITPRICE_HELPER_PATH . 'inc/homepage/homepage-data.php';
require_once HITPRICE_HELPER_PATH . 'inc/product/product-data.php';
require_once HITPRICE_HELPER_PATH . 'inc/checkout/checkout-fields.php';
