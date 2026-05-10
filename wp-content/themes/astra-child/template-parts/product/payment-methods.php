<?php
/**
 * Payment methods strip: Cash on Delivery | Open Parcel | 7-Day Check Warranty.
 * Items are managed from WP Admin → HitPrice → Single Product Page.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$methods = function_exists( 'hp_get_payment_methods' ) ? hp_get_payment_methods() : array();
?>
<div class="hp-payment-methods">
	<?php foreach ( $methods as $method ) : ?>
		<div class="hp-payment-method">
			<span class="hp-payment-method__icon">
				<i class="<?php echo esc_attr( $method['icon_class'] ); ?>" aria-hidden="true"></i>
			</span>
			<div class="hp-payment-method__text">
				<span class="hp-payment-method__title"><?php echo esc_html( $method['title'] ); ?></span>
				<span class="hp-payment-method__desc"><?php echo esc_html( $method['subtitle'] ); ?></span>
			</div>
		</div>
	<?php endforeach; ?>
</div>
