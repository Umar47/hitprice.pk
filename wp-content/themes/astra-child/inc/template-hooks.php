<?php
/**
 * Hook integration with Astra.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace Astra header/footer markup with child theme parts.
 */
function hitprice_register_template_hooks() {
	if ( function_exists( 'astra_header_markup' ) ) {
		remove_action( 'astra_header', 'astra_header_markup' );
		add_action( 'astra_header', 'hitprice_render_site_header' );
	}

	if ( function_exists( 'astra_footer_markup' ) ) {
		remove_action( 'astra_footer', 'astra_footer_markup' );
	}

	// Remove Astra builder footer when the header/footer builder is active.
	if ( class_exists( 'Astra_Builder_Footer' ) ) {
		remove_action( 'astra_footer', array( Astra_Builder_Footer::get_instance(), 'footer_markup' ), 10 );
	}

	add_action( 'astra_footer', 'hitprice_render_site_footer' );
}
add_action( 'wp', 'hitprice_register_template_hooks', 20 );

/**
 * Render child theme header.
 */
function hitprice_render_site_header() {
	hitprice_get_template_part( 'template-parts/header/header' );
}

/**
 * Render child theme footer.
 */
function hitprice_render_site_footer() {
	hitprice_get_template_part( 'template-parts/footer/footer' );
}

/**
 * Register single product page hooks.
 */
