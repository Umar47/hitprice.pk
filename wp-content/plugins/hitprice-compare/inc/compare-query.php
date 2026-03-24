<?php
/**
 * Compare page query and rendering.
 *
 * @package HitPriceCompare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch products by IDs in a single optimised query.
 *
 * @param int[] $ids Product IDs.
 * @return WC_Product[] Keyed by product ID, preserving input order.
 */
function hpc_get_products_by_ids( $ids ) {
	if ( empty( $ids ) ) {
		return array();
	}

	$ids = array_map( 'absint', $ids );
	$ids = array_filter( $ids );
	$ids = array_unique( $ids );
	$ids = array_slice( $ids, 0, HPC_MAX_ITEMS );

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => HPC_MAX_ITEMS,
		'no_found_rows'  => true,
	);

	$query    = new WP_Query( $args );
	$products = array();

	foreach ( $query->posts as $post ) {
		$product = wc_get_product( $post->ID );
		if ( $product ) {
			$products[ $post->ID ] = $product;
		}
	}

	wp_reset_postdata();

	return $products;
}

/**
 * Collect all unique spec keys across products for table header alignment.
 *
 * @param int[] $product_ids Product IDs.
 * @return string[] Ordered list of spec labels.
 */
function hpc_collect_spec_keys( $product_ids ) {
	$keys = array();

	foreach ( $product_ids as $id ) {
		$specs = hpc_get_product_specs( $id );
		foreach ( array_keys( $specs ) as $key ) {
			if ( ! in_array( $key, $keys, true ) ) {
				$keys[] = $key;
			}
		}
	}

	return $keys;
}

/**
 * Register [hpc_compare_page] shortcode.
 */
function hpc_register_shortcode() {
	add_shortcode( 'hpc_compare_page', 'hpc_render_compare_page' );
}
add_action( 'init', 'hpc_register_shortcode' );

/**
 * Render the comparison page content.
 *
 * @return string HTML output.
 */
function hpc_render_compare_page() {
	// Read IDs from URL.
	$raw_ids = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( $_GET['ids'] ) ) : '';

	if ( empty( $raw_ids ) ) {
		return hpc_render_empty_state();
	}

	$ids = array_map( 'absint', explode( ',', $raw_ids ) );
	$ids = array_filter( $ids );

	if ( count( $ids ) < 2 ) {
		return hpc_render_minimum_state();
	}

	$products = hpc_get_products_by_ids( $ids );

	if ( count( $products ) < 2 ) {
		return hpc_render_minimum_state();
	}

	$product_ids = array_keys( $products );
	$spec_keys   = hpc_collect_spec_keys( $product_ids );
	$all_specs   = array();

	foreach ( $product_ids as $pid ) {
		$all_specs[ $pid ] = hpc_get_product_specs( $pid );
	}

	ob_start();
	?>
	<div class="hpc-compare-page" data-max="<?php echo esc_attr( HPC_MAX_ITEMS ); ?>">

		<!-- Product cards row -->
		<div class="hpc-compare-cards" style="--hpc-cols:<?php echo count( $products ); ?>">
			<!-- Empty first cell for spec labels column -->
			<div class="hpc-compare-cards__label-spacer"></div>

			<?php foreach ( $products as $pid => $product ) : ?>
				<div class="hpc-compare-card" data-product-id="<?php echo esc_attr( $pid ); ?>">
					<button type="button" class="hpc-compare-card__remove" data-product-id="<?php echo esc_attr( $pid ); ?>" aria-label="<?php esc_attr_e( 'Remove from comparison', 'hitprice-compare' ); ?>">&times;</button>
					<div class="hpc-compare-card__img">
						<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<h3 class="hpc-compare-card__title">
						<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
					</h3>
					<div class="hpc-compare-card__price">
						<?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
					<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" class="hpc-compare-card__cta">
						<?php echo esc_html( $product->add_to_cart_text() ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Specs comparison table -->
		<?php if ( ! empty( $spec_keys ) ) : ?>
			<div class="hpc-compare-table-wrap">
				<table class="hpc-compare-table">
					<thead>
						<tr>
							<th class="hpc-compare-table__label-col"><?php esc_html_e( 'Specification', 'hitprice-compare' ); ?></th>
							<?php foreach ( $products as $pid => $product ) : ?>
								<th><?php echo esc_html( $product->get_name() ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<!-- Price row -->
						<tr>
							<td class="hpc-compare-table__label"><?php esc_html_e( 'Price', 'hitprice-compare' ); ?></td>
							<?php foreach ( $products as $product ) : ?>
								<td><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
							<?php endforeach; ?>
						</tr>

						<!-- Rating row -->
						<tr>
							<td class="hpc-compare-table__label"><?php esc_html_e( 'Rating', 'hitprice-compare' ); ?></td>
							<?php foreach ( $products as $product ) : ?>
								<td>
									<?php
									$rating = $product->get_average_rating();
									$count  = $product->get_review_count();
									if ( $rating > 0 ) {
										echo wc_get_rating_html( $rating, $count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									} else {
										echo '—';
									}
									?>
								</td>
							<?php endforeach; ?>
						</tr>

						<!-- Availability row -->
						<tr>
							<td class="hpc-compare-table__label"><?php esc_html_e( 'Availability', 'hitprice-compare' ); ?></td>
							<?php foreach ( $products as $product ) : ?>
								<td>
									<?php if ( $product->is_in_stock() ) : ?>
										<span class="hpc-stock hpc-stock--in"><?php esc_html_e( 'In stock', 'hitprice-compare' ); ?></span>
									<?php else : ?>
										<span class="hpc-stock hpc-stock--out"><?php esc_html_e( 'Out of stock', 'hitprice-compare' ); ?></span>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>

						<!-- Attribute / spec rows -->
						<?php foreach ( $spec_keys as $key ) : ?>
							<tr>
								<td class="hpc-compare-table__label"><?php echo esc_html( $key ); ?></td>
								<?php foreach ( $product_ids as $pid ) : ?>
									<td><?php echo esc_html( isset( $all_specs[ $pid ][ $key ] ) ? $all_specs[ $pid ][ $key ] : '—' ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

	</div>
	<?php
	return ob_get_clean();
}

/**
 * Empty state — no products selected.
 *
 * @return string
 */
function hpc_render_empty_state() {
	ob_start();
	?>
	<div class="hpc-compare-empty">
		<h2><?php esc_html_e( 'No products to compare', 'hitprice-compare' ); ?></h2>
		<p><?php esc_html_e( 'Browse our products and click the Compare button to add items here.', 'hitprice-compare' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hpc-compare-empty__cta">
			<?php esc_html_e( 'Go to shop', 'hitprice-compare' ); ?>
		</a>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Minimum state — fewer than 2 products.
 *
 * @return string
 */
function hpc_render_minimum_state() {
	ob_start();
	?>
	<div class="hpc-compare-empty">
		<h2><?php esc_html_e( 'Need at least 2 products', 'hitprice-compare' ); ?></h2>
		<p><?php esc_html_e( 'Add at least two products to compare their specs side by side.', 'hitprice-compare' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hpc-compare-empty__cta">
			<?php esc_html_e( 'Browse products', 'hitprice-compare' ); ?>
		</a>
	</div>
	<?php
	return ob_get_clean();
}
