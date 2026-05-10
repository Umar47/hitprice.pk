<?php
/**
 * HitPrice Global Settings admin page.
 * Manages global trust badges, sale banner label, viewers range, and shipping policy.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'hp_register_global_settings_menu' );

function hp_register_global_settings_menu() {
	add_menu_page(
		__( 'HitPrice', 'hitprice-helper' ),
		__( 'HitPrice', 'hitprice-helper' ),
		'manage_options',
		'hitprice-settings',
		'hp_render_global_settings_page',
		'dashicons-store',
		58
	);

	add_submenu_page(
		'hitprice-settings',
		__( 'Single Product Page – Settings', 'hitprice-helper' ),
		__( 'Single Product Page', 'hitprice-helper' ),
		'manage_options',
		'hitprice-settings',
		'hp_render_global_settings_page'
	);
}

add_action( 'admin_enqueue_scripts', 'hp_global_settings_enqueue' );

function hp_global_settings_enqueue( $hook ) {
	if ( 'toplevel_page_hitprice-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_style(
		'hp-global-settings',
		HITPRICE_HELPER_URL . 'assets/css/admin-global-settings.css',
		[],
		filemtime( HITPRICE_HELPER_PATH . 'assets/css/admin-global-settings.css' )
	);
	wp_enqueue_script(
		'hp-global-settings',
		HITPRICE_HELPER_URL . 'assets/js/admin-global-settings.js',
		[ 'jquery', 'media-upload' ],
		filemtime( HITPRICE_HELPER_PATH . 'assets/js/admin-global-settings.js' ),
		true
	);
}

add_action( 'admin_post_hp_save_global_settings', 'hp_save_global_settings' );

function hp_save_global_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Unauthorized.', 'hitprice-helper' ) );
	}

	check_admin_referer( 'hp_global_settings_save', 'hp_global_settings_nonce' );

	/* phpcs:disable WordPress.Security.NonceVerification -- nonce checked above via check_admin_referer */
	$settings = [];

	// Keep existing badge data so gallery/trust strips don't lose their icons.
	$existing = get_option( 'hp_global_settings', [] );
	if ( ! empty( $existing['badges'] ) ) {
		$settings['badges'] = $existing['badges'];
	}

	// Gallery top icons (PTA + Best Price).
	$gallery_top = array();
	foreach ( array( 'pta', 'best_price' ) as $gkey ) {
		$raw = isset( $_POST[ 'hp_gallery_top_' . $gkey ] ) && is_array( $_POST[ 'hp_gallery_top_' . $gkey ] )
			? $_POST[ 'hp_gallery_top_' . $gkey ]
			: array();
		$gallery_top[ $gkey ] = array(
			'image_id'  => absint( $raw['image_id'] ?? 0 ),
			'image_url' => esc_url_raw( $raw['image_url'] ?? '' ),
			'label'     => sanitize_text_field( $raw['label'] ?? '' ),
		);
	}
	$settings['gallery_top'] = $gallery_top;

	// Gallery bottom icons repeater (up to 4 rows).
	$raw_gallery_bottom = isset( $_POST['hp_gallery_bottom_icons'] ) && is_array( $_POST['hp_gallery_bottom_icons'] )
		? $_POST['hp_gallery_bottom_icons']
		: array();
	$gallery_bottom = array();
	foreach ( array_slice( $raw_gallery_bottom, 0, 4 ) as $row ) {
		if ( empty( $row['title'] ) ) {
			continue;
		}
		$gallery_bottom[] = array(
			'image_id'  => absint( $row['image_id'] ?? 0 ),
			'image_url' => esc_url_raw( $row['image_url'] ?? '' ),
			'title'     => sanitize_text_field( $row['title'] ),
			'subtitle'  => sanitize_text_field( $row['subtitle'] ?? '' ),
		);
	}
	$settings['gallery_bottom_icons'] = $gallery_bottom;

	// Price icons repeater (up to 6 rows).
	$raw_icons = isset( $_POST['hp_price_icons'] ) && is_array( $_POST['hp_price_icons'] )
		? $_POST['hp_price_icons']
		: [];

	$price_icons = [];
	foreach ( array_slice( $raw_icons, 0, 6 ) as $row ) {
		if ( empty( $row['title'] ) ) {
			continue;
		}
		$price_icons[] = [
			'image_id'  => absint( $row['image_id'] ?? 0 ),
			'image_url' => esc_url_raw( $row['image_url'] ?? '' ),
			'title'     => sanitize_text_field( $row['title'] ),
			'subtitle'  => sanitize_text_field( $row['subtitle'] ?? '' ),
		];
	}
	$settings['price_icons'] = $price_icons;

	// Sale banner.
	$settings['sale_banner_enabled'] = ! empty( $_POST['hp_sale_banner_enabled'] );
	$settings['sale_banner_text']    = wp_kses_post( wp_unslash( $_POST['hp_sale_banner_text'] ?? '' ) );

	// Viewers range.
	$viewers_min = max( 1, absint( $_POST['hp_viewers_min'] ?? 12 ) );
	$viewers_max = absint( $_POST['hp_viewers_max'] ?? 48 );
	$viewers_max = max( $viewers_min + 1, $viewers_max );

	$settings['viewers_min'] = $viewers_min;
	$settings['viewers_max'] = $viewers_max;

	// Why Buy section.
	$settings['why_buy_enabled'] = ! empty( $_POST['hp_why_buy_enabled'] );
	$settings['why_buy_title']   = sanitize_text_field( wp_unslash( $_POST['hp_why_buy_title'] ?? 'Why buy from Hitprice.pk?' ) );
	$raw_why_buy = isset( $_POST['hp_why_buy_items'] ) && is_array( $_POST['hp_why_buy_items'] )
		? $_POST['hp_why_buy_items']
		: array();
	$why_buy_items = array();
	foreach ( array_slice( $raw_why_buy, 0, 5 ) as $row ) {
		if ( empty( $row['title'] ) ) {
			continue;
		}
		$why_buy_items[] = array(
			'image_id'    => absint( $row['image_id'] ?? 0 ),
			'image_url'   => esc_url_raw( $row['image_url'] ?? '' ),
			'title'       => sanitize_text_field( $row['title'] ),
			'description' => sanitize_text_field( $row['description'] ?? '' ),
		);
	}
	$settings['why_buy_items'] = $why_buy_items;

	// Bottom trust strip (4 fixed items).
	$raw_trust   = isset( $_POST['hp_trust_strip'] ) && is_array( $_POST['hp_trust_strip'] ) ? $_POST['hp_trust_strip'] : [];
	$trust_strip = [];
	foreach ( array_slice( $raw_trust, 0, 4 ) as $row ) {
		$trust_strip[] = [
			'icon_class' => sanitize_text_field( $row['icon_class'] ?? '' ),
			'title'      => sanitize_text_field( $row['title'] ?? '' ),
			'subtitle'   => sanitize_text_field( $row['subtitle'] ?? '' ),
		];
	}
	$settings['trust_strip'] = $trust_strip;

	// Payment methods strip (up to 3 items).
	$raw_pm          = isset( $_POST['hp_payment_methods'] ) && is_array( $_POST['hp_payment_methods'] ) ? $_POST['hp_payment_methods'] : [];
	$payment_methods = [];
	foreach ( array_slice( $raw_pm, 0, 3 ) as $row ) {
		if ( empty( $row['title'] ) ) {
			continue;
		}
		$payment_methods[] = [
			'icon_class' => sanitize_text_field( $row['icon_class'] ?? '' ),
			'title'      => sanitize_text_field( $row['title'] ),
			'subtitle'   => sanitize_text_field( $row['subtitle'] ?? '' ),
		];
	}
	$settings['payment_methods'] = $payment_methods;

	// Shipping policy.
	$settings['shipping_policy'] = wp_kses_post( wp_unslash( $_POST['hp_shipping_policy'] ?? '' ) );
	/* phpcs:enable */

	update_option( 'hp_global_settings', $settings );

	wp_safe_redirect(
		add_query_arg(
			[ 'page' => 'hitprice-settings', 'saved' => '1' ],
			admin_url( 'admin.php' )
		)
	);
	exit;
}

