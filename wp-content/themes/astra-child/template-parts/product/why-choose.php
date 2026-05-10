<?php
/**
 * "Why Choose [Product]?" section.
 *
 * Renders the WooCommerce long description (product → Description tab content)
 * under a product-specific heading. Returns early when the long description
 * is empty so no hollow section shell appears on the page.
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

$description = $product->get_description();
if ( empty( trim( $description ) ) ) {
	return;
}
?>
<section class="hp-why-choose">
	<div class="hp-why-choose__inner">
		<h2 class="hp-why-choose__heading">
			<?php
			printf(
				/* translators: %s: product name */
				esc_html__( 'Why Choose %s?', 'hitprice' ),
				esc_html( $product->get_name() )
			);
			?>
		</h2>
		<div class="hp-why-choose__content">
			<?php echo wp_kses_post( $description ); ?>
		</div>
	</div>
</section>
