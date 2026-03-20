<?php
/**
 * Product payment options display block.
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

$price = $product->get_price();

if ( ! $price || $price <= 0 ) {
	return;
}

$monthly = ceil( $price / 12 );
?>
<div class="hp-payment-options">
	<h4 class="hp-payment-options__heading"><?php esc_html_e( 'Payment options', 'hitprice' ); ?></h4>
	<div class="hp-payment-options__list">
		<div class="hp-payment-options__item hp-payment-options__item--full">
			<span class="hp-payment-options__label"><?php esc_html_e( 'Full price', 'hitprice' ); ?></span>
			<span class="hp-payment-options__value"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>
		<div class="hp-payment-options__item hp-payment-options__item--installment">
			<span class="hp-payment-options__label"><?php esc_html_e( '12 monthly payments', 'hitprice' ); ?></span>
			<span class="hp-payment-options__value">
				<?php
				printf(
					/* translators: %s: monthly amount */
					esc_html__( 'Rs. %s/mo', 'hitprice' ),
					esc_html( number_format( $monthly ) )
				);
				?>
			</span>
			<span class="hp-payment-options__note"><?php esc_html_e( '0% interest available on select banks', 'hitprice' ); ?></span>
		</div>
	</div>
</div>
