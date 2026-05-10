<?php
/**
 * "Why buy from Hitprice.pk?" section.
 * Data managed from HitPrice Settings → Before Key Highlights.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_why_buy_section' ) ) {
	return;
}

$section = hp_get_why_buy_section();

if ( empty( $section['enabled'] ) || empty( $section['items'] ) ) {
	return;
}

$items = array_slice( $section['items'], 0, 5 );
$title = $section['title'];
?>
<section class="hp-why-buy">
	<div class="hp-why-buy__inner">

		<?php if ( $title ) : ?>
			<h2 class="hp-why-buy__heading"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>

		<div class="hp-why-buy__grid">
			<?php foreach ( $items as $item ) :
				if ( empty( $item['title'] ) ) { continue; }
			?>
				<div class="hp-why-buy__item">
					<?php if ( ! empty( $item['image_url'] ) ) : ?>
						<div class="hp-why-buy__icon-wrap" aria-hidden="true">
							<img src="<?php echo esc_url( $item['image_url'] ); ?>"
							     alt=""
							     width="52" height="52"
							     loading="lazy"
							     decoding="async"
							     class="hp-why-buy__icon">
						</div>
					<?php endif; ?>
					<div class="hp-why-buy__text">
						<span class="hp-why-buy__title"><?php echo esc_html( $item['title'] ); ?></span>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<span class="hp-why-buy__desc"><?php echo esc_html( $item['description'] ); ?></span>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
</section>
