<?php
/**
 * Hooks — asset loading, floating bar, action bridges.
 *
 * @package HitPriceCompare
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue compare assets only where needed.
 */
function hpc_enqueue_assets() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}

	$load = is_woocommerce() || is_cart() || hpc_is_compare_page();

	if ( ! $load ) {
		return;
	}

	wp_enqueue_style(
		'hpc-compare',
		HPC_URL . 'assets/css/compare.css',
		array(),
		HPC_VERSION
	);

	wp_enqueue_script(
		'hpc-compare',
		HPC_URL . 'assets/js/compare.js',
		array(),
		HPC_VERSION,
		true
	);

	wp_localize_script(
		'hpc-compare',
		'hpcConfig',
		array(
			'maxItems'   => HPC_MAX_ITEMS,
			'compareUrl' => hpc_get_compare_page_url(),
			'i18n'       => array(
				'compare'  => __( 'Compare', 'hitprice-compare' ),
				'added'    => __( 'Added', 'hitprice-compare' ),
				'full'     => __( 'Max 4 items', 'hitprice-compare' ),
				'item'     => __( 'item selected', 'hitprice-compare' ),
				'items'    => __( 'items selected', 'hitprice-compare' ),
				'barCta'   => __( 'Compare Now', 'hitprice-compare' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'hpc_enqueue_assets' );

/**
 * Output floating compare bar markup in footer.
 */
function hpc_render_floating_bar() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return;
	}

	if ( ! ( is_woocommerce() || is_cart() || hpc_is_compare_page() ) ) {
		return;
	}
	?>
	<div class="hpc-bar hpc-bar--hidden" id="hpc-bar" aria-hidden="true">
		<div class="hpc-bar__inner">
			<span class="hpc-bar__count" id="hpc-bar-count"></span>
			<a href="#" class="hpc-bar__cta" id="hpc-bar-cta">
				<?php esc_html_e( 'Compare Now', 'hitprice-compare' ); ?>
			</a>
			<button type="button" class="hpc-bar__clear" id="hpc-bar-clear" aria-label="<?php esc_attr_e( 'Clear all', 'hitprice-compare' ); ?>">&times;</button>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'hpc_render_floating_bar' );

/**
 * Bridge action so themes can use do_action('hp_compare_button', $id).
 *
 * @param int $product_id Product ID.
 */
function hpc_action_bridge( $product_id ) {
	hpc_compare_button( $product_id );
}
add_action( 'hp_compare_button', 'hpc_action_bridge' );

/**
 * Check if current page is the compare page.
 *
 * @return bool
 */
function hpc_is_compare_page() {
	global $post;
	if ( $post && has_shortcode( $post->post_content, 'hpc_compare_page' ) ) {
		return true;
	}
	return false;
}

/**
 * Get the compare page URL.
 *
 * Finds the page containing [hpc_compare_page] shortcode.
 * Falls back to home URL with /compare path.
 *
 * @return string
 */
function hpc_get_compare_page_url() {
	static $url = null;

	if ( null !== $url ) {
		return $url;
	}

	// Find the page with the shortcode.
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			's'              => '[hpc_compare_page]',
			'posts_per_page' => 1,
			'no_found_rows'  => true,
		)
	);

	if ( ! empty( $pages ) ) {
		$url = get_permalink( $pages[0]->ID );
		return $url;
	}

	$url = home_url( '/compare/' );
	return $url;
}
