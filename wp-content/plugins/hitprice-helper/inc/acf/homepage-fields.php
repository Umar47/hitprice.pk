<?php
/**
 * Homepage ACF field registration.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register homepage flexible content fields.
 *
 * @return void
 */
function hp_register_homepage_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_hp_homepage_builder',
			'title'                 => 'Homepage Builder',
			'fields'                => array(
				array(
					'key'          => 'field_hp_home_sections',
					'label'        => 'Homepage Sections',
					'name'         => 'home_sections',
					'type'         => 'flexible_content',
					'button_label' => 'Add Section',
					'layouts'      => array(
						'layout_hp_hero_section'                => hp_get_homepage_hero_layout_fields(),
						'layout_hp_featured_categories_section' => hp_get_homepage_featured_categories_layout_fields(),
						'layout_hp_product_block_section'       => hp_get_homepage_product_block_layout_fields(),
						'layout_hp_promo_banner_section'        => hp_get_homepage_promo_banner_layout_fields(),
						'layout_hp_usp_section'                 => hp_get_homepage_usp_layout_fields(),
						'layout_hp_preview_tiles_section'       => hp_get_homepage_preview_tiles_layout_fields(),
						'layout_hp_campaign_tiles_section'      => hp_get_homepage_campaign_tiles_layout_fields(),
						'layout_hp_trust_section'               => hp_get_homepage_trust_layout_fields(),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-homepage.php',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'acf_after_title',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
add_action( 'acf/init', 'hp_register_homepage_acf_fields' );

/**
 * Get common text fields for section intros.
 *
 * @param string $prefix Key prefix.
 * @return array
 */
function hp_get_homepage_common_intro_fields( $prefix ) {
	return array(
		array(
			'key'   => 'field_' . $prefix . '_eyebrow',
			'label' => 'Eyebrow',
			'name'  => 'eyebrow',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_' . $prefix . '_heading',
			'label' => 'Heading',
			'name'  => 'heading',
			'type'  => 'text',
		),
		array(
			'key'   => 'field_' . $prefix . '_description',
			'label' => 'Description',
			'name'  => 'description',
			'type'  => 'textarea',
			'rows'  => 3,
		),
	);
}

/**
 * Hero layout fields.
 *
 * @return array
 */
function hp_get_homepage_hero_layout_fields() {
	return array(
		'key'        => 'layout_hp_hero_section',
		'name'       => 'hero_section',
		'label'      => 'Hero Section',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_hero' ),
			array(
				array(
					'key'   => 'field_hp_hero_primary_cta_label',
					'label' => 'Primary CTA Label',
					'name'  => 'primary_cta_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_hero_primary_cta_url',
					'label' => 'Primary CTA URL',
					'name'  => 'primary_cta_url',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_hp_hero_secondary_cta_label',
					'label' => 'Secondary CTA Label',
					'name'  => 'secondary_cta_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_hero_secondary_cta_url',
					'label' => 'Secondary CTA URL',
					'name'  => 'secondary_cta_url',
					'type'  => 'url',
				),
				array(
					'key'           => 'field_hp_hero_background_image',
					'label'         => 'Background Image',
					'name'          => 'background_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'          => 'field_hp_hero_cards',
					'label'        => 'Hero Cards',
					'name'         => 'hero_cards',
					'type'         => 'repeater',
					'layout'       => 'row',
					'button_label' => 'Add Card',
					'min'          => 0,
					'max'          => 3,
					'sub_fields'   => array(
						array(
							'key'   => 'field_hp_hero_cards_eyebrow',
							'label' => 'Eyebrow',
							'name'  => 'eyebrow',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_hero_cards_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_hero_cards_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'           => 'field_hp_hero_cards_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
					),
				),
			)
		),
	);
}

/**
 * Featured categories layout fields.
 *
 * @return array
 */
function hp_get_homepage_featured_categories_layout_fields() {
	return array(
		'key'        => 'layout_hp_featured_categories_section',
		'name'       => 'featured_categories_section',
		'label'      => 'Featured Categories',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_featured_categories' ),
			array(
				array(
					'key'        => 'field_hp_featured_categories_categories',
					'label'      => 'Categories',
					'name'       => 'categories',
					'type'       => 'taxonomy',
					'taxonomy'   => 'product_cat',
					'field_type' => 'multi_select',
					'return_format' => 'id',
					'add_term'   => 0,
					'save_terms' => 0,
					'load_terms' => 0,
				),
				array(
					'key'   => 'field_hp_featured_categories_cta_label',
					'label' => 'CTA Label',
					'name'  => 'cta_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_featured_categories_cta_url',
					'label' => 'CTA URL',
					'name'  => 'cta_url',
					'type'  => 'url',
				),
			)
		),
	);
}

/**
 * Product block layout fields.
 *
 * @return array
 */
function hp_get_homepage_product_block_layout_fields() {
	return array(
		'key'        => 'layout_hp_product_block_section',
		'name'       => 'product_block_section',
		'label'      => 'Product Block',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_product_block' ),
			array(
				array(
					'key'     => 'field_hp_product_block_source_type',
					'label'   => 'Product Source',
					'name'    => 'source_type',
					'type'    => 'button_group',
					'choices' => array(
						'manual'   => 'Manual Selection',
						'featured' => 'Featured Products',
						'latest'   => 'Latest Products',
						'category' => 'Products by Category',
					),
					'default_value' => 'latest',
				),
				array(
					'key'               => 'field_hp_product_block_manual_products',
					'label'             => 'Manual Products',
					'name'              => 'manual_products',
					'type'              => 'relationship',
					'post_type'         => array( 'product' ),
					'return_format'     => 'id',
					'filters'           => array( 'search' ),
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_hp_product_block_source_type',
								'operator' => '==',
								'value'    => 'manual',
							),
						),
					),
				),
				array(
					'key'               => 'field_hp_product_block_query_category',
					'label'             => 'Query Category',
					'name'              => 'query_category',
					'type'              => 'taxonomy',
					'taxonomy'          => 'product_cat',
					'field_type'        => 'select',
					'return_format'     => 'id',
					'conditional_logic' => array(
						array(
							array(
								'field'    => 'field_hp_product_block_source_type',
								'operator' => '==',
								'value'    => 'category',
							),
						),
					),
				),
				array(
					'key'           => 'field_hp_product_block_products_limit',
					'label'         => 'Products Limit',
					'name'          => 'products_limit',
					'type'          => 'number',
					'default_value' => 8,
					'min'           => 1,
					'max'           => 12,
				),
				array(
					'key'   => 'field_hp_product_block_show_rating',
					'label' => 'Show Rating',
					'name'  => 'show_rating',
					'type'  => 'true_false',
					'ui'    => 1,
					'default_value' => 1,
				),
				array(
					'key'   => 'field_hp_product_block_show_price',
					'label' => 'Show Price',
					'name'  => 'show_price',
					'type'  => 'true_false',
					'ui'    => 1,
					'default_value' => 1,
				),
				array(
					'key'   => 'field_hp_product_block_cta_label',
					'label' => 'CTA Label',
					'name'  => 'cta_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_product_block_cta_url',
					'label' => 'CTA URL',
					'name'  => 'cta_url',
					'type'  => 'url',
				),
			)
		),
	);
}

