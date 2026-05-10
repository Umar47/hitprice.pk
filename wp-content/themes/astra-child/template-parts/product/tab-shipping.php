<?php
/**
 * Shipping & Returns tab panel.
 *
 * Content pulled from the "Shipping Policy" wp_editor field in
 * HitPrice Global Settings (hp_global_settings → shipping_policy).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_global_setting' ) ) {
	return;
}

$policy = hp_get_global_setting( 'shipping_policy', '' );

if ( ! $policy ) {
	echo '<p class="hp-tab-empty">' . esc_html__( 'Shipping & returns policy coming soon.', 'hitprice' ) . '</p>';
	return;
}
?>
<div class="hp-shipping">
	<div class="hp-shipping__content">
		<?php echo wp_kses_post( $policy ); ?>
	</div>
</div>
