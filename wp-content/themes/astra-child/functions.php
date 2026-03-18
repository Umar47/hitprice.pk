<?php
/**
 * Hit Price Astra child theme setup.
 *
 * @package HitPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue child theme assets.
 *
 * Keep this minimal and use the child theme for presentation-layer work only.
 */
function hitprice_enqueue_child_assets() {
	$theme = wp_get_theme();

	wp_enqueue_style(
		'hitprice-child-style',
		get_stylesheet_uri(),
		array( 'astra-theme-css' ),
		$theme->get( 'Version' )
	);

	wp_enqueue_style(
		'hitprice-header-footer',
		get_stylesheet_directory_uri() . '/assets/css/header-footer.css',
		array( 'hitprice-child-style' ),
		filemtime( get_stylesheet_directory() . '/assets/css/header-footer.css' )
	);

	wp_enqueue_script(
		'hitprice-header',
		get_stylesheet_directory_uri() . '/assets/js/header.js',
		array(),
		filemtime( get_stylesheet_directory() . '/assets/js/header.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'hitprice_enqueue_child_assets', 20 );

/**
 * Register child theme menus.
 */
function hitprice_register_theme_menus() {
	register_nav_menus(
		array(
			'hitprice_header_menu' => __( 'Hit Price Header Menu', 'hitprice' ),
			'hitprice_footer_menu' => __( 'Hit Price Footer Menu', 'hitprice' ),
		)
	);
}
add_action( 'after_setup_theme', 'hitprice_register_theme_menus' );

/**
 * Replace Astra header markup with the custom child theme header.
 */
function hitprice_replace_astra_header() {
	if ( ! function_exists( 'astra_header_markup' ) ) {
		return;
	}

	remove_action( 'astra_header', 'astra_header_markup' );
	add_action( 'astra_header', 'hitprice_render_site_header' );
}
add_action( 'wp', 'hitprice_replace_astra_header', 20 );

/**
 * Output the custom header.
 */
function hitprice_render_site_header() {
	?>
	<header id="masthead" class="site-header hitprice-site-header" itemscope="itemscope" itemtype="https://schema.org/WPHeader">
		<div class="hitprice-header-bar">
			<div class="hitprice-shell hitprice-header-bar__inner">
				<p class="hitprice-header-bar__message">Original products, trusted brands, nationwide delivery.</p>
				<div class="hitprice-header-bar__links" aria-label="<?php esc_attr_e( 'Utility links', 'hitprice' ); ?>">
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'My Account', 'hitprice' ); ?></a>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop All', 'hitprice' ); ?></a>
				</div>
			</div>
		</div>

		<div class="hitprice-header-main">
			<div class="hitprice-shell hitprice-header-main__inner">
				<button class="hitprice-mobile-toggle" type="button" aria-expanded="false" aria-controls="hitprice-mobile-panel">
					<span class="screen-reader-text"><?php esc_html_e( 'Open menu', 'hitprice' ); ?></span>
					<span class="hitprice-mobile-toggle__icon" aria-hidden="true"></span>
				</button>

				<div class="hitprice-branding">
					<a class="hitprice-branding__link" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="hitprice-branding__eyebrow"><?php esc_html_e( 'Hit Price', 'hitprice' ); ?></span>
						<span class="hitprice-branding__title"><?php bloginfo( 'name' ); ?></span>
					</a>
				</div>

				<div class="hitprice-search">
					<form role="search" method="get" class="hitprice-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label class="screen-reader-text" for="hitprice-product-search"><?php esc_html_e( 'Search products', 'hitprice' ); ?></label>
						<input id="hitprice-product-search" type="search" class="hitprice-search-form__field" placeholder="<?php esc_attr_e( 'Search mobiles, electronics, appliances and more', 'hitprice' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s" />
						<input type="hidden" name="post_type" value="product" />
						<button type="submit" class="hitprice-search-form__submit"><?php esc_html_e( 'Search', 'hitprice' ); ?></button>
					</form>
				</div>

				<div class="hitprice-header-actions" aria-label="<?php esc_attr_e( 'Header actions', 'hitprice' ); ?>">
					<a class="hitprice-header-action" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<span class="hitprice-header-action__label"><?php esc_html_e( 'Account', 'hitprice' ); ?></span>
					</a>
					<a class="hitprice-header-action" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<span class="hitprice-header-action__label"><?php esc_html_e( 'Cart', 'hitprice' ); ?></span>
						<?php if ( function_exists( 'WC' ) && WC()->cart ) : ?>
							<span class="hitprice-header-action__count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
						<?php endif; ?>
					</a>
				</div>
			</div>
		</div>

		<div class="hitprice-header-nav">
			<div class="hitprice-shell">
				<nav class="hitprice-desktop-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'hitprice' ); ?>">
					<?php hitprice_render_header_menu(); ?>
				</nav>
			</div>
		</div>

		<div id="hitprice-mobile-panel" class="hitprice-mobile-panel" hidden>
			<div class="hitprice-shell">
				<nav class="hitprice-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'hitprice' ); ?>">
					<?php hitprice_render_header_menu(); ?>
				</nav>
			</div>
		</div>
	</header>
	<?php
}

/**
 * Render the header menu with a reliable fallback.
 */
function hitprice_render_header_menu() {
	if ( has_nav_menu( 'hitprice_header_menu' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'hitprice_header_menu',
				'container'      => false,
				'menu_class'     => 'hitprice-menu',
				'fallback_cb'    => false,
			)
		);

		return;
	}

	$fallback_links = array(
		array(
			'label' => __( 'Shop', 'hitprice' ),
			'url'   => wc_get_page_permalink( 'shop' ),
		),
		array(
			'label' => __( 'Mobiles', 'hitprice' ),
			'url'   => hitprice_get_product_term_link( array( 'mobile-phones', 'mobiles', 'mobile' ) ),
		),
		array(
			'label' => __( 'Accessories', 'hitprice' ),
			'url'   => hitprice_get_product_term_link( array( 'mobile-accessories', 'accessories' ) ),
		),
		array(
			'label' => __( 'Computers', 'hitprice' ),
			'url'   => hitprice_get_product_term_link( array( 'computers', 'computer' ) ),
		),
		array(
			'label' => __( 'TV & Audio', 'hitprice' ),
			'url'   => hitprice_get_product_term_link( array( 'tv', 'television', 'electronics' ) ),
		),
		array(
			'label' => __( 'Home Appliances', 'hitprice' ),
			'url'   => hitprice_get_product_term_link( array( 'home-appliances', 'appliances', 'kitchen-appliances' ) ),
		),
	);

	echo '<ul class="hitprice-menu hitprice-menu--fallback">';

	foreach ( $fallback_links as $link ) {
		if ( empty( $link['url'] ) ) {
			continue;
		}

		printf(
			'<li class="menu-item"><a href="%1$s">%2$s</a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}

	echo '</ul>';
}

/**
 * Resolve a product category link from candidate slugs.
 *
 * @param array $slugs Candidate slugs.
 * @return string
 */
function hitprice_get_product_term_link( $slugs ) {
	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );

		if ( $term && ! is_wp_error( $term ) ) {
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}
	}

	return wc_get_page_permalink( 'shop' );
}

