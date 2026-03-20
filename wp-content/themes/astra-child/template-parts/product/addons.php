<?php
/**
 * Product add-ons UI — static checkbox-style options.
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
<div class="hp-addons">
	<h4 class="hp-addons__heading"><?php esc_html_e( 'Protect your device', 'hitprice' ); ?></h4>
	<label class="hp-addons__option">
		<input type="checkbox" class="hp-addons__checkbox" name="hp_addon_protection" value="1" disabled>
		<span class="hp-addons__label">
			<span class="hp-addons__name"><?php esc_html_e( 'Screen Protection Plan', 'hitprice' ); ?></span>
			<span class="hp-addons__price"><?php esc_html_e( 'Rs. 2,500/yr', 'hitprice' ); ?></span>
		</span>
	</label>
	<label class="hp-addons__option">
		<input type="checkbox" class="hp-addons__checkbox" name="hp_addon_warranty" value="1" disabled>
		<span class="hp-addons__label">
			<span class="hp-addons__name"><?php esc_html_e( 'Extended Warranty', 'hitprice' ); ?></span>
			<span class="hp-addons__price"><?php esc_html_e( 'Rs. 3,000/yr', 'hitprice' ); ?></span>
		</span>
	</label>
	<p class="hp-addons__note"><?php esc_html_e( 'Add-on options coming soon.', 'hitprice' ); ?></p>
</div>
