<?php
/**
 * Header action links.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_count = hitprice_get_cart_count();
?>
<div class="hitprice-header-actions" aria-label="<?php esc_attr_e( 'Header actions', 'hitprice' ); ?>">
	<div class="ast-header-woo-cart hitprice-header-cart">
		<div class="ast-site-header-cart ast-menu-cart-with-border ast-desktop-cart-flyout">
			<div class="ast-site-header-cart-li">
				<a href="<?php echo esc_url( hitprice_get_cart_url() ); ?>" class="cart-container ast-cart-desktop-position-left ast-cart-mobile-position-left ast-cart-tablet-position-left" aria-label="<?php echo esc_attr( sprintf( __( 'View shopping cart, %d items', 'hitprice' ), $cart_count ) ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Cart', 'hitprice' ); ?></span>
					<span class="hitprice-header-cart__icon ast-icon-shopping-bag" aria-hidden="true"></span>
					<div class="ast-cart-menu-wrap" aria-hidden="true">
						<span class="count">
							<span class="ast-count-text"><?php echo esc_html( $cart_count ); ?></span>
						</span>
					</div>
				</a>
			</div>
			<div class="ast-site-header-cart-data">
				<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
			</div>
		</div>
	</div>
</div>
