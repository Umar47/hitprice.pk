<?php
/**
 * Sale banner — rendered after tax note, before price icons.
 * Controlled via HitPrice Settings → Sale Banner (enable toggle).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_sale_banner_settings' ) ) {
	return;
}

$banner = hp_get_sale_banner_settings();
if ( empty( $banner['enabled'] ) ) {
	return;
}

$text = $banner['text'] ?? '';
?>
<div class="hp-sale-banner" role="note" aria-label="<?php esc_attr_e( 'Sale offer', 'hitprice' ); ?>">
	<span class="hp-sale-banner__text"><?php echo wp_kses_post( $text ); ?></span>
</div>
