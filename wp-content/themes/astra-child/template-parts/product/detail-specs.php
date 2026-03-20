<?php
/**
 * Product detail specs — iterates ACF flexible content layouts.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'hp_get_product_detail_specs' ) ) {
	return;
}

global $product;

$specs = hp_get_product_detail_specs( $product->get_id() );

if ( empty( $specs ) ) {
	return;
}
?>
<section class="hp-detail-specs" aria-labelledby="hp-detail-specs-heading">
	<div class="hp-detail-specs__inner">
		<h2 class="hp-detail-specs__heading" id="hp-detail-specs-heading">
			<?php esc_html_e( 'Specifications', 'hitprice' ); ?>
		</h2>

		<?php foreach ( $specs as $layout ) : ?>
			<?php if ( 'text_block' === $layout['acf_fc_layout'] ) : ?>
				<div class="hp-detail-specs__block hp-detail-specs__text">
					<?php if ( ! empty( $layout['heading'] ) ) : ?>
						<h3 class="hp-detail-specs__subheading"><?php echo esc_html( $layout['heading'] ); ?></h3>
					<?php endif; ?>
					<div class="hp-detail-specs__content">
						<?php echo wp_kses_post( $layout['content'] ); ?>
					</div>
				</div>

			<?php elseif ( 'key_value_table' === $layout['acf_fc_layout'] ) : ?>
				<div class="hp-detail-specs__block hp-detail-specs__table-wrap">
					<?php if ( ! empty( $layout['heading'] ) ) : ?>
						<h3 class="hp-detail-specs__subheading"><?php echo esc_html( $layout['heading'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $layout['rows'] ) ) : ?>
						<table class="hp-detail-specs__table">
							<tbody>
								<?php foreach ( $layout['rows'] as $row ) : ?>
									<tr>
										<th><?php echo esc_html( $row['label'] ); ?></th>
										<td><?php echo esc_html( $row['value'] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php endif; ?>
				</div>

			<?php elseif ( 'media_block' === $layout['acf_fc_layout'] ) : ?>
				<div class="hp-detail-specs__block hp-detail-specs__media">
					<?php if ( ! empty( $layout['heading'] ) ) : ?>
						<h3 class="hp-detail-specs__subheading"><?php echo esc_html( $layout['heading'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $layout['image'] ) ) : ?>
						<figure class="hp-detail-specs__figure">
							<img
								src="<?php echo esc_url( $layout['image']['sizes']['large'] ?? $layout['image']['url'] ); ?>"
								alt="<?php echo esc_attr( $layout['image']['alt'] ?? '' ); ?>"
								loading="lazy"
							>
							<?php if ( ! empty( $layout['caption'] ) ) : ?>
								<figcaption><?php echo esc_html( $layout['caption'] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
