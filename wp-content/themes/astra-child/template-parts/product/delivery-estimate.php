<?php
/**
 * Delivery estimate box.
 * Shows estimated delivery window: today + 2 to today + 4 days.
 * Only shown when product is in stock.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product || ! $product->is_in_stock() ) {
	return;
}

$now           = current_time( 'timestamp' );
$date_from     = strtotime( '+2 days', $now );
$date_to       = strtotime( '+4 days', $now );
$date_from_str = date_i18n( 'D, j M', $date_from );
$date_to_str   = date_i18n( 'D, j M', $date_to );
?>
<div class="hp-delivery-estimate">
	<svg class="hp-delivery-estimate__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
		<rect x="1" y="3" width="15" height="13" rx="1"/>
		<path d="M16 8h4l3 3v5h-7V8z"/>
		<circle cx="5.5" cy="18.5" r="2.5"/>
		<circle cx="18.5" cy="18.5" r="2.5"/>
	</svg>
	<span>
		<?php
		printf(
			/* translators: 1: start date, 2: end date */
			wp_kses(
				/* translators: 1: start date e.g. "Mon, 26 May", 2: end date */
				__( 'Get it between <strong>%1$s &ndash; %2$s</strong>', 'hitprice' ),
				array( 'strong' => array() )
			),
			esc_html( $date_from_str ),
			esc_html( $date_to_str )
		);
		?>
	</span>
</div>
