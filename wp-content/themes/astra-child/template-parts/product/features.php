<?php
/**
 * Product features section — 3 feature cards from ACF repeater.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_product_features' ) ) {
	return;
}

global $product;

$features = hp_get_product_features( $product->get_id() );

if ( empty( $features ) ) {
	return;
}
?>
<section class="hp-features" aria-labelledby="hp-features-heading">
	<div class="hp-features__inner">
		<h2 class="hp-features__heading" id="hp-features-heading">
			<?php esc_html_e( 'What makes it great', 'hitprice' ); ?>
		</h2>
		<div class="hp-features__grid">
			<?php foreach ( $features as $feature ) : ?>
				<div class="hp-features__card">
					<?php if ( ! empty( $feature['image'] ) ) : ?>
						<div class="hp-features__image">
							<img
								src="<?php echo esc_url( $feature['image']['sizes']['medium'] ?? $feature['image']['url'] ); ?>"
								alt="<?php echo esc_attr( $feature['image']['alt'] ?? $feature['title'] ); ?>"
								width="<?php echo esc_attr( $feature['image']['sizes']['medium-width'] ?? $feature['image']['width'] ); ?>"
								height="<?php echo esc_attr( $feature['image']['sizes']['medium-height'] ?? $feature['image']['height'] ); ?>"
								loading="lazy"
							>
						</div>
					<?php endif; ?>
					<h3 class="hp-features__title"><?php echo esc_html( $feature['title'] ); ?></h3>
					<?php if ( ! empty( $feature['description'] ) ) : ?>
						<p class="hp-features__desc"><?php echo esc_html( $feature['description'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
