<?php
/**
 * Homepage "Why Buy From Us" section.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args     = hitprice_get_template_args();
$title    = isset( $args['title'] ) ? (string) $args['title'] : '';
$subtitle = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$items    = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<section class="hp-section hp-why-buy">
	<?php if ( $title || $subtitle ) : ?>
		<header class="hp-section__head hp-section__head--center">
			<div class="hp-section__head-text">
				<?php if ( $title ) : ?>
					<h2 class="hp-section__title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( $subtitle ) : ?>
					<p class="hp-section__subtitle"><?php echo esc_html( $subtitle ); ?></p>
				<?php endif; ?>
			</div>
		</header>
	<?php endif; ?>

	<ul class="hp-why-buy__list" role="list">
		<?php foreach ( $items as $item ) : ?>
			<li class="hp-why-buy__item">
				<h3 class="hp-why-buy__title"><?php echo esc_html( $item['title'] ); ?></h3>

				<?php if ( ! empty( $item['description'] ) ) : ?>
					<p class="hp-why-buy__text"><?php echo esc_html( $item['description'] ); ?></p>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
