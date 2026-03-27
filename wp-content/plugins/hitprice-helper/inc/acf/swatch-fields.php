<?php
/**
 * Per-variation swatch color field.
 *
 * Adds a color picker inside each WooCommerce variation panel so admins
 * can define exact hex colors for variation swatches on the product page.
 * Data is stored as variation post meta and exposed via the variation JSON.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue wp-color-picker assets on product edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function hp_swatch_admin_scripts( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'hp_swatch_admin_scripts' );

/**
 * Render the swatch color picker inside each variation panel.
 *
 * @param int      $loop           Variation loop index.
 * @param array    $variation_data Variation data array.
 * @param \WP_Post $variation      Variation post object.
 */
function hp_swatch_variation_field( $loop, $variation_data, $variation ) {
	$color = get_post_meta( $variation->ID, '_hp_swatch_color', true );
	?>
	<div class="form-row form-row-first hp-swatch-field">
		<label>
			<?php esc_html_e( 'Swatch Color', 'hitprice' ); ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_attr_e( 'Pick a hex color for this variation\'s swatch. Leave empty to use automatic detection.', 'hitprice' ); ?>"></span>
		</label>
		<input type="text"
		       class="hp-swatch-color-picker"
		       name="hp_swatch_color[<?php echo esc_attr( $loop ); ?>]"
		       value="<?php echo esc_attr( $color ); ?>"
		       data-default-color=""
		       placeholder="<?php esc_attr_e( 'Auto', 'hitprice' ); ?>" />
	</div>
	<?php
}
add_action( 'woocommerce_product_after_variable_attributes', 'hp_swatch_variation_field', 10, 3 );

/**
 * Save the swatch color when a variation is saved.
 *
 * @param int $variation_id Variation post ID.
 * @param int $loop         Variation loop index.
 */
function hp_swatch_save_variation( $variation_id, $loop ) {
	if ( ! isset( $_POST['hp_swatch_color'][ $loop ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce handles nonce.
		return;
	}

	$raw   = wp_unslash( $_POST['hp_swatch_color'][ $loop ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$color = sanitize_hex_color( $raw );

	if ( $color ) {
		update_post_meta( $variation_id, '_hp_swatch_color', $color );
	} else {
		delete_post_meta( $variation_id, '_hp_swatch_color' );
	}
}
add_action( 'woocommerce_save_product_variation', 'hp_swatch_save_variation', 10, 2 );

/**
 * Expose swatch color in the front-end variation JSON.
 *
 * @param array                $data      Variation data for JS.
 * @param \WC_Product_Variable $product   Parent product.
 * @param \WC_Product_Variation $variation Variation object.
 * @return array
 */
function hp_swatch_variation_data( $data, $product, $variation ) {
	$color = get_post_meta( $variation->get_id(), '_hp_swatch_color', true );
	$data['hp_swatch_color'] = $color ? sanitize_hex_color( $color ) : '';
	return $data;
}
add_filter( 'woocommerce_available_variation', 'hp_swatch_variation_data', 10, 3 );

/**
 * Inline script to initialise wp-color-picker on dynamically loaded variations.
 */
function hp_swatch_admin_footer_script() {
	$screen = get_current_screen();
	if ( ! $screen || 'product' !== $screen->post_type ) {
		return;
	}
	?>
	<script>
	jQuery(function($){
		function hpInitSwatchPickers(){
			$('.hp-swatch-color-picker').not('.hp-cp-ready').each(function(){
				var $input = $(this);
				$input.addClass('hp-cp-ready').wpColorPicker({
					change: function(e, ui){
						$(e.target).val(ui.color.toString()).trigger('change');
					},
					clear: function(e){
						$(e.target).val('').trigger('change');
					}
				});
			});
		}
		$(document.body).on('woocommerce_variations_loaded woocommerce_variations_added', hpInitSwatchPickers);
	});
	</script>
	<?php
}
add_action( 'admin_footer', 'hp_swatch_admin_footer_script' );