function hitprice_register_product_hooks() {
	if ( ! is_product() ) {
		return;
	}

	// ── Layout wrapper ───────────────────────────────────────────────────
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_product_layout_open',  1 );
	add_action( 'woocommerce_after_single_product_summary',  'hp_render_product_layout_close', 1 );

	// ── Breadcrumb — before layout wrapper opens (priority 0 < layout at 1) ──
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_product_breadcrumb', 0 );

	// ── Gallery column ───────────────────────────────────────────────────
	// Mobile-only stock + viewers (rendered above gallery, hidden on desktop via CSS).
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_stock_viewers_mobile', 14 );
	// Open gallery outer wrapper + badge overlays before WC gallery (priority 20).
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_gallery_outer_open',   15 );
	// Close gallery outer wrapper + render trust strip after WC gallery.
	add_action( 'woocommerce_before_single_product_summary', 'hp_render_gallery_outer_close',  25 );

	// ── Summary column ───────────────────────────────────────────────────
	// Remove WC defaults that are replaced by custom elements.
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating',  10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta',    40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	// Suppress excerpt via hook removal (standard WC path).
	$hp_suppress_excerpt = function() {
		foreach ( range( 1, 100 ) as $p ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', $p );
		}
	};
	add_action( 'woocommerce_single_product_summary', $hp_suppress_excerpt,  2 );
	add_action( 'woocommerce_single_product_summary', $hp_suppress_excerpt, 98 );

	// Suppress excerpt rendered directly by Astra's layout builder (not hookable).
	add_action( 'astra_woo_single_short_description_before', 'hp_suppress_astra_short_desc_start' );
	add_action( 'astra_woo_single_short_description_after',  'hp_suppress_astra_short_desc_end' );

	// Suppress .product_meta rendered directly by Astra's layout builder (not hookable).
	add_action( 'astra_woo_single_category_before', 'hp_suppress_astra_meta_start' );
	add_action( 'astra_woo_single_category_after',  'hp_suppress_astra_meta_end' );

	// Desktop-only stock + viewers (before title at 5, hidden on mobile via CSS).
	add_action( 'woocommerce_single_product_summary', 'hp_render_stock_viewers_desktop',  3 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_summary_header_open',    4 );
	// WC title stays at priority 5.
	// Brand | rating | SKU row — between title (5) and price (10).
	add_action( 'woocommerce_single_product_summary', 'hp_render_summary_meta',           8 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_summary_header_close',   9 );
	// WC price stays at priority 10.
	add_action( 'astra_woo_single_price_after', 'hp_render_tax_note' );
	// Sale banner + price icons — Astra fires these before rendering ATC/variations.
	add_action( 'astra_woo_single_add_to_cart_before', 'hp_render_sale_banner',           5 );
	add_action( 'astra_woo_single_add_to_cart_before', 'hp_render_summary_trust_badges', 10 );
	// WC variations + add-to-cart stays at priority 30.
	// Wrap qty + ATC + Buy Now in a single flex row.
	add_action( 'woocommerce_before_add_to_cart_quantity', 'hp_open_product_actions_row',   1 );
	add_action( 'woocommerce_after_add_to_cart_button',    'hp_render_buy_now_button',     10 );
	add_action( 'woocommerce_after_add_to_cart_button',    'hp_close_product_actions_row', 99 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_payment_methods',        55 );
	add_action( 'woocommerce_single_product_summary', 'hp_render_delivery_estimate',      60 );

	// ── After summary sections ───────────────────────────────────────────
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs',  10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products',   20 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display',            15 );

	// Section order: Key Highlights (full-width) → Related Slider → Tabs → Why Choose → Trust Strip

	// Why Buy section — after product layout, before Key Highlights.
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_why_buy',            3 );

	// Phase 4 — full-width Key Highlights (wysiwyg + infographic image).
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_key_highlights',     5 );

	// Phase 5 — related products slider.
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_product_compare',    10 );

	// Phase 6 — custom tab system (suppresses WC native tabs via filter below).
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_product_tabs',       15 );

	// Phase 8.
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_why_choose',         20 );
	add_action( 'woocommerce_after_single_product_summary', 'hp_render_trust_strip_bottom', 25 );

	// ── Footer ───────────────────────────────────────────────────────────
	add_action( 'wp_footer', 'hp_render_product_sticky_bar' );

	// ── Phase 7 — Review image upload ────────────────────────────────────
	// Inject file input + nonce after the review textarea, before Submit.
	add_action( 'comment_form_after_comment_field', 'hp_inject_review_image_upload' );
	// Render saved images below each review's text.
	add_action( 'woocommerce_review_after_comment_text', 'hp_render_review_images_for_comment' );
}
add_action( 'wp', 'hitprice_register_product_hooks', 20 );

/**
 * Render the live search overlay shell in the footer (once per page).
 */
function hitprice_render_search_overlay() {
	hitprice_get_template_part( 'template-parts/search/overlay' );
}
add_action( 'wp_footer', 'hitprice_render_search_overlay', 5 );

/**
 * Capture product tabs data for accordion rendering, then suppress default tab output.
 *
 * @param array $tabs Product tabs.
 * @return array Empty array to suppress default tab UI.
 */
function hp_capture_product_tabs_for_accordions( $tabs ) {
	global $hp_product_tabs;
	$hp_product_tabs = $tabs;

	return array();
}

/*
|--------------------------------------------------------------------------
| Single Product — Phase 3 Render Functions
|--------------------------------------------------------------------------
*/

/** Start output buffer to swallow Astra's direct woocommerce_template_single_excerpt() call. */
function hp_suppress_astra_short_desc_start() {
	ob_start();
}

/** Discard the buffered short description output. */
function hp_suppress_astra_short_desc_end() {
	ob_end_clean();
}

/** Start output buffer to swallow Astra's direct woocommerce_template_single_meta() call. */
function hp_suppress_astra_meta_start() {
	ob_start();
}

/** Discard the buffered product meta output. */
function hp_suppress_astra_meta_end() {
	ob_end_clean();
}

/** Breadcrumb rendered as first child of .hp-product-layout. */
function hp_render_product_breadcrumb() {
	if ( function_exists( 'woocommerce_breadcrumb' ) ) {
		woocommerce_breadcrumb();
	}
}

/** Mobile-only stock + viewers — shown above gallery on mobile, hidden on desktop. */
function hp_render_stock_viewers_mobile() {
	hitprice_get_template_part( 'template-parts/product/stock-viewers', array( 'modifier' => 'mobile' ) );
}

/** Desktop-only stock + viewers — shown in summary column, hidden on mobile. */
function hp_render_stock_viewers_desktop() {
	hitprice_get_template_part( 'template-parts/product/stock-viewers', array( 'modifier' => 'desktop' ) );
}

/** Wrap the title/meta block so actions can align with the mockup. */
function hp_render_summary_header_open() {
	?>
	<div class="hp-summary-header">
		<div class="hp-summary-header__main">
	<?php
}

/** Close the title/meta wrapper. */
function hp_render_summary_header_close() {
	echo '</div></div>';
}

/** Open the gallery outer wrapper and render badge overlays inside it. */
function hp_render_gallery_outer_open() {
	echo '<div class="hp-gallery-outer">';
	hitprice_get_template_part( 'template-parts/product/gallery-badges' );
}

/** Render gallery trust strip then close the gallery outer wrapper (strip sits inside the white card). */
function hp_render_gallery_outer_close() {
	hitprice_get_template_part( 'template-parts/product/gallery-trust-strip' );
	echo '</div><!-- /.hp-gallery-outer -->';
}

/** Brand | rating | SKU meta row. */
function hp_render_summary_meta() {
	hitprice_get_template_part( 'template-parts/product/summary-meta' );
}

/** "Inclusive of all taxes" note below the price. */
function hp_render_tax_note() {
	echo '<p class="hp-tax-note">' . esc_html__( 'Inclusive of all taxes', 'hitprice' ) . '</p>';
}

/** Conditional sale banner using WC native sale dates. */
function hp_render_sale_banner() {
	hitprice_get_template_part( 'template-parts/product/sale-banner' );
}

/** Trust badges row: PTA (conditional per product), Genuine, Best Price, Weekly Deals. */
function hp_render_summary_trust_badges() {
	hitprice_get_template_part( 'template-parts/product/trust-badges' );
}

/** Opens the single purchase-action flex row (qty + ATC + Buy Now). */
function hp_open_product_actions_row() {
	echo '<div class="hp-product-actions-row">';
}

/** Closes the purchase-action flex row. */
function hp_close_product_actions_row() {
	echo '</div>';
}

/**
 * Buy Now button — rendered inside the .cart form after the Add to Cart button.
 * JS handles add-to-cart + checkout redirect (simple and variable products).
 */
function hp_render_buy_now_button() {
	global $product;
	if ( ! $product ) {
		return;
	}
	?>
	<button type="button"
	        class="hp-buy-now-btn"
	        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
	        data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
	        data-checkout-url="<?php echo esc_url( wc_get_checkout_url() ); ?>">
		<?php esc_html_e( 'Buy Now', 'hitprice' ); ?>
	</button>
	<?php
}

/** Payment methods strip: Cash on Delivery, Open Parcel, 7-Day Warranty. */
function hp_render_payment_methods() {
	hitprice_get_template_part( 'template-parts/product/payment-methods' );
}

/** Delivery estimate box — visible only when product is in stock. */
function hp_render_delivery_estimate() {
	hitprice_get_template_part( 'template-parts/product/delivery-estimate' );
}

/*
|--------------------------------------------------------------------------
| Single Product — Phase 4 Render Functions
|--------------------------------------------------------------------------
*/

/** Key Highlights full-width section (wysiwyg + infographic image). */
function hp_render_key_highlights() {
	hitprice_get_template_part( 'template-parts/product/key-highlights' );
}

/*
|--------------------------------------------------------------------------
| Single Product — Phase 6 Render Functions
|--------------------------------------------------------------------------
*/

/** Custom tab system — suppresses WC native tab output. */
function hp_render_product_tabs() {
	hitprice_get_template_part( 'template-parts/product/tabs' );
}

/**
 * Return an empty array so WC skips its native tab wrapper and panel output.
 * Our custom tab system renders the same content inside hp_render_product_tabs().
 *
 * @param array $tabs WC product tabs.
 * @return array Empty array.
 */
function hp_suppress_wc_product_tabs( $tabs ) {
	return array();
}
add_filter( 'woocommerce_product_tabs', 'hp_suppress_wc_product_tabs', 99 );

/*
|--------------------------------------------------------------------------
| Single Product — Phase 8 Render Functions
|--------------------------------------------------------------------------
*/

/** "Why buy from Hitprice.pk?" row — after product layout, before Key Highlights. */
function hp_render_why_buy() {
	hitprice_get_template_part( 'template-parts/product/why-buy' );
}

/** "Why Choose [Product]?" — renders long description. Returns early when empty. */
function hp_render_why_choose() {
	hitprice_get_template_part( 'template-parts/product/why-choose' );
}

/** Bottom 4-badge trust strip: safe payments, easy returns, support, satisfaction. */
function hp_render_trust_strip_bottom() {
	hitprice_get_template_part( 'template-parts/product/trust-strip-bottom' );
}

/*
|--------------------------------------------------------------------------
| Single Product — Phase 7 Render Functions
|--------------------------------------------------------------------------
*/

/**
 * Inject the review image upload zone + nonce into the WC review comment form.
 *
 * Fires on comment_form_after_comment_field — after the review textarea,
 * before the Submit button. Shown only to logged-in users on product pages.
 */
function hp_inject_review_image_upload() {
	if ( ! is_product() || ! is_user_logged_in() ) {
		return;
	}
	?>
	<div class="hp-review-upload">
		<label class="hp-review-upload__label" for="hp_review_images">
			<?php esc_html_e( 'Add Photos (optional, up to 3)', 'hitprice' ); ?>
		</label>
		<div class="hp-review-upload__zone" id="hp-review-upload-zone">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
				<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
				<polyline points="17 8 12 3 7 8"/>
				<line x1="12" y1="3" x2="12" y2="15"/>
			</svg>
			<span class="hp-review-upload__prompt"><?php esc_html_e( 'Click or drag images here', 'hitprice' ); ?></span>
			<span class="hp-review-upload__hint"><?php esc_html_e( 'JPG, PNG, WEBP · max 5 MB each', 'hitprice' ); ?></span>
			<input type="file"
			       id="hp_review_images"
			       name="hp_review_images[]"
			       accept="image/jpeg,image/png,image/webp"
			       multiple
			       class="hp-review-upload__input"
			       aria-label="<?php esc_attr_e( 'Upload review images', 'hitprice' ); ?>">
		</div>
		<div id="hp-review-upload-previews" class="hp-review-upload__previews" aria-live="polite"></div>
		<?php wp_nonce_field( 'hp_review_image_nonce', 'hp_review_nonce' ); ?>
	</div>
	<?php
}

/**
 * Display uploaded review images below each review's comment text.
 *
 * Fires on woocommerce_review_after_comment_text with the comment object.
 *
 * @param WP_Comment $comment The current review comment.
 */
function hp_render_review_images_for_comment( $comment ) {
	if ( ! function_exists( 'hp_get_review_images' ) ) {
		return;
	}

	$images = hp_get_review_images( $comment->comment_ID );
	if ( empty( $images ) ) {
		return;
	}
	?>
	<div class="hp-review-images">
		<?php foreach ( $images as $img ) : ?>
			<a href="<?php echo esc_url( $img['full'] ); ?>"
			   class="hp-review-images__link"
			   target="_blank"
			   rel="noopener noreferrer">
				<img src="<?php echo esc_url( $img['url'] ); ?>"
				     alt="<?php echo esc_attr( $img['alt'] ? $img['alt'] : __( 'Review photo', 'hitprice' ) ); ?>"
				     loading="lazy"
				     decoding="async"
				     class="hp-review-images__thumb">
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}
