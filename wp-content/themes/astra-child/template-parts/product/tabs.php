<?php
/**
 * Product tab system — Overview, Specifications, Reviews, Q&A, Shipping & Returns.
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

$review_count = $product->get_review_count();
$review_label = $review_count
	? sprintf( _n( 'Reviews (%d)', 'Reviews (%d)', $review_count, 'hitprice' ), $review_count )
	: esc_html__( 'Reviews', 'hitprice' );

$tabs = array(
	'overview'       => esc_html__( 'Overview', 'hitprice' ),
	'specifications' => esc_html__( 'Specifications', 'hitprice' ),
	'reviews'        => $review_label,
	'qa'             => esc_html__( 'Q&A', 'hitprice' ),
	'shipping'       => esc_html__( 'Shipping & Returns', 'hitprice' ),
);
?>
<section class="hp-tabs" id="hp-tabs" data-hp-tabs>
	<div class="hp-tabs__nav-wrap">
		<nav class="hp-tabs__nav" role="tablist" aria-label="<?php esc_attr_e( 'Product information tabs', 'hitprice' ); ?>">
			<?php foreach ( $tabs as $id => $label ) : ?>
				<button class="hp-tabs__tab<?php echo 'overview' === $id ? ' is-active' : ''; ?>"
				        role="tab"
				        id="hp-tabnav-<?php echo esc_attr( $id ); ?>"
				        aria-controls="hp-tab-<?php echo esc_attr( $id ); ?>"
				        aria-selected="<?php echo 'overview' === $id ? 'true' : 'false'; ?>"
				        data-tab="<?php echo esc_attr( $id ); ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</nav>
	</div>

	<div class="hp-tabs__panels">

		<div class="hp-tabs__panel is-active"
		     id="hp-tab-overview"
		     role="tabpanel"
		     aria-labelledby="hp-tabnav-overview"
		     tabindex="0">
			<?php hitprice_get_template_part( 'template-parts/product/tab-overview' ); ?>
		</div>

		<div class="hp-tabs__panel"
		     id="hp-tab-specifications"
		     role="tabpanel"
		     aria-labelledby="hp-tabnav-specifications"
		     tabindex="0"
		     hidden>
			<?php hitprice_get_template_part( 'template-parts/product/tab-specifications' ); ?>
		</div>

		<div class="hp-tabs__panel"
		     id="hp-tab-reviews"
		     role="tabpanel"
		     aria-labelledby="hp-tabnav-reviews"
		     tabindex="0"
		     hidden>
			<?php hitprice_get_template_part( 'template-parts/product/tab-reviews' ); ?>
		</div>

		<div class="hp-tabs__panel"
		     id="hp-tab-qa"
		     role="tabpanel"
		     aria-labelledby="hp-tabnav-qa"
		     tabindex="0"
		     hidden>
			<?php hitprice_get_template_part( 'template-parts/product/tab-qa' ); ?>
		</div>

		<div class="hp-tabs__panel"
		     id="hp-tab-shipping"
		     role="tabpanel"
		     aria-labelledby="hp-tabnav-shipping"
		     tabindex="0"
		     hidden>
			<?php hitprice_get_template_part( 'template-parts/product/tab-shipping' ); ?>
		</div>

	</div>
</section>
