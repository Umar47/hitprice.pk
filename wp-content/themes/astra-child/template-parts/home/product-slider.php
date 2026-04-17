<?php
/**
 * Homepage product slider (reused for Hot Deals & Latest Phones).
 *
 * Expected args:
 *  - slider_id : string (unique key, e.g. "hot-deals")
 *  - title     : string
 *  - subtitle  : string
 *  - products  : WC_Product[]
 *  - cta_label : string
 *  - cta_url   : string
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args      = hitprice_get_template_args();
$products  = isset( $args['products'] ) && is_array( $args['products'] ) ? $args['products'] : array();
$title     = isset( $args['title'] ) ? (string) $args['title'] : '';
$subtitle  = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$cta_label = isset( $args['cta_label'] ) ? (string) $args['cta_label'] : '';
$cta_url   = isset( $args['cta_url'] ) ? (string) $args['cta_url'] : '';
$slider_key = isset( $args['slider_id'] ) ? sanitize_html_class( (string) $args['slider_id'] ) : 'products';

if ( empty( $products ) ) {
	return;
}

$slider_dom_id = 'hp-ps-' . $slider_key . '-' . wp_unique_id();
$is_single     = count( $products ) === 1;
?>
<section class="hp-section hp-product-slider-section hp-product-slider-section--<?php echo esc_attr( $slider_key ); ?>">
	<header class="hp-section__head">
		<div class="hp-section__head-text">
			<?php if ( $title ) : ?>
				<h2 class="hp-section__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( $subtitle ) : ?>
				<p class="hp-section__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $cta_label && $cta_url ) : ?>
			<a class="hp-section__more" href="<?php echo esc_url( $cta_url ); ?>">
				<?php echo esc_html( $cta_label ); ?>
				<svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" focusable="false">
					<path d="M5 3l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</a>
		<?php endif; ?>
	</header>

	<div
		class="hp-slider hp-slider--products<?php echo $is_single ? ' is-single' : ''; ?>"
		data-hp-slider="products"
		aria-roledescription="carousel"
		<?php if ( $title ) : ?>aria-label="<?php echo esc_attr( $title ); ?>"<?php endif; ?>
	>
		<div class="hp-slider__viewport" id="<?php echo esc_attr( $slider_dom_id ); ?>">
			<ul class="hp-slider__track" role="list">
				<?php foreach ( $products as $product ) : ?>
					<?php
					if ( ! ( $product instanceof WC_Product ) ) {
						continue;
					}

					$product_id   = $product->get_id();
					$permalink    = get_permalink( $product_id );
					$image_id     = $product->get_image_id();
					$image_html   = $image_id
						? wp_get_attachment_image(
							$image_id,
							'woocommerce_thumbnail',
							false,
							array(
								'loading'  => 'lazy',
								'decoding' => 'async',
								'class'    => 'hp-product-card__img',
							)
						)
						: '';
					$price_html   = $product->get_price_html();
					$rating_count = $product->get_rating_count();
					$average      = $product->get_average_rating();
					?>
					<li class="hp-slider__slide hp-product-card">
						<a class="hp-product-card__media" href="<?php echo esc_url( $permalink ); ?>">
							<?php if ( $image_html ) : ?>
								<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<span class="hp-product-card__placeholder" aria-hidden="true">
									<?php echo esc_html( strtoupper( mb_substr( $product->get_name(), 0, 1 ) ) ); ?>
								</span>
							<?php endif; ?>
						</a>

						<div class="hp-product-card__body">
							<h3 class="hp-product-card__title">
								<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
							</h3>

							<?php if ( wc_review_ratings_enabled() && $rating_count > 0 ) : ?>
								<div class="hp-product-card__rating" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: rating out of 5 */ __( 'Rated %s out of 5', 'hitprice' ), $average ) ); ?>">
									<?php echo wc_get_rating_html( $average, $rating_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php endif; ?>

							<?php if ( $price_html ) : ?>
								<div class="hp-product-card__price">
									<?php echo wp_kses_post( $price_html ); ?>
								</div>
							<?php endif; ?>

							<a class="hp-product-card__link" href="<?php echo esc_url( $permalink ); ?>">
								<?php esc_html_e( 'View details', 'hitprice' ); ?>
							</a>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( ! $is_single ) : ?>
			<button
				type="button"
				class="hp-slider__arrow hp-slider__arrow--prev"
				data-hp-slider-prev
				aria-controls="<?php echo esc_attr( $slider_dom_id ); ?>"
				aria-label="<?php esc_attr_e( 'Previous products', 'hitprice' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path d="M12.5 4L6.5 10l6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>
			<button
				type="button"
				class="hp-slider__arrow hp-slider__arrow--next"
				data-hp-slider-next
				aria-controls="<?php echo esc_attr( $slider_dom_id ); ?>"
				aria-label="<?php esc_attr_e( 'Next products', 'hitprice' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path d="M7.5 4l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</button>

			<div class="hp-slider__dots" data-hp-slider-dots role="tablist" aria-label="<?php esc_attr_e( 'Product slider navigation', 'hitprice' ); ?>"></div>
		<?php endif; ?>
	</div>
</section>
