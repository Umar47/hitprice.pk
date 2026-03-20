<?php
/**
 * Flexible homepage hero section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section          = hitprice_get_template_args();
$shop_url         = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$deals_url        = add_query_arg( 'orderby', 'date', $shop_url );
$background_image = isset( $section['background_image']['url'] ) ? $section['background_image']['url'] : '';
$background_alt   = isset( $section['background_image']['alt'] ) ? $section['background_image']['alt'] : ( $section['heading'] ?? '' );
$hero_cards       = ! empty( $section['hero_cards'] ) && is_array( $section['hero_cards'] ) ? array_values( $section['hero_cards'] ) : array();
$feature_card     = isset( $hero_cards[0] ) ? $hero_cards[0] : array();
$support_cards    = array_slice( $hero_cards, 1, 2 );
?>
<section class="hitprice-home-hero">
	<div class="hitprice-shell">
		<div class="hitprice-home-hero__grid">
			<div class="hitprice-home-hero__content">
				<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
					<p class="hitprice-home-kicker"><?php echo esc_html( $section['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['heading'] ) ) : ?>
					<h1 class="hitprice-home-hero__title"><?php echo esc_html( $section['heading'] ); ?></h1>
				<?php endif; ?>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="hitprice-home-hero__text"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $feature_card['title'] ) || ! empty( $feature_card['description'] ) ) : ?>
					<div class="hitprice-home-hero__offer">
						<?php if ( ! empty( $feature_card['eyebrow'] ) ) : ?>
							<span class="hitprice-home-hero__offer-label"><?php echo esc_html( $feature_card['eyebrow'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $feature_card['title'] ) ) : ?>
							<strong><?php echo esc_html( $feature_card['title'] ); ?></strong>
						<?php endif; ?>
						<?php if ( ! empty( $feature_card['description'] ) ) : ?>
							<p><?php echo esc_html( $feature_card['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="hitprice-home-hero__actions">
					<?php if ( ! empty( $section['primary_cta_label'] ) ) : ?>
						<a class="hitprice-home-button hitprice-home-button--primary" href="<?php echo esc_url( ! empty( $section['primary_cta_url'] ) ? $section['primary_cta_url'] : $shop_url ); ?>"><?php echo esc_html( $section['primary_cta_label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $section['secondary_cta_label'] ) ) : ?>
						<a class="hitprice-home-button hitprice-home-button--secondary" href="<?php echo esc_url( ! empty( $section['secondary_cta_url'] ) ? $section['secondary_cta_url'] : $deals_url ); ?>"><?php echo esc_html( $section['secondary_cta_label'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<div class="hitprice-home-hero__visual">
				<?php if ( $background_image ) : ?>
					<article class="hitprice-home-feature-card">
						<div class="hitprice-home-feature-card__media">
							<img src="<?php echo esc_url( $background_image ); ?>" alt="<?php echo esc_attr( $background_alt ); ?>" loading="eager" decoding="async">
						</div>
						<div class="hitprice-home-feature-card__body">
							<?php if ( ! empty( $feature_card['eyebrow'] ) ) : ?>
								<span class="hitprice-home-device-card__eyebrow"><?php echo esc_html( $feature_card['eyebrow'] ); ?></span>
							<?php endif; ?>
							<?php if ( ! empty( $feature_card['title'] ) ) : ?>
								<strong><?php echo esc_html( $feature_card['title'] ); ?></strong>
							<?php endif; ?>
							<?php if ( ! empty( $feature_card['description'] ) ) : ?>
								<p><?php echo esc_html( $feature_card['description'] ); ?></p>
							<?php endif; ?>
						</div>
					</article>
				<?php endif; ?>
				<?php if ( ! empty( $support_cards ) ) : ?>
					<div class="hitprice-home-hero__visual-grid">
						<?php foreach ( $support_cards as $index => $card ) : ?>
							<div class="hitprice-home-device-card<?php echo 0 === $index ? ' hitprice-home-device-card--large' : ' hitprice-home-device-card--accent'; ?>">
								<?php if ( ! empty( $card['eyebrow'] ) ) : ?>
									<span class="hitprice-home-device-card__eyebrow"><?php echo esc_html( $card['eyebrow'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $card['title'] ) ) : ?>
									<strong><?php echo esc_html( $card['title'] ); ?></strong>
								<?php endif; ?>
								<?php if ( ! empty( $card['description'] ) ) : ?>
									<span><?php echo esc_html( $card['description'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( count( $hero_cards ) > 1 ) : ?>
					<div class="hitprice-home-stat">
						<span class="hitprice-home-stat__value"><?php echo esc_html( count( $hero_cards ) ); ?></span>
						<span class="hitprice-home-stat__label"><?php esc_html_e( 'Configured hero content blocks ready for homepage merchandising', 'hitprice' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
