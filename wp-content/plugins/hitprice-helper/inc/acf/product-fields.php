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
					'max'          => 10,
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
