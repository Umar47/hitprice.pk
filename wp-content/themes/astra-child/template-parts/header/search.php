<?php
/**
 * Header product search trigger.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$placeholder = esc_attr__( 'Search mobiles, electronics, appliances and more', 'hitprice' );
?>
<div class="hitprice-search">
	<form
		role="search"
		method="get"
		class="hitprice-search-form"
		action="<?php echo esc_url( home_url( '/' ) ); ?>"
		data-hp-search-form
	>
		<label class="screen-reader-text" for="hitprice-product-search"><?php esc_html_e( 'Search products', 'hitprice' ); ?></label>
		<input
			id="hitprice-product-search"
			type="search"
			class="hitprice-search-form__field"
			placeholder="<?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			name="s"
			autocomplete="off"
			autocorrect="off"
			autocapitalize="off"
			spellcheck="false"
			inputmode="search"
			enterkeyhint="search"
			aria-label="<?php esc_attr_e( 'Search products', 'hitprice' ); ?>"
			aria-haspopup="listbox"
			aria-expanded="false"
			data-hp-search-trigger
		/>
		<input type="hidden" name="post_type" value="product" />
		<button type="submit" class="hitprice-search-form__submit" aria-label="<?php esc_attr_e( 'Open search', 'hitprice' ); ?>" data-hp-search-trigger>
			<span class="hitprice-search-form__submit-text"><?php esc_html_e( 'Search', 'hitprice' ); ?></span>
		</button>
	</form>
</div>
