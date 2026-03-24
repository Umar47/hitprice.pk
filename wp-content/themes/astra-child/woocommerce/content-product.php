<?php
/**
 * Custom WooCommerce loop product card.
 *
 * @package HitPrice
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

$product_id    = $product->get_id();
$product_url   = get_permalink( $product_id );
$image_id      = $product->get_image_id();
$image_html    = $image_id ? wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
$category_list = wc_get_product_category_list( $product_id, ', ', '', '' );
$short_desc    = wp_trim_words( wp_strip_all_tags( (string) $product->get_short_description() ), 10, '...' );
$stock_text    = $product->is_in_stock() ? __( 'In stock', 'hitprice' ) : __( 'Check availability', 'hitprice' );
$button_text   = $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? __( 'Add to cart', 'hitprice' ) : __( 'View details', 'hitprice' );
?>
<li <?php wc_product_class( 'hitprice-product-card', $product ); ?>>
	<a class="hitprice-product-card__media" href="<?php echo esc_url( $product_url ); ?>">
		<?php if ( $image_html ) : ?>
			<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php else : ?>
			<span class="hitprice-product-card__placeholder"><?php echo esc_html( strtoupper( mb_substr( $product->get_name(), 0, 1 ) ) ); ?></span>
		<?php endif; ?>
	</a>

	<div class="hitprice-product-card__content">
		<p class="hitprice-product-card__stock"><?php echo esc_html( $stock_text ); ?></p>

		<?php if ( $category_list ) : ?>
			<p class="hitprice-product-card__category"><?php echo wp_kses_post( $category_list ); ?></p>
		<?php endif; ?>

		<h2 class="hitprice-product-card__title">
			<a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h2>

		<?php if ( $short_desc ) : ?>
			<p class="hitprice-product-card__desc"><?php echo esc_html( $short_desc ); ?></p>
		<?php endif; ?>

		<div class="hitprice-product-card__meta">
			<div class="hitprice-product-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
			<?php if ( wc_review_ratings_enabled() ) : ?>
				<div class="hitprice-product-card__rating">
					<?php woocommerce_template_loop_rating(); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="hitprice-product-card__actions">
			<?php if ( function_exists( 'hpc_compare_button' ) ) : ?>
				<label class="hitprice-product-card__compare">
					<input type="checkbox" class="hpc-compare-check" data-product-id="<?php echo esc_attr( $product_id ); ?>">
					<span class="hitprice-product-card__compare-label"><?php esc_html_e( 'Compare', 'hitprice-compare' ); ?></span>
				</label>
			<?php endif; ?>
			<a class="hitprice-product-card__link" href="<?php echo esc_url( $product_url ); ?>"><?php esc_html_e( 'View product', 'hitprice' ); ?></a>
			<?php
			echo wp_kses_post(
				sprintf(
					'<a href="%1$s" data-quantity="1" class="%2$s" %3$s>%4$s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( implode( ' ', array_filter( array( 'button', 'hitprice-product-card__button', $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '', 'product_type_' . $product->get_type() ) ) ) ),
					wc_implode_html_attributes(
						array(
							'data-product_id'  => $product_id,
							'data-product_sku' => $product->get_sku(),
							'aria-label'       => $product->add_to_cart_description(),
							'rel'              => 'nofollow',
						)
					),
					esc_html( $button_text )
				)
			);
			?>
		</div>
	</div>
</li>
