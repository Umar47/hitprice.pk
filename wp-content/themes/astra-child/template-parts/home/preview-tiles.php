<?php
/**
 * Homepage preview tiles section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$preview_tiles = array(
	array(
		'kicker' => 'Phones',
		'title'  => 'Latest mobile launches and premium picks',
		'copy'   => 'Short, image-led preview card with one clear path into the category.',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/hero-phone.jpg',
		'url'    => $shop_url,
		'cta'    => 'Explore phones',
	),
	array(
		'kicker' => 'Home entertainment',
		'title'  => 'TV and living room upgrades',
		'copy'   => 'Keep the copy tight and let the image plus heading do most of the work.',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/promo-tv.jpg',
		'url'    => $shop_url,
		'cta'    => 'Shop entertainment',
	),
	array(
		'kicker' => 'Appliances',
		'title'  => 'Seasonal and practical home essentials',
		'copy'   => 'Good for appliance pushes that need consistent visibility on the homepage.',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/promo-appliance.jpg',
		'url'    => $shop_url,
		'cta'    => 'See appliance picks',
	),
);
?>
<section class="hitprice-home-section hitprice-home-section--preview">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<p class="hitprice-home-kicker">Shop more</p>
				<h2 class="hitprice-home-section__title">Preview tiles for categories worth featuring next.</h2>
			</div>
		</div>
		<div class="hitprice-previewtiles">
			<?php foreach ( $preview_tiles as $preview_tile ) : ?>
				<article class="hitprice-preview-tile">
					<a class="hitprice-preview-tile__media" href="<?php echo esc_url( $preview_tile['url'] ); ?>">
						<img src="<?php echo esc_url( $preview_tile['image'] ); ?>" alt="<?php echo esc_attr( $preview_tile['title'] ); ?>" loading="lazy" decoding="async">
					</a>
					<div class="hitprice-preview-tile__body">
						<p class="hitprice-home-kicker"><?php echo esc_html( $preview_tile['kicker'] ); ?></p>
						<h3 class="hitprice-preview-tile__title"><?php echo esc_html( $preview_tile['title'] ); ?></h3>
						<p class="hitprice-preview-tile__copy"><?php echo esc_html( $preview_tile['copy'] ); ?></p>
						<a class="hitprice-home-text-link" href="<?php echo esc_url( $preview_tile['url'] ); ?>"><?php echo esc_html( $preview_tile['cta'] ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