function hp_render_global_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = get_option( 'hp_global_settings', [] );
	$saved    = isset( $_GET['saved'] ) && '1' === sanitize_key( $_GET['saved'] ); // phpcs:ignore WordPress.Security.NonceVerification

	$price_icons = isset( $settings['price_icons'] ) && is_array( $settings['price_icons'] )
		? $settings['price_icons']
		: [];
	if ( empty( $price_icons ) ) {
		$price_icons = [ [ 'image_id' => 0, 'image_url' => '', 'title' => '', 'subtitle' => '' ] ];
	}

	// Gallery top icons.
	$gallery_top_defaults = array(
		'pta'        => array( 'image_id' => 0, 'image_url' => '', 'label' => 'PTA Approved' ),
		'best_price' => array( 'image_id' => 0, 'image_url' => '', 'label' => 'Best Price Guarantee' ),
	);
	$gallery_top = isset( $settings['gallery_top'] ) && is_array( $settings['gallery_top'] )
		? array_merge( $gallery_top_defaults, $settings['gallery_top'] )
		: $gallery_top_defaults;

	// Gallery bottom icons.
	$gallery_bottom = isset( $settings['gallery_bottom_icons'] ) && is_array( $settings['gallery_bottom_icons'] )
		? $settings['gallery_bottom_icons']
		: [];
	if ( empty( $gallery_bottom ) ) {
		$gallery_bottom = [ [ 'image_id' => 0, 'image_url' => '', 'title' => '', 'subtitle' => '' ] ];
	}

	// Why Buy section.
	$why_buy_enabled = ! empty( $settings['why_buy_enabled'] );
	$why_buy_title   = $settings['why_buy_title'] ?? 'Why buy from Hitprice.pk?';
	$why_buy_items   = isset( $settings['why_buy_items'] ) && is_array( $settings['why_buy_items'] )
		? $settings['why_buy_items']
		: [];
	if ( empty( $why_buy_items ) ) {
		$why_buy_items = [ [ 'image_id' => 0, 'image_url' => '', 'title' => '', 'description' => '' ] ];
	}

	// Payment methods strip items.
	$payment_methods_defaults = [
		[ 'icon_class' => 'fa-regular fa-credit-card', 'title' => 'Cash on Delivery',    'subtitle' => 'Pay when you receive' ],
		[ 'icon_class' => 'fa-solid fa-box-open',       'title' => 'Open Parcel',          'subtitle' => 'Check before you pay' ],
		[ 'icon_class' => 'fa-solid fa-shield-halved',  'title' => '7-Day Check Warranty', 'subtitle' => 'Free return & replacement' ],
	];
	$payment_methods = ( isset( $settings['payment_methods'] ) && is_array( $settings['payment_methods'] ) && ! empty( $settings['payment_methods'] ) )
		? $settings['payment_methods']
		: $payment_methods_defaults;

	// Bottom trust strip items.
	$trust_strip_defaults = [
		[ 'icon_class' => 'fa-solid fa-shield-halved', 'title' => 'Safe & Secure Payments',  'subtitle' => 'Your payment information is 100% secure.' ],
		[ 'icon_class' => 'fa-solid fa-rotate-left',   'title' => 'Easy Returns',             'subtitle' => '7 days easy return & refund policy.' ],
		[ 'icon_class' => 'fa-solid fa-headset',       'title' => 'Customer Support 24/7',    'subtitle' => 'We are here to help you anytime.' ],
		[ 'icon_class' => 'fa-solid fa-medal',         'title' => '100% Satisfaction',        'subtitle' => 'We are committed to provide best quality products.' ],
	];
	$trust_strip_items = ( isset( $settings['trust_strip'] ) && is_array( $settings['trust_strip'] ) && count( $settings['trust_strip'] ) === 4 )
		? $settings['trust_strip']
		: $trust_strip_defaults;
	?>
	<div class="wrap hp-settings-wrap hp-spp-settings">
		<h1><?php esc_html_e( 'Single Product Page – Settings', 'hitprice-helper' ); ?></h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Settings saved.', 'hitprice-helper' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'hp_global_settings_save', 'hp_global_settings_nonce' ); ?>
			<input type="hidden" name="action" value="hp_save_global_settings">

			<!-- GALLERY ICONS -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Gallery Badge Overlays', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Two circular badge images shown on the gallery image. PTA badge only shows if "PTA Approved" is enabled on the product.', 'hitprice-helper' ); ?></p>
				</div>

			<table class="form-table">
				<?php foreach ( array( 'pta' => __( 'PTA Approved (top-left)', 'hitprice-helper' ), 'best_price' => __( 'Best Price Guarantee (top-right)', 'hitprice-helper' ) ) as $gkey => $glabel ) :
					$gt      = $gallery_top[ $gkey ];
					$gt_id   = absint( $gt['image_id'] );
					$gt_url  = esc_url( $gt['image_url'] );
					$gt_lbl  = esc_attr( $gt['label'] );
					$gt_thumb = $gt_id ? wp_get_attachment_image_url( $gt_id, 'thumbnail' ) : '';
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $glabel ); ?></th>
					<td>
						<div class="hp-gallery-top-field" data-key="<?php echo esc_attr( $gkey ); ?>">
							<div class="hp-icon-row__preview hp-gallery-top-preview">
								<?php if ( $gt_thumb ) : ?>
									<img src="<?php echo esc_url( $gt_thumb ); ?>" alt="" class="hp-icon-thumb">
								<?php else : ?>
									<span class="hp-icon-empty"><?php esc_html_e( 'No icon', 'hitprice-helper' ); ?></span>
								<?php endif; ?>
							</div>
							<input type="hidden" name="hp_gallery_top_<?php echo esc_attr( $gkey ); ?>[image_id]"  value="<?php echo esc_attr( $gt_id ); ?>" class="hp-gallery-top-image-id">
							<input type="hidden" name="hp_gallery_top_<?php echo esc_attr( $gkey ); ?>[image_url]" value="<?php echo esc_attr( $gt_url ); ?>" class="hp-gallery-top-image-url">
							<button type="button" class="button hp-gallery-top-upload-btn"><?php esc_html_e( 'Upload Icon', 'hitprice-helper' ); ?></button>
							<button type="button" class="button hp-gallery-top-remove-btn<?php echo $gt_id ? '' : ' hidden'; ?>"><?php esc_html_e( 'Remove', 'hitprice-helper' ); ?></button>
							<label style="margin-left:8px;">
								<?php esc_html_e( 'Label', 'hitprice-helper' ); ?>
								<input type="text" name="hp_gallery_top_<?php echo esc_attr( $gkey ); ?>[label]" value="<?php echo $gt_lbl; ?>" class="regular-text" placeholder="<?php echo esc_attr( $glabel ); ?>">
							</label>
						</div>
					</td>
				</tr>
				<?php endforeach; ?>
			</table>
			</div><!-- .hp-card -->

			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Gallery Trust Strip', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Up to 4 icons shown in a row at the bottom of the gallery card. Each item has an icon, a title, and an optional subtitle.', 'hitprice-helper' ); ?></p>
				</div>

			<div class="hp-icon-repeater" id="hp-gallery-bottom-repeater">
				<?php foreach ( $gallery_bottom as $i => $row ) :
					$img_id  = absint( $row['image_id'] ?? 0 );
					$img_url = esc_url( $row['image_url'] ?? '' );
					$title   = esc_attr( $row['title'] ?? '' );
					$sub     = esc_attr( $row['subtitle'] ?? '' );
					$thumb   = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				?>
				<div class="hp-icon-row hp-gallery-bottom-row">
					<div class="hp-icon-row__preview">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="hp-icon-thumb">
						<?php else : ?>
							<span class="hp-icon-empty"><?php esc_html_e( 'No icon', 'hitprice-helper' ); ?></span>
						<?php endif; ?>
					</div>
					<input type="hidden" name="hp_gallery_bottom_icons[<?php echo esc_attr( $i ); ?>][image_id]"  value="<?php echo esc_attr( $img_id ); ?>" class="hp-gbot-image-id">
					<input type="hidden" name="hp_gallery_bottom_icons[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( $img_url ); ?>" class="hp-gbot-image-url">
					<div class="hp-icon-row__fields">
						<button type="button" class="button hp-gbot-upload-btn"><?php esc_html_e( 'Upload Icon', 'hitprice-helper' ); ?></button>
						<button type="button" class="button hp-gbot-remove-img-btn<?php echo $img_id ? '' : ' hidden'; ?>"><?php esc_html_e( 'Remove Icon', 'hitprice-helper' ); ?></button>
						<label>
							<?php esc_html_e( 'Title', 'hitprice-helper' ); ?>
							<input type="text" name="hp_gallery_bottom_icons[<?php echo esc_attr( $i ); ?>][title]"    value="<?php echo $title; ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. 100% Genuine', 'hitprice-helper' ); ?>">
						</label>
						<label>
							<?php esc_html_e( 'Subtitle', 'hitprice-helper' ); ?>
							<input type="text" name="hp_gallery_bottom_icons[<?php echo esc_attr( $i ); ?>][subtitle]" value="<?php echo $sub; ?>"   class="widefat" placeholder="<?php esc_attr_e( 'e.g. Verified product', 'hitprice-helper' ); ?>">
						</label>
					</div>
					<button type="button" class="button button-link-delete hp-gbot-delete-row"><?php esc_html_e( 'Remove Row', 'hitprice-helper' ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button hp-btn-add-row" id="hp-gbot-add-row">
				<?php esc_html_e( '+ Add Icon', 'hitprice-helper' ); ?>
			</button>
			<p class="hp-card__note"><?php esc_html_e( 'Maximum 4 icons.', 'hitprice-helper' ); ?></p>
			</div><!-- .hp-card -->

			<!-- PRICE ICONS REPEATER -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'After Product Price — Icons Row', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Up to 6 icons shown in a row below the product price. Each item has an icon image, a title, and an optional subtitle.', 'hitprice-helper' ); ?></p>
				</div>

			<div class="hp-icon-repeater" id="hp-icon-repeater">
				<?php foreach ( $price_icons as $i => $row ) :
					$img_id  = absint( $row['image_id'] ?? 0 );
					$img_url = esc_url( $row['image_url'] ?? '' );
					$title   = esc_attr( $row['title'] ?? '' );
					$sub     = esc_attr( $row['subtitle'] ?? '' );
					$thumb   = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				?>
				<div class="hp-icon-row">
					<div class="hp-icon-row__preview">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="hp-icon-thumb">
						<?php else : ?>
							<span class="hp-icon-empty"><?php esc_html_e( 'No icon', 'hitprice-helper' ); ?></span>
						<?php endif; ?>
					</div>
					<input type="hidden" name="hp_price_icons[<?php echo esc_attr( $i ); ?>][image_id]"  value="<?php echo esc_attr( $img_id ); ?>" class="hp-icon-image-id">
					<input type="hidden" name="hp_price_icons[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( $img_url ); ?>" class="hp-icon-image-url">
					<div class="hp-icon-row__fields">
						<button type="button" class="button hp-icon-upload-btn"><?php esc_html_e( 'Upload Icon', 'hitprice-helper' ); ?></button>
						<button type="button" class="button hp-icon-remove-img-btn<?php echo $img_id ? '' : ' hidden'; ?>"><?php esc_html_e( 'Remove Icon', 'hitprice-helper' ); ?></button>
						<label>
							<?php esc_html_e( 'Title', 'hitprice-helper' ); ?>
							<input type="text" name="hp_price_icons[<?php echo esc_attr( $i ); ?>][title]"    value="<?php echo $title; ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. PTA Approved', 'hitprice-helper' ); ?>">
						</label>
						<label>
							<?php esc_html_e( 'Subtitle', 'hitprice-helper' ); ?>
							<input type="text" name="hp_price_icons[<?php echo esc_attr( $i ); ?>][subtitle]" value="<?php echo $sub; ?>"   class="widefat" placeholder="<?php esc_attr_e( 'e.g. All devices are PTA approved', 'hitprice-helper' ); ?>">
						</label>
					</div>
					<button type="button" class="button button-link-delete hp-icon-delete-row"><?php esc_html_e( 'Remove Row', 'hitprice-helper' ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button hp-btn-add-row" id="hp-icon-add-row">
				<?php esc_html_e( '+ Add Icon', 'hitprice-helper' ); ?>
			</button>
			<p class="hp-card__note"><?php esc_html_e( 'Maximum 6 icons.', 'hitprice-helper' ); ?></p>
			</div><!-- .hp-card -->

			<!-- PAYMENT METHODS STRIP -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'After add to cart button — Icons Row', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Up to 3 icons shown below the Add to Cart button (Cash on Delivery, Open Parcel, Warranty, etc.). Each item has an icon image, title, and subtitle.', 'hitprice-helper' ); ?></p>
				</div>

			<div class="hp-trust-strip-repeater" id="hp-pm-repeater">
				<?php foreach ( $payment_methods as $i => $row ) :
					$ic    = esc_attr( $row['icon_class'] ?? '' );
					$title = esc_attr( $row['title'] ?? '' );
					$sub   = esc_attr( $row['subtitle'] ?? '' );
				?>
				<div class="hp-trust-strip-row hp-pm-row">
					<span class="hp-trust-strip-row__num"><?php printf( esc_html__( 'Item %d', 'hitprice-helper' ), $i + 1 ); ?></span>
					<div class="hp-trust-strip-row__fields">
						<label>
							<?php esc_html_e( 'Icon Class', 'hitprice-helper' ); ?>
							<input type="text"
								name="hp_payment_methods[<?php echo esc_attr( $i ); ?>][icon_class]"
								value="<?php echo $ic; ?>"
								class="regular-text"
								placeholder="fa-regular fa-credit-card">
						</label>
						<label>
							<?php esc_html_e( 'Title', 'hitprice-helper' ); ?>
							<input type="text"
								name="hp_payment_methods[<?php echo esc_attr( $i ); ?>][title]"
								value="<?php echo $title; ?>"
								class="regular-text"
								placeholder="<?php esc_attr_e( 'e.g. Cash on Delivery', 'hitprice-helper' ); ?>">
						</label>
						<label class="hp-trust-strip-row__subtitle">
							<?php esc_html_e( 'Subtitle', 'hitprice-helper' ); ?>
							<input type="text"
								name="hp_payment_methods[<?php echo esc_attr( $i ); ?>][subtitle]"
								value="<?php echo $sub; ?>"
								class="widefat"
								placeholder="<?php esc_attr_e( 'e.g. Pay when you receive', 'hitprice-helper' ); ?>">
						</label>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button hp-btn-add-row" id="hp-pm-add-row">
				<?php esc_html_e( '+ Add Icon', 'hitprice-helper' ); ?>
			</button>
			<p class="hp-card__note"><?php esc_html_e( 'Maximum 3 icons.', 'hitprice-helper' ); ?></p>
			</div><!-- .hp-card -->

			<!-- SALE BANNER -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Sale Banner', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Shows a banner above the icons row with a fire emoji, main text, and subtext.', 'hitprice-helper' ); ?></p>
				</div>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Banner', 'hitprice-helper' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="hp_sale_banner_enabled" value="1"
								<?php checked( ! empty( $settings['sale_banner_enabled'] ) ); ?>>
							<?php esc_html_e( 'Show sale banner on product pages', 'hitprice-helper' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hp_sale_banner_text"><?php esc_html_e( 'Banner Text', 'hitprice-helper' ); ?></label></th>
					<td>
						<input type="text" id="hp_sale_banner_text" name="hp_sale_banner_text"
							value="<?php echo esc_attr( $settings['sale_banner_text'] ?? 'Weekly Sale Offer' ); ?>"
							class="regular-text"
							placeholder="Weekly Sale Offer">
					</td>
				</tr>
			</table>
			</div><!-- .hp-card -->

			<!-- VIEWERS RANGE -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Viewers Notice', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Controls the "N people viewing this product" notice. Each product shows a consistent random number within this range, changing once per day.', 'hitprice-helper' ); ?></p>
				</div>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="hp_viewers_min"><?php esc_html_e( 'Minimum', 'hitprice-helper' ); ?></label>
					</th>
					<td>
						<input type="number"
							id="hp_viewers_min"
							name="hp_viewers_min"
							value="<?php echo esc_attr( $settings['viewers_min'] ?? 12 ); ?>"
							min="1" max="999"
							class="small-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="hp_viewers_max"><?php esc_html_e( 'Maximum', 'hitprice-helper' ); ?></label>
					</th>
					<td>
						<input type="number"
							id="hp_viewers_max"
							name="hp_viewers_max"
							value="<?php echo esc_attr( $settings['viewers_max'] ?? 48 ); ?>"
							min="2" max="999"
							class="small-text">
					</td>
				</tr>
			</table>
			</div><!-- .hp-card -->

			<!-- WHY BUY SECTION -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Why Buy Strip — Before Key Highlights', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( '"Why buy from Hitprice.pk?" row — shown after the product layout, before Key Highlights. Up to 5 items with icon, title, and description.', 'hitprice-helper' ); ?></p>
				</div>
			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Section', 'hitprice-helper' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="hp_why_buy_enabled" value="1" <?php checked( $why_buy_enabled ); ?>>
							<?php esc_html_e( 'Show this section on product pages', 'hitprice-helper' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="hp_why_buy_title"><?php esc_html_e( 'Section Title', 'hitprice-helper' ); ?></label></th>
					<td>
						<input type="text" id="hp_why_buy_title" name="hp_why_buy_title"
							value="<?php echo esc_attr( $why_buy_title ); ?>"
							class="regular-text"
							placeholder="Why buy from Hitprice.pk?">
					</td>
				</tr>
			</table>

			<div class="hp-icon-repeater" id="hp-why-buy-repeater">
				<?php foreach ( $why_buy_items as $i => $row ) :
					$img_id  = absint( $row['image_id'] ?? 0 );
					$img_url = esc_url( $row['image_url'] ?? '' );
					$title   = esc_attr( $row['title'] ?? '' );
					$desc    = esc_attr( $row['description'] ?? '' );
					$thumb   = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
				?>
				<div class="hp-icon-row hp-why-buy-row">
					<div class="hp-icon-row__preview">
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" class="hp-icon-thumb">
						<?php else : ?>
							<span class="hp-icon-empty"><?php esc_html_e( 'No icon', 'hitprice-helper' ); ?></span>
						<?php endif; ?>
					</div>
					<input type="hidden" name="hp_why_buy_items[<?php echo esc_attr( $i ); ?>][image_id]"  value="<?php echo esc_attr( $img_id ); ?>" class="hp-wb-image-id">
					<input type="hidden" name="hp_why_buy_items[<?php echo esc_attr( $i ); ?>][image_url]" value="<?php echo esc_attr( $img_url ); ?>" class="hp-wb-image-url">
					<div class="hp-icon-row__fields">
						<button type="button" class="button hp-wb-upload-btn"><?php esc_html_e( 'Upload Icon', 'hitprice-helper' ); ?></button>
						<button type="button" class="button hp-wb-remove-img-btn<?php echo $img_id ? '' : ' hidden'; ?>"><?php esc_html_e( 'Remove Icon', 'hitprice-helper' ); ?></button>
						<label>
							<?php esc_html_e( 'Title', 'hitprice-helper' ); ?>
							<input type="text" name="hp_why_buy_items[<?php echo esc_attr( $i ); ?>][title]"       value="<?php echo $title; ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g. PTA Approved', 'hitprice-helper' ); ?>">
						</label>
						<label>
							<?php esc_html_e( 'Description', 'hitprice-helper' ); ?>
							<input type="text" name="hp_why_buy_items[<?php echo esc_attr( $i ); ?>][description]" value="<?php echo $desc; ?>"  class="widefat" placeholder="<?php esc_attr_e( 'e.g. All our phones are officially PTA approved.', 'hitprice-helper' ); ?>">
						</label>
					</div>
					<button type="button" class="button button-link-delete hp-wb-delete-row"><?php esc_html_e( 'Remove Row', 'hitprice-helper' ); ?></button>
				</div>
				<?php endforeach; ?>
			</div>

			<button type="button" class="button hp-btn-add-row" id="hp-wb-add-row">
				<?php esc_html_e( '+ Add Item', 'hitprice-helper' ); ?>
			</button>
			<p class="hp-card__note"><?php esc_html_e( 'Maximum 5 items.', 'hitprice-helper' ); ?></p>
			</div><!-- .hp-card -->

			<!-- BOTTOM TRUST STRIP -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Bottom Trust Strip', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Exactly 4 items shown in a horizontal strip below the product tabs. Use Font Awesome class names for icons (e.g. fa-solid fa-shield-halved).', 'hitprice-helper' ); ?></p>
				</div>

				<div class="hp-trust-strip-repeater">
					<?php foreach ( $trust_strip_items as $i => $row ) :
						$ic  = esc_attr( $row['icon_class'] ?? '' );
						$ttl = esc_attr( $row['title'] ?? '' );
						$sub = esc_attr( $row['subtitle'] ?? '' );
					?>
					<div class="hp-trust-strip-row">
						<span class="hp-trust-strip-row__num"><?php printf( esc_html__( 'Item %d', 'hitprice-helper' ), $i + 1 ); ?></span>
						<div class="hp-trust-strip-row__fields">
							<label>
								<?php esc_html_e( 'Icon Class', 'hitprice-helper' ); ?>
								<input type="text"
									name="hp_trust_strip[<?php echo esc_attr( $i ); ?>][icon_class]"
									value="<?php echo $ic; ?>"
									class="regular-text"
									placeholder="fa-solid fa-shield-halved">
							</label>
							<label>
								<?php esc_html_e( 'Title', 'hitprice-helper' ); ?>
								<input type="text"
									name="hp_trust_strip[<?php echo esc_attr( $i ); ?>][title]"
									value="<?php echo $ttl; ?>"
									class="regular-text"
									placeholder="<?php esc_attr_e( 'e.g. Safe & Secure Payments', 'hitprice-helper' ); ?>">
							</label>
							<label class="hp-trust-strip-row__subtitle">
								<?php esc_html_e( 'Subtitle', 'hitprice-helper' ); ?>
								<input type="text"
									name="hp_trust_strip[<?php echo esc_attr( $i ); ?>][subtitle]"
									value="<?php echo $sub; ?>"
									class="widefat"
									placeholder="<?php esc_attr_e( 'e.g. Your payment information is 100% secure.', 'hitprice-helper' ); ?>">
							</label>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div><!-- .hp-card -->

			<!-- SHIPPING POLICY -->
			<div class="hp-card">
				<div class="hp-card__header">
					<h2 class="hp-card__title"><?php esc_html_e( 'Shipping & Returns Policy', 'hitprice-helper' ); ?></h2>
					<p class="hp-card__desc"><?php esc_html_e( 'Shown in the "Shipping & Returns" tab on all product pages.', 'hitprice-helper' ); ?></p>
				</div>
				<?php
				wp_editor(
					wp_kses_post( $settings['shipping_policy'] ?? '' ),
					'hp_shipping_policy',
					[
						'textarea_name' => 'hp_shipping_policy',
						'media_buttons' => false,
						'teeny'         => false,
						'textarea_rows' => 12,
					]
				);
				?>
			</div><!-- .hp-card -->

			<div class="hp-submit-wrap">
				<?php submit_button( __( 'Save Settings', 'hitprice-helper' ), 'primary large', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}

// hp_get_badge_keys(), hp_get_global_setting(), hp_get_global_badge()
// moved to inc/product/product-data.php so they load on the front end.
