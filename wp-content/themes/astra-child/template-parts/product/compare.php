<?php
/**
 * "You may also like" related products slider.
 *
 * Fetches up to 8 same-category products (with cross-sell + latest fallbacks)
 * and renders them as a horizontally scrollable card slider.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_compare_products' ) ) {
	return;
}

global $product;

$related = hp_get_compare_products( $product->get_id(), 8 );

if ( empty( $related ) ) {
	return;
}
?>
<section class="hp-related" aria-labelledby="hp-related-heading">
	<div class="hp-related__inner">
		<div class="hp-related__header">
			<h2 class="hp-related__heading" id="hp-related-heading">
				<?php esc_html_e( 'You may also like', 'hitprice' ); ?>
			</h2>
			<div class="hp-related__arrows" aria-hidden="true">
				<button class="hp-related__arrow hp-related__arrow--prev" data-dir="prev" aria-label="<?php esc_attr_e( 'Previous', 'hitprice' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="15 18 9 12 15 6"/>
					</svg>
				</button>
				<button class="hp-related__arrow hp-related__arrow--next" data-dir="next" aria-label="<?php esc_attr_e( 'Next', 'hitprice' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<polyline points="9 18 15 12 9 6"/>
					</svg>
				</button>
			</div>
		</div>

		<div class="hp-related__track" data-hp-slider="related">
			<?php foreach ( $related as $rel ) : ?>
				<div class="hp-related__card">
					<a href="<?php echo esc_url( $rel->get_permalink() ); ?>" class="hp-related__link">
						<div class="hp-related__image">
							<?php echo $rel->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<h3 class="hp-related__title"><?php echo esc_html( $rel->get_name() ); ?></h3>
						<div class="hp-related__price">
							<?php echo $rel->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</a>
					<a href="<?php echo esc_url( $rel->get_permalink() ); ?>" class="hp-related__cta">
						<?php esc_html_e( 'Buy Now', 'hitprice' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
