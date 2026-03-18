<?php
/**
 * Footer wrapper.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer id="colophon" class="site-footer hitprice-site-footer" itemscope="itemscope" itemtype="https://schema.org/WPFooter">
	<?php hitprice_get_template_part( 'template-parts/footer/footer-widgets' ); ?>
	<?php hitprice_get_template_part( 'template-parts/footer/copyright' ); ?>
</footer>
