<?php
/**
 * Homepage "Shop By Category" cards (max 4).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args     = hitprice_get_template_args();
$title    = isset( $args['title'] ) ? (string) $args['title'] : '';
$subtitle = isset( $args['subtitle'] ) ? (string) $args['subtitle'] : '';
$cards    = isset( $args['cards'] ) && is_array( $args['cards'] ) ? $args['cards'] : array();

if ( empty( $cards ) ) {
	return;
}
?>
<section class="hp-section hp-shop-categories">
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

	<ul class="hp-shop-categories__grid hp-shop-categories__grid--<?php echo esc_attr( (string) count( $cards ) ); ?>" role="list">
		<?php foreach ( $cards as $card ) : ?>
			<?php
			$image       = $card['background_image'];
			$image_url   = is_array( $image ) && ! empty( $image['url'] ) ? $image['url'] : '';
			$image_alt   = is_array( $image ) && ! empty( $image['alt'] ) ? $image['alt'] : $card['title'];
			$card_url    = isset( $card['cta_url'] ) ? $card['cta_url'] : '';
			$card_label  = isset( $card['cta_label'] ) ? $card['cta_label'] : '';
			$card_text   = isset( $card['text'] ) ? $card['text'] : '';

			if ( ! $image_url ) {
				continue;
			}
			?>
			<li class="hp-shop-category-card">
				<div class="hp-shop-category-card__media">
					<img
						src="<?php echo esc_url( $image_url ); ?>"
						alt="<?php echo esc_attr( $image_alt ); ?>"
						loading="lazy"
						decoding="async"
					>
				</div>
				<div class="hp-shop-category-card__body">
					<h3 class="hp-shop-category-card__title"><?php echo esc_html( $card['title'] ); ?></h3>

					<?php if ( $card_text ) : ?>
						<p class="hp-shop-category-card__text"><?php echo esc_html( $card_text ); ?></p>
					<?php endif; ?>

					<?php if ( $card_label && $card_url ) : ?>
						<a class="hp-btn hp-btn--primary hp-shop-category-card__cta" href="<?php echo esc_url( $card_url ); ?>">
							<?php echo esc_html( $card_label ); ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
