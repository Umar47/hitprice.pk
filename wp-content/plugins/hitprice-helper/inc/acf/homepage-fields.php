<?php
/**
 * Homepage ACF field registration.
 *
 * Fixed tabbed field group for the Hit Price Homepage template.
 * Sections: Hero Slider, Trust Strip, Hot Deals, Latest Phones,
 * Shop By Category, Why Buy From Us.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register homepage field group.
 *
 * @return void
 */
function hp_register_homepage_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_hp_homepage',
			'title'                 => 'Hit Price Homepage',
			'fields'                => array_merge(
				hp_get_homepage_hero_fields(),
				hp_get_homepage_trust_strip_fields(),
				hp_get_homepage_hot_deals_fields(),
				hp_get_homepage_latest_phones_fields(),
				hp_get_homepage_shop_categories_fields(),
				hp_get_homepage_why_buy_fields()
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
 * Hero slider tab fields.
 *
 * @return array
 */
function hp_get_homepage_hero_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_hero',
			'label'     => 'Hero Slider',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'           => 'field_hp_hero_autoplay_enabled',
			'label'         => 'Enable Autoplay',
			'name'          => 'hero_autoplay_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
		),
		array(
			'key'               => 'field_hp_hero_autoplay_speed',
			'label'             => 'Autoplay Speed (seconds)',
			'name'              => 'hero_autoplay_speed',
			'type'              => 'number',
			'default_value'     => 7,
			'min'               => 2,
			'max'               => 30,
			'step'              => 1,
			'append'            => 's',
			'instructions'      => 'Time each slide stays visible before advancing.',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_hp_hero_autoplay_enabled',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		),
		array(
			'key'          => 'field_hp_hero_slides',
			'label'        => 'Hero Slides',
			'name'         => 'hero_slides',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Slide',
			'min'          => 0,
			'sub_fields'   => array(
				array(
					'key'           => 'field_hp_hero_slide_background',
					'label'         => 'Background Image (Desktop)',
					'name'          => 'background_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'required'      => 1,
					'instructions'  => 'Used on screens 640px wide and up.',
				),
				array(
					'key'           => 'field_hp_hero_slide_background_mobile',
					'label'         => 'Background Image (Mobile)',
					'name'          => 'background_image_mobile',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'instructions'  => 'Optional. Used on screens narrower than 640px. Falls back to the desktop image if empty.',
				),
				array(
					'key'   => 'field_hp_hero_slide_heading',
					'label' => 'Heading',
					'name'  => 'heading',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_hero_slide_subheading',
					'label' => 'Subheading',
					'name'  => 'subheading',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_hp_hero_slide_offer',
					'label'        => 'Offer / Price Text',
					'name'         => 'offer_text',
					'type'         => 'text',
					'instructions' => 'Optional price or offer line.',
				),
				array(
					'key'   => 'field_hp_hero_slide_cta1_label',
					'label' => 'CTA 1 Label',
					'name'  => 'cta1_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_hero_slide_cta1_url',
					'label' => 'CTA 1 URL',
					'name'  => 'cta1_url',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_hp_hero_slide_cta2_label',
					'label' => 'CTA 2 Label',
					'name'  => 'cta2_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_hp_hero_slide_cta2_url',
					'label' => 'CTA 2 URL',
					'name'  => 'cta2_url',
					'type'  => 'url',
				),
			),
		),
	);
}

/**
 * Trust strip tab fields.
 *
 * @return array
 */
function hp_get_homepage_trust_strip_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_trust',
			'label'     => 'Trust Strip',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'          => 'field_hp_trust_items',
			'label'        => 'Trust Items',
			'name'         => 'trust_items',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Item',
			'instructions' => 'Image-based strip. Upload an image per item. Text should be inside the image.',
			'min'          => 0,
			'sub_fields'   => array(
				array(
					'key'           => 'field_hp_trust_item_image',
					'label'         => 'Image',
					'name'          => 'image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'required'      => 1,
				),
				array(
					'key'   => 'field_hp_trust_item_url',
					'label' => 'Optional Link URL',
					'name'  => 'url',
					'type'  => 'url',
				),
			),
		),
	);
}

/**
 * Hot deals tab fields.
 *
 * @return array
 */
function hp_get_homepage_hot_deals_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_hot_deals',
			'label'     => 'Hot Deals',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'           => 'field_hp_hot_deals_autoplay_enabled',
			'label'         => 'Enable Autoplay',
			'name'          => 'hot_deals_autoplay_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 0,
		),
		array(
			'key'               => 'field_hp_hot_deals_autoplay_speed',
			'label'             => 'Autoplay Speed (seconds)',
			'name'              => 'hot_deals_autoplay_speed',
			'type'              => 'number',
			'default_value'     => 5,
			'min'               => 2,
			'max'               => 30,
			'step'              => 1,
			'append'            => 's',
			'instructions'      => 'Time each page of products stays visible before advancing.',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_hp_hot_deals_autoplay_enabled',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		),
		array(
			'key'           => 'field_hp_hot_deals_title',
			'label'         => 'Section Title',
			'name'          => 'hot_deals_title',
			'type'          => 'text',
			'default_value' => "Hot Deals You Shouldn't Miss",
		),
		array(
			'key'   => 'field_hp_hot_deals_subtitle',
			'label' => 'Subtitle',
			'name'  => 'hot_deals_subtitle',
			'type'  => 'text',
		),
		array(
			'key'           => 'field_hp_hot_deals_products',
			'label'         => 'Selected Products',
			'name'          => 'hot_deals_products',
			'type'          => 'relationship',
			'post_type'     => array( 'product' ),
			'return_format' => 'id',
			'filters'       => array( 'search', 'taxonomy' ),
			'min'           => 0,
		),
		array(
			'key'           => 'field_hp_hot_deals_cta_label',
			'label'         => 'Show More Label',
			'name'          => 'hot_deals_cta_label',
			'type'          => 'text',
			'default_value' => 'Show More',
		),
		array(
			'key'   => 'field_hp_hot_deals_cta_url',
			'label' => 'Show More URL',
			'name'  => 'hot_deals_cta_url',
			'type'  => 'url',
		),
	);
}

