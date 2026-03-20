<?php
/**
 * Template Name: Hit Price Homepage
 * Custom homepage page template.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="primary" class="site-main hitprice-front-page">
	<?php
	$home_sections = function_exists( 'hp_get_homepage_sections' ) ? hp_get_homepage_sections( get_the_ID() ) : array();

	if ( ! empty( $home_sections ) ) :
		foreach ( $home_sections as $section ) :
			$layout   = isset( $section['acf_fc_layout'] ) ? $section['acf_fc_layout'] : '';
			$template = function_exists( 'hp_get_homepage_section_template' ) ? hp_get_homepage_section_template( $layout ) : '';

			if ( $template ) {
				hitprice_get_template_part( $template, $section );
			}
		endforeach;
	else :
		hitprice_get_template_part( 'template-parts/home/hero' );
		hitprice_get_template_part( 'template-parts/home/categories' );
		hitprice_get_template_part( 'template-parts/home/promo-grid' );
		hitprice_get_template_part( 'template-parts/home/preview-tiles' );
		hitprice_get_template_part( 'template-parts/home/featured-products' );
		hitprice_get_template_part( 'template-parts/home/trust-strip' );
	endif;
	?>
</main>
<?php
get_footer();
