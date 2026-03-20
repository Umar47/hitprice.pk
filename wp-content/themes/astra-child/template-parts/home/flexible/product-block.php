<?php
/**
 * Flexible product block section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$section       = hitprice_get_template_args();
$products      = function_exists( 'hp_get_homepage_product_block_products' ) ? hp_get_homepage_product_block_products( $section ) : array();
$cta_url       = function_exists( 'hp_get_homepage_product_block_cta_url' ) ? hp_get_homepage_product_block_cta_url( $section ) : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
$cta_label     = ! empty( $section['cta_label'] ) ? $section['cta_label'] : __( 'See full catalog', 'hitprice' );
$show_price    = ! empty( $section['show_price'] );
$show_rating   = ! empty( $section['show_rating'] );
?>
<section class="hitprice-home-section hitprice-home-section--products">
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
			<a class="hitprice-home-text-link" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<div class="hitprice-product-grid">
				<?php foreach ( $products as $product ) : ?>
					<?php if ( ! $product instanceof WC_Product ) { continue; } ?>
					<?php
					$product_id = $product->get_id();
					$image_id   = $product->get_image_id();
					$image_html = $image_id ? wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 'lazy' ) ) : '';
					?>
					<article class="hitprice-product-card">
						<a class="hitprice-product-card__image" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
							<?php if ( $image_html ) : ?>
								<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<span class="hitprice-product-card__image-placeholder"><?php echo esc_html( strtoupper( mb_substr( $product->get_name(), 0, 1 ) ) ); ?></span>
							<?php endif; ?>
						</a>
						<div class="hitprice-product-card__body">
							<p class="hitprice-product-card__meta"><?php echo esc_html( $product->is_in_stock() ? __( 'In stock', 'hitprice' ) : __( 'Check availability', 'hitprice' ) ); ?></p>
							<h3 class="hitprice-product-card__title">
								<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
							</h3>
							<?php if ( $show_price ) : ?>
								<p class="hitprice-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
							<?php endif; ?>
							<?php if ( $show_rating && function_exists( 'wc_get_rating_html' ) ) : ?>
								<div class="hitprice-product-card__rating"><?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?></div>
							<?php endif; ?>
							<a class="hitprice-home-text-link" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php esc_html_e( 'View product', 'hitprice' ); ?></a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="hitprice-home-empty-state">
				<h3><?php esc_html_e( 'No products selected yet', 'hitprice' ); ?></h3>
				<p><?php esc_html_e( 'Choose manual products or switch this block to a featured, latest, or category-based query from ACF.', 'hitprice' ); ?></p>
				<a class="hitprice-home-button hitprice-home-button--secondary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
