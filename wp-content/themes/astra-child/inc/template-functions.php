<?php
/**
 * Template helper functions.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a template part from the child theme.
 *
 * @param string $slug Template slug.
 * @param array  $args Optional arguments.
 */
function hitprice_get_template_part( $slug, $args = array() ) {
	if ( ! empty( $args ) ) {
		set_query_var( 'hitprice_template_args', $args );
	}

	get_template_part( $slug );

	if ( ! empty( $args ) ) {
		set_query_var( 'hitprice_template_args', null );
	}
}

/**
 * Read template arguments in a part file.
 *
 * @return array
 */
function hitprice_get_template_args() {
	$args = get_query_var( 'hitprice_template_args', array() );

	return is_array( $args ) ? $args : array();
}

/**
 * Resolve product page urls safely.
 *
 * @param string $page WooCommerce page key.
 * @return string
 */
function hitprice_get_wc_page_url( $page ) {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( $page );
	}

	return home_url( '/' );
}

/**
 * Resolve cart page url safely.
 *
 * @return string
 */
function hitprice_get_cart_url() {
	if ( function_exists( 'wc_get_cart_url' ) ) {
		return wc_get_cart_url();
	}

	return home_url( '/' );
}

/**
 * Return cart count.
 *
 * @return int
 */
function hitprice_get_cart_count() {
	if ( function_exists( 'WC' ) && WC()->cart ) {
		return (int) WC()->cart->get_cart_contents_count();
	}

	return 0;
}

/**
 * Render the assigned header menu.
 */
function hitprice_render_header_menu() {
	wp_nav_menu(
		array(
			'theme_location' => 'hitprice_header_menu',
			'container'      => false,
			'menu_class'     => 'hitprice-menu',
			'fallback_cb'    => false,
		)
	);
}

/**
 * Render header cart count markup.
 *
 * @return void
 */
function hitprice_render_header_cart_count() {
	$cart_count = hitprice_get_cart_count();
	?>
	<div class="ast-cart-menu-wrap hitprice-header-cart__count-wrap" aria-hidden="true">
		<span class="count hitprice-header-cart__count<?php echo $cart_count > 0 ? ' has-items' : ''; ?>">
			<span class="ast-count-text"><?php echo esc_html( $cart_count ); ?></span>
		</span>
	</div>
	<?php
}

/**
 * Render header cart badge markup.
 *
 * @return void
 */
function hitprice_render_header_cart_badge() {
	$cart_count = hitprice_get_cart_count();
	?>
	<span class="hitprice-header-cart__badge<?php echo $cart_count > 0 ? ' has-items' : ''; ?>" aria-hidden="true">
		<span class="hitprice-header-cart__badge-text"><?php echo esc_html( $cart_count ); ?></span>
	</span>
	<?php
}

/**
 * Refresh custom header cart fragments.
 *
 * @param array $fragments Existing fragments.
 * @return array
 */
function hitprice_refresh_header_cart_fragments( $fragments ) {
	ob_start();
	hitprice_render_header_cart_count();
	$fragments['.hitprice-header-cart__count-wrap'] = ob_get_clean();

	ob_start();
	hitprice_render_header_cart_badge();
	$fragments['.hitprice-header-cart__badge'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'hitprice_refresh_header_cart_fragments' );

/**
 * Get current product archive intro text.
 *
 * @return string
 */
function hitprice_get_archive_intro_text() {
	if ( is_product_taxonomy() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term && ! empty( $term->description ) ) {
			return wp_strip_all_tags( $term->description );
		}

		return __( 'Browse curated products in this category with cleaner filtering, better scanning, and faster comparison.', 'hitprice' );
	}

	return __( 'Discover mobiles, accessories, electronics, and appliances in a cleaner product archive built for faster browsing and stronger purchase decisions.', 'hitprice' );
}

/**
 * Get top level product categories for archive quick links.
 *
 * @param int $limit Number of terms to load.
 * @return WP_Term[]
 */
function hitprice_get_archive_category_links( $limit = 6 ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => absint( $limit ),
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return is_array( $terms ) ? $terms : array();
}

/**
 * Get current shop archive url.
 *
 * @return string
 */
