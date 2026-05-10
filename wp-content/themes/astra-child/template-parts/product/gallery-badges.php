<?php
/**
 * Gallery badge overlays: PTA Approved (conditional) + Best Price Guarantee (always).
 * Positioned absolutely inside .hp-gallery-outer.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_gallery_top_icon' ) ) {
	return;
}

global $product;
if ( ! $product ) {
	return;
}

$show_pta   = function_exists( 'hp_is_pta_approved' ) && hp_is_pta_approved( $product->get_id() );
$best_price = hp_get_gallery_top_icon( 'best_price' );

if ( ! $show_pta && empty( $best_price['image_url'] ) ) {
	return;
}
?>
<div class="hp-gallery-badges" aria-hidden="true">

	<?php if ( $show_pta ) :
		$pta = hp_get_gallery_top_icon( 'pta' );
	?>
		<div class="hp-gallery-badge hp-gallery-badge--pta">
			<?php if ( ! empty( $pta['image_url'] ) ) : ?>
				<img src="<?php echo esc_url( $pta['image_url'] ); ?>"
				     alt="<?php echo esc_attr( $pta['label'] ); ?>"
				     width="90" height="90"
				     loading="eager"
				     decoding="async"
				     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block'">
				<span class="hp-gallery-badge__fallback" style="display:none"><?php echo esc_html( $pta['label'] ); ?></span>
			<?php elseif ( ! empty( $pta['label'] ) ) : ?>
				<span class="hp-gallery-badge__fallback"><?php echo esc_html( $pta['label'] ); ?></span>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<span></span><!-- placeholder to keep Best Price on the right -->
	<?php endif; ?>

	<?php if ( ! empty( $best_price['image_url'] ) ) : ?>
		<div class="hp-gallery-badge hp-gallery-badge--best-price">
			<img src="<?php echo esc_url( $best_price['image_url'] ); ?>"
			     alt="<?php echo esc_attr( $best_price['label'] ); ?>"
			     width="90" height="90"
			     loading="eager"
			     decoding="async"
			     onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block'">
			<span class="hp-gallery-badge__fallback" style="display:none"><?php echo esc_html( $best_price['label'] ); ?></span>
		</div>
	<?php endif; ?>

</div>
