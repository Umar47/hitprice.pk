<?php
/**
 * Flexible featured categories section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = hitprice_get_template_args();
$category_ids = ! empty( $section['categories'] ) ? array_filter( array_map( 'absint', (array) $section['categories'] ) ) : array();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$cta_label  = ! empty( $section['cta_label'] ) ? $section['cta_label'] : __( 'Browse all products', 'hitprice' );
$cta_url    = ! empty( $section['cta_url'] ) ? $section['cta_url'] : $shop_url;
$terms      = array();

if ( ! empty( $category_ids ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'include'    => $category_ids,
			'orderby'    => 'include',
		)
	);
}

if ( is_wp_error( $terms ) || empty( $terms ) ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 6,
			'parent'     => 0,
		)
	);
}
?>
<section class="hitprice-home-section hitprice-home-section--categories">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
					<p class="hitprice-home-kicker"><?php echo esc_html( $section['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['heading'] ) ) : ?>
					<h2 class="hitprice-home-section__title"><?php echo esc_html( $section['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="hitprice-home-section__intro"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
			</div>
			<a class="hitprice-home-text-link" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</div>
		<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
			<div class="hitprice-category-tilettes" aria-label="<?php esc_attr_e( 'Featured product categories', 'hitprice' ); ?>">
				<?php foreach ( $terms as $term ) : ?>
					<a class="hitprice-category-tilette" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<span class="hitprice-category-tilette__label"><?php echo esc_html( $term->name ); ?></span>
						<span class="hitprice-category-tilette__copy"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( (string) $term->description ), 7, '...' ) ?: __( 'Explore top products and current offers.', 'hitprice' ) ); ?></span>
						<span class="hitprice-category-tilette__meta"><?php esc_html_e( 'Shop now', 'hitprice' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