function hitprice_get_current_shop_archive_url() {
	if ( is_product_taxonomy() ) {
		$term_link = get_term_link( get_queried_object() );

		if ( ! is_wp_error( $term_link ) ) {
			return $term_link;
		}
	}

	return hitprice_get_wc_page_url( 'shop' );
}

/**
 * Build archive filter url.
 *
 * @param array $args Query args to merge.
 * @return string
 */
function hitprice_get_archive_filter_url( $args = array() ) {
	$current_args = array();

	foreach ( array( 'orderby', 'min_price', 'max_price', 'hp_stock', 'hp_sale', 'hp_featured' ) as $key ) {
		if ( isset( $_GET[ $key ] ) ) {
			$current_args[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
		}
	}

	$merged = array_merge( $current_args, $args );

	foreach ( $merged as $key => $value ) {
		if ( '' === $value || null === $value ) {
			unset( $merged[ $key ] );
		}
	}

	return add_query_arg( $merged, hitprice_get_current_shop_archive_url() );
}

/**
 * Get selected archive filters from the current request.
 *
 * @return array
 */
function hitprice_get_selected_archive_filters() {
	$selected = array(
		'orderby'    => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '',
		'hp_stock'   => isset( $_GET['hp_stock'] ) && 'in-stock' === sanitize_key( wp_unslash( $_GET['hp_stock'] ) ),
		'hp_sale'    => isset( $_GET['hp_sale'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hp_sale'] ) ),
		'hp_featured'=> isset( $_GET['hp_featured'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hp_featured'] ) ),
		'hp_price'   => array(),
		'hp_cat'     => array(),
		'attributes' => array(),
	);

	if ( isset( $_GET['hp_cat'] ) ) {
		$selected['hp_cat'] = array_map( 'absint', (array) wp_unslash( $_GET['hp_cat'] ) );
		$selected['hp_cat'] = array_filter( $selected['hp_cat'] );
	}

	if ( isset( $_GET['hp_price'] ) ) {
		$selected['hp_price'] = array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['hp_price'] ) );
		$selected['hp_price'] = array_filter( $selected['hp_price'] );
	}

	foreach ( hitprice_get_filterable_attribute_taxonomies() as $attribute_taxonomy ) {
		$query_key = 'filter_' . $attribute_taxonomy['taxonomy'];
		if ( isset( $_GET[ $query_key ] ) ) {
			$selected['attributes'][ $attribute_taxonomy['taxonomy'] ] = array_map(
				'sanitize_title',
				(array) wp_unslash( $_GET[ $query_key ] )
			);
		}
	}

	return $selected;
}

/**
 * Get filterable product attributes for archive sidebar.
 *
 * @return array[]
 */
function hitprice_get_filterable_attribute_taxonomies() {
	static $filterable_attributes = null;

	if ( null !== $filterable_attributes ) {
		return $filterable_attributes;
	}

	$filterable_attributes = array();

	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return $filterable_attributes;
	}

	foreach ( wc_get_attribute_taxonomies() as $attribute_taxonomy ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute_taxonomy->attribute_name );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => 8,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		$filterable_attributes[] = array(
			'taxonomy' => $taxonomy,
			'label'    => $attribute_taxonomy->attribute_label,
			'terms'    => $terms,
		);
	}

	return $filterable_attributes;
}

/**
 * Get filter sidebar sections for the archive.
 *
 * @return array[]
 */
function hitprice_get_archive_filter_sections() {
	$selected = hitprice_get_selected_archive_filters();
	$sections = array();

	if ( is_shop() ) {
		$category_terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
				'number'     => 8,
			)
		);

		if ( ! is_wp_error( $category_terms ) && ! empty( $category_terms ) ) {
			$sections[] = array(
				'title'   => __( 'Category', 'hitprice' ),
				'type'    => 'checkboxes',
				'options' => array_map(
					static function( $term ) use ( $selected ) {
						return array(
							'label'    => $term->name,
							'value'    => (string) $term->term_id,
							'name'     => 'hp_cat[]',
							'count'    => (int) $term->count,
							'checked'  => in_array( (int) $term->term_id, $selected['hp_cat'], true ),
						);
					},
					$category_terms
				),
			);
		}
	}

	$sections[] = array(
		'title'   => __( 'Get it fast', 'hitprice' ),
		'type'    => 'checkboxes',
		'options' => array(
			array(
				'label'   => __( 'Delivery + Setup', 'hitprice' ),
				'value'   => '1',
				'name'    => 'hp_sale',
				'checked' => $selected['hp_sale'],
			),
			array(
				'label'   => __( 'In-store pickup', 'hitprice' ),
				'value'   => '1',
				'name'    => 'hp_featured',
				'checked' => $selected['hp_featured'],
			),
			array(
				'label'   => __( '2-day shipping', 'hitprice' ),
				'value'   => 'in-stock',
				'name'    => 'hp_stock',
				'checked' => $selected['hp_stock'],
			),
		),
	);

	$sections[] = array(
		'title'   => __( 'Details', 'hitprice' ),
		'type'    => 'checkboxes',
		'options' => array(
			array(
				'label'   => __( 'On sale', 'hitprice' ),
				'value'   => '1',
				'name'    => 'hp_sale',
				'checked' => $selected['hp_sale'],
			),
			array(
				'label'   => __( 'Featured', 'hitprice' ),
				'value'   => '1',
				'name'    => 'hp_featured',
				'checked' => $selected['hp_featured'],
			),
		),
	);

	$sections[] = array(
		'title'   => __( 'Price', 'hitprice' ),
		'type'    => 'radios',
		'options' => array(
			array(
				'label'   => __( 'Under Rs. 25,000', 'hitprice' ),
				'value'   => '0-25000',
				'name'    => 'hp_price[]',
				'checked' => in_array( '0-25000', $selected['hp_price'], true ),
			),
			array(
				'label'   => __( 'Rs. 25,000 to Rs. 50,000', 'hitprice' ),
				'value'   => '25000-50000',
				'name'    => 'hp_price[]',
				'checked' => in_array( '25000-50000', $selected['hp_price'], true ),
			),
			array(
				'label'   => __( 'Rs. 50,000 to Rs. 100,000', 'hitprice' ),
				'value'   => '50000-100000',
				'name'    => 'hp_price[]',
				'checked' => in_array( '50000-100000', $selected['hp_price'], true ),
			),
			array(
				'label'   => __( 'Rs. 100,000+', 'hitprice' ),
				'value'   => '100000-*',
				'name'    => 'hp_price[]',
				'checked' => in_array( '100000-*', $selected['hp_price'], true ),
			),
		),
	);

	foreach ( hitprice_get_filterable_attribute_taxonomies() as $attribute_taxonomy ) {
		$sections[] = array(
			'title'   => $attribute_taxonomy['label'],
			'type'    => 'checkboxes',
			'options' => array_map(
				static function( $term ) use ( $attribute_taxonomy, $selected ) {
					$current_values = isset( $selected['attributes'][ $attribute_taxonomy['taxonomy'] ] ) ? $selected['attributes'][ $attribute_taxonomy['taxonomy'] ] : array();

					return array(
						'label'   => $term->name,
						'value'   => $term->slug,
						'name'    => 'filter_' . $attribute_taxonomy['taxonomy'] . '[]',
						'count'   => (int) $term->count,
						'checked' => in_array( $term->slug, $current_values, true ),
					);
				},
				$attribute_taxonomy['terms']
			),
		);
	}

	return $sections;
}

