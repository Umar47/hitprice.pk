<?php
/**
 * Specifications tab panel.
 *
 * Renders the hp_detail_specs flexible content field:
 * — key_value_table layouts render as striped label/value tables.
 * — text_block layouts render as a heading + rich-text content block.
 * — media_block layouts render a heading + image (with optional caption).
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

if ( ! function_exists( 'hp_get_product_detail_specs' ) ) {
	return;
}

$blocks = hp_get_product_detail_specs( $product->get_id() );

if ( empty( $blocks ) ) {
	echo '<p class="hp-tab-empty">' . esc_html__( 'No specifications available.', 'hitprice' ) . '</p>';
	return;
}
?>
<div class="hp-specs">
	<?php foreach ( $blocks as $block ) :
		$layout = $block['acf_fc_layout'] ?? '';
		switch ( $layout ) :

			case 'key_value_table':
				if ( empty( $block['rows'] ) ) {
					break;
				}
				?>
				<div class="hp-specs__section">
					<?php if ( ! empty( $block['heading'] ) ) : ?>
						<h3 class="hp-specs__subheading"><?php echo esc_html( $block['heading'] ); ?></h3>
					<?php endif; ?>
					<table class="hp-specs__table">
						<tbody>
							<?php foreach ( $block['rows'] as $row ) : ?>
								<tr>
									<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
									<td><?php echo esc_html( $row['value'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php
				break;

			case 'text_block':
				if ( empty( $block['content'] ) ) {
					break;
				}
				?>
				<div class="hp-specs__section">
					<?php if ( ! empty( $block['heading'] ) ) : ?>
						<h3 class="hp-specs__subheading"><?php echo esc_html( $block['heading'] ); ?></h3>
					<?php endif; ?>
					<div class="hp-specs__text">
						<?php echo wp_kses_post( $block['content'] ); ?>
					</div>
				</div>
				<?php
				break;

			case 'media_block':
				if ( empty( $block['image']['url'] ) ) {
					break;
				}
				?>
				<div class="hp-specs__section">
					<?php if ( ! empty( $block['heading'] ) ) : ?>
						<h3 class="hp-specs__subheading"><?php echo esc_html( $block['heading'] ); ?></h3>
					<?php endif; ?>
					<figure class="hp-specs__figure">
						<img src="<?php echo esc_url( $block['image']['url'] ); ?>"
						     alt="<?php echo esc_attr( $block['image']['alt'] ?? '' ); ?>"
						     loading="lazy"
						     decoding="async">
						<?php if ( ! empty( $block['caption'] ) ) : ?>
							<figcaption><?php echo esc_html( $block['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				</div>
				<?php
				break;

		endswitch;
	endforeach; ?>
</div>
