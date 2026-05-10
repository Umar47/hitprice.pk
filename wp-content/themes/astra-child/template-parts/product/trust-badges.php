<?php
/**
 * Price icons row — rendered after tax note, before add-to-cart.
 * Icons managed from HitPrice Settings → Single Product Page Icons.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_price_icons' ) ) {
	return;
}

$icons = hp_get_price_icons();
if ( empty( $icons ) ) {
	return;
}
?>
<div class="hp-price-icons">
	<?php foreach ( $icons as $item ) : ?>
		<div class="hp-price-icon">
			<?php if ( ! empty( $item['image_url'] ) ) : ?>
				<img src="<?php echo esc_url( $item['image_url'] ); ?>"
				     alt=""
				     width="40" height="40"
				     loading="lazy"
				     aria-hidden="true"
				     class="hp-price-icon__img">
			<?php endif; ?>
			<div class="hp-price-icon__body">
				<span class="hp-price-icon__title"><?php echo esc_html( $item['title'] ); ?></span>
				<?php if ( ! empty( $item['subtitle'] ) ) : ?>
					<span class="hp-price-icon__subtitle"><?php echo esc_html( $item['subtitle'] ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
