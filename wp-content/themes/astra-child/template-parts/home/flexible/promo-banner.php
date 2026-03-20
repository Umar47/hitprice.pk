<?php
/**
 * Flexible promo banner section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section    = hitprice_get_template_args();
$variant    = ! empty( $section['theme_variant'] ) ? sanitize_html_class( $section['theme_variant'] ) : 'light';
$image_url  = isset( $section['image']['url'] ) ? $section['image']['url'] : '';
$image_alt  = isset( $section['image']['alt'] ) ? $section['image']['alt'] : ( $section['heading'] ?? '' );
$cta_url    = ! empty( $section['cta_url'] ) ? $section['cta_url'] : '#';
?>
<section class="hitprice-home-section hitprice-home-section--banner">
	<div class="hitprice-shell">
		<article class="hitprice-home-banner hitprice-home-banner--<?php echo esc_attr( $variant ); ?>">
			<div class="hitprice-home-banner__content">
				<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
					<p class="hitprice-home-kicker"><?php echo esc_html( $section['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['heading'] ) ) : ?>
					<h2 class="hitprice-home-section__title"><?php echo esc_html( $section['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="hitprice-home-section__intro"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['cta_label'] ) ) : ?>
					<a class="hitprice-home-button hitprice-home-button--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $section['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
			<?php if ( $image_url ) : ?>
				<div class="hitprice-home-banner__media">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>
		</article>
	</div>
</section>
