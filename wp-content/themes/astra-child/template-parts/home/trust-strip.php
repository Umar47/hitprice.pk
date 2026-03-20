<?php
/**
 * Homepage trust section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$trust_items = array(
	array(
		'title' => 'Cleaner product discovery',
		'copy'  => 'A homepage flow designed to reduce friction and get shoppers to the right category or product family faster.',
	),
	array(
		'title' => 'Mobile-first shopping',
		'copy'  => 'Built to scan quickly on smaller screens without clutter, oversized banners, or heavy interactions.',
	),
	array(
		'title' => 'Conversion-led layout',
		'copy'  => 'More emphasis on products, categories, trust, and deals that actually move customers deeper into the funnel.',
	),
	array(
		'title' => 'Campaign-ready sections',
		'copy'  => 'The structure leaves room for flash sales, installment offers, bank deals, and seasonal pushes without redesigning the page.',
	),
);
?>
<section class="hitprice-home-section hitprice-home-section--trust">
	<div class="hitprice-shell">
		<div class="hitprice-trust-strip">
			<div class="hitprice-trust-strip__intro">
				<p class="hitprice-home-kicker">Why this homepage works</p>
				<h2 class="hitprice-home-section__title">A modern storefront tuned for clarity, speed, and sales momentum.</h2>
				<p class="hitprice-trust-strip__copy">The design combines premium focus, practical navigation, and selective deal energy without becoming noisy or slow.</p>
				<div class="hitprice-trust-strip__badges" aria-label="<?php esc_attr_e( 'Trust highlights', 'hitprice' ); ?>">
					<span>Authentic products</span>
					<span>Category-first browsing</span>
					<span>Future ACF-ready structure</span>
				</div>
			</div>
			<div class="hitprice-trust-grid">
				<?php foreach ( $trust_items as $trust_item ) : ?>
					<article class="hitprice-trust-card">
						<h3 class="hitprice-trust-card__title"><?php echo esc_html( $trust_item['title'] ); ?></h3>
						<p class="hitprice-trust-card__copy"><?php echo esc_html( $trust_item['copy'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
