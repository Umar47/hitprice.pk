<?php
/**
 * Stock status pill + "N people viewing" notice.
 *
 * Accepts args:
 *   modifier (string) — 'mobile' or 'desktop', controls CSS class for show/hide per breakpoint.
 *
 * Viewers count is generated client-side via seeded pseudo-random (product ID + date as seed)
 * so it is consistent per product per day with zero server overhead.
 * PHP outputs the range as data attributes read by single-product.js.
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

$args        = hitprice_get_template_args();
$modifier    = isset( $args['modifier'] ) ? sanitize_key( $args['modifier'] ) : '';
$viewers_min = function_exists( 'hp_get_global_setting' ) ? (int) hp_get_global_setting( 'viewers_min', 12 ) : 12;
$viewers_max = function_exists( 'hp_get_global_setting' ) ? (int) hp_get_global_setting( 'viewers_max', 48 ) : 48;
$in_stock    = $product->is_in_stock();
$product_id  = $product->get_id();

$class = 'hp-stock-viewers';
if ( $modifier ) {
	$class .= ' hp-stock-viewers--' . $modifier;
}
?>
<div class="<?php echo esc_attr( $class ); ?>"
     data-product-id="<?php echo esc_attr( $product_id ); ?>"
     data-viewers-min="<?php echo esc_attr( $viewers_min ); ?>"
     data-viewers-max="<?php echo esc_attr( $viewers_max ); ?>">

	<span class="hp-stock-badge hp-stock-badge--<?php echo $in_stock ? 'in' : 'out'; ?>">
		<?php if ( $in_stock ) : ?>
			<svg width="8" height="8" viewBox="0 0 8 8" aria-hidden="true" focusable="false">
				<circle cx="4" cy="4" r="4" fill="currentColor"/>
			</svg>
			<?php esc_html_e( 'In Stock', 'hitprice' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Out of Stock', 'hitprice' ); ?>
		<?php endif; ?>
	</span>

	<?php if ( $in_stock ) : ?>
		<span class="hp-viewers-notice" aria-live="polite">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false">
				<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
				<circle cx="12" cy="12" r="3"/>
			</svg>
			<span class="hp-viewers-count"></span>
			<?php esc_html_e( 'people are viewing this product', 'hitprice' ); ?>
		</span>
	<?php endif; ?>

</div>
