<?php
/**
 * Draft read and status management.
 *
 * Handles fetching and managing drafts from both
 * hp_ai_social_posts and hp_ai_blogs tables.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Draft {

	/**
	 * Valid draft statuses.
	 *
	 * @var array
	 */
	const STATUSES = array( 'draft', 'approved', 'posted', 'published', 'rejected' );

	/**
	 * Valid draft types.
	 *
	 * @var array
	 */
	const TYPES = array( 'social', 'blog' );

	/**
	 * Get social posts table name.
	 *
	 * @return string
	 */
	private static function social_table() {
		global $wpdb;
		return $wpdb->prefix . 'hp_ai_social_posts';
	}

	/**
	 * Get blogs table name.
	 *
	 * @return string
	 */
	private static function blog_table() {
		global $wpdb;
		return $wpdb->prefix . 'hp_ai_blogs';
	}

	/**
	 * Get a single social post draft.
	 *
	 * @param int $id Draft ID.
	 * @return object|null
	 */
	public static function get_social( $id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT sp.*, t.title AS topic_title, t.content_type
				 FROM " . self::social_table() . " sp
				 LEFT JOIN " . HP_AI_Topic::table_name() . " t ON sp.topic_id = t.id
				 WHERE sp.id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Get a single blog draft.
	 *
	 * @param int $id Draft ID.
	 * @return object|null
	 */
	public static function get_blog( $id ) {

		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*, t.title AS topic_title, t.content_type
				 FROM " . self::blog_table() . " b
				 LEFT JOIN " . HP_AI_Topic::table_name() . " t ON b.topic_id = t.id
				 WHERE b.id = %d",
				absint( $id )
			)
		);
	}

	/**
	 * Get social post drafts with filters.
	 *
	 * @param array $args {
	 *     @type string $status   Filter by status.
	 *     @type int    $per_page Results per page.
	 *     @type int    $page     Page number.
	 * }
	 * @return array { 'items' => array, 'total' => int }
	 */
	public static function get_social_list( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'status'   => '',
			'per_page' => 20,
			'page'     => 1,
		);

		$args     = wp_parse_args( $args, $defaults );
		$table    = self::social_table();
		$t_table  = HP_AI_Topic::table_name();
		$where    = array( '1=1' );
		$vals     = array();

		$valid_social_statuses = array( 'draft', 'approved', 'posted', 'rejected' );

		if ( $args['status'] && in_array( $args['status'], $valid_social_statuses, true ) ) {
			$where[] = 'sp.status = %s';
			$vals[]  = $args['status'];
		}

		$where_sql = implode( ' AND ', $where );
		$per_page  = max( 1, min( 100, absint( $args['per_page'] ) ) );
		$page      = max( 1, absint( $args['page'] ) );
		$offset    = ( $page - 1 ) * $per_page;

		// Count.
		if ( ! empty( $vals ) ) {
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} sp WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$vals
				)
			);
		} else {
			$total = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$table} sp WHERE 1=1" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		}

		// Fetch.
		$query_vals   = $vals;
		$query_vals[] = $per_page;
		$query_vals[] = $offset;

		if ( ! empty( $vals ) ) {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT sp.*, t.title AS topic_title, t.content_type
					 FROM {$table} sp
					 LEFT JOIN {$t_table} t ON sp.topic_id = t.id
					 WHERE {$where_sql}
					 ORDER BY sp.created_at DESC
					 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$query_vals
				)
			);
		} else {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT sp.*, t.title AS topic_title, t.content_type
					 FROM {$table} sp
					 LEFT JOIN {$t_table} t ON sp.topic_id = t.id
					 WHERE 1=1
					 ORDER BY sp.created_at DESC
					 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$per_page,
					$offset
				)
			);
		}

		return array(
			'items' => $items ?: array(),
			'total' => $total,
		);
	}

	/**
	 * Get blog drafts with filters.
	 *
	 * @param array $args {
	 *     @type string $status   Filter by status.
	 *     @type int    $per_page Results per page.
	 *     @type int    $page     Page number.
	 * }
	 * @return array { 'items' => array, 'total' => int }
	 */
	public static function get_blog_list( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'status'   => '',
			'per_page' => 20,
			'page'     => 1,
		);

		$args     = wp_parse_args( $args, $defaults );
		$table    = self::blog_table();
		$t_table  = HP_AI_Topic::table_name();
		$where    = array( '1=1' );
		$vals     = array();

		$valid_blog_statuses = array( 'draft', 'approved', 'published', 'rejected' );

		if ( $args['status'] && in_array( $args['status'], $valid_blog_statuses, true ) ) {
			$where[] = 'b.status = %s';
			$vals[]  = $args['status'];
		}

		$where_sql = implode( ' AND ', $where );
		$per_page  = max( 1, min( 100, absint( $args['per_page'] ) ) );
		$page      = max( 1, absint( $args['page'] ) );
		$offset    = ( $page - 1 ) * $per_page;

		if ( ! empty( $vals ) ) {
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} b WHERE {$where_sql}", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$vals
				)
			);
		} else {
			$total = (int) $wpdb->get_var(
				"SELECT COUNT(*) FROM {$table} b WHERE 1=1" // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
		}

		$query_vals   = $vals;
		$query_vals[] = $per_page;
		$query_vals[] = $offset;

		if ( ! empty( $vals ) ) {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT b.*, t.title AS topic_title, t.content_type
					 FROM {$table} b
					 LEFT JOIN {$t_table} t ON b.topic_id = t.id
					 WHERE {$where_sql}
					 ORDER BY b.created_at DESC
					 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$query_vals
				)
			);
		} else {
			$items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT b.*, t.title AS topic_title, t.content_type
					 FROM {$table} b
					 LEFT JOIN {$t_table} t ON b.topic_id = t.id
					 WHERE 1=1
					 ORDER BY b.created_at DESC
					 LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$per_page,
					$offset
				)
			);
		}

		return array(
			'items' => $items ?: array(),
			'total' => $total,
		);
	}

	/**
	 * Update social post status.
	 *
	 * @param int    $id     Post ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public static function update_social_status( $id, $status ) {

		$valid = array( 'draft', 'approved', 'posted', 'rejected' );
		if ( ! in_array( $status, $valid, true ) ) {
			return false;
		}

		global $wpdb;

		$data = array( 'status' => $status );
		if ( 'posted' === $status ) {
			$data['posted_at'] = current_time( 'mysql' );
		}

		$updated = $wpdb->update(
			self::social_table(),
			$data,
			array( 'id' => absint( $id ) ),
			array_fill( 0, count( $data ), '%s' ),
			array( '%d' )
		);

		if ( false !== $updated ) {
			HP_AI_Logger::log( $status === 'posted' ? 'post' : $status, 'social_post', $id );
		}

		return false !== $updated;
	}

	/**
	 * Update blog draft status.
	 *
	 * @param int    $id     Blog ID.
	 * @param string $status New status.
	 * @return bool
	 */
	public static function update_blog_status( $id, $status ) {

		$valid = array( 'draft', 'approved', 'published', 'rejected' );
		if ( ! in_array( $status, $valid, true ) ) {
			return false;
		}

		global $wpdb;

		$data = array( 'status' => $status );
		if ( 'published' === $status ) {
			$data['published_at'] = current_time( 'mysql' );
		}

		$updated = $wpdb->update(
			self::blog_table(),
			$data,
			array( 'id' => absint( $id ) ),
			array_fill( 0, count( $data ), '%s' ),
			array( '%d' )
		);

		if ( false !== $updated ) {
			HP_AI_Logger::log( $status === 'published' ? 'post' : $status, 'blog', $id );
		}

		return false !== $updated;
	}

	/**
	 * Delete a social post draft.
	 *
	 * @param int $id Post ID.
	 * @return bool
	 */
	public static function delete_social( $id ) {

		global $wpdb;

		return false !== $wpdb->delete(
			self::social_table(),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}

	/**
	 * Delete a blog draft.
	 *
	 * @param int $id Blog ID.
	 * @return bool
	 */
	public static function delete_blog( $id ) {

		global $wpdb;

		return false !== $wpdb->delete(
			self::blog_table(),
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}

	/**
	 * Get status counts for social posts.
	 *
	 * @return array
	 */
	public static function get_social_status_counts() {

		global $wpdb;

		$table   = self::social_table();
		$results = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$table} GROUP BY status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			OBJECT_K
		);

		$counts = array();
		foreach ( array( 'draft', 'approved', 'posted', 'rejected' ) as $s ) {
			$counts[ $s ] = isset( $results[ $s ] ) ? (int) $results[ $s ]->count : 0;
		}

		return $counts;
	}

	/**
	 * Get status counts for blogs.
	 *
	 * @return array
	 */
	public static function get_blog_status_counts() {

		global $wpdb;

		$table   = self::blog_table();
		$results = $wpdb->get_results(
			"SELECT status, COUNT(*) as count FROM {$table} GROUP BY status", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			OBJECT_K
		);

		$counts = array();
		foreach ( array( 'draft', 'approved', 'published', 'rejected' ) as $s ) {
			$counts[ $s ] = isset( $results[ $s ] ) ? (int) $results[ $s ]->count : 0;
		}

		return $counts;
	}

	/**
	 * Get count of posts created this month (social + blog).
	 *
	 * @return int
	 */
	public static function get_monthly_count() {

		global $wpdb;

		$month_start = gmdate( 'Y-m-01 00:00:00' );

		$social = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::social_table() . " WHERE created_at >= %s",
				$month_start
			)
		);

		$blog = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::blog_table() . " WHERE created_at >= %s",
				$month_start
			)
		);

		return $social + $blog;
	}

	/**
	 * Get count of posted/published items this month.
	 *
	 * @return int
	 */
	public static function get_monthly_posted_count() {

		global $wpdb;

		$month_start = gmdate( 'Y-m-01 00:00:00' );

		$social = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::social_table() . " WHERE status = 'posted' AND posted_at >= %s",
				$month_start
			)
		);

		$blog = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM " . self::blog_table() . " WHERE status = 'published' AND published_at >= %s",
				$month_start
			)
		);

		return $social + $blog;
	}
}
