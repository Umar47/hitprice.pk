<?php
/**
 * Review image upload handler.
 *
 * Processes image uploads on comment_post, stores attachment IDs in comment
 * meta, and exposes hp_get_review_images() for front-end rendering.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * After a comment is saved, upload any attached review images to the media
 * library and store the attachment IDs in comment meta.
 *
 * Security: nonce verified, user must be logged-in, comment must be a WC
 * review, MIME type confirmed server-side via finfo, size capped at 5 MB,
 * count capped at 3, extension list restricted via wp_handle_upload mimes.
 *
 * @param int $comment_id Newly inserted comment ID.
 */
add_action( 'comment_post', 'hp_handle_review_image_upload' );

function hp_handle_review_image_upload( $comment_id ) {
	// No nonce present — not our form submission.
	if ( empty( $_POST['hp_review_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hp_review_nonce'] ) ), 'hp_review_image_nonce' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$comment = get_comment( $comment_id );
	if ( ! $comment || 'review' !== $comment->comment_type ) {
		return;
	}

	// No files in the request.
	if ( empty( $_FILES['hp_review_images']['name'][0] ) ) {
		return;
	}

	// Load WP upload / image helpers (not available outside admin by default).
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$allowed_mimes = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
	);
	$allowed_types  = array_values( $allowed_mimes );
	$max_size       = 5 * 1024 * 1024; // 5 MB
	$max_images     = 3;
	$files          = $_FILES['hp_review_images']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	$count          = min( count( $files['name'] ), $max_images );
	$attachment_ids = array();
	$post_id        = (int) $comment->comment_post_ID;

	for ( $i = 0; $i < $count; $i++ ) {
		if ( empty( $files['name'][ $i ] ) || UPLOAD_ERR_OK !== (int) $files['error'][ $i ] ) {
			continue;
		}

		if ( (int) $files['size'][ $i ] > $max_size ) {
			continue;
		}

		// Verify MIME using server-side finfo — do not trust the browser-supplied type.
		if ( function_exists( 'finfo_open' ) ) {
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			$mime  = $finfo->file( $files['tmp_name'][ $i ] );
			if ( ! in_array( $mime, $allowed_types, true ) ) {
				continue;
			}
		}

		$single_file = array(
			'name'     => $files['name'][ $i ],
			'type'     => $files['type'][ $i ],
			'tmp_name' => $files['tmp_name'][ $i ],
			'error'    => $files['error'][ $i ],
			'size'     => $files['size'][ $i ],
		);

		$uploaded = wp_handle_upload(
			$single_file,
			array(
				'test_form' => false,
				'mimes'     => $allowed_mimes,
			)
		);

		if ( ! empty( $uploaded['error'] ) || empty( $uploaded['file'] ) ) {
			continue;
		}

		$filetype   = wp_check_filetype( basename( $uploaded['file'] ), null );
		$wp_upload  = wp_upload_dir();
		$attachment = array(
			'guid'           => $wp_upload['url'] . '/' . basename( $uploaded['file'] ),
			'post_mime_type' => $filetype['type'],
			'post_title'     => sanitize_file_name( pathinfo( $uploaded['file'], PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attach_id = wp_insert_attachment( $attachment, $uploaded['file'], $post_id );

		if ( is_wp_error( $attach_id ) || ! $attach_id ) {
			continue;
		}

		$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		$attachment_ids[] = $attach_id;
	}

	if ( ! empty( $attachment_ids ) ) {
		update_comment_meta( $comment_id, 'hp_review_images', $attachment_ids );
	}
}

/**
 * Return uploaded image data for a review comment.
 *
 * @param int $comment_id Comment ID.
 * @return array[] Each item: array{ url: string, full: string, alt: string }.
 */
function hp_get_review_images( $comment_id ) {
	$ids = get_comment_meta( (int) $comment_id, 'hp_review_images', true );

	if ( empty( $ids ) || ! is_array( $ids ) ) {
		return array();
	}

	$images = array();
	foreach ( $ids as $id ) {
		$id  = (int) $id;
		$url = wp_get_attachment_image_url( $id, 'medium' );
		if ( ! $url ) {
			continue;
		}
		$full     = wp_get_attachment_image_url( $id, 'full' );
		$images[] = array(
			'url'  => $url,
			'full' => $full ? $full : $url,
			'alt'  => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
		);
	}

	return $images;
}
