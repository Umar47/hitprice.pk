<?php
/**
 * Header navigation.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args    = hitprice_get_template_args();
$context = isset( $args['context'] ) ? sanitize_key( $args['context'] ) : 'desktop';

if ( 'mobile' === $context ) :
	?>
	<div id="hitprice-mobile-panel" class="hitprice-mobile-panel" hidden>
		<div class="hitprice-shell">
			<nav class="hitprice-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'hitprice' ); ?>">
				<?php hitprice_render_header_menu(); ?>
			</nav>
		</div>
	</div>
	<?php
	return;
endif;
?>
<div class="hitprice-header-nav">
	<div class="hitprice-shell">
		<nav class="hitprice-desktop-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'hitprice' ); ?>">
			<?php hitprice_render_header_menu(); ?>
		</nav>
	</div>
</div>
