<?php
/**
 * Topic CRUD operations.
 *
 * Handles creating, reading, updating, and deleting topics
 * in the hp_ai_topics table.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Topic {

	/**
	 * Valid content types.
	 *
	 * @var array
	 */
	const CONTENT_TYPES = array(
		'comparison' => 'Phone Comparison',
		'launch'     => 'New Launch',
		'upcoming'   => 'Upcoming / Leaks',
		'tips'       => 'Tips & Tricks',
		'budget'     => 'Best Under Budget',
		'deal'       => 'Deal / Price Alert',
	);

	/**
	 * Valid statuses.
	 *
	 * @var array
	 */
	const STATUSES = array( 'pending', 'generated', 'skipped' );

	/**
	 * Get the database table name.
	 *
	 * @return string
	 */
	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'hp_ai_topics';
	}

	/**
	 * Public accessor for table name (used by JOIN queries in other classes).
	 *
	 * @return string
	 */
	public static function table_name() {
		return self::table();
	}

	/**
	 * Insert a new topic.
	 *
	 * @param array $data {
	 *     Topic data.
	 *     @type string $title        Topic title.
	 *     @type string $content_type Content type key.
	 *     @type string $keywords     Comma-separated keywords.
	 *     @type int    $priority     0 or 1.
	 *     @type string $month_target Format: YYYY-MM.
	 * }
	 * @return int|WP_Error Inserted ID or error.
	 */
	public static function create( $data ) {

		global $wpdb;

		$title = isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '';
		if ( empty( $title ) ) {
			return new WP_Error( 'missing_title', __( 'Topic title is required.', 'hitprice-content-engine' ) );
		}

		$content_type = isset( $data['content_type'] ) ? sanitize_text_field( $data['content_type'] ) : 'comparison';
		if ( ! array_key_exists( $content_type, self::CONTENT_TYPES ) ) {
			$content_type = 'comparison';
		}

		$keywords     = isset( $data['keywords'] ) ? sanitize_textarea_field( $data['keywords'] ) : '';
		$priority     = isset( $data['priority'] ) ? absint( $data['priority'] ) : 0;
		$priority     = $priority ? 1 : 0;
		$month_target = isset( $data['month_target'] ) ? sanitize_text_field( $data['month_target'] ) : '';

		if ( $month_target && ! preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $month_target ) ) {
			$month_target = '';
		}

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'title'        => $title,
				'content_type' => $content_type,
				'keywords'     => $keywords,
				'priority'     => $priority,
				'status'       => 'pending',
				'month_target' => $month_target ?: null,
				'created_by'   => get_current_user_id(),
				'created_at'   => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to insert topic.', 'hitprice-content-engine' ) );
		}

		$topic_id = $wpdb->insert_id;

		HP_AI_Logger::log( 'topic_create', 'topic', $topic_id, array(
			'title'        => $title,
			'content_type' => $content_type,
		) );

		return $topic_id;
	}

	/**
	 * Get a single topic by ID.
	 *
	 * @param int $id Topic ID.
	 * @return object|null
	 */
	public static function get( $id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . self::table() . " WHERE id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Get topics with optional filters.
	 *
	 * @param array $args {
	 *     Optional filters.
	 *     @type string $status       Filter by status.
	 *     @type string $content_type Filter by content type.
	 *     @type string $month_target Filter by target month.
	 *     @type string $search       Search title/keywords.
	 *     @type int    $per_page     Results per page.
	 *     @type int    $page         Page number.
	 *     @type string $orderby      Column to order by.
	 *     @type string $order        ASC or DESC.
	 * }
	 * @return array { 'items' => array, 'total' => int }
	 */
	public static function get_list( $args = array() ) {

		global $wpdb;

		$table = self::table();

		$defaults = array(
			'status'       => '',
			'content_type' => '',
			'month_target' => '',
			'search'       => '',
			'per_page'     => 20,
			'page'         => 1,
			'orderby'      => 'created_at',
			'order'        => 'DESC',
		);

		$args  = wp_parse_args( $args, $defaults );
		$where = array( '1=1' );
		$vals  = array();

		if ( $args['status'] && in_array( $args['status'], self::STATUSES, true ) ) {
			$where[] = 'status = %s';
			$vals[]  = $args['status'];
		}

		if ( $args['content_type'] && array_key_exists( $args['content_type'], self::CONTENT_TYPES ) ) {
			$where[] = 'content_type = %s';
			$vals[]  = $args['content_type'];
		}

		if ( $args['month_target'] && preg_match( '/^\d{4}-(0[1-9]|1[0-2])$/', $args['month_target'] ) ) {
			$where[] = 'month_target = %s';
			$vals[]  = $args['month_target'];
		}

		if ( $args['search'] ) {
			$like    = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[] = '(title LIKE %s OR keywords LIKE %s)';
			$vals[]  = $like;
			$vals[]  = $like;
		}

		$where_sql = implode( ' AND ', $where );

		// Whitelist orderby.
		$allowed_orderby = array( 'id', 'title', 'content_type', 'priority', 'status', 'month_target', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$per_page = absint( $args['per_page'] );
		$per_page = max( 1, min( 100, $per_page ) );
		$page     = max( 1, absint( $args['page'] ) );
		$offset   = ( $page - 1 ) * $per_page;

		// Count total.
		if ( ! empty( $vals ) ) {
			$count_query = $wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$vals
			);
		} else {
			$count_query = "SELECT COUNT(*) FROM {$table} WHERE 1=1";
		}

		$total = (int) $wpdb->get_var( $count_query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		// Fetch rows.
		$query_vals   = $vals;
		$query_vals[] = $per_page;
		$query_vals[] = $offset;

		if ( ! empty( $vals ) ) {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$query_vals
				)
			);
		} else {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE 1=1 ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$per_page,
					$offset
				)
			);
		}

		return array(
			'items' => $rows ?: array(),
			'total' => $total,
		);
	}

	/**
	 * Update topic status.
	 *
	 * @param int    $id     Topic ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public static function update_status( $id, $status ) {

		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return false;
		}

		global $wpdb;

		$updated = $wpdb->update(
			self::table(),
			array( 'status' => $status ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Delete a topic.
	 *
	 * @param int $id Topic ID.
	 * @return bool
	 */
	public static function delete( $id ) {

		global $wpdb;

		$topic = self::get( $id );
		if ( ! $topic ) {
			return false;
		}

		$deleted = $wpdb->delete(
			self::table(),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);

		if ( $deleted ) {
			HP_AI_Logger::log( 'topic_delete', 'topic', $id, array(
				'title' => $topic->title,
			) );
		}

		return false !== $deleted;
	}

	/**
	 * Get counts grouped by status.
	 *
	 * @return array Associative: status => count.
	 */
	public static function get_status_counts() {

		global $wpdb;

		$table   = self::table();
		$results = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$table} GROUP BY status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			OBJECT_K
		);

		$counts = array();
		foreach ( self::STATUSES as $status ) {
			$counts[ $status ] = isset( $results[ $status ] ) ? (int) $results[ $status ]->count : 0;
		}

		return $counts;
	}

	/**
	 * Get all pending topics for dropdown selector.
	 *
	 * @return array
	 */
	public static function get_pending_for_select() {

		global $wpdb;

		$table = self::table();

		return $wpdb->get_results(
			"SELECT id, title, content_type FROM {$table} WHERE status = 'pending' ORDER BY priority DESC, created_at ASC" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
