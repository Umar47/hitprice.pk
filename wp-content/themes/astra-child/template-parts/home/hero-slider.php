<?php
/**
 * Homepage hero slider.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args   = hitprice_get_template_args();
$slides = isset( $args['slides'] ) && is_array( $args['slides'] ) ? $args['slides'] : array();

if ( empty( $slides ) ) {
	return;
}

$slider_id = 'hp-hero-' . wp_unique_id();
$is_single = count( $slides ) === 1;
?>
<section class="hp-section hp-hero">
	<div
		class="hp-slider hp-slider--hero<?php echo $is_single ? ' is-single' : ''; ?>"
		data-hp-slider="hero"
		aria-roledescription="carousel"
		aria-label="<?php esc_attr_e( 'Homepage highlights', 'hitprice' ); ?>"
	>
		<div class="hp-slider__viewport" id="<?php echo esc_attr( $slider_id ); ?>">
			<ul class="hp-slider__track" role="list">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php
					$image = $slide['background_image'];
					$image_url = is_array( $image ) && ! empty( $image['url'] ) ? $image['url'] : '';
					$image_alt = is_array( $image ) && ! empty( $image['alt'] ) ? $image['alt'] : ( $slide['heading'] ?: __( 'Hero slide', 'hitprice' ) );
					$image_src_set = is_array( $image ) && ! empty( $image['sizes'] ) ? $image['sizes'] : array();

					if ( ! $image_url ) {
						continue;
					}
					?>
					<li
						class="hp-slider__slide hp-hero__slide"
						role="group"
						aria-roledescription="slide"
						aria-label="<?php echo esc_attr( sprintf( /* translators: 1: current slide, 2: total slides */ __( '%1$d of %2$d', 'hitprice' ), $index + 1, count( $slides ) ) ); ?>"
					>
						<div class="hp-hero__media">
							<img
								src="<?php echo esc_url( $image_url ); ?>"
								alt="<?php echo esc_attr( $image_alt ); ?>"
								loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
								decoding="async"
								<?php if ( 0 === $index ) : ?>fetchpriority="high"<?php endif; ?>
							>
						</div>

						<?php if ( $slide['heading'] || $slide['subheading'] || $slide['offer_text'] || $slide['cta1_label'] || $slide['cta2_label'] ) : ?>
							<div class="hp-hero__content">
								<div class="hp-hero__content-inner">
									<?php if ( $slide['heading'] ) : ?>
										<h2 class="hp-hero__heading"><?php echo esc_html( $slide['heading'] ); ?></h2>
									<?php endif; ?>

									<?php if ( $slide['subheading'] ) : ?>
										<p class="hp-hero__subheading"><?php echo esc_html( $slide['subheading'] ); ?></p>
									<?php endif; ?>

									<?php if ( $slide['offer_text'] ) : ?>
										<p class="hp-hero__offer"><?php echo esc_html( $slide['offer_text'] ); ?></p>
									<?php endif; ?>

									<?php if ( $slide['cta1_label'] || $slide['cta2_label'] ) : ?>
										<div class="hp-hero__ctas">
											<?php if ( $slide['cta1_label'] && $slide['cta1_url'] ) : ?>
												<a class="hp-btn hp-btn--primary" href="<?php echo esc_url( $slide['cta1_url'] ); ?>">
													<?php echo esc_html( $slide['cta1_label'] ); ?>
												</a>
											<?php endif; ?>

											<?php if ( $slide['cta2_label'] && $slide['cta2_url'] ) : ?>
												<a class="hp-btn hp-btn--secondary" href="<?php echo esc_url( $slide['cta2_url'] ); ?>">
													<?php echo esc_html( $slide['cta2_label'] ); ?>
												</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( ! $is_single ) : ?>
			<button
				type="button"
				class="hp-slider__arrow hp-slider__arrow--prev"
				data-hp-slider-prev
				aria-controls="<?php echo esc_attr( $slider_id ); ?>"
				aria-label="<?php esc_attr_e( 'Previous slide', 'hitprice' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path d="M12.5 4L6.5 10l6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<button
				type="button"
				class="hp-slider__arrow hp-slider__arrow--next"
				data-hp-slider-next
				aria-controls="<?php echo esc_attr( $slider_id ); ?>"
				aria-label="<?php esc_attr_e( 'Next slide', 'hitprice' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path d="M7.5 4l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<div class="hp-slider__dots" data-hp-slider-dots role="tablist" aria-label="<?php esc_attr_e( 'Slide navigation', 'hitprice' ); ?>">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<button
						type="button"
						class="hp-slider__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-hp-slider-dot="<?php echo esc_attr( (string) $index ); ?>"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number */ __( 'Go to slide %d', 'hitprice' ), $index + 1 ) ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
