<?php
/**
 * Header top area.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="hitprice-header-bar">
	<div class="hitprice-shell hitprice-header-bar__inner">
		<p class="hitprice-header-bar__message"><?php esc_html_e( 'Original products, trusted brands, nationwide delivery.', 'hitprice' ); ?></p>
		<div class="hitprice-header-bar__links" aria-label="<?php esc_attr_e( 'Utility links', 'hitprice' ); ?>">
			<a href="<?php echo esc_url( hitprice_get_wc_page_url( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'hitprice' ); ?></a>
			<a href="<?php echo esc_url( hitprice_get_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Shop All', 'hitprice' ); ?></a>
		</div>
	</div>
</div>
