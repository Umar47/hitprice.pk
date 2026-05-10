<?php
/**
 * Key Highlights section.
 *
 * Desktop: 2-column layout — left (ACF WYSIWYG content), right (ACF infographic image).
 * Mobile:  Single column. Infographic image hidden. Overview specs surfaced above
 *          the highlights list as a compact 4-icon row (spec icons are hidden inside
 *          the Overview tab on mobile, shown here instead).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product ) {
	return;
}

$product_id = $product->get_id();
$content    = function_exists( 'hp_get_key_highlights_content' ) ? hp_get_key_highlights_content( $product_id ) : '';
$image      = function_exists( 'hp_get_key_highlights_image' )   ? hp_get_key_highlights_image( $product_id )   : null;
$specs      = function_exists( 'hp_get_overview_specs' )         ? hp_get_overview_specs( $product_id )         : array();

if ( ! $content && ! $image ) {
	return;
}

// Show at most 4 specs in the mobile surface row.
$surface_specs = array_slice( $specs, 0, 4 );
?>
<section class="hp-key-highlights">
	<div class="hp-key-highlights__inner">

		<?php if ( ! empty( $surface_specs ) ) : ?>
			<div class="hp-overview-specs-surface" aria-hidden="true">
				<?php foreach ( $surface_specs as $spec ) : ?>
					<div class="hp-overview-spec-item">
						<?php if ( ! empty( $spec['icon'] ) ) : ?>
							<i class="<?php echo esc_attr( $spec['icon'] ); ?> hp-overview-spec-item__icon" aria-hidden="true"></i>
						<?php endif; ?>
						<span class="hp-overview-spec-item__value"><?php echo esc_html( $spec['value'] ); ?></span>
						<span class="hp-overview-spec-item__title"><?php echo esc_html( $spec['title'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="hp-key-highlights__cols<?php echo $image ? '' : ' hp-key-highlights__cols--no-image'; ?>">

			<?php if ( $content ) : ?>
				<div class="hp-key-highlights__content">
					<h2 class="hp-key-highlights__heading">
						<?php esc_html_e( 'Key Highlights', 'hitprice' ); ?>
					</h2>
					<?php echo $content; // Already sanitized via wp_kses_post() in hp_get_key_highlights_content() ?>
				</div>
			<?php endif; ?>

			<?php if ( $image ) : ?>
				<div class="hp-key-highlights__image-wrap" aria-hidden="true">
					<img src="<?php echo esc_url( $image['url'] ); ?>"
					     alt="<?php echo esc_attr( $image['alt'] ?? '' ); ?>"
					     width="<?php echo esc_attr( $image['width'] ?? '' ); ?>"
					     height="<?php echo esc_attr( $image['height'] ?? '' ); ?>"
					     loading="lazy"
					     decoding="async"
					     class="hp-key-highlights__image">
				</div>
			<?php endif; ?>

		</div>

	</div>
</section>
