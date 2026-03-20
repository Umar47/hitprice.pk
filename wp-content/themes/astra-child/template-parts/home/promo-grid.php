<?php
/**
 * Homepage promo section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$promos   = array(
	array(
		'kicker' => 'Mobile deals',
		'title'  => 'Big savings on flagship phones and must-have accessories',
		'copy'   => 'Create one dominant campaign tile for launches, offers, and price-led messaging that deserves the most attention.',
		'style'  => 'primary',
		'url'    => $shop_url,
		'cta'    => 'Shop phone deals',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/category-phone.jpg',
		'points' => array(
			'Trade-up style campaign space',
			'Room for pricing and financing',
			'One strong CTA above the fold',
		),
	),
	array(
		'kicker' => 'TV and entertainment',
		'title'  => 'Smart screens and living room upgrades',
		'copy'   => 'Supporting promotional tiles should feel cleaner and shorter, with image, headline, and one route deeper.',
		'style'  => 'light',
		'url'    => $shop_url,
		'cta'    => 'Explore TVs',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/promo-tv.jpg',
		'points' => array(
			'Short supporting copy',
			'Stronger image-led preview',
		),
	),
	array(
		'kicker' => 'Home essentials',
		'title'  => 'Cooling, kitchen, and appliance picks',
		'copy'   => 'Use the third tile for seasonal demand, practical products, or bank-offer campaigns that need constant visibility.',
		'style'  => 'outline',
		'url'    => $shop_url,
		'cta'    => 'Shop home deals',
		'image'  => get_stylesheet_directory_uri() . '/assets/images/home/promo-appliance.jpg',
		'points' => array(
			'Seasonal merchandising zone',
			'Good for practical categories',
		),
	),
);
?>
<section class="hitprice-home-section hitprice-home-section--promos">
	<div class="hitprice-shell">
		<div class="hitprice-home-section__head">
			<div>
				<p class="hitprice-home-kicker">Featured offers</p>
				<h2 class="hitprice-home-section__title">A campaign grid with one lead story and supporting offer tiles.</h2>
			</div>
		</div>
		<div class="hitprice-tilegrid">
			<?php foreach ( $promos as $index => $promo ) : ?>
				<article class="hitprice-promo-card hitprice-promo-card--<?php echo esc_attr( $promo['style'] ); ?><?php echo 0 === $index ? ' hitprice-promo-card--feature' : ''; ?>">
					<div class="hitprice-promo-card__media">
						<img src="<?php echo esc_url( $promo['image'] ); ?>" alt="<?php echo esc_attr( $promo['title'] ); ?>" loading="lazy" decoding="async">
					</div>
					<div class="hitprice-promo-card__body">
						<p class="hitprice-home-kicker"><?php echo esc_html( $promo['kicker'] ); ?></p>
						<h2 class="hitprice-promo-card__title"><?php echo esc_html( $promo['title'] ); ?></h2>
						<p class="hitprice-promo-card__copy"><?php echo esc_html( $promo['copy'] ); ?></p>
						<ul class="hitprice-promo-card__points">
							<?php foreach ( $promo['points'] as $point ) : ?>
								<li><?php echo esc_html( $point ); ?></li>
							<?php endforeach; ?>
						</ul>
						<a class="hitprice-home-text-link" href="<?php echo esc_url( $promo['url'] ); ?>"><?php echo esc_html( $promo['cta'] ); ?></a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
