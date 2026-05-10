<?php
/**
 * Payment methods strip: Cash on Delivery | Open Parcel | 7-Day Check Warranty.
 * Fixed UX elements — icons are inline SVGs, not user-uploaded.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$methods = array(
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
		'title' => __( 'Cash on Delivery', 'hitprice' ),
		'desc'  => __( 'Pay when you receive', 'hitprice' ),
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>',
		'title' => __( 'Open Parcel', 'hitprice' ),
		'desc'  => __( 'Check before you pay', 'hitprice' ),
	),
	array(
		'svg'   => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
		'title' => __( '7-Day Check Warranty', 'hitprice' ),
		'desc'  => __( 'Free return & replacement', 'hitprice' ),
	),
);
?>
<div class="hp-payment-methods">
	<?php foreach ( $methods as $method ) : ?>
		<div class="hp-payment-method">
			<span class="hp-payment-method__icon">
				<?php echo $method['svg']; // Hardcoded SVGs — not user input ?>
			</span>
			<div class="hp-payment-method__text">
				<span class="hp-payment-method__title"><?php echo esc_html( $method['title'] ); ?></span>
				<span class="hp-payment-method__desc"><?php echo esc_html( $method['desc'] ); ?></span>
			</div>
		</div>
	<?php endforeach; ?>
</div>
