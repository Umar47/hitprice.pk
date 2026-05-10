<?php
/**
 * Overview tab panel.
 *
 * Left column: WC short description.
 * Right column: Overview specs repeater (icon + title + value, max 8).
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product ) {
	return;
}

$description = $product->get_short_description();
$specs       = function_exists( 'hp_get_overview_specs' ) ? hp_get_overview_specs( $product->get_id() ) : array();

if ( ! $description && empty( $specs ) ) {
	echo '<p class="hp-tab-empty">' . esc_html__( 'No overview available.', 'hitprice' ) . '</p>';
	return;
}
?>
<div class="hp-overview<?php echo ( $description && ! empty( $specs ) ) ? ' hp-overview--cols' : ''; ?>">

	<?php if ( $description ) : ?>
		<div class="hp-overview__description">
			<h2 class="hp-overview__heading"><?php esc_html_e( 'About this item', 'hitprice' ); ?></h2>
			<?php echo wp_kses_post( $description ); ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $specs ) ) : ?>
		<div class="hp-overview__specs">
			<?php foreach ( $specs as $spec ) : ?>
				<div class="hp-overview__spec">
					<?php if ( ! empty( $spec['icon'] ) ) : ?>
						<div class="hp-overview__spec-icon" aria-hidden="true">
							<i class="<?php echo esc_attr( $spec['icon'] ); ?>"></i>
						</div>
					<?php endif; ?>
					<div class="hp-overview__spec-text">
						<span class="hp-overview__spec-title"><?php echo esc_html( $spec['title'] ); ?></span>
						<span class="hp-overview__spec-value"><?php echo esc_html( $spec['value'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
