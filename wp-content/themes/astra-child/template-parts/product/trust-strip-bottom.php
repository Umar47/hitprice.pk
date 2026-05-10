<?php
/**
 * Bottom trust strip — 4 Font Awesome icon items.
 *
 * Data managed from HitPrice Settings → Bottom Trust Strip.
 * Falls back to defaults when nothing is configured.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_product_trust_strip_items' ) ) {
	return;
}

$items = hp_get_product_trust_strip_items();

if ( empty( $items ) ) {
	return;
}
?>
<section class="hp-trust-strip-bottom">
	<div class="hp-trust-strip-bottom__inner">
		<?php foreach ( $items as $item ) :
			if ( empty( $item['title'] ) ) { continue; }
		?>
			<div class="hp-tsb-item">
				<?php if ( ! empty( $item['icon_class'] ) ) : ?>
					<div class="hp-tsb-item__icon-wrap" aria-hidden="true">
						<i class="<?php echo esc_attr( $item['icon_class'] ); ?>"></i>
					</div>
				<?php endif; ?>
				<div class="hp-tsb-item__text">
					<span class="hp-tsb-item__label"><?php echo esc_html( $item['title'] ); ?></span>
					<?php if ( ! empty( $item['subtitle'] ) ) : ?>
						<span class="hp-tsb-item__desc"><?php echo esc_html( $item['subtitle'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
