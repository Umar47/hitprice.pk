<?php
/**
 * Product compare section — 4-col grid of related products.
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

$compare_products = hp_get_compare_products( $product->get_id(), 4 );

if ( empty( $compare_products ) ) {
	return;
}
?>
<section class="hp-compare" aria-labelledby="hp-compare-heading">
	<div class="hp-compare__inner">
		<h2 class="hp-compare__heading" id="hp-compare-heading">
			<?php esc_html_e( 'Compare similar products', 'hitprice' ); ?>
		</h2>
		<div class="hp-compare__grid">
			<?php foreach ( $compare_products as $compare_product ) : ?>
				<div class="hp-compare__card">
					<a href="<?php echo esc_url( $compare_product->get_permalink() ); ?>" class="hp-compare__link">
						<div class="hp-compare__image">
							<?php echo $compare_product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<h3 class="hp-compare__title"><?php echo esc_html( $compare_product->get_name() ); ?></h3>
						<div class="hp-compare__price">
							<?php echo $compare_product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<?php if ( $compare_product->get_short_description() ) : ?>
							<p class="hp-compare__excerpt">
								<?php echo esc_html( wp_trim_words( wp_strip_all_tags( $compare_product->get_short_description() ), 12 ) ); ?>
							</p>
						<?php endif; ?>
					</a>
					<a href="<?php echo esc_url( $compare_product->get_permalink() ); ?>" class="hp-compare__cta">
						<?php esc_html_e( 'View details', 'hitprice' ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
