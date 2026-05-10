<?php
/**
 * ACF field groups for single product pages.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register product ACF field groups.
 */
function hp_register_product_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ── Page Settings: PTA badge, Key Highlights, Overview Specs ──────────
	acf_add_local_field_group(
		array(
			'key'    => 'group_hp_product_page_settings',
			'title'  => 'HitPrice — Page Settings',
			'fields' => array(

				// PTA Approved toggle
				array(
					'key'               => 'field_hp_pta_approved',
					'label'             => 'Show PTA Approved Badge',
					'name'              => 'hp_pta_approved',
					'type'              => 'true_false',
					'message'           => 'Show the PTA Approved badge on this product',
					'default_value'     => 0,
					'ui'                => 1,
					'ui_on_text'        => 'Yes',
					'ui_off_text'       => 'No',
					'instructions'      => 'Enable only for officially PTA-approved devices.',
				),

				// Key Highlights — left column (WYSIWYG)
				array(
					'key'          => 'field_hp_key_highlights_content',
					'label'        => 'Key Highlights — Content (Left)',
					'name'         => 'hp_key_highlights_content',
					'type'         => 'wysiwyg',
					'tabs'         => 'all',
					'toolbar'      => 'basic',
					'media_upload' => 0,
					'instructions' => 'Free text with HTML support. Use a bullet list (<ul>) for the checkmark-style highlights.',
				),

				// Key Highlights — right column (image)
				array(
					'key'           => 'field_hp_key_highlights_image',
					'label'         => 'Key Highlights — Infographic Image (Right)',
					'name'          => 'hp_key_highlights_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'Product infographic or lifestyle image. Hidden on mobile — the overview specs row is shown instead.',
				),

				// Overview Specs repeater (max 8)
				array(
					'key'          => 'field_hp_overview_specs',
					'label'        => 'Overview Specs (Right Column — Desktop)',
					'name'         => 'hp_overview_specs',
					'type'         => 'repeater',
					'max'          => 8,
					'min'          => 0,
					'layout'       => 'table',
					'button_label' => 'Add Spec',
					'instructions' => 'Icon + title + value cards shown in the Overview tab (desktop) and as a compact row above Key Highlights (mobile). Max 8 items.',
					'sub_fields'   => array(
						array(
							'key'          => 'field_hp_os_icon',
							'label'        => 'Icon',
							'name'         => 'icon',
							'type'         => 'text',
							'placeholder'  => 'e.g. fa-solid fa-mobile',
							'instructions' => 'Font Awesome class names. Browse at fontawesome.com/icons',
							'column_width' => '20',
						),
						array(
							'key'          => 'field_hp_os_title',
							'label'        => 'Title',
							'name'         => 'title',
							'type'         => 'text',
							'required'     => 1,
							'placeholder'  => 'e.g. Display',
							'column_width' => '40',
						),
						array(
							'key'          => 'field_hp_os_value',
							'label'        => 'Value',
							'name'         => 'value',
							'type'         => 'text',
							'required'     => 1,
							'placeholder'  => 'e.g. 6.7" AMOLED',
							'column_width' => '40',
						),
					),
				),

			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position'   => 'normal',
			'style'      => 'default',
			'menu_order' => 5,
		)
	);

	// Feature Cards repeater (max 3).
	acf_add_local_field_group(
		array(
			'key'      => 'group_hp_product_features',
			'title'    => 'Product Features',
			'fields'   => array(
				array(
					'key'          => 'field_hp_feature_cards',
					'label'        => 'Feature Cards',
					'name'         => 'hp_feature_cards',
					'type'         => 'repeater',
					'max'          => 3,
					'layout'       => 'block',
					'button_label' => 'Add Feature Card',
					'sub_fields'   => array(
						array(
							'key'           => 'field_hp_feature_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'        => 'field_hp_feature_title',
							'label'      => 'Title',
							'name'       => 'title',
							'type'       => 'text',
							'required'   => 1,
						),
						array(
							'key'        => 'field_hp_feature_description',
							'label'      => 'Description',
							'name'       => 'description',
							'type'       => 'textarea',
							'rows'       => 3,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position'     => 'normal',
			'style'        => 'default',
			'menu_order'   => 10,
		)
	);

	// Detail Specs flexible content (max 10 layouts).
	acf_add_local_field_group(
		array(
			'key'      => 'group_hp_product_detail_specs',
			'title'    => 'Product Detail Specs',
			'fields'   => array(
				array(
					'key'          => 'field_hp_detail_specs',
					'label'        => 'Detail Sections',
					'name'         => 'hp_detail_specs',
					'type'         => 'flexible_content',
					'button_label' => 'Add Section',
					'layouts'      => array(
						'layout_text_block'     => array(
							'key'        => 'layout_hp_text_block',
							'name'       => 'text_block',
							'label'      => 'Text Block',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_hp_tb_heading',
									'label' => 'Heading',
									'name'  => 'heading',
									'type'  => 'text',
								),
								array(
									'key'   => 'field_hp_tb_content',
									'label' => 'Content',
									'name'  => 'content',
									'type'  => 'wysiwyg',
									'tabs'  => 'all',
								),
							),
						),
						'layout_key_value_table' => array(
							'key'        => 'layout_hp_kv_table',
							'name'       => 'key_value_table',
							'label'      => 'Key-Value Table',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_hp_kv_heading',
									'label' => 'Heading',
									'name'  => 'heading',
									'type'  => 'text',
								),
								array(
									'key'          => 'field_hp_kv_rows',
									'label'        => 'Rows',
									'name'         => 'rows',
									'type'         => 'repeater',
									'max'          => 20,
									'layout'       => 'table',
									'button_label' => 'Add Row',
									'sub_fields'   => array(
										array(
											'key'   => 'field_hp_kv_label',
											'label' => 'Label',
											'name'  => 'label',
											'type'  => 'text',
										),
										array(
											'key'   => 'field_hp_kv_value',
											'label' => 'Value',
											'name'  => 'value',
											'type'  => 'text',
										),
									),
								),
							),
						),
						'layout_media_block'     => array(
							'key'        => 'layout_hp_media_block',
							'name'       => 'media_block',
							'label'      => 'Media Block',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_hp_mb_heading',
									'label' => 'Heading',
									'name'  => 'heading',
									'type'  => 'text',
								),
								array(
									'key'           => 'field_hp_mb_image',
									'label'         => 'Image',
									'name'          => 'image',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'medium',
								),
								array(
									'key'   => 'field_hp_mb_caption',
									'label' => 'Caption',
									'name'  => 'caption',
									'type'  => 'text',
								),
							),
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'product',
					),
				),
			),
			'position'     => 'normal',
			'style'        => 'default',
			'menu_order'   => 20,
		)
	);
}
add_action( 'acf/init', 'hp_register_product_acf_fields' );
