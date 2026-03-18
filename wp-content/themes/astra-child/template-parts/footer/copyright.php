<?php
/**
 * Footer copyright area.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="hitprice-footer-bottom">
	<div class="hitprice-shell hitprice-footer-bottom__inner">
		<p class="hitprice-footer-bottom__copy">
			<?php
			printf(
				esc_html__( '© %1$s %2$s. All rights reserved.', 'hitprice' ),
				esc_html( gmdate( 'Y' ) ),
				esc_html( get_bloginfo( 'name' ) )
			);
			?>
		</p>
	</div>
</div>
