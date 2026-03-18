<?php
/**
 * Header wrapper.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<header id="masthead" class="site-header hitprice-site-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
	<?php hitprice_get_template_part( 'template-parts/header/top-bar' ); ?>

	<div class="hitprice-header-main">
		<div class="hitprice-shell hitprice-header-main__inner">
			<button class="hitprice-mobile-toggle" type="button" aria-expanded="false" aria-controls="hitprice-mobile-panel">
				<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'hitprice' ); ?></span>
				<span class="hitprice-mobile-toggle__icon" aria-hidden="true"></span>
			</button>

			<?php hitprice_get_template_part( 'template-parts/header/branding' ); ?>
			<?php hitprice_get_template_part( 'template-parts/header/search' ); ?>
			<?php hitprice_get_template_part( 'template-parts/header/actions' ); ?>
		</div>
	</div>

	<?php hitprice_get_template_part( 'template-parts/header/navigation', array( 'context' => 'desktop' ) ); ?>
	<?php hitprice_get_template_part( 'template-parts/header/navigation', array( 'context' => 'mobile' ) ); ?>
</header>
