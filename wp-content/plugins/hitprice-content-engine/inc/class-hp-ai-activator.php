<?php
/**
 * Plugin activation handler.
 *
 * Creates custom database tables and sets default options.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Activator {

	/**
	 * Current DB schema version.
	 *
	 * Bump this when table schemas change to trigger an upgrade.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.1.0';

	/**
	 * Run on plugin activation.
	 *
	 * @return void
	 */
	public static function activate() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		self::create_tables();
		self::set_default_options();

		update_option( 'hp_ai_db_version', self::DB_VERSION );
	}

	/**
	 * Create custom database tables.
	 *
	 * Uses dbDelta for safe creation and future upgrades.
	 *
	 * @return void
	 */
	private static function create_tables() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = array();

		// Topics table.
		$sql[] = "CREATE TABLE {$wpdb->prefix}hp_ai_topics (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			content_type VARCHAR(50) NOT NULL DEFAULT 'comparison',
			keywords TEXT NULL,
			priority TINYINT(1) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			month_target VARCHAR(7) NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY status (status),
			KEY content_type (content_type),
			KEY month_target (month_target)
		) {$charset_collate};";

		// Social posts table.
		$sql[] = "CREATE TABLE {$wpdb->prefix}hp_ai_social_posts (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			topic_id BIGINT(20) UNSIGNED NOT NULL,
			platform VARCHAR(20) NOT NULL DEFAULT 'facebook',
			caption TEXT NULL,
			hashtags TEXT NULL,
			image_text TEXT NULL,
			carousel_ideas TEXT NULL,
			hook_line VARCHAR(255) NULL,
			cta_text VARCHAR(255) NULL,
			ai_provider VARCHAR(20) NULL,
			ai_model VARCHAR(50) NULL,
			ai_raw_response LONGTEXT NULL,
			tokens_used INT(11) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'draft',
			posted_at DATETIME NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY topic_id (topic_id),
			KEY platform (platform),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		// Blog posts table.
		$sql[] = "CREATE TABLE {$wpdb->prefix}hp_ai_blogs (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			topic_id BIGINT(20) UNSIGNED NOT NULL,
			title VARCHAR(255) NOT NULL,
			slug VARCHAR(255) NULL,
			content LONGTEXT NULL,
			excerpt TEXT NULL,
			meta_title VARCHAR(255) NULL,
			meta_description VARCHAR(500) NULL,
			focus_keyword VARCHAR(100) NULL,
			featured_image_prompt TEXT NULL,
			wp_post_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			ai_provider VARCHAR(20) NULL,
			ai_model VARCHAR(50) NULL,
			ai_raw_response LONGTEXT NULL,
			tokens_used INT(11) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'draft',
			published_at DATETIME NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY topic_id (topic_id),
			KEY wp_post_id (wp_post_id),
			KEY status (status),
			KEY focus_keyword (focus_keyword),
			KEY created_at (created_at)
		) {$charset_collate};";

		// Audit log table.
		$sql[] = "CREATE TABLE {$wpdb->prefix}hp_ai_log (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			action VARCHAR(50) NOT NULL,
			object_type VARCHAR(20) NOT NULL,
			object_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			details TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY action (action),
			KEY object_type_id (object_type, object_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		foreach ( $sql as $query ) {
			dbDelta( $query );
		}
	}

	/**
	 * Set default plugin options.
	 *
	 * Uses add_option so existing values are never overwritten.
	 *
	 * @return void
	 */
	private static function set_default_options() {

		add_option( 'hp_ai_mode', 'mock' );
		add_option( 'hp_ai_provider', 'claude' );
		add_option( 'hp_ai_model', 'claude-sonnet-4-6' );
		add_option( 'hp_ai_monthly_target', 30 );
		add_option( 'hp_ai_default_hashtags', '#hitprice #hitpricepk #mobileprice #pakistan' );
	}
}