/**
 * Get active archive filter count.
 *
 * @return int
 */
function hitprice_get_active_archive_filter_count() {
	$selected = hitprice_get_selected_archive_filters();
	$count    = 0;

	$count += count( $selected['hp_cat'] );
	$count += $selected['hp_stock'] ? 1 : 0;
	$count += $selected['hp_sale'] ? 1 : 0;
	$count += $selected['hp_featured'] ? 1 : 0;
	$count += count( $selected['hp_price'] );

	foreach ( $selected['attributes'] as $values ) {
		$count += count( $values );
	}

	return $count;
}

/**
 * Render hidden query fields for archive forms.
 *
 * @param array $exclude Keys to exclude.
 * @return void
 */
function hitprice_render_archive_hidden_fields( $exclude = array() ) {
	$selected = hitprice_get_selected_archive_filters();

	if ( ! in_array( 'orderby', $exclude, true ) && ! empty( $selected['orderby'] ) ) {
		printf(
			'<input type="hidden" name="orderby" value="%s">',
			esc_attr( $selected['orderby'] )
		);
	}
}

/**
 * Apply custom archive filters to shop queries.
 *
 * @param WP_Query $query Query instance.
 * @return void
 */
function hitprice_apply_archive_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() ) ) {
		return;
	}

	$meta_query = (array) $query->get( 'meta_query', array() );
	$tax_query  = (array) $query->get( 'tax_query', array() );

	if ( isset( $_GET['hp_stock'] ) && 'in-stock' === sanitize_key( wp_unslash( $_GET['hp_stock'] ) ) ) {
		$meta_query[] = array(
			'key'   => '_stock_status',
			'value' => 'instock',
		);
	}

	if ( isset( $_GET['hp_sale'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hp_sale'] ) ) ) {
		$sale_ids = wc_get_product_ids_on_sale();
		$query->set( 'post__in', ! empty( $sale_ids ) ? $sale_ids : array( 0 ) );
	}

	if ( isset( $_GET['hp_featured'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['hp_featured'] ) ) ) {
		$tax_query[] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => array( 'featured' ),
		);
	}

	if ( is_shop() && isset( $_GET['hp_cat'] ) ) {
		$category_ids = array_map( 'absint', (array) wp_unslash( $_GET['hp_cat'] ) );
		$category_ids = array_filter( $category_ids );

		if ( ! empty( $category_ids ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}
	}

	if ( isset( $_GET['hp_price'] ) ) {
		$price_ranges = array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['hp_price'] ) );
		$price_ranges = array_filter( $price_ranges );

		if ( ! empty( $price_ranges ) ) {
			$price_meta_group = array( 'relation' => 'OR' );

			foreach ( $price_ranges as $price_range ) {
				$parts = array_map( 'trim', explode( '-', $price_range ) );

				if ( 2 !== count( $parts ) ) {
					continue;
				}

				$min_price = is_numeric( $parts[0] ) ? (float) $parts[0] : 0;
				$max_price = '*' === $parts[1] ? null : ( is_numeric( $parts[1] ) ? (float) $parts[1] : null );

				$price_clause = array(
					'key'  => '_price',
					'type' => 'DECIMAL',
				);

				if ( null === $max_price ) {
					$price_clause['value']   = $min_price;
					$price_clause['compare'] = '>=';
				} else {
					$price_clause['value']   = array( $min_price, $max_price );
					$price_clause['compare'] = 'BETWEEN';
				}

				$price_meta_group[] = $price_clause;
			}

			if ( count( $price_meta_group ) > 1 ) {
				$meta_query[] = $price_meta_group;
			}
		}
	}

	foreach ( hitprice_get_filterable_attribute_taxonomies() as $attribute_taxonomy ) {
		$query_key = 'filter_' . $attribute_taxonomy['taxonomy'];

		if ( isset( $_GET[ $query_key ] ) ) {
			$selected_terms = array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $query_key ] ) );
			$selected_terms = array_filter( $selected_terms );

			if ( ! empty( $selected_terms ) ) {
				$tax_query[] = array(
					'taxonomy' => $attribute_taxonomy['taxonomy'],
					'field'    => 'slug',
					'terms'    => $selected_terms,
				);
			}
		}
	}

	$query->set( 'meta_query', $meta_query );
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'hitprice_apply_archive_filters' );

