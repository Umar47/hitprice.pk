<?php
/**
 * Search analytics table installer.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HP_SEARCH_DB_VERSION', '1.0.0' );
define( 'HP_SEARCH_DB_OPTION', 'hp_search_db_version' );

/**
 * Get the search log table name.
 *
 * @return string
 */
function hp_search_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'hp_search_log';
}

/**
 * Create or upgrade the search log table.
 */
function hp_search_install_table() {
	global $wpdb;

	$installed = get_option( HP_SEARCH_DB_OPTION );
	if ( HP_SEARCH_DB_VERSION === $installed ) {
		return;
	}

	$table   = hp_search_table_name();
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		term VARCHAR(191) NOT NULL DEFAULT '',
		normalized_term VARCHAR(191) NOT NULL DEFAULT '',
		results_count INT UNSIGNED NOT NULL DEFAULT 0,
		user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
		session_hash VARCHAR(64) NOT NULL DEFAULT '',
		ip_hash VARCHAR(64) NOT NULL DEFAULT '',
		clicked_product_id BIGINT UNSIGNED NULL DEFAULT NULL,
		created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY  (id),
		KEY normalized_term (normalized_term),
		KEY created_at (created_at),
		KEY clicked_product_id (clicked_product_id)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( HP_SEARCH_DB_OPTION, HP_SEARCH_DB_VERSION, false );
}

/**
 * Run install on plugin activation.
 */
function hp_search_activate() {
	hp_search_install_table();
}

/**
 * Run install if version mismatch detected on load (admin only).
 */
function hp_search_maybe_upgrade() {
	if ( ! is_admin() ) {
		return;
	}
	if ( get_option( HP_SEARCH_DB_OPTION ) !== HP_SEARCH_DB_VERSION ) {
		hp_search_install_table();
	}
}
add_action( 'plugins_loaded', 'hp_search_maybe_upgrade', 20 );
