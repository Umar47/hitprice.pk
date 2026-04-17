<?php
/**
 * Template Name: Hit Price Homepage
 *
 * Fixed 6-section homepage:
 * 1. Hero Slider
 * 2. Trust Strip
 * 3. Hot Deals (product slider)
 * 4. Latest Phones (product slider)
 * 5. Shop By Category
 * 6. Why Buy From Us
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$post_id = (int) get_the_ID();
?>
<main id="primary" class="site-main hp-home">
	<?php
	hitprice_get_template_part(
		'template-parts/home/hero-slider',
		array( 'slides' => hp_get_hero_slides( $post_id ) )
	);

	hitprice_get_template_part(
		'template-parts/home/trust-strip',
		array( 'items' => hp_get_trust_strip_items( $post_id ) )
	);

	hitprice_get_template_part(
		'template-parts/home/product-slider',
		array_merge(
			hp_get_hot_deals_data( $post_id ),
			array( 'slider_id' => 'hot-deals' )
		)
	);

	hitprice_get_template_part(
		'template-parts/home/product-slider',
		array_merge(
			hp_get_latest_phones_data( $post_id ),
			array( 'slider_id' => 'latest-phones' )
		)
	);

	hitprice_get_template_part(
		'template-parts/home/shop-categories',
		hp_get_shop_categories_data( $post_id )
	);

	hitprice_get_template_part(
		'template-parts/home/why-buy',
		hp_get_why_buy_data( $post_id )
	);
	?>
</main>
<?php
get_footer();