/**
 * Render archive products markup.
 *
 * @return void
 */
function hitprice_render_archive_products_markup() {
	if ( woocommerce_product_loop() ) {
		woocommerce_product_loop_start();

		while ( have_posts() ) {
			the_post();
			do_action( 'woocommerce_shop_loop' );
			wc_get_template_part( 'content', 'product' );
		}

		woocommerce_product_loop_end();
	} else {
		?>
		<div class="hitprice-shop-empty">
			<h2><?php esc_html_e( 'No products found', 'hitprice' ); ?></h2>
			<p><?php esc_html_e( 'Try another category or return to the full shop catalog to continue browsing.', 'hitprice' ); ?></p>
			<a class="hitprice-shop-button" href="<?php echo esc_url( hitprice_get_wc_page_url( 'shop' ) ); ?>"><?php esc_html_e( 'Go to shop', 'hitprice' ); ?></a>
		</div>
		<?php
	}
}

/**
 * Render archive sort options.
 *
 * @return void
 */
function hitprice_render_archive_sort_options() {
	$selected_filters         = hitprice_get_selected_archive_filters();
	$catalog_orderby_options  = apply_filters(
		'woocommerce_catalog_orderby',
		array(
			'menu_order' => __( 'Default sorting', 'woocommerce' ),
			'popularity' => __( 'Sort by popularity', 'woocommerce' ),
			'rating'     => __( 'Sort by average rating', 'woocommerce' ),
			'date'       => __( 'Sort by latest', 'woocommerce' ),
			'price'      => __( 'Sort by price: low to high', 'woocommerce' ),
			'price-desc' => __( 'Sort by price: high to low', 'woocommerce' ),
		)
	);
	$current_orderby          = $selected_filters['orderby'] ? $selected_filters['orderby'] : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', 'menu_order' ) );

	foreach ( $catalog_orderby_options as $orderby_value => $orderby_label ) {
		printf(
			'<option value="%s" %s>%s</option>',
			esc_attr( $orderby_value ),
			selected( $current_orderby, $orderby_value, false ),
			esc_html( $orderby_label )
		);
	}
}

