<?php
/**
 * Audit logger.
 *
 * Tracks who generated content, when actions occurred, and
 * records errors for debugging. All entries go to hp_ai_log table.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Logger {

	/**
	 * Valid action types.
	 *
	 * @var array
	 */
	const VALID_ACTIONS = array(
		'generate',
		'edit',
		'approve',
		'reject',
		'post',
		'error',
		'settings_update',
		'topic_create',
		'topic_delete',
	);

	/**
	 * Valid object types.
	 *
	 * @var array
	 */
	const VALID_OBJECT_TYPES = array( 'topic', 'social_post', 'blog', 'system' );

	/**
	 * Log an action.
	 *
	 * @param string $action      Action name (must be in VALID_ACTIONS).
	 * @param string $object_type Object type (topic, post, system).
	 * @param int    $object_id   Related object ID (0 for system actions).
	 * @param array  $details     Additional context as key-value pairs.
	 * @return int|false Inserted row ID or false on failure.
	 */
	public static function log( $action, $object_type, $object_id = 0, $details = array() ) {

		if ( ! in_array( $action, self::VALID_ACTIONS, true ) ) {
			return false;
		}

		if ( ! in_array( $object_type, self::VALID_OBJECT_TYPES, true ) ) {
			return false;
		}

		global $wpdb;

		$table = $wpdb->prefix . 'hp_ai_log';

		$inserted = $wpdb->insert(
			$table,
			array(
				'action'      => sanitize_text_field( $action ),
				'object_type' => sanitize_text_field( $object_type ),
				'object_id'   => absint( $object_id ),
				'user_id'     => get_current_user_id(),
				'details'     => wp_json_encode( $details ),
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get recent log entries.
	 *
	 * @param int    $limit       Number of entries to return.
	 * @param string $action      Optional filter by action type.
	 * @param string $object_type Optional filter by object type.
	 * @return array
	 */
	public static function get_recent( $limit = 10, $action = '', $object_type = '' ) {

		global $wpdb;

		$table = $wpdb->prefix . 'hp_ai_log';
		$where = array( '1=1' );
		$args  = array();

		if ( $action && in_array( $action, self::VALID_ACTIONS, true ) ) {
			$where[] = 'action = %s';
			$args[]  = $action;
		}

		if ( $object_type && in_array( $object_type, self::VALID_OBJECT_TYPES, true ) ) {
			$where[] = 'object_type = %s';
			$args[]  = $object_type;
		}

		$limit    = absint( $limit );
		$where_sq = implode( ' AND ', $where );

		if ( ! empty( $args ) ) {
			$args[] = $limit;
			$query  = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where_sq} ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table and $where_sq are built from hardcoded values.
				$args
			);
		} else {
			$query = $wpdb->prepare(
				"SELECT * FROM {$table} WHERE 1=1 ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit
			);
		}

		return $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- query is prepared above.
	}
}
