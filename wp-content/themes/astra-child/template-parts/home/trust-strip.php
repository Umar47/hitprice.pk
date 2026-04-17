<?php
/**
 * Homepage trust strip (image-only).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args  = hitprice_get_template_args();
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<section class="hp-section hp-trust-strip" aria-label="<?php esc_attr_e( 'Promotional highlights', 'hitprice' ); ?>">
	<ul class="hp-trust-strip__list" role="list">
		<?php foreach ( $items as $item ) : ?>
			<?php
			$image = $item['image'];
			$url   = isset( $item['url'] ) ? $item['url'] : '';

			$image_url = is_array( $image ) && ! empty( $image['url'] ) ? $image['url'] : '';
			$image_alt = is_array( $image ) && ! empty( $image['alt'] ) ? $image['alt'] : '';

			if ( ! $image_url ) {
				continue;
			}
			?>
			<li class="hp-trust-strip__item">
				<?php if ( $url ) : ?>
					<a class="hp-trust-strip__link" href="<?php echo esc_url( $url ); ?>">
						<img
							src="<?php echo esc_url( $image_url ); ?>"
							alt="<?php echo esc_attr( $image_alt ); ?>"
							loading="lazy"
							decoding="async"
						>
					</a>
				<?php else : ?>
					<img
						src="<?php echo esc_url( $image_url ); ?>"
						alt="<?php echo esc_attr( $image_alt ); ?>"
						loading="lazy"
						decoding="async"
					>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