/**
 * Ajax archive filtering.
 *
 * @return void
 */
function hitprice_ajax_filter_products() {
	check_ajax_referer( 'hitprice_shop_archive', 'nonce' );

	$query_vars = isset( $_POST['query'] ) && is_array( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : array();

	foreach ( $query_vars as $key => $value ) {
		$_GET[ $key ] = $value;
	}

	$paged = isset( $query_vars['paged'] ) ? absint( $query_vars['paged'] ) : 1;

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'paged'          => max( 1, $paged ),
		'meta_query'     => WC()->query->get_meta_query(),
		'tax_query'      => WC()->query->get_tax_query(),
	);

	if ( is_shop() ) {
		$args['page_id'] = wc_get_page_id( 'shop' );
	}

	$orderby = isset( $query_vars['orderby'] ) ? wc_clean( $query_vars['orderby'] ) : get_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
	$ordering_args = WC()->query->get_catalog_ordering_args( $orderby, $query_vars['order'] ?? '' );
	$args          = array_merge( $args, $ordering_args );

	$query = new WP_Query( $args );

	ob_start();
	$GLOBALS['wp_query'] = $query;
	hitprice_render_archive_products_markup();
	$products_html = ob_get_clean();

	ob_start();
	woocommerce_result_count();
	$result_count = ob_get_clean();

	ob_start();
	woocommerce_pagination();
	$pagination = ob_get_clean();

	wp_send_json_success(
		array(
			'products'    => $products_html,
			'resultCount' => $result_count,
			'pagination'  => $pagination,
		)
	);
}
add_action( 'wp_ajax_hitprice_filter_products', 'hitprice_ajax_filter_products' );
add_action( 'wp_ajax_nopriv_hitprice_filter_products', 'hitprice_ajax_filter_products' );

/**
 * Hit Price archive filter widget.
 */
