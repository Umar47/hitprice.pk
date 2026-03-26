<?php
/**
 * Content generation orchestrator.
 *
 * Takes a topic and output type, calls the appropriate content
 * source (mock for now, AI API later), validates the response,
 * sanitizes all fields, and saves the result as a draft.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Generator {

	/**
	 * Generate content for a topic and save as draft.
	 *
	 * @param int    $topic_id    Topic ID.
	 * @param string $output_type 'social' or 'blog'.
	 * @return int|WP_Error Draft ID on success, WP_Error on failure.
	 */
	public static function generate( $topic_id, $output_type = 'social' ) {

		$topic = HP_AI_Topic::get( $topic_id );

		if ( ! $topic ) {
			return new WP_Error( 'invalid_topic', __( 'Topic not found.', 'hitprice-content-engine' ) );
		}

		if ( 'pending' !== $topic->status ) {
			return new WP_Error( 'invalid_status', __( 'Topic is not in pending status.', 'hitprice-content-engine' ) );
		}

		if ( ! in_array( $output_type, array( 'social', 'blog' ), true ) ) {
			return new WP_Error( 'invalid_type', __( 'Output type must be social or blog.', 'hitprice-content-engine' ) );
		}

		// Get content from mock (or AI later).
		$mode = get_option( 'hp_ai_mode', 'mock' );

		if ( 'mock' === $mode ) {
			$data = 'social' === $output_type
				? HP_AI_Mock::social( $topic )
				: HP_AI_Mock::blog( $topic );
		} else {
			// Future: HP_AI_Service::call( $topic, $output_type )
			// For now, fall back to mock even in live mode.
			$data = 'social' === $output_type
				? HP_AI_Mock::social( $topic )
				: HP_AI_Mock::blog( $topic );
		}

		// Validate response structure.
		if ( 'social' === $output_type ) {
			$valid = self::validate_social( $data );
		} else {
			$valid = self::validate_blog( $data );
		}

		if ( is_wp_error( $valid ) ) {
			HP_AI_Logger::log( 'error', $output_type === 'social' ? 'social_post' : 'blog', 0, array(
				'topic_id' => $topic_id,
				'reason'   => $valid->get_error_message(),
			) );
			return $valid;
		}

		// Sanitize and save.
		if ( 'social' === $output_type ) {
			$draft_id = self::save_social_draft( $topic, $data );
		} else {
			$draft_id = self::save_blog_draft( $topic, $data );
		}

		if ( is_wp_error( $draft_id ) ) {
			return $draft_id;
		}

		// Mark topic as generated.
		HP_AI_Topic::update_status( $topic_id, 'generated' );

		// Log.
		HP_AI_Logger::log( 'generate', $output_type === 'social' ? 'social_post' : 'blog', $draft_id, array(
			'topic_id'    => $topic_id,
			'topic_title' => $topic->title,
			'mode'        => $mode,
			'provider'    => $data['ai_provider'] ?? 'unknown',
		) );

		return $draft_id;
	}

	/**
	 * Validate social post data structure.
	 *
	 * @param array $data Generated data.
	 * @return true|WP_Error
	 */
	private static function validate_social( $data ) {

		$required = array( 'caption', 'hook_line', 'hashtags' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					sprintf(
						/* translators: %s: field name */
						__( 'Generated social post is missing required field: %s', 'hitprice-content-engine' ),
						$field
					)
				);
			}
		}

		return true;
	}

	/**
	 * Validate blog post data structure.
	 *
	 * @param array $data Generated data.
	 * @return true|WP_Error
	 */
	private static function validate_blog( $data ) {

		$required = array( 'title', 'content', 'excerpt' );

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new WP_Error(
					'missing_field',
					sprintf(
						/* translators: %s: field name */
						__( 'Generated blog post is missing required field: %s', 'hitprice-content-engine' ),
						$field
					)
				);
			}
		}

		return true;
	}

	/**
	 * Sanitize and save a social post draft.
	 *
	 * @param object $topic Topic row.
	 * @param array  $data  Generated social post data.
	 * @return int|WP_Error Inserted row ID or error.
	 */
	private static function save_social_draft( $topic, $data ) {

		global $wpdb;

		$table = $wpdb->prefix . 'hp_ai_social_posts';

		$inserted = $wpdb->insert(
			$table,
			array(
				'topic_id'        => absint( $topic->id ),
				'platform'        => sanitize_text_field( $data['platform'] ?? 'facebook' ),
				'caption'         => sanitize_textarea_field( $data['caption'] ),
				'hashtags'        => sanitize_textarea_field( $data['hashtags'] ),
				'image_text'      => sanitize_text_field( $data['image_text'] ?? '' ),
				'carousel_ideas'  => sanitize_textarea_field( $data['carousel_ideas'] ?? '' ),
				'hook_line'       => sanitize_text_field( wp_trim_words( $data['hook_line'], 30 ) ),
				'cta_text'        => sanitize_text_field( $data['cta_text'] ?? '' ),
				'ai_provider'     => sanitize_text_field( $data['ai_provider'] ?? '' ),
				'ai_model'        => sanitize_text_field( $data['ai_model'] ?? '' ),
				'ai_raw_response' => wp_json_encode( $data ),
				'tokens_used'     => absint( $data['tokens_used'] ?? 0 ),
				'status'          => 'draft',
				'created_by'      => get_current_user_id(),
				'created_at'      => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to save social post draft.', 'hitprice-content-engine' ) );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Sanitize and save a blog post draft.
	 *
	 * @param object $topic Topic row.
	 * @param array  $data  Generated blog post data.
	 * @return int|WP_Error Inserted row ID or error.
	 */
	private static function save_blog_draft( $topic, $data ) {

		global $wpdb;

		$table = $wpdb->prefix . 'hp_ai_blogs';

		$inserted = $wpdb->insert(
			$table,
			array(
				'topic_id'              => absint( $topic->id ),
				'title'                 => sanitize_text_field( $data['title'] ),
				'slug'                  => sanitize_title( $data['slug'] ?? $data['title'] ),
				'content'               => wp_kses_post( $data['content'] ),
				'excerpt'               => sanitize_textarea_field( $data['excerpt'] ),
				'meta_title'            => sanitize_text_field( $data['meta_title'] ?? '' ),
				'meta_description'      => sanitize_text_field( wp_trim_words( $data['meta_description'] ?? '', 30 ) ),
				'focus_keyword'         => sanitize_text_field( $data['focus_keyword'] ?? '' ),
				'featured_image_prompt' => sanitize_textarea_field( $data['featured_image_prompt'] ?? '' ),
				'ai_provider'           => sanitize_text_field( $data['ai_provider'] ?? '' ),
				'ai_model'              => sanitize_text_field( $data['ai_model'] ?? '' ),
				'ai_raw_response'       => wp_json_encode( $data ),
				'tokens_used'           => absint( $data['tokens_used'] ?? 0 ),
				'status'                => 'draft',
				'created_by'            => get_current_user_id(),
				'created_at'            => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'db_error', __( 'Failed to save blog draft.', 'hitprice-content-engine' ) );
		}

		return $wpdb->insert_id;
	}
}
