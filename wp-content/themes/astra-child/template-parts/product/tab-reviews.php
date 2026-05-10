<?php
/**
 * Reviews tab panel.
 *
 * Renders a star-rating breakdown bar chart (PHP data → CSS bars) above the
 * WooCommerce native reviews list + comment form.
 *
 * Phase 7 hooks (registered in template-hooks.php):
 *  - comment_form_after_comment_field → hp_inject_review_image_upload()
 *  - woocommerce_review_after_comment_text → hp_render_review_images_for_comment()
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

// ── Star rating breakdown ────────────────────────────────────────────────
if ( $product ) {
	$avg    = (float) $product->get_average_rating();
	$total  = (int)   $product->get_review_count();
	$counts = $product->get_rating_counts(); // array{ '1'...'5' => int }

	if ( $total > 0 ) {
		?>
		<div class="hp-rating-breakdown">

			<div class="hp-rating-breakdown__summary">
				<div class="hp-rating-breakdown__avg-number">
					<?php echo esc_html( number_format( $avg, 1 ) ); ?>
				</div>
				<div class="hp-rating-breakdown__avg-stars">
					<?php echo wc_get_rating_html( $avg, $total ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<p class="hp-rating-breakdown__total">
					<?php
					printf(
						/* translators: %s: number of reviews */
						esc_html( _n( 'Based on %s review', 'Based on %s reviews', $total, 'hitprice' ) ),
						esc_html( number_format_i18n( $total ) )
					);
					?>
				</p>
			</div>

			<div class="hp-rating-breakdown__bars">
				<?php for ( $star = 5; $star >= 1; $star-- ) :
					$count = isset( $counts[ (string) $star ] ) ? (int) $counts[ (string) $star ] : 0;
					$pct   = round( ( $count / $total ) * 100 );
				?>
					<div class="hp-rating-breakdown__row">
						<span class="hp-rating-breakdown__star-lbl" aria-hidden="true"><?php echo esc_html( $star ); ?> ★</span>
						<div class="hp-rating-breakdown__track" role="img" aria-label="<?php
							/* translators: 1: star count 2: percentage */
							printf( esc_attr__( '%1$d-star reviews: %2$d%%', 'hitprice' ), $star, $pct );
						?>">
							<div class="hp-rating-breakdown__bar" style="width:<?php echo esc_attr( $pct ); ?>%"></div>
						</div>
						<span class="hp-rating-breakdown__count"><?php echo esc_html( $count ); ?></span>
					</div>
				<?php endfor; ?>
			</div>

		</div>
		<?php
	}
}

// ── WooCommerce reviews list + comment form ──────────────────────────────
if ( function_exists( 'woocommerce_template_single_reviews' ) ) {
	woocommerce_template_single_reviews();
}
