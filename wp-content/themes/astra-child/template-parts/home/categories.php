<?php
/**
 * Homepage category shortcuts.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fallback_categories = array(
	array(
		'label' => 'Mobile phones',
		'copy'  => 'Latest launches',
		'tag'   => 'Best sellers',
	),
	array(
		'label' => 'Accessories',
		'copy'  => 'Chargers and audio',
		'tag'   => 'Attach more',
	),
	array(
		'label' => 'TV & Entertainment',
		'copy'  => 'Living room upgrades',
		'tag'   => 'Family picks',
	),
	array(
		'label' => 'AC & Cooling',
		'copy'  => 'Seasonal essentials',
		'tag'   => 'Seasonal',
	),
	array(
		'label' => 'Kitchen',
		'copy'  => 'Everyday appliances',
		'tag'   => 'Daily use',
	),
	array(
		'label' => 'Computers',
		'copy'  => 'Work and study',
		'tag'   => 'Work & study',
	),
);

$category_items = array();
$terms          = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'number'     => 6,
		'parent'     => 0,
	)
);

if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
	foreach ( $terms as $term ) {
		$category_items[] = array(
			'label' => $term->name,
			'copy'  => wp_trim_words( wp_strip_all_tags( (string) $term->description ), 4, '...' ),
			'url'   => get_term_link( $term ),
			'tag'   => 'Shop now',
		);
	}
}

if ( empty( $category_items ) ) {
	foreach ( $fallback_categories as $fallback_category ) {
		$fallback_category['url'] = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
		$category_items[]         = $fallback_category;
	}
}
?>
<section class="hitprice-home-section hitprice-home-section--categories">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<p class="hitprice-home-kicker">Shop by category</p>
				<h2 class="hitprice-home-section__title">Quick links into the categories people shop most.</h2>
			</div>
			<a class="hitprice-home-text-link" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>">Browse all products</a>
		</div>
		<div class="hitprice-category-tilettes" aria-label="<?php esc_attr_e( 'Top shopping categories', 'hitprice' ); ?>">
			<?php foreach ( $category_items as $category_item ) : ?>
				<a class="hitprice-category-tilette" href="<?php echo esc_url( $category_item['url'] ); ?>">
					<span class="hitprice-category-tilette__label"><?php echo esc_html( $category_item['label'] ); ?></span>
					<span class="hitprice-category-tilette__copy"><?php echo esc_html( $category_item['copy'] ? $category_item['copy'] : 'Explore top products and current offers' ); ?></span>
					<span class="hitprice-category-tilette__meta">Shop now</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
