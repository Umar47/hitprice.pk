<?php
/**
 * Bulk Specs Importer — admin enhancement for ACF flexible content
 * "Product Detail Specs" on the WooCommerce product edit screen.
 *
 * Adds an "Add Bulk" button next to "Add Section" that opens a modal
 * letting admins paste competitor spec HTML and auto-populate sections
 * (key_value_table layouts) without changing ACF structure.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue bulk-specs assets only on product edit screens.
 *
 * @param string $hook Current admin page hook suffix.
 */
function hp_bulk_specs_enqueue_assets( $hook ) {

	// Limit to post edit screens.
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	// Capability gate (WooCommerce maps edit_products; fall back to edit_posts).
	if ( ! current_user_can( 'edit_products' ) && ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	// Restrict to product post type.
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	$base_url  = HITPRICE_HELPER_URL . 'assets/';
	$base_path = HITPRICE_HELPER_PATH . 'assets/';

	$css_rel = 'css/admin-bulk-specs.css';
	$js_rel  = 'js/admin-bulk-specs.js';

	$css_ver = file_exists( $base_path . $css_rel ) ? filemtime( $base_path . $css_rel ) : '1.0.0';
	$js_ver  = file_exists( $base_path . $js_rel ) ? filemtime( $base_path . $js_rel ) : '1.0.0';

	wp_enqueue_style(
		'hp-bulk-specs',
		$base_url . $css_rel,
		array(),
		$css_ver
	);

	wp_enqueue_script(
		'hp-bulk-specs',
		$base_url . $js_rel,
		array( 'jquery', 'acf-input' ),
		$js_ver,
		true
	);

	// Pass field keys + i18n to JS. No server endpoints — parsing is client-side.
	wp_localize_script(
		'hp-bulk-specs',
		'HP_BULK_SPECS',
		array(
			'targetFieldKey'  => 'field_hp_detail_specs',
			'layoutName'      => 'key_value_table',
			'headingFieldKey' => 'field_hp_kv_heading',
			'rowsFieldKey'    => 'field_hp_kv_rows',
			'labelFieldKey'   => 'field_hp_kv_label',
			'valueFieldKey'   => 'field_hp_kv_value',
			'i18n'            => array(
				'buttonLabel' => __( 'Add Bulk', 'hitprice-helper' ),
				'modalTitle'  => __( 'Bulk Import Product Specs', 'hitprice-helper' ),
				'modalHint'   => __( 'Paste competitor spec HTML below. Each .p-spec-table block becomes a new section.', 'hitprice-helper' ),
				'placeholder' => __( 'Paste HTML here...', 'hitprice-helper' ),
				'parseInsert' => __( 'Parse & Insert', 'hitprice-helper' ),
				'cancel'      => __( 'Cancel', 'hitprice-helper' ),
				'noSections'  => __( 'No valid spec sections found in the pasted HTML.', 'hitprice-helper' ),
				'imported'    => __( 'Imported %1$d section(s) and %2$d row(s).', 'hitprice-helper' ),
				'parseError'  => __( 'Could not parse the HTML. Please verify the structure.', 'hitprice-helper' ),
				'closeLabel'  => __( 'Close', 'hitprice-helper' ),
			),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'hp_bulk_specs_enqueue_assets' );