/**
 * Promo banner layout fields.
 *
 * @return array
 */
function hp_get_homepage_promo_banner_layout_fields() {
	return array(
		'key'        => 'layout_hp_promo_banner_section',
		'name'       => 'promo_banner_section',
		'label'      => 'Promo Banner',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_promo_banner' ),
			array(
				array(
					'key'   => 'field_hp_promo_banner_cta_label',
					'label' => 'CTA Label',
					'name'  => 'cta_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_promo_banner_cta_url',
					'label' => 'CTA URL',
					'name'  => 'cta_url',
					'type'  => 'url',
				),
				array(
					'key'           => 'field_hp_promo_banner_image',
					'label'         => 'Image',
					'name'          => 'image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
				),
				array(
					'key'           => 'field_hp_promo_banner_theme_variant',
					'label'         => 'Theme Variant',
					'name'          => 'theme_variant',
					'type'          => 'select',
					'choices'       => array(
						'light'   => 'Light',
						'dark'    => 'Dark',
						'accent'  => 'Accent',
						'outline' => 'Outline',
					),
					'default_value' => 'light',
				),
			)
		),
	);
}

/**
 * USP layout fields.
 *
 * @return array
 */
function hp_get_homepage_usp_layout_fields() {
	return array(
		'key'        => 'layout_hp_usp_section',
		'name'       => 'usp_section',
		'label'      => 'Features / USP',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_usp' ),
			array(
				array(
					'key'          => 'field_hp_usp_items',
					'label'        => 'Items',
					'name'         => 'items',
					'type'         => 'repeater',
					'layout'       => 'row',
					'button_label' => 'Add Item',
					'sub_fields'   => array(
						array(
							'key'           => 'field_hp_usp_items_icon_image',
							'label'         => 'Icon Image',
							'name'          => 'icon_image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'thumbnail',
						),
						array(
							'key'   => 'field_hp_usp_items_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_usp_items_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			)
		),
	);
}

/**
 * Preview tiles layout fields.
 *
 * @return array
 */
function hp_get_homepage_preview_tiles_layout_fields() {
	return array(
		'key'        => 'layout_hp_preview_tiles_section',
		'name'       => 'preview_tiles_section',
		'label'      => 'Preview Tiles',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_preview_tiles' ),
			array(
				array(
					'key'          => 'field_hp_preview_tiles_tiles',
					'label'        => 'Tiles',
					'name'         => 'tiles',
					'type'         => 'repeater',
					'layout'       => 'row',
					'button_label' => 'Add Tile',
					'sub_fields'   => array(
						array(
							'key'   => 'field_hp_preview_tiles_tiles_eyebrow',
							'label' => 'Eyebrow',
							'name'  => 'eyebrow',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_preview_tiles_tiles_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_preview_tiles_tiles_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'           => 'field_hp_preview_tiles_tiles_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'   => 'field_hp_preview_tiles_tiles_cta_label',
							'label' => 'CTA Label',
							'name'  => 'cta_label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_preview_tiles_tiles_cta_url',
							'label' => 'CTA URL',
							'name'  => 'cta_url',
							'type'  => 'url',
						),
					),
				),
			)
		),
	);
}

/**
 * Campaign tiles layout fields.
 *
 * @return array
 */
function hp_get_homepage_campaign_tiles_layout_fields() {
	return array(
		'key'        => 'layout_hp_campaign_tiles_section',
		'name'       => 'campaign_tiles_section',
		'label'      => 'Campaign Tiles',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_campaign_tiles' ),
			array(
				array(
					'key'          => 'field_hp_campaign_tiles_tiles',
					'label'        => 'Tiles',
					'name'         => 'tiles',
					'type'         => 'repeater',
					'layout'       => 'row',
					'button_label' => 'Add Tile',
					'min'          => 1,
					'max'          => 3,
					'sub_fields'   => array(
						array(
							'key'           => 'field_hp_campaign_tiles_tiles_style_variant',
							'label'         => 'Style Variant',
							'name'          => 'style_variant',
							'type'          => 'select',
							'choices'       => array(
								'primary' => 'Primary',
								'light'   => 'Light',
								'outline' => 'Outline',
							),
							'default_value' => 'light',
						),
						array(
							'key'   => 'field_hp_campaign_tiles_tiles_eyebrow',
							'label' => 'Eyebrow',
							'name'  => 'eyebrow',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_campaign_tiles_tiles_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_campaign_tiles_tiles_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
						array(
							'key'           => 'field_hp_campaign_tiles_tiles_image',
							'label'         => 'Image',
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'array',
							'preview_size'  => 'medium',
						),
						array(
							'key'   => 'field_hp_campaign_tiles_tiles_cta_label',
							'label' => 'CTA Label',
							'name'  => 'cta_label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_campaign_tiles_tiles_cta_url',
							'label' => 'CTA URL',
							'name'  => 'cta_url',
							'type'  => 'url',
						),
					),
				),
			)
		),
	);
}

/**
 * Trust layout fields.
 *
 * @return array
 */
function hp_get_homepage_trust_layout_fields() {
	return array(
		'key'        => 'layout_hp_trust_section',
		'name'       => 'trust_section',
		'label'      => 'Trust Section',
		'display'    => 'block',
		'sub_fields' => array_merge(
			hp_get_homepage_common_intro_fields( 'hp_trust' ),
			array(
				array(
					'key'          => 'field_hp_trust_badges',
					'label'        => 'Badges',
					'name'         => 'badges',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add Badge',
					'sub_fields'   => array(
						array(
							'key'   => 'field_hp_trust_badges_label',
							'label' => 'Label',
							'name'  => 'label',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_hp_trust_cards',
					'label'        => 'Trust Cards',
					'name'         => 'cards',
					'type'         => 'repeater',
					'layout'       => 'row',
					'button_label' => 'Add Card',
					'sub_fields'   => array(
						array(
							'key'   => 'field_hp_trust_cards_title',
							'label' => 'Title',
							'name'  => 'title',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_hp_trust_cards_description',
							'label' => 'Description',
							'name'  => 'description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			)
		),
	);
}
