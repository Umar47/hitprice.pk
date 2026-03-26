<?php
/**
 * AJAX request handlers.
 *
 * All handlers verify nonce and capability before processing.
 * Registered on admin_init so they're available for logged-in users only.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Ajax {

	/**
	 * Register AJAX hooks.
	 */
	public function __construct() {

		add_action( 'wp_ajax_hp_ai_add_topic', array( $this, 'add_topic' ) );
		add_action( 'wp_ajax_hp_ai_delete_topic', array( $this, 'delete_topic' ) );
		add_action( 'wp_ajax_hp_ai_update_topic_status', array( $this, 'update_topic_status' ) );
		add_action( 'wp_ajax_hp_ai_get_topics', array( $this, 'get_topics' ) );
		add_action( 'wp_ajax_hp_ai_get_dashboard_stats', array( $this, 'get_dashboard_stats' ) );
		add_action( 'wp_ajax_hp_ai_get_social_drafts', array( $this, 'get_social_drafts' ) );
		add_action( 'wp_ajax_hp_ai_get_blog_drafts', array( $this, 'get_blog_drafts' ) );
		add_action( 'wp_ajax_hp_ai_update_draft_status', array( $this, 'update_draft_status' ) );
		add_action( 'wp_ajax_hp_ai_delete_draft', array( $this, 'delete_draft' ) );
		add_action( 'wp_ajax_hp_ai_generate', array( $this, 'generate' ) );
	}

	/**
	 * Verify nonce and capability.
	 *
	 * Sends JSON error and dies if check fails.
	 *
	 * @return void
	 */
	private function verify_request() {

		if ( ! check_ajax_referer( 'hp_ai_admin_nonce', '_ajax_nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'hitprice-content-engine' ) ), 403 );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'hitprice-content-engine' ) ), 403 );
		}
	}

	/**
	 * Add a new topic.
	 *
	 * @return void
	 */
	public function add_topic() {

		$this->verify_request();

		$data = array(
			'title'        => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
			'content_type' => isset( $_POST['content_type'] ) ? sanitize_text_field( wp_unslash( $_POST['content_type'] ) ) : 'comparison',
			'keywords'     => isset( $_POST['keywords'] ) ? sanitize_textarea_field( wp_unslash( $_POST['keywords'] ) ) : '',
			'priority'     => isset( $_POST['priority'] ) ? absint( $_POST['priority'] ) : 0,
			'month_target' => isset( $_POST['month_target'] ) ? sanitize_text_field( wp_unslash( $_POST['month_target'] ) ) : '',
		);

		$result = HP_AI_Topic::create( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$topic = HP_AI_Topic::get( $result );

		wp_send_json_success( array(
			'message' => __( 'Topic added.', 'hitprice-content-engine' ),
			'topic'   => $topic,
		) );
	}

	/**
	 * Delete a topic.
	 *
	 * @return void
	 */
	public function delete_topic() {

		$this->verify_request();

		$id = isset( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid topic ID.', 'hitprice-content-engine' ) ) );
		}

		$deleted = HP_AI_Topic::delete( $id );

		if ( ! $deleted ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete topic.', 'hitprice-content-engine' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Topic deleted.', 'hitprice-content-engine' ) ) );
	}

	/**
	 * Update a topic's status.
	 *
	 * @return void
	 */
	public function update_topic_status() {

		$this->verify_request();

		$id     = isset( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

		if ( ! $id || ! $status ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'hitprice-content-engine' ) ) );
		}

		$updated = HP_AI_Topic::update_status( $id, $status );

		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update status.', 'hitprice-content-engine' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Status updated.', 'hitprice-content-engine' ) ) );
	}

	/**
	 * Get topics list (for AJAX table refresh).
	 *
	 * @return void
	 */
	public function get_topics() {

		$this->verify_request();

		$args = array(
			'status'       => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '',
			'content_type' => isset( $_POST['content_type'] ) ? sanitize_text_field( wp_unslash( $_POST['content_type'] ) ) : '',
			'search'       => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'per_page'     => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20,
			'page'         => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
		);

		$result = HP_AI_Topic::get_list( $args );
		$counts = HP_AI_Topic::get_status_counts();

		wp_send_json_success( array(
			'items'  => $result['items'],
			'total'  => $result['total'],
			'counts' => $counts,
		) );
	}

	/**
	 * Get dashboard statistics.
	 *
	 * @return void
	 */
	public function get_dashboard_stats() {

		$this->verify_request();

		$topic_counts  = HP_AI_Topic::get_status_counts();
		$social_counts = HP_AI_Draft::get_social_status_counts();
		$blog_counts   = HP_AI_Draft::get_blog_status_counts();

		wp_send_json_success( array(
			'topics_pending' => $topic_counts['pending'],
			'drafts_ready'   => $social_counts['draft'] + $blog_counts['draft'],
			'posted_month'   => HP_AI_Draft::get_monthly_posted_count(),
			'generated_month' => HP_AI_Draft::get_monthly_count(),
			'monthly_target' => (int) get_option( 'hp_ai_monthly_target', 30 ),
		) );
	}

	/**
	 * Get social post drafts.
	 *
	 * @return void
	 */
	public function get_social_drafts() {

		$this->verify_request();

		$args = array(
			'status'   => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '',
			'per_page' => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20,
			'page'     => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
		);

		$result = HP_AI_Draft::get_social_list( $args );
		$counts = HP_AI_Draft::get_social_status_counts();

		wp_send_json_success( array(
			'items'  => $result['items'],
			'total'  => $result['total'],
			'counts' => $counts,
		) );
	}

	/**
	 * Get blog drafts.
	 *
	 * @return void
	 */
	public function get_blog_drafts() {

		$this->verify_request();

		$args = array(
			'status'   => isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '',
			'per_page' => isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 20,
			'page'     => isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1,
		);

		$result = HP_AI_Draft::get_blog_list( $args );
		$counts = HP_AI_Draft::get_blog_status_counts();

		wp_send_json_success( array(
			'items'  => $result['items'],
			'total'  => $result['total'],
			'counts' => $counts,
		) );
	}

	/**
	 * Update a draft's status.
	 *
	 * @return void
	 */
	public function update_draft_status() {

		$this->verify_request();

		$id     = isset( $_POST['draft_id'] ) ? absint( $_POST['draft_id'] ) : 0;
		$type   = isset( $_POST['draft_type'] ) ? sanitize_text_field( wp_unslash( $_POST['draft_type'] ) ) : '';
		$status = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';

		if ( ! $id || ! $type || ! $status ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'hitprice-content-engine' ) ) );
		}

		if ( 'social' === $type ) {
			$updated = HP_AI_Draft::update_social_status( $id, $status );
		} elseif ( 'blog' === $type ) {
			$updated = HP_AI_Draft::update_blog_status( $id, $status );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid draft type.', 'hitprice-content-engine' ) ) );
			return;
		}

		if ( ! $updated ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update status.', 'hitprice-content-engine' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Draft status updated.', 'hitprice-content-engine' ) ) );
	}

	/**
	 * Delete a draft.
	 *
	 * @return void
	 */
	public function delete_draft() {

		$this->verify_request();

		$id   = isset( $_POST['draft_id'] ) ? absint( $_POST['draft_id'] ) : 0;
		$type = isset( $_POST['draft_type'] ) ? sanitize_text_field( wp_unslash( $_POST['draft_type'] ) ) : '';

		if ( ! $id || ! $type ) {
			wp_send_json_error( array( 'message' => __( 'Invalid request.', 'hitprice-content-engine' ) ) );
		}

		if ( 'social' === $type ) {
			$deleted = HP_AI_Draft::delete_social( $id );
		} elseif ( 'blog' === $type ) {
			$deleted = HP_AI_Draft::delete_blog( $id );
		} else {
			wp_send_json_error( array( 'message' => __( 'Invalid draft type.', 'hitprice-content-engine' ) ) );
			return;
		}

		if ( ! $deleted ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete draft.', 'hitprice-content-engine' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Draft deleted.', 'hitprice-content-engine' ) ) );
	}

	/**
	 * Trigger content generation for a topic.
	 *
	 * Uses HP_AI_Generator to produce mock (or future AI) content
	 * and save it as a draft in the database.
	 *
	 * @return void
	 */
	public function generate() {

		$this->verify_request();

		$topic_id    = isset( $_POST['topic_id'] ) ? absint( $_POST['topic_id'] ) : 0;
		$output_type = isset( $_POST['output_type'] ) ? sanitize_text_field( wp_unslash( $_POST['output_type'] ) ) : 'social';

		if ( ! $topic_id ) {
			wp_send_json_error( array( 'message' => __( 'Please select a topic.', 'hitprice-content-engine' ) ) );
		}

		if ( ! in_array( $output_type, array( 'social', 'blog' ), true ) ) {
			$output_type = 'social';
		}

		$result = HP_AI_Generator::generate( $topic_id, $output_type );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$type_label = 'social' === $output_type
			? __( 'Social post', 'hitprice-content-engine' )
			: __( 'Blog article', 'hitprice-content-engine' );

		wp_send_json_success( array(
			'message'  => sprintf(
				/* translators: 1: content type label, 2: draft ID */
				__( '%1$s draft #%2$d created successfully. View it in Drafts.', 'hitprice-content-engine' ),
				$type_label,
				$result
			),
			'draft_id' => $result,
		) );
	}
}
