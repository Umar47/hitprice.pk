<?php
/**
 * Compare helper functions.
 *
 * @package HitPriceCompare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get product specs for comparison.
 *
 * Uses ACF field 'product_specs' when available, otherwise falls back
 * to WooCommerce product attributes. Future ACF integration requires
 * zero UI changes — just populate the ACF field.
 *
 * @param int $product_id Product ID.
 * @return array Key-value pairs of spec label => value.
 */
function hpc_get_product_specs( $product_id ) {
	// ACF path — ready for when fields are created.
	if ( function_exists( 'get_field' ) ) {
		$acf_specs = get_field( 'product_specs', $product_id );

		if ( ! empty( $acf_specs ) && is_array( $acf_specs ) ) {
			$specs = array();
			foreach ( $acf_specs as $row ) {
				$label = isset( $row['label'] ) ? trim( $row['label'] ) : '';
				$value = isset( $row['value'] ) ? trim( $row['value'] ) : '';
				if ( $label !== '' ) {
					$specs[ $label ] = $value;
				}
			}
			if ( ! empty( $specs ) ) {
				return $specs;
			}
		}
	}

	// Fallback — build specs from WooCommerce attributes.
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array();
	}

	$specs      = array();
	$attributes = $product->get_attributes();

	foreach ( $attributes as $attribute ) {
		if ( $attribute instanceof WC_Product_Attribute ) {
			$label = wc_attribute_label( $attribute->get_name(), $product );
			$value = $product->get_attribute( $attribute->get_name() );
		} else {
			continue;
		}

		if ( $label !== '' && $value !== '' ) {
			$specs[ $label ] = $value;
		}
	}

	// Add weight/dimensions if set.
	$weight = $product->get_weight();
	if ( $weight ) {
		$specs[ __( 'Weight', 'hitprice-compare' ) ] = $weight . ' ' . get_option( 'woocommerce_weight_unit', 'kg' );
	}

	$length = $product->get_length();
	$width  = $product->get_width();
	$height = $product->get_height();
	if ( $length && $width && $height ) {
		$dim_unit = get_option( 'woocommerce_dimension_unit', 'cm' );
		$specs[ __( 'Dimensions', 'hitprice-compare' ) ] = "{$length} × {$width} × {$height} {$dim_unit}";
	}

	return $specs;
}

/**
 * Render the compare button for a product.
 *
 * Safe to call anywhere — product loops, single product, widgets.
 *
 * @param int $product_id Product ID.
 */
function hpc_compare_button( $product_id ) {
	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return;
	}
	?>
	<button type="button"
		class="hpc-compare-btn"
		data-product-id="<?php echo esc_attr( $product_id ); ?>"
		aria-label="<?php esc_attr_e( 'Add to compare', 'hitprice-compare' ); ?>">
		<svg class="hpc-compare-btn__icon" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 4h12M2 8h8M2 12h5"/></svg>
		<span class="hpc-compare-btn__text"><?php esc_html_e( 'Compare', 'hitprice-compare' ); ?></span>
	</button>
	<?php
}