/**
 * Latest phones tab fields.
 *
 * @return array
 */
function hp_get_homepage_latest_phones_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_latest_phones',
			'label'     => 'Latest Phones',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'           => 'field_hp_latest_phones_autoplay_enabled',
			'label'         => 'Enable Autoplay',
			'name'          => 'latest_phones_autoplay_enabled',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 0,
		),
		array(
			'key'               => 'field_hp_latest_phones_autoplay_speed',
			'label'             => 'Autoplay Speed (seconds)',
			'name'              => 'latest_phones_autoplay_speed',
			'type'              => 'number',
			'default_value'     => 5,
			'min'               => 2,
			'max'               => 30,
			'step'              => 1,
			'append'            => 's',
			'instructions'      => 'Time each page of products stays visible before advancing.',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_hp_latest_phones_autoplay_enabled',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
		),
		array(
			'key'           => 'field_hp_latest_phones_title',
			'label'         => 'Section Title',
			'name'          => 'latest_phones_title',
			'type'          => 'text',
			'default_value' => 'Latest Phones Just Arrived',
		),
		array(
			'key'   => 'field_hp_latest_phones_subtitle',
			'label' => 'Subtitle',
			'name'  => 'latest_phones_subtitle',
			'type'  => 'text',
		),
		array(
			'key'           => 'field_hp_latest_phones_products',
			'label'         => 'Selected Products',
			'name'          => 'latest_phones_products',
			'type'          => 'relationship',
			'post_type'     => array( 'product' ),
			'return_format' => 'id',
			'filters'       => array( 'search', 'taxonomy' ),
			'min'           => 0,
		),
		array(
			'key'           => 'field_hp_latest_phones_cta_label',
			'label'         => 'Show More Label',
			'name'          => 'latest_phones_cta_label',
			'type'          => 'text',
			'default_value' => 'Show More',
		),
		array(
			'key'   => 'field_hp_latest_phones_cta_url',
			'label' => 'Show More URL',
			'name'  => 'latest_phones_cta_url',
			'type'  => 'url',
		),
	);
}

/**
 * Shop by category tab fields.
 *
 * @return array
 */
function hp_get_homepage_shop_categories_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_shop_categories',
			'label'     => 'Shop By Category',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'           => 'field_hp_shop_categories_title',
			'label'         => 'Section Title',
			'name'          => 'shop_categories_title',
			'type'          => 'text',
			'default_value' => 'Browse By Categories',
		),
		array(
			'key'   => 'field_hp_shop_categories_subtitle',
			'label' => 'Subtitle',
			'name'  => 'shop_categories_subtitle',
			'type'  => 'text',
		),
		array(
			'key'          => 'field_hp_shop_category_cards',
			'label'        => 'Category Cards',
			'name'         => 'shop_category_cards',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Card',
			'min'          => 0,
			'max'          => 4,
			'sub_fields'   => array(
				array(
					'key'           => 'field_hp_shop_category_background',
					'label'         => 'Background Image',
					'name'          => 'background_image',
					'type'          => 'image',
					'return_format' => 'array',
					'preview_size'  => 'medium',
					'required'      => 1,
				),
				array(
					'key'      => 'field_hp_shop_category_title',
					'label'    => 'Title',
					'name'     => 'title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_hp_shop_category_text',
					'label' => 'Optional Text',
					'name'  => 'text',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_hp_shop_category_cta_label',
					'label'         => 'CTA Label',
					'name'          => 'cta_label',
					'type'          => 'text',
					'default_value' => 'Shop Now',
				),
				array(
					'key'   => 'field_hp_shop_category_cta_url',
					'label' => 'CTA URL',
					'name'  => 'cta_url',
					'type'  => 'url',
				),
			),
		),
	);
}

/**
 * Why buy from us tab fields.
 *
 * @return array
 */
function hp_get_homepage_why_buy_fields() {
	return array(
		array(
			'key'       => 'field_hp_tab_why_buy',
			'label'     => 'Why Buy From Us',
			'type'      => 'tab',
			'placement' => 'top',
		),
		array(
			'key'           => 'field_hp_why_buy_title',
			'label'         => 'Section Title',
			'name'          => 'why_buy_title',
			'type'          => 'text',
			'default_value' => 'Why Buy From Us',
		),
		array(
			'key'   => 'field_hp_why_buy_subtitle',
			'label' => 'Subtitle',
			'name'  => 'why_buy_subtitle',
			'type'  => 'text',
		),
		array(
			'key'          => 'field_hp_why_buy_items',
			'label'        => 'Points',
			'name'         => 'why_buy_items',
			'type'         => 'repeater',
			'layout'       => 'block',
			'button_label' => 'Add Point',
			'min'          => 0,
			'max'          => 4,
			'sub_fields'   => array(
				array(
					'key'      => 'field_hp_why_buy_item_title',
					'label'    => 'Title',
					'name'     => 'title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_hp_why_buy_item_description',
					'label' => 'Description',
					'name'  => 'description',
					'type'  => 'textarea',
					'rows'  => 3,
				),
			),
		),
	);
}
