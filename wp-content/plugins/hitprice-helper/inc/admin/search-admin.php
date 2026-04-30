<?php
/**
 * Search Analytics admin page.
 *
 * @package HitPriceHelper
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const HP_SEARCH_ADMIN_SLUG = 'hp-search-analytics';
const HP_SEARCH_ADMIN_CAP  = 'manage_options';

/**
 * Register the admin menu.
 */
function hp_search_admin_register_menu() {
	add_menu_page(
		__( 'Search Analytics', 'hitprice' ),
		__( 'Search Analytics', 'hitprice' ),
		HP_SEARCH_ADMIN_CAP,
		HP_SEARCH_ADMIN_SLUG,
		'hp_search_admin_render_page',
		'dashicons-search',
		58
	);
}
add_action( 'admin_menu', 'hp_search_admin_register_menu' );

/**
 * Register settings.
 */
function hp_search_admin_register_settings() {
	register_setting(
		'hp_search_settings',
		'hp_search_logging_enabled',
		array(
			'type'              => 'boolean',
			'sanitize_callback' => static function ( $v ) {
				return $v ? 1 : 0;
			},
			'default'           => 1,
		)
	);

	register_setting(
		'hp_search_settings',
		'hp_search_trending_fallback',
		array(
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => '',
		)
	);
}
add_action( 'admin_init', 'hp_search_admin_register_settings' );

/**
 * Get current admin tab.
 *
 * @return string
 */
function hp_search_admin_current_tab() {
	$allowed = array( 'overview', 'top', 'zero', 'settings' );
	$tab     = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return in_array( $tab, $allowed, true ) ? $tab : 'overview';
}

/**
 * Render the admin page.
 */
