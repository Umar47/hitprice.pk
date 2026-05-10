<?php
/**
 * Product summary meta row: Brand | Star rating | Review count | SKU
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product ) {
	return;
}

$product_id   = $product->get_id();
$brand_output = '';

// Brand — try 'product_brand' taxonomy first, then 'pa_brand' attribute.
foreach ( array( 'product_brand', 'pa_brand' ) as $taxonomy ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		continue;
	}
	$terms = wc_get_product_terms( $product_id, $taxonomy, array( 'fields' => 'all' ) );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$term      = $terms[0];
		$term_link = get_term_link( $term );
		$href      = is_wp_error( $term_link ) ? '#' : $term_link;
		$brand_output = '<a href="' . esc_url( $href ) . '" class="hp-summary-meta__brand">' . esc_html( $term->name ) . '</a>';
		break;
	}
}

$avg_rating   = (float) $product->get_average_rating();
$review_count = (int) $product->get_review_count();
$sku          = $product->get_sku();

$has_rating = $review_count > 0 || $avg_rating > 0;
?>
<div class="hp-summary-header__actions" aria-label="<?php esc_attr_e( 'Product actions', 'hitprice' ); ?>">
	<button type="button" class="hp-summary-header__action" aria-label="<?php esc_attr_e( 'Add to wishlist', 'hitprice' ); ?>">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<path d="M12 21s-6.7-4.35-9.19-8.17C.93 9.95 2.14 6.1 5.6 5.16c2.04-.56 4.01.11 5.4 1.74 1.39-1.63 3.36-2.3 5.4-1.74 3.46.94 4.67 4.79 2.79 7.67C18.7 16.65 12 21 12 21z"/>
		</svg>
	</button>
	<button type="button" class="hp-summary-header__action" aria-label="<?php esc_attr_e( 'Share product', 'hitprice' ); ?>">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
			<circle cx="18" cy="5" r="3"/>
			<circle cx="6" cy="12" r="3"/>
			<circle cx="18" cy="19" r="3"/>
			<path d="M8.59 13.51 15.42 17.49"/>
			<path d="M15.41 6.51 8.59 10.49"/>
		</svg>
	</button>
</div>
<div class="hp-summary-meta">
	<?php if ( $brand_output ) : ?>
		<span class="hp-summary-meta__brand-row">
			<span class="hp-summary-meta__key"><?php esc_html_e( 'Brand:', 'hitprice' ); ?></span>
			<?php echo wp_kses( $brand_output, array( 'a' => array( 'href' => array(), 'class' => array() ) ) ); ?>
		</span>
	<?php endif; ?>

	<div class="hp-summary-meta__details">
		<?php if ( $has_rating ) : ?>
			<span class="hp-summary-meta__item hp-summary-meta__rating">
				<span class="hp-summary-meta__stars" aria-hidden="true">
					<?php echo wc_get_rating_html( $avg_rating ); // WC-escaped output ?>
				</span>
				<span class="hp-summary-meta__score"><?php echo esc_html( number_format( $avg_rating, 1 ) ); ?></span>
				<a href="#hp-tab-reviews" class="hp-summary-meta__review-link">
					<?php
					printf(
						'(%s %s)',
						esc_html( $review_count ),
						esc_html__( 'Reviews', 'hitprice' )
					);
					?>
				</a>
			</span>
		<?php endif; ?>

		<?php if ( $sku ) : ?>
			<?php if ( $has_rating ) : ?>
				<span class="hp-summary-meta__sep" aria-hidden="true">|</span>
			<?php endif; ?>
			<span class="hp-summary-meta__item hp-summary-meta__sku">
				<span class="hp-summary-meta__key"><?php esc_html_e( 'SKU:', 'hitprice' ); ?></span>
				<span><?php echo esc_html( $sku ); ?></span>
			</span>
		<?php endif; ?>
	</div>
</div>
