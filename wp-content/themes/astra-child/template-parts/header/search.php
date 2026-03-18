<?php
/**
 * Header product search.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="hitprice-search">
	<form role="search" method="get" class="hitprice-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="hitprice-product-search"><?php esc_html_e( 'Search products', 'hitprice' ); ?></label>
		<input id="hitprice-product-search" type="search" class="hitprice-search-form__field" placeholder="<?php esc_attr_e( 'Search mobiles, electronics, appliances and more', 'hitprice' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
		<input type="hidden" name="post_type" value="product" />
		<button type="submit" class="hitprice-search-form__submit"><?php esc_html_e( 'Search', 'hitprice' ); ?></button>
	</form>
</div>
