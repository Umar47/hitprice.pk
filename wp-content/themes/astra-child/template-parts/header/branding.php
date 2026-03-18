<?php
/**
 * Site branding.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( function_exists( 'astra_site_branding_markup' ) ) {
	echo '<div class="hitprice-branding">';
	astra_site_branding_markup();
	echo '</div>';
}
