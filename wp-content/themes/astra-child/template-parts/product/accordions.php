<?php
/**
 * Product accordions — renders WooCommerce tabs data as accordion UI.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $hp_product_tabs;

if ( empty( $hp_product_tabs ) || ! is_array( $hp_product_tabs ) ) {
	return;
}
?>
<section class="hp-accordions" aria-labelledby="hp-accordions-heading">
	<div class="hp-accordions__inner">
		<h2 class="hp-accordions__heading" id="hp-accordions-heading">
			<?php esc_html_e( 'Product details', 'hitprice' ); ?>
		</h2>
		<?php foreach ( $hp_product_tabs as $key => $tab ) : ?>
			<details class="hp-accordions__item" id="hp-tab-<?php echo esc_attr( $key ); ?>">
				<summary class="hp-accordions__trigger">
					<span class="hp-accordions__title"><?php echo esc_html( $tab['title'] ); ?></span>
					<span class="hp-accordions__icon" aria-hidden="true"></span>
				</summary>
				<div class="hp-accordions__panel">
					<?php
					if ( isset( $tab['callback'] ) ) {
						call_user_func( $tab['callback'], $key, $tab );
					}
					?>
				</div>
			</details>
		<?php endforeach; ?>
	</div>
</section>
