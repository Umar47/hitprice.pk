<?php
/**
 * Fired when the plugin is deleted via WordPress admin.
 *
 * Drops all custom tables and removes plugin options.
 * This file is called by WordPress core — not by the plugin itself.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Drop custom tables.
$tables = array(
	$wpdb->prefix . 'hp_ai_topics',
	$wpdb->prefix . 'hp_ai_social_posts',
	$wpdb->prefix . 'hp_ai_blogs',
	$wpdb->prefix . 'hp_ai_log',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table names are hardcoded above.
}

// Remove plugin options.
$options = array(
	'hp_ai_mode',
	'hp_ai_provider',
	'hp_ai_model',
	'hp_ai_monthly_target',
	'hp_ai_default_hashtags',
	'hp_ai_db_version',
);

foreach ( $options as $option ) {
	delete_option( $option );
}