class HitPrice_Shop_Filter_Widget extends WP_Widget {
	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'hitprice_shop_filter_widget',
			__( 'Hit Price Shop Filters', 'hitprice' ),
			array(
				'description' => __( 'A lightweight WooCommerce archive filter widget for the shop sidebar.', 'hitprice' ),
			)
		);
	}

	/**
	 * Output widget.
	 *
	 * @param array $args Sidebar args.
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title      = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Filter products', 'hitprice' );
		$categories = hitprice_get_archive_category_links( 8 );

		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $args['before_title'] . esc_html( $title ) . $args['after_title'] );
		?>
		<div class="hitprice-filter-group">
			<h3 class="hitprice-filter-group__heading"><?php esc_html_e( 'Availability', 'hitprice' ); ?></h3>
			<ul class="hitprice-filter-links">
				<li><a class="<?php echo empty( $_GET['hp_stock'] ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( hitprice_get_archive_filter_url( array( 'hp_stock' => '' ) ) ); ?>"><?php esc_html_e( 'All items', 'hitprice' ); ?></a></li>
				<li><a class="<?php echo isset( $_GET['hp_stock'] ) && 'in-stock' === sanitize_key( wp_unslash( $_GET['hp_stock'] ) ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( hitprice_get_archive_filter_url( array( 'hp_stock' => 'in-stock' ) ) ); ?>"><?php esc_html_e( 'In stock', 'hitprice' ); ?></a></li>
			</ul>
		</div>
		<div class="hitprice-filter-group">
			<h3 class="hitprice-filter-group__heading"><?php esc_html_e( 'Offers', 'hitprice' ); ?></h3>
			<ul class="hitprice-filter-links">
				<li><a class="<?php echo empty( $_GET['hp_sale'] ) ? '' : 'is-active'; ?>" href="<?php echo esc_url( hitprice_get_archive_filter_url( array( 'hp_sale' => '1' ) ) ); ?>"><?php esc_html_e( 'On sale', 'hitprice' ); ?></a></li>
				<li><a class="<?php echo empty( $_GET['hp_featured'] ) ? '' : 'is-active'; ?>" href="<?php echo esc_url( hitprice_get_archive_filter_url( array( 'hp_featured' => '1' ) ) ); ?>"><?php esc_html_e( 'Featured', 'hitprice' ); ?></a></li>
				<li><a href="<?php echo esc_url( hitprice_get_archive_filter_url( array( 'hp_sale' => '', 'hp_featured' => '', 'hp_stock' => '' ) ) ); ?>"><?php esc_html_e( 'Clear filters', 'hitprice' ); ?></a></li>
			</ul>
		</div>
		<?php if ( ! empty( $categories ) ) : ?>
			<div class="hitprice-filter-group">
				<h3 class="hitprice-filter-group__heading"><?php esc_html_e( 'Categories', 'hitprice' ); ?></h3>
				<ul class="hitprice-filter-links">
					<?php foreach ( $categories as $category ) : ?>
						<?php
						$is_current = is_product_taxonomy() && get_queried_object() instanceof WP_Term && (int) get_queried_object()->term_id === (int) $category->term_id;
						?>
						<li>
							<a class="<?php echo $is_current ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>">
								<?php echo esc_html( $category->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Widget admin form.
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Filter products', 'hitprice' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'hitprice' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	/**
	 * Save widget form.
	 *
	 * @param array $new_instance New data.
	 * @param array $old_instance Old data.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}
}

/**
 * Register custom widgets.
 *
 * @return void
 */
function hitprice_register_custom_widgets() {
	register_widget( 'HitPrice_Shop_Filter_Widget' );
}
add_action( 'widgets_init', 'hitprice_register_custom_widgets' );

/*
|--------------------------------------------------------------------------
| Single Product Page — Rendering Functions
|--------------------------------------------------------------------------
*/

/**
 * Open the product layout grid wrapper.
 */
function hp_render_product_layout_open() {
	echo '<div class="hp-product-layout">';
}

/**
 * Close the product layout grid wrapper.
 */
function hp_render_product_layout_close() {
	echo '</div><!-- .hp-product-layout -->';
}

/**
 * Render product add-ons UI.
 */
function hp_render_product_addons_ui() {
	hitprice_get_template_part( 'template-parts/product/addons' );
}

/**
 * Render product trade-in block.
 */
function hp_render_product_tradein_block() {
	hitprice_get_template_part( 'template-parts/product/tradein' );
}

/**
 * Render product payment options.
 */
function hp_render_product_payment_options() {
	hitprice_get_template_part( 'template-parts/product/payment-options' );
}

/**
 * Render product compare section.
 */
function hp_render_product_compare() {
	hitprice_get_template_part( 'template-parts/product/compare' );
}

/**
 * Render product features section.
 */
function hp_render_product_features() {
	hitprice_get_template_part( 'template-parts/product/features' );
}

/**
 * Render product accordions (replaces tabs UI).
 */
function hp_render_product_accordions() {
	hitprice_get_template_part( 'template-parts/product/accordions' );
}

/**
 * Render product detail specs section.
 */
function hp_render_product_detail_specs() {
	hitprice_get_template_part( 'template-parts/product/detail-specs' );
}

/**
 * Render product sticky bar.
 */
function hp_render_product_sticky_bar() {
	if ( ! is_product() ) {
		return;
	}
	hitprice_get_template_part( 'template-parts/product/sticky-bar' );
}
