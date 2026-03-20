<?php
/**
 * Flexible USP section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = hitprice_get_template_args();
$items   = ! empty( $section['items'] ) && is_array( $section['items'] ) ? $section['items'] : array();
?>
<section class="hitprice-home-section hitprice-home-section--usp">
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
		<?php if ( ! empty( $items ) ) : ?>
			<div class="hitprice-trust-grid hitprice-usp-grid">
				<?php foreach ( $items as $item ) : ?>
					<article class="hitprice-trust-card hitprice-usp-card">
						<?php if ( ! empty( $item['icon_image']['url'] ) ) : ?>
							<div class="hitprice-usp-card__icon">
								<img src="<?php echo esc_url( $item['icon_image']['url'] ); ?>" alt="<?php echo esc_attr( $item['icon_image']['alt'] ?? $item['title'] ?? '' ); ?>" loading="lazy" decoding="async">
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="hitprice-trust-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="hitprice-trust-card__copy"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
