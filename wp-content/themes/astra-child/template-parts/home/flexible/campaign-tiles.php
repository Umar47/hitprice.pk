<?php
/**
 * Flexible campaign tiles section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = hitprice_get_template_args();
$tiles   = ! empty( $section['tiles'] ) && is_array( $section['tiles'] ) ? array_values( $section['tiles'] ) : array();
?>
<section class="hitprice-home-section hitprice-home-section--promos">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
					<p class="hitprice-home-kicker"><?php echo esc_html( $section['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['heading'] ) ) : ?>
					<h2 class="hitprice-home-section__title"><?php echo esc_html( $section['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="hitprice-home-section__intro"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( ! empty( $tiles ) ) : ?>
			<div class="hitprice-tilegrid">
				<?php foreach ( $tiles as $index => $tile ) : ?>
					<?php $tile_url = ! empty( $tile['cta_url'] ) ? $tile['cta_url'] : '#'; ?>
					<article class="hitprice-promo-card hitprice-promo-card--<?php echo esc_attr( ! empty( $tile['style_variant'] ) ? $tile['style_variant'] : 'light' ); ?><?php echo 0 === $index ? ' hitprice-promo-card--feature' : ''; ?>">
						<?php if ( ! empty( $tile['image']['url'] ) ) : ?>
							<div class="hitprice-promo-card__media">
								<img src="<?php echo esc_url( $tile['image']['url'] ); ?>" alt="<?php echo esc_attr( $tile['image']['alt'] ?? $tile['title'] ?? '' ); ?>" loading="lazy" decoding="async">
							</div>
						<?php endif; ?>
						<div class="hitprice-promo-card__body">
							<?php if ( ! empty( $tile['eyebrow'] ) ) : ?>
								<p class="hitprice-home-kicker"><?php echo esc_html( $tile['eyebrow'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $tile['title'] ) ) : ?>
								<h3 class="hitprice-promo-card__title"><?php echo esc_html( $tile['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $tile['description'] ) ) : ?>
								<p class="hitprice-promo-card__copy"><?php echo esc_html( $tile['description'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $tile['cta_label'] ) ) : ?>
								<a class="hitprice-home-text-link" href="<?php echo esc_url( $tile_url ); ?>"><?php echo esc_html( $tile['cta_label'] ); ?></a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