function hp_search_admin_render_page() {
	if ( ! current_user_can( HP_SEARCH_ADMIN_CAP ) ) {
		wp_die( esc_html__( 'You do not have permission to view this page.', 'hitprice' ) );
	}

	$tab     = hp_search_admin_current_tab();
	$tabs    = array(
		'overview' => __( 'Overview', 'hitprice' ),
		'top'      => __( 'Top Searches', 'hitprice' ),
		'zero'     => __( 'Zero-Result', 'hitprice' ),
		'settings' => __( 'Settings', 'hitprice' ),
	);
	$base_url = admin_url( 'admin.php?page=' . HP_SEARCH_ADMIN_SLUG );
	?>
	<div class="wrap hp-search-admin">
		<h1><?php esc_html_e( 'Search Analytics', 'hitprice' ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tab', $slug, $base_url ) ); ?>" class="nav-tab <?php echo $tab === $slug ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<?php
		switch ( $tab ) {
			case 'top':
				hp_search_admin_render_top();
				break;
			case 'zero':
				hp_search_admin_render_zero();
				break;
			case 'settings':
				hp_search_admin_render_settings();
				break;
			case 'overview':
			default:
				hp_search_admin_render_overview();
				break;
		}
		?>
	</div>
	<?php
}

/**
 * Overview tab.
 */
function hp_search_admin_render_overview() {
	$summary    = hp_get_search_summary( 7 );
	$volume     = hp_get_search_volume_daily( 14 );
	$top_clicks = hp_get_top_clicked_products( 30, 10 );

	$max_volume = 0;
	foreach ( $volume as $row ) {
		$max_volume = max( $max_volume, (int) $row->hits );
	}
	?>
	<div class="hp-search-cards">
		<div class="hp-search-card">
			<span class="hp-search-card__label"><?php esc_html_e( 'Searches (7d)', 'hitprice' ); ?></span>
			<strong class="hp-search-card__value"><?php echo esc_html( number_format_i18n( $summary['total_searches'] ) ); ?></strong>
		</div>
		<div class="hp-search-card">
			<span class="hp-search-card__label"><?php esc_html_e( 'Unique terms (7d)', 'hitprice' ); ?></span>
			<strong class="hp-search-card__value"><?php echo esc_html( number_format_i18n( $summary['unique_terms'] ) ); ?></strong>
		</div>
		<div class="hp-search-card">
			<span class="hp-search-card__label"><?php esc_html_e( 'Zero-result (7d)', 'hitprice' ); ?></span>
			<strong class="hp-search-card__value"><?php echo esc_html( number_format_i18n( $summary['zero_result_searches'] ) ); ?></strong>
		</div>
		<div class="hp-search-card">
			<span class="hp-search-card__label"><?php esc_html_e( 'Suggestion clicks (7d)', 'hitprice' ); ?></span>
			<strong class="hp-search-card__value"><?php echo esc_html( number_format_i18n( $summary['clicks'] ) ); ?></strong>
		</div>
	</div>

	<h2><?php esc_html_e( 'Daily volume (last 14 days)', 'hitprice' ); ?></h2>
	<?php if ( empty( $volume ) ) : ?>
		<p><?php esc_html_e( 'No search data yet.', 'hitprice' ); ?></p>
	<?php else : ?>
		<div class="hp-search-volume">
			<?php foreach ( $volume as $row ) :
				$hits   = (int) $row->hits;
				$pct    = $max_volume > 0 ? max( 4, round( ( $hits / $max_volume ) * 100 ) ) : 0;
				$day    = (string) $row->day;
				?>
				<div class="hp-search-volume__row">
					<span class="hp-search-volume__day"><?php echo esc_html( $day ); ?></span>
					<span class="hp-search-volume__bar"><span style="width: <?php echo esc_attr( (string) $pct ); ?>%"></span></span>
					<span class="hp-search-volume__count"><?php echo esc_html( number_format_i18n( $hits ) ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<h2><?php esc_html_e( 'Top clicked products (last 30 days)', 'hitprice' ); ?></h2>
	<?php if ( empty( $top_clicks ) ) : ?>
		<p><?php esc_html_e( 'No suggestion clicks tracked yet.', 'hitprice' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Product', 'hitprice' ); ?></th>
					<th><?php esc_html_e( 'Clicks', 'hitprice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $top_clicks as $row ) :
					$pid     = (int) $row->clicked_product_id;
					$product = function_exists( 'wc_get_product' ) ? wc_get_product( $pid ) : null;
					$title   = $product instanceof WC_Product ? $product->get_name() : sprintf( '#%d', $pid );
					$edit    = get_edit_post_link( $pid );
					?>
					<tr>
						<td>
							<?php if ( $edit ) : ?>
								<a href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( $title ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $title ); ?>
							<?php endif; ?>
						</td>
						<td><?php echo esc_html( number_format_i18n( (int) $row->clicks ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>

	<style>
		.hp-search-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin: 16px 0 24px; }
		.hp-search-card { background: #fff; border: 1px solid #e1e5eb; padding: 14px 16px; border-radius: 6px; }
		.hp-search-card__label { display: block; font-size: 12px; color: #50575e; margin-bottom: 4px; }
		.hp-search-card__value { display: block; font-size: 24px; line-height: 1.2; color: #1d2327; }
		.hp-search-volume { background: #fff; border: 1px solid #e1e5eb; border-radius: 6px; padding: 12px 14px; }
		.hp-search-volume__row { display: grid; grid-template-columns: 110px 1fr 60px; gap: 10px; align-items: center; padding: 4px 0; font-size: 13px; }
		.hp-search-volume__bar { height: 10px; background: #f0f0f1; border-radius: 4px; overflow: hidden; }
		.hp-search-volume__bar > span { display: block; height: 100%; background: #0053e2; border-radius: 4px; }
		.hp-search-volume__count { text-align: right; }
	</style>
	<?php
}

/**
 * Top searches tab.
 */
function hp_search_admin_render_top() {
	$rows = hp_get_top_searches( 30, 50 );
	?>
	<p><?php esc_html_e( 'Most-searched terms in the last 30 days.', 'hitprice' ); ?></p>
	<?php if ( empty( $rows ) ) : ?>
		<p><?php esc_html_e( 'No search data yet.', 'hitprice' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Term', 'hitprice' ); ?></th>
					<th><?php esc_html_e( 'Searches', 'hitprice' ); ?></th>
					<th><?php esc_html_e( 'Avg results', 'hitprice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $row->term ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (int) $row->hits ) ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (float) $row->avg_results, 1 ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php
}

/**
 * Zero-result searches tab.
 */
function hp_search_admin_render_zero() {
	$rows = hp_get_zero_result_searches( 30, 100 );
	?>
	<p><?php esc_html_e( 'Terms users searched for that returned no products. Use these for product gaps and content marketing.', 'hitprice' ); ?></p>
	<?php if ( empty( $rows ) ) : ?>
		<p><?php esc_html_e( 'No zero-result searches recorded.', 'hitprice' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Term', 'hitprice' ); ?></th>
					<th><?php esc_html_e( 'Times searched', 'hitprice' ); ?></th>
					<th><?php esc_html_e( 'Last seen', 'hitprice' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $row->term ); ?></td>
						<td><?php echo esc_html( number_format_i18n( (int) $row->hits ) ); ?></td>
						<td><?php echo esc_html( (string) $row->last_seen ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php
}

/**
 * Settings tab.
 */
function hp_search_admin_render_settings() {
	$logging  = (bool) get_option( 'hp_search_logging_enabled', 1 );
	$trending = (string) get_option( 'hp_search_trending_fallback', '' );
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'hp_search_settings' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="hp_search_logging_enabled"><?php esc_html_e( 'Search logging', 'hitprice' ); ?></label>
				</th>
				<td>
					<label>
						<input type="checkbox" id="hp_search_logging_enabled" name="hp_search_logging_enabled" value="1" <?php checked( $logging ); ?> />
						<?php esc_html_e( 'Record search terms and result counts for analytics.', 'hitprice' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Disable to stop writing to the search log table. Existing data is preserved.', 'hitprice' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="hp_search_trending_fallback"><?php esc_html_e( 'Trending fallback terms', 'hitprice' ); ?></label>
				</th>
				<td>
					<input type="text" id="hp_search_trending_fallback" name="hp_search_trending_fallback" value="<?php echo esc_attr( $trending ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'iPhone 15, Samsung TV, Air Conditioner, Washing Machine', 'hitprice' ); ?>" />
					<p class="description"><?php esc_html_e( 'Comma-separated. Shown in the search overlay when there is not enough real search history yet.', 'hitprice' ); ?></p>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}
