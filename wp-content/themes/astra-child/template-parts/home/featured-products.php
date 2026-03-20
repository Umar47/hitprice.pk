<?php
/**
 * Homepage featured products section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$products = array();
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

if ( function_exists( 'wc_get_products' ) ) {
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'limit'   => 8,
			'orderby' => 'date',
			'order'   => 'DESC',
			'return'  => 'objects',
		)
	);
}
?>
<section class="hitprice-home-section hitprice-home-section--products">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<p class="hitprice-home-kicker">Latest products</p>
				<h2 class="hitprice-home-section__title">Fresh arrivals and fast-moving picks.</h2>
				<p class="hitprice-home-section__intro">This block is where strong imagery, visible pricing, and fast scanability should work together. The final version can later switch to featured, sale, or curated product sets.</p>
			</div>
			<a class="hitprice-home-text-link" href="<?php echo esc_url( $shop_url ); ?>">See full catalog</a>
		</div>
		<?php if ( ! empty( $products ) ) : ?>
			<div class="hitprice-product-grid">
				<?php foreach ( $products as $product ) : ?>
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
							<p class="hitprice-product-card__meta"><?php echo esc_html( $product->is_in_stock() ? 'In stock' : 'Check availability' ); ?></p>
							<h3 class="hitprice-product-card__title">
								<a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
							</h3>
							<p class="hitprice-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
							<p class="hitprice-product-card__support">Add review stars, savings badges, or quick selling points here once product data is ready.</p>
							<a class="hitprice-home-text-link" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">View product</a>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="hitprice-home-empty-state">
				<h3>No featured products yet</h3>
				<p>Add WooCommerce products to populate this section automatically, or later swap this to an ACF-driven curated list for more merchandising control.</p>
				<a class="hitprice-home-button hitprice-home-button--secondary" href="<?php echo esc_url( $shop_url ); ?>">Open shop page</a>
			</div>
		<?php endif; ?>
	</div>
</section>
