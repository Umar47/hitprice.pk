<?php
/**
 * Compact Key Highlights card inside the product summary column.
 * Shows the ACF WYSIWYG content only (no infographic image).
 * The full Key Highlights section (with image) renders as a separate
 * full-width section below the hero.
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

$content = function_exists( 'hp_get_key_highlights_content' )
	? hp_get_key_highlights_content( $product->get_id() )
	: '';

if ( ! $content ) {
	return;
}
?>
<div class="hp-kh-summary">
	<h2 class="hp-kh-summary__heading">
		<?php esc_html_e( 'Key Highlights', 'hitprice' ); ?>
	</h2>
	<div class="hp-kh-summary__content">
		<?php echo $content; // Already sanitized via wp_kses_post() in hp_get_key_highlights_content() ?>
	</div>
</div>
