<?php
/**
 * Product sticky bar — fixed bottom bar with product name, price, CTA.
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
?>
<div class="hp-sticky-bar hp-sticky-bar--hidden" id="hp-sticky-bar" aria-hidden="true">
	<div class="hp-sticky-bar__inner">
		<div class="hp-sticky-bar__info">
			<span class="hp-sticky-bar__name"><?php echo esc_html( $product->get_name() ); ?></span>
			<span class="hp-sticky-bar__price" id="hp-sticky-bar-price">
				<?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
		<div class="hp-sticky-bar__actions">
			<?php if ( $product->is_in_stock() ) : ?>
				<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="hp-sticky-bar__cta">
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
				</a>
			<?php else : ?>
				<span class="hp-sticky-bar__out-of-stock">
					<?php esc_html_e( 'Out of stock', 'hitprice' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>
