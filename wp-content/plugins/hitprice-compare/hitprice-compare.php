<?php
/**
 * Plugin Name: HitPrice Compare
 * Description: Lightweight WooCommerce product comparison — localStorage IDs, server-side rendering.
 * Version:     1.0.0
 * Author:      HitPrice
 * Requires Plugins: woocommerce
 * Text Domain: hitprice-compare
 *
 * @package HitPriceCompare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HPC_VERSION', '1.0.0' );
define( 'HPC_PATH', plugin_dir_path( __FILE__ ) );
define( 'HPC_URL', plugin_dir_url( __FILE__ ) );
define( 'HPC_MAX_ITEMS', 4 );

require_once HPC_PATH . 'inc/compare-functions.php';
require_once HPC_PATH . 'inc/compare-query.php';
require_once HPC_PATH . 'inc/compare-hooks.php';
