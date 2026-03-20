<?php
/**
 * Custom WooCommerce archive template.
 *
 * @package HitPrice
 */

defined( 'ABSPATH' ) || exit;

$archive_title    = woocommerce_page_title( false );
$archive_intro    = hitprice_get_archive_intro_text();
$category_links   = hitprice_get_archive_category_links( 6 );
$shop_page_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$current_term     = is_product_taxonomy() ? get_queried_object() : null;
$archive_subtitle = is_shop() ? __( 'Shop archive', 'hitprice' ) : __( 'Category collection', 'hitprice' );
$filter_sections  = hitprice_get_archive_filter_sections();
$active_filters   = hitprice_get_active_archive_filter_count();
$selected_filters = hitprice_get_selected_archive_filters();

get_header( 'shop' );
?>
<main id="primary" class="site-main hitprice-shop-main">
	<div class="hitprice-shop-hero">
		<div class="hitprice-shell">
			<div class="hitprice-shop-hero__inner">
				<div class="hitprice-shop-hero__content">
					<p class="hitprice-shop-hero__eyebrow"><?php echo esc_html( $archive_subtitle ); ?></p>
					<h1 class="hitprice-shop-hero__title"><?php echo esc_html( $archive_title ); ?></h1>
					<p class="hitprice-shop-hero__copy"><?php echo esc_html( $archive_intro ); ?></p>
				</div>
				<div class="hitprice-shop-hero__panel">
					<span class="hitprice-shop-hero__panel-label"><?php esc_html_e( 'Why this archive', 'hitprice' ); ?></span>
					<p><?php esc_html_e( 'The layout keeps product discovery clearer, highlights core categories sooner, and gives the cards a more premium product-first presentation.', 'hitprice' ); ?></p>
					<a class="hitprice-shop-hero__link" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Browse all products', 'hitprice' ); ?></a>
				</div>
			</div>
			<?php if ( ! empty( $category_links ) ) : ?>
				<nav class="hitprice-shop-chips" aria-label="<?php esc_attr_e( 'Top product categories', 'hitprice' ); ?>">
					<a class="hitprice-shop-chip<?php echo is_shop() ? ' is-active' : ''; ?>" href="<?php echo esc_url( $shop_page_url ); ?>">
						<?php esc_html_e( 'All products', 'hitprice' ); ?>
					</a>
					<?php foreach ( $category_links as $category_link ) : ?>
						<?php
						$is_current = $current_term instanceof WP_Term && (int) $current_term->term_id === (int) $category_link->term_id;
						?>
						<a class="hitprice-shop-chip<?php echo $is_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $category_link ) ); ?>">
							<?php echo esc_html( $category_link->name ); ?>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>
	</div>

	<div class="hitprice-shell">
		<?php do_action( 'woocommerce_before_main_content' ); ?>

		<div class="hitprice-shop-layout">
			<aside class="hitprice-shop-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'hitprice' ); ?>" data-hitprice-filter-panel>
				<div class="hitprice-shop-sidebar__header">
					<div class="hitprice-shop-sidebar__topline">
						<span class="hitprice-shop-sidebar__promo"><?php esc_html_e( 'Lowest price with trade-in offer', 'hitprice' ); ?></span>
						<button class="hitprice-shop-sidebar__switch" type="button" aria-pressed="false" aria-label="<?php esc_attr_e( 'Toggle trade-in offer', 'hitprice' ); ?>">
							<span class="hitprice-shop-sidebar__switch-thumb" aria-hidden="true"></span>
						</button>
					</div>
					<div>
						<h2 class="hitprice-shop-sidebar__title"><?php esc_html_e( 'Filters', 'hitprice' ); ?></h2>
					</div>
					<div class="hitprice-shop-sidebar__actions">
						<a class="hitprice-shop-sidebar__clear" href="<?php echo esc_url( hitprice_get_current_shop_archive_url() ); ?>"><?php esc_html_e( 'Clear all', 'hitprice' ); ?></a>
						<button class="hitprice-shop-sidebar__close" type="button" data-hitprice-filter-close aria-label="<?php esc_attr_e( 'Close filters', 'hitprice' ); ?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
				</div>
				<form class="hitprice-filter-rail" method="get" action="<?php echo esc_url( hitprice_get_current_shop_archive_url() ); ?>" data-hitprice-filter-form>
					<?php hitprice_render_archive_hidden_fields(); ?>

					<?php foreach ( $filter_sections as $index => $filter_section ) : ?>
						<section class="hitprice-filter-group<?php echo 'Price' === $filter_section['title'] ? ' is-multi-select' : ''; ?>" data-filter-group>
							<button class="hitprice-filter-group__toggle" type="button" aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>">
								<span><?php echo esc_html( $filter_section['title'] ); ?></span>
								<span class="hitprice-filter-group__icon" aria-hidden="true"></span>
							</button>
							<div class="hitprice-filter-group__content"<?php echo 0 === $index ? '' : ' hidden'; ?>>
								<ul class="hitprice-filter-options">
									<?php foreach ( $filter_section['options'] as $option ) : ?>
										<li>
											<label class="hitprice-filter-option">
												<input
													type="checkbox"
													name="<?php echo esc_attr( $option['name'] ); ?>"
													value="<?php echo esc_attr( $option['value'] ); ?>"
													<?php checked( ! empty( $option['checked'] ) ); ?>
												>
												<span class="hitprice-filter-option__control" aria-hidden="true"></span>
												<span class="hitprice-filter-option__label"><?php echo esc_html( $option['label'] ); ?></span>
												<?php if ( isset( $option['count'] ) ) : ?>
													<span class="hitprice-filter-option__count"><?php echo esc_html( (string) $option['count'] ); ?></span>
												<?php endif; ?>
											</label>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</section>
					<?php endforeach; ?>
				</form>
			</aside>

			<section class="hitprice-shop-archive" aria-label="<?php echo esc_attr( $archive_title ); ?>">
				<div class="hitprice-shop-mobile-actions">
					<button class="hitprice-shop-filter-button" type="button" data-hitprice-filter-open>
						<?php esc_html_e( 'Filter', 'hitprice' ); ?>
						<?php if ( $active_filters > 0 ) : ?>
							<span class="hitprice-shop-filter-button__count"><?php echo esc_html( (string) $active_filters ); ?></span>
						<?php endif; ?>
					</button>
				</div>
				<?php if ( woocommerce_product_loop() ) : ?>
					<header class="hitprice-shop-toolbar">
						<div class="hitprice-shop-toolbar__summary">
							<span class="hitprice-shop-toolbar__label"><?php esc_html_e( 'Available now', 'hitprice' ); ?></span>
							<?php woocommerce_result_count(); ?>
						</div>
						<form class="hitprice-shop-toolbar__sort" method="get" action="<?php echo esc_url( hitprice_get_current_shop_archive_url() ); ?>" data-hitprice-sort-form>
							<span class="hitprice-shop-toolbar__label"><?php esc_html_e( 'Sort by', 'hitprice' ); ?></span>
							<select name="orderby" class="orderby" aria-label="<?php esc_attr_e( 'Shop order', 'hitprice' ); ?>" onchange="this.form.submit()">
								<?php hitprice_render_archive_sort_options(); ?>
							</select>
							<?php if ( ! empty( $selected_filters['hp_cat'] ) ) : ?>
								<?php foreach ( $selected_filters['hp_cat'] as $selected_category ) : ?>
									<input type="hidden" name="hp_cat[]" value="<?php echo esc_attr( (string) $selected_category ); ?>">
								<?php endforeach; ?>
							<?php endif; ?>
							<?php if ( $selected_filters['hp_stock'] ) : ?>
								<input type="hidden" name="hp_stock" value="in-stock">
							<?php endif; ?>
							<?php if ( $selected_filters['hp_sale'] ) : ?>
								<input type="hidden" name="hp_sale" value="1">
							<?php endif; ?>
							<?php if ( $selected_filters['hp_featured'] ) : ?>
								<input type="hidden" name="hp_featured" value="1">
							<?php endif; ?>
							<?php if ( ! empty( $selected_filters['hp_price'] ) ) : ?>
								<?php foreach ( $selected_filters['hp_price'] as $selected_price ) : ?>
									<input type="hidden" name="hp_price[]" value="<?php echo esc_attr( $selected_price ); ?>">
								<?php endforeach; ?>
							<?php endif; ?>
							<?php foreach ( $selected_filters['attributes'] as $taxonomy => $values ) : ?>
								<?php foreach ( $values as $value ) : ?>
									<input type="hidden" name="<?php echo esc_attr( 'filter_' . $taxonomy ); ?>[]" value="<?php echo esc_attr( $value ); ?>">
								<?php endforeach; ?>
							<?php endforeach; ?>
						</form>
					</header>

					<div class="hitprice-shop-results" data-hitprice-results>
						<?php hitprice_render_archive_products_markup(); ?>
					</div>

					<div class="hitprice-shop-pagination" data-hitprice-pagination>
						<?php do_action( 'woocommerce_after_shop_loop' ); ?>
					</div>
				<?php else : ?>
					<div class="hitprice-shop-empty">
						<h2><?php esc_html_e( 'No products found', 'hitprice' ); ?></h2>
						<p><?php esc_html_e( 'Try another category or return to the full shop catalog to continue browsing.', 'hitprice' ); ?></p>
						<a class="hitprice-shop-button" href="<?php echo esc_url( $shop_page_url ); ?>"><?php esc_html_e( 'Go to shop', 'hitprice' ); ?></a>
					</div>
				<?php endif; ?>
			</section>
		</div>

		<?php do_action( 'woocommerce_after_main_content' ); ?>
	</div>
</main>
<?php
get_footer( 'shop' );
