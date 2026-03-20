<?php
/**
 * Homepage hero section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url         = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$deals_url        = add_query_arg( 'orderby', 'date', $shop_url );
$hero_phone_image = get_stylesheet_directory_uri() . '/assets/images/home/hero-phone.jpg';
?>
<section class="hitprice-home-hero">
	<div class="hitprice-shell">
		<div class="hitprice-home-hero__grid">
			<div class="hitprice-home-hero__content">
				<p class="hitprice-home-kicker">Hit Price curated picks</p>
				<h1 class="hitprice-home-hero__title">Latest tech, smarter prices, delivered with less friction.</h1>
				<p class="hitprice-home-hero__text">Shop mobiles, accessories, TVs, ACs, kitchen appliances, and computers through a cleaner storefront built to move people from discovery to checkout faster.</p>
				<div class="hitprice-home-hero__offer">
					<span class="hitprice-home-hero__offer-label">This week only</span>
					<strong>Launch-ready deals across phones, screens, and everyday home upgrades.</strong>
					<p>Use this space later for live campaigns, bank discounts, installment offers, or category pushes.</p>
				</div>
				<div class="hitprice-home-hero__actions">
					<a class="hitprice-home-button hitprice-home-button--primary" href="<?php echo esc_url( $shop_url ); ?>">Shop now</a>
					<a class="hitprice-home-button hitprice-home-button--secondary" href="<?php echo esc_url( $deals_url ); ?>">View latest deals</a>
				</div>
				<ul class="hitprice-home-hero__points" aria-label="<?php esc_attr_e( 'Shopping advantages', 'hitprice' ); ?>">
					<li><strong>Fast discovery</strong><span>Lead customers into key categories without clutter.</span></li>
					<li><strong>Stronger promos</strong><span>Showcase the offers that actually move clicks.</span></li>
					<li><strong>Mobile-first UX</strong><span>Built for quick scanning on smaller screens.</span></li>
				</ul>
				<div class="hitprice-home-hero__metrics" aria-label="<?php esc_attr_e( 'Homepage highlights', 'hitprice' ); ?>">
					<div class="hitprice-home-metric">
						<strong>Top picks</strong>
						<span>Flagship launches, value phones, and must-have accessories.</span>
					</div>
					<div class="hitprice-home-metric">
						<strong>Big appliances</strong>
						<span>Clearer entry points for TV, AC, kitchen, and home upgrades.</span>
					</div>
					<div class="hitprice-home-metric">
						<strong>Designed to convert</strong>
						<span>Premium look with practical ecommerce hierarchy.</span>
					</div>
				</div>
			</div>
			<div class="hitprice-home-hero__visual">
				<article class="hitprice-home-feature-card">
					<div class="hitprice-home-feature-card__media">
						<img src="<?php echo esc_url( $hero_phone_image ); ?>" alt="<?php esc_attr_e( 'Premium smartphone on desk', 'hitprice' ); ?>" loading="eager" decoding="async">
					</div>
					<div class="hitprice-home-feature-card__body">
						<span class="hitprice-home-device-card__eyebrow">Flagship phones</span>
						<strong>Latest arrivals with premium positioning</strong>
						<p>Use strong product photography, short campaign copy, and one direct CTA to make the first scroll feel intentional.</p>
					</div>
				</article>
				<div class="hitprice-home-hero__visual-grid">
					<div class="hitprice-home-device-card hitprice-home-device-card--large">
						<span class="hitprice-home-device-card__eyebrow">Home upgrade</span>
						<strong>TVs, AC, appliances</strong>
						<span>High-value categories deserve simpler browsing paths and stronger urgency cues.</span>
					</div>
					<div class="hitprice-home-device-card hitprice-home-device-card--accent">
						<span class="hitprice-home-device-card__eyebrow">Hit Price promise</span>
						<strong>Modern layout, practical shopping flow</strong>
						<span>Borrow the best parts of premium telecom and ecommerce UX without the bloat.</span>
					</div>
				</div>
				<div class="hitprice-home-stat">
					<span class="hitprice-home-stat__value">6+</span>
					<span class="hitprice-home-stat__label">Core categories ready for rapid browsing and future campaign targeting</span>
				</div>
			</div>
		</div>
	</div>
</section>
