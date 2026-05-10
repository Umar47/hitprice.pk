<?php
/**
 * Compact trust row rendered inside the gallery card under the thumbnails.
 * Icons managed from HitPrice Settings → Gallery Icons.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_gallery_bottom_icons' ) ) {
	return;
}

$items = hp_get_gallery_bottom_icons();
if ( empty( $items ) ) {
	return;
}
?>
<div class="hp-gallery-trust-strip">
	<div class="hp-gallery-trust-strip__grid" role="list" aria-label="<?php esc_attr_e( 'Store trust highlights', 'hitprice' ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<div class="hp-gallery-trust-strip__item" role="listitem">
				<div class="hp-gallery-trust-strip__icon-wrap" aria-hidden="true">
					<?php if ( ! empty( $item['image_url'] ) ) : ?>
						<img src="<?php echo esc_url( $item['image_url'] ); ?>"
						     alt=""
						     width="36" height="36"
						     loading="lazy"
						     class="hp-gallery-trust-strip__icon">
					<?php else : ?>
						<span class="hp-gallery-trust-strip__icon-placeholder"></span>
					<?php endif; ?>
				</div>
				<div class="hp-gallery-trust-strip__text">
					<span class="hp-gallery-trust-strip__label"><?php echo esc_html( $item['title'] ); ?></span>
					<?php if ( ! empty( $item['subtitle'] ) ) : ?>
						<span class="hp-gallery-trust-strip__desc"><?php echo esc_html( $item['subtitle'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
