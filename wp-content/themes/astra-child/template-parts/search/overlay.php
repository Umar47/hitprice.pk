<?php
/**
 * Live search overlay shell.
 *
 * Rendered once per page via wp_footer.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$home_url    = home_url( '/' );
$placeholder = esc_attr__( 'Search mobiles, electronics, appliances and more', 'hitprice' );
$trending    = function_exists( 'hp_get_trending_terms_for_overlay' ) ? hp_get_trending_terms_for_overlay( 8 ) : array();

// Pre-render latest 6 published, visible products for the idle state.
// Using wc_get_products() avoids a raw WP_Query and respects WC visibility rules.
$featured_products = array();
if ( function_exists( 'wc_get_products' ) ) {
	$featured_products = wc_get_products(
		array(
			'status'     => 'publish',
			'limit'      => 6,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'visibility' => 'catalog',
		)
	);
}
?>
<div
	class="hp-search-overlay"
	id="hp-search-overlay"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Search', 'hitprice' ); ?>"
	aria-hidden="true"
	data-hp-search-overlay
	hidden
>
	<div class="hp-search-overlay__backdrop" data-hp-search-close></div>

	<div class="hp-search-overlay__panel" role="document">
		<form
			class="hp-search-overlay__form"
			role="search"
			method="get"
			action="<?php echo esc_url( $home_url ); ?>"
			data-hp-search-overlay-form
		>
			<button
				type="button"
				class="hp-search-overlay__back"
				aria-label="<?php esc_attr_e( 'Close search', 'hitprice' ); ?>"
				data-hp-search-close
			>
				<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
					<path d="M15 4l-8 8 8 8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
				</svg>
			</button>

			<label class="screen-reader-text" for="hp-search-overlay-input"><?php esc_html_e( 'Search products', 'hitprice' ); ?></label>
			<input
				id="hp-search-overlay-input"
				type="search"
				class="hp-search-overlay__input"
				name="s"
				placeholder="<?php echo $placeholder; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
				autocomplete="off"
				autocorrect="off"
				autocapitalize="off"
				spellcheck="false"
				inputmode="search"
				enterkeyhint="search"
				aria-controls="hp-search-overlay-listbox"
				aria-autocomplete="list"
				aria-expanded="false"
				role="combobox"
				data-hp-search-input
			/>
			<input type="hidden" name="post_type" value="product" />

			<button
				type="button"
				class="hp-search-overlay__clear"
				aria-label="<?php esc_attr_e( 'Clear search', 'hitprice' ); ?>"
				data-hp-search-clear
				hidden
			>
				<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
					<path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
				</svg>
			</button>

			<button type="button" class="hp-search-overlay__cancel" data-hp-search-close>
				<?php esc_html_e( 'Cancel', 'hitprice' ); ?>
			</button>
		</form>

		<div class="hp-search-overlay__body" data-hp-search-body>

			<!-- Recent searches — populated by JS from localStorage -->
			<section class="hp-search-overlay__recent" data-hp-search-recent hidden>
				<div class="hp-search-overlay__heading-row">
					<h2 class="hp-search-overlay__heading"><?php esc_html_e( 'Recent searches', 'hitprice' ); ?></h2>
					<button type="button" class="hp-search-overlay__clear-recent" data-hp-search-clear-recent>
						<?php esc_html_e( 'Clear', 'hitprice' ); ?>
					</button>
				</div>
				<ul class="hp-search-overlay__chips" data-hp-search-recent-list></ul>
			</section>

			<!-- Trending chips — populated by JS after fetch -->
			<section
				class="hp-search-overlay__trending"
				data-hp-search-trending
				<?php echo empty( $trending ) ? 'hidden' : ''; ?>
			>
				<h2 class="hp-search-overlay__heading"><?php esc_html_e( 'Trending searches', 'hitprice' ); ?></h2>
				<ul class="hp-search-overlay__chips" data-hp-search-trending-list>
					<?php foreach ( $trending as $term ) : ?>
						<li>
							<button type="button" class="hp-search-overlay__chip" data-hp-search-term="<?php echo esc_attr( $term ); ?>">
								<?php echo esc_html( $term ); ?>
							</button>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>

			<?php if ( ! empty( $featured_products ) ) : ?>
			<!-- Featured products — shown in idle state, hidden when user starts typing -->
			<section class="hp-search-overlay__featured" data-hp-search-featured>
				<h2 class="hp-search-overlay__heading"><?php esc_html_e( 'Popular products', 'hitprice' ); ?></h2>
				<ul class="hp-search-overlay__featured-grid">
					<?php foreach ( $featured_products as $fp ) : ?>
						<?php
						$fp_id       = $fp->get_id();
						$fp_name     = wp_strip_all_tags( $fp->get_name() );
						$fp_url      = get_permalink( $fp_id );
						$fp_price    = $fp->get_price_html();
						$fp_img_id   = (int) $fp->get_image_id();
						$fp_img_url  = $fp_img_id
							? wp_get_attachment_image_url( $fp_img_id, 'woocommerce_gallery_thumbnail' )
							: ( function_exists( 'wc_placeholder_img_src' ) ? wc_placeholder_img_src( 'woocommerce_gallery_thumbnail' ) : '' );
						?>
						<li>
							<a
								href="<?php echo esc_url( $fp_url ); ?>"
								class="hp-search-overlay__featured-card"
								aria-label="<?php echo esc_attr( $fp_name ); ?>"
							>
								<span class="hp-search-overlay__featured-img">
									<?php if ( $fp_img_url ) : ?>
										<img
											src="<?php echo esc_url( $fp_img_url ); ?>"
											alt=""
											width="72"
											height="72"
											loading="lazy"
											decoding="async"
										>
									<?php endif; ?>
								</span>
								<span class="hp-search-overlay__featured-name"><?php echo esc_html( $fp_name ); ?></span>
								<span class="hp-search-overlay__featured-price">
									<?php echo wp_kses(
										$fp_price,
										array(
											'del'  => array( 'aria-hidden' => array() ),
											'ins'  => array(),
											'span' => array( 'class' => array() ),
											'bdi'  => array(),
										)
									); ?>
								</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
			<?php endif; ?>

			<!-- Live search results — hidden until user types -->
			<section class="hp-search-overlay__results" data-hp-search-results hidden>
				<div class="hp-search-overlay__terms" data-hp-search-terms hidden>
					<h2 class="hp-search-overlay__heading"><?php esc_html_e( 'Suggestions', 'hitprice' ); ?></h2>
					<ul class="hp-search-overlay__chips" data-hp-search-terms-list></ul>
				</div>

				<div class="hp-search-overlay__products" data-hp-search-products hidden>
					<h2 class="hp-search-overlay__heading"><?php esc_html_e( 'Matching products', 'hitprice' ); ?></h2>
					<ul
						id="hp-search-overlay-listbox"
						class="hp-search-overlay__product-list"
						role="listbox"
						data-hp-search-products-list
					></ul>
					<a class="hp-search-overlay__view-all" href="#" data-hp-search-view-all>
						<?php esc_html_e( 'View all results', 'hitprice' ); ?>
					</a>
				</div>
			</section>

			<div class="hp-search-overlay__state hp-search-overlay__loading" data-hp-search-loading hidden>
				<span class="hp-search-overlay__spinner" aria-hidden="true"></span>
				<span><?php esc_html_e( 'Searching…', 'hitprice' ); ?></span>
			</div>

			<div class="hp-search-overlay__state hp-search-overlay__empty" data-hp-search-empty hidden>
				<p><?php esc_html_e( 'No matching products found.', 'hitprice' ); ?></p>
				<p class="hp-search-overlay__empty-hint"><?php esc_html_e( 'Try a different keyword or browse categories.', 'hitprice' ); ?></p>
			</div>

			<div class="hp-search-overlay__state hp-search-overlay__error" data-hp-search-error hidden>
				<p><?php esc_html_e( 'Search is temporarily unavailable. Please try again.', 'hitprice' ); ?></p>
			</div>
		</div>
	</div>
</div>
