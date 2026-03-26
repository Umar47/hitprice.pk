<?php
/**
 * Plugin Name: HitPrice Content Engine
 * Plugin URI:  https://hitprice.pk
 * Description: AI-powered social media content generator for hitprice.pk — generates drafts for Facebook & Instagram.
 * Version:     1.0.0
 * Author:      HitPrice
 * Author URI:  https://hitprice.pk
 * Text Domain: hitprice-content-engine
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin constants.
 */
define( 'HP_AI_VERSION', '1.0.0' );
define( 'HP_AI_PLUGIN_FILE', __FILE__ );
define( 'HP_AI_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'HP_AI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'HP_AI_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload plugin classes.
 *
 * Maps class names like HP_AI_Admin to inc/class-hp-ai-admin.php.
 */
spl_autoload_register( function ( $class ) {

	$prefix = 'HP_AI_';

	if ( 0 !== strpos( $class, $prefix ) ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );
	$filename = 'class-hp-ai-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
	$filepath = HP_AI_PLUGIN_DIR . 'inc/' . $filename;

	if ( file_exists( $filepath ) ) {
		require_once $filepath;
	}
} );

/**
 * Run on plugin activation.
 */
register_activation_hook( __FILE__, array( 'HP_AI_Activator', 'activate' ) );

/**
 * Run on plugin deactivation.
 */
register_deactivation_hook( __FILE__, array( 'HP_AI_Deactivator', 'deactivate' ) );

/**
 * Boot the plugin after all plugins are loaded.
 */
function hp_ai_init() {

	if ( is_admin() ) {
		new HP_AI_Admin();
		new HP_AI_Ajax();
	}
}
add_action( 'plugins_loaded', 'hp_ai_init' );
