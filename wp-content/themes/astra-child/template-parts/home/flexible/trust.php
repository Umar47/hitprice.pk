<?php
/**
 * Flexible trust section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section = hitprice_get_template_args();
$badges  = ! empty( $section['badges'] ) && is_array( $section['badges'] ) ? $section['badges'] : array();
$cards   = ! empty( $section['cards'] ) && is_array( $section['cards'] ) ? $section['cards'] : array();
?>
<section class="hitprice-home-section hitprice-home-section--trust">
	<div class="hitprice-shell">
		<div class="hitprice-trust-strip">
			<div class="hitprice-trust-strip__intro">
				<?php if ( ! empty( $section['eyebrow'] ) ) : ?>
					<p class="hitprice-home-kicker"><?php echo esc_html( $section['eyebrow'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $section['heading'] ) ) : ?>
					<h2 class="hitprice-home-section__title"><?php echo esc_html( $section['heading'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="hitprice-trust-strip__copy"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $badges ) ) : ?>
					<div class="hitprice-trust-strip__badges" aria-label="<?php esc_attr_e( 'Trust highlights', 'hitprice' ); ?>">
						<?php foreach ( $badges as $badge ) : ?>
							<?php if ( ! empty( $badge['label'] ) ) : ?>
								<span><?php echo esc_html( $badge['label'] ); ?></span>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php if ( ! empty( $cards ) ) : ?>
				<div class="hitprice-trust-grid">
					<?php foreach ( $cards as $card ) : ?>
						<article class="hitprice-trust-card">
							<?php if ( ! empty( $card['title'] ) ) : ?>
								<h3 class="hitprice-trust-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $card['description'] ) ) : ?>
								<p class="hitprice-trust-card__copy"><?php echo esc_html( $card['description'] ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
