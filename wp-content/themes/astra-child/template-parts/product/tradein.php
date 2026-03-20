<?php
/**
 * Product trade-in block — static UI.
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
<div class="hp-tradein">
	<div class="hp-tradein__icon" aria-hidden="true">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
	</div>
	<div class="hp-tradein__content">
		<h4 class="hp-tradein__heading"><?php esc_html_e( 'Trade in your old device', 'hitprice' ); ?></h4>
		<p class="hp-tradein__text">
			<?php esc_html_e( 'Get up to Rs. 25,000 off when you trade in an eligible device. Discount applied at checkout.', 'hitprice' ); ?>
		</p>
		<span class="hp-tradein__link"><?php esc_html_e( 'Check trade-in value', 'hitprice' ); ?></span>
	</div>
</div>
