<?php
/**
 * Footer main area scaffold.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="hitprice-footer-main">
	<div class="hitprice-shell">
		<div class="hitprice-footer-main__grid">
			<div class="hitprice-footer-block">
				<h2 class="hitprice-footer-block__title"><?php esc_html_e( 'Hit Price', 'hitprice' ); ?></h2>
				<p class="hitprice-footer-block__text"><?php esc_html_e( 'Fast, trustworthy ecommerce for mobiles, electronics and home appliances.', 'hitprice' ); ?></p>
			</div>

			<div class="hitprice-footer-block">
				<h2 class="hitprice-footer-block__title"><?php esc_html_e( 'Footer Menu', 'hitprice' ); ?></h2>
				<?php
				if ( has_nav_menu( 'hitprice_footer_menu' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'hitprice_footer_menu',
							'container'      => false,
							'menu_class'     => 'hitprice-footer-menu',
							'fallback_cb'    => false,
						)
					);
				} else {
					?>
					<ul class="hitprice-footer-menu">
						<li><a href="<?php echo esc_url( hitprice_get_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'hitprice' ); ?></a></li>
						<li><a href="<?php echo esc_url( hitprice_get_wc_page_url( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'hitprice' ); ?></a></li>
						<li><a href="<?php echo esc_url( hitprice_get_cart_url() ); ?>"><?php esc_html_e( 'Cart', 'hitprice' ); ?></a></li>
					</ul>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
