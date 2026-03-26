<?php
/**
 * Topics management admin page.
 *
 * Add new topics via inline form, view/filter topic list,
 * and manage topic status with AJAX actions.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content_types = HP_AI_Topic::CONTENT_TYPES;
$statuses      = HP_AI_Topic::STATUSES;
$status_counts = HP_AI_Topic::get_status_counts();
$total_count   = array_sum( $status_counts );

// Server-side initial load.
$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
$filter_type   = isset( $_GET['content_type'] ) ? sanitize_text_field( wp_unslash( $_GET['content_type'] ) ) : '';
$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$paged         = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

$result = HP_AI_Topic::get_list( array(
	'status'       => $filter_status,
	'content_type' => $filter_type,
	'search'       => $search,
	'page'         => $paged,
	'per_page'     => 20,
) );

$topics      = $result['items'];
$total_items = $result['total'];
$total_pages = ceil( $total_items / 20 );
?>

<div class="wrap hp-ai-wrap">
	<h1>
		<?php esc_html_e( 'Topics', 'hitprice-content-engine' ); ?>
		<button type="button" id="hp-ai-toggle-add-form" class="page-title-action"><?php esc_html_e( 'Add New Topic', 'hitprice-content-engine' ); ?></button>
	</h1>

	<!-- Add Topic Form (hidden by default) -->
	<div id="hp-ai-add-topic-form" class="hp-ai-section" style="display:none;">
		<h2><?php esc_html_e( 'Add New Topic', 'hitprice-content-engine' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="hp-ai-topic-title"><?php esc_html_e( 'Title', 'hitprice-content-engine' ); ?> <span class="required">*</span></label></th>
				<td><input type="text" id="hp-ai-topic-title" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., Samsung S25 Ultra vs iPhone 16 Pro Max', 'hitprice-content-engine' ); ?>" /></td>
			</tr>
			<tr>
				<th><label for="hp-ai-topic-type"><?php esc_html_e( 'Content Type', 'hitprice-content-engine' ); ?></label></th>
				<td>
					<select id="hp-ai-topic-type">
						<?php foreach ( $content_types as $key => $label ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr>
				<th><label for="hp-ai-topic-keywords"><?php esc_html_e( 'Keywords', 'hitprice-content-engine' ); ?></label></th>
				<td>
					<textarea id="hp-ai-topic-keywords" rows="2" class="large-text" placeholder="<?php esc_attr_e( 'samsung, iphone, camera, battery, comparison', 'hitprice-content-engine' ); ?>"></textarea>
					<p class="description"><?php esc_html_e( 'Comma-separated keywords for AI prompt context.', 'hitprice-content-engine' ); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="hp-ai-topic-month"><?php esc_html_e( 'Target Month', 'hitprice-content-engine' ); ?></label></th>
				<td><input type="month" id="hp-ai-topic-month" value="<?php echo esc_attr( gmdate( 'Y-m' ) ); ?>" /></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Priority', 'hitprice-content-engine' ); ?></th>
				<td>
					<label>
						<input type="checkbox" id="hp-ai-topic-priority" value="1" />
						<?php esc_html_e( 'High priority (generate first)', 'hitprice-content-engine' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<p>
			<button type="button" id="hp-ai-save-topic" class="button button-primary"><?php esc_html_e( 'Add Topic', 'hitprice-content-engine' ); ?></button>
			<button type="button" id="hp-ai-cancel-topic" class="button"><?php esc_html_e( 'Cancel', 'hitprice-content-engine' ); ?></button>
		</p>
		<div id="hp-ai-topic-result" class="hp-ai-generate-result" style="display:none;"></div>
	</div>

	<!-- Filters -->
	<div class="hp-ai-filter-bar">
		<ul class="subsubsub">
			<li>
				<a href="<?php echo esc_url( remove_query_arg( array( 'status', 'paged' ) ) ); ?>" class="<?php echo empty( $filter_status ) ? 'current' : ''; ?>">
					<?php esc_html_e( 'All', 'hitprice-content-engine' ); ?>
					<span class="count">(<?php echo esc_html( $total_count ); ?>)</span>
				</a> |
			</li>
			<?php foreach ( $statuses as $s ) : ?>
				<li>
					<a href="<?php echo esc_url( add_query_arg( array( 'status' => $s, 'paged' => 1 ) ) ); ?>" class="<?php echo $filter_status === $s ? 'current' : ''; ?>">
						<?php echo esc_html( ucfirst( $s ) ); ?>
						<span class="count">(<?php echo esc_html( $status_counts[ $s ] ); ?>)</span>
					</a>
					<?php echo $s !== end( $statuses ) ? '|' : ''; ?>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="hp-ai-filter-right">
			<select id="hp-ai-filter-type" onchange="this.form.submit()" form="hp-ai-filter-form">
				<option value=""><?php esc_html_e( 'All Types', 'hitprice-content-engine' ); ?></option>
				<?php foreach ( $content_types as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filter_type, $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<form id="hp-ai-filter-form" method="get" action="" class="hp-ai-search-form">
				<input type="hidden" name="page" value="hp-ai-content-topics" />
				<?php if ( $filter_status ) : ?>
					<input type="hidden" name="status" value="<?php echo esc_attr( $filter_status ); ?>" />
				<?php endif; ?>
				<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search topics...', 'hitprice-content-engine' ); ?>" />
				<button type="submit" class="button"><?php esc_html_e( 'Search', 'hitprice-content-engine' ); ?></button>
			</form>
		</div>
	</div>

	<!-- Topics Table -->
	<table class="widefat striped hp-ai-topics-table">
		<thead>
			<tr>
				<th class="hp-ai-col-title"><?php esc_html_e( 'Title', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-type"><?php esc_html_e( 'Type', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-status"><?php esc_html_e( 'Status', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-priority"><?php esc_html_e( 'Priority', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-month"><?php esc_html_e( 'Target', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-date"><?php esc_html_e( 'Created', 'hitprice-content-engine' ); ?></th>
				<th class="hp-ai-col-actions"><?php esc_html_e( 'Actions', 'hitprice-content-engine' ); ?></th>
			</tr>
		</thead>
		<tbody id="hp-ai-topics-body">
			<?php if ( empty( $topics ) ) : ?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No topics found. Click "Add New Topic" to get started.', 'hitprice-content-engine' ); ?></td>
				</tr>
			<?php else : ?>
				<?php foreach ( $topics as $topic ) : ?>
					<tr id="hp-ai-topic-row-<?php echo esc_attr( $topic->id ); ?>">
						<td class="hp-ai-col-title">
							<strong><?php echo esc_html( $topic->title ); ?></strong>
							<?php if ( $topic->keywords ) : ?>
								<br><span class="description"><?php echo esc_html( wp_trim_words( $topic->keywords, 8 ) ); ?></span>
							<?php endif; ?>
						</td>
						<td class="hp-ai-col-type">
							<span class="hp-ai-badge hp-ai-badge--info">
								<?php echo esc_html( $content_types[ $topic->content_type ] ?? $topic->content_type ); ?>
							</span>
						</td>
						<td class="hp-ai-col-status">
							<?php
							$status_class = 'info';
							if ( 'pending' === $topic->status ) {
								$status_class = 'warning';
							} elseif ( 'generated' === $topic->status ) {
								$status_class = 'success';
							} elseif ( 'skipped' === $topic->status ) {
								$status_class = 'error';
							}
							?>
							<span class="hp-ai-badge hp-ai-badge--<?php echo esc_attr( $status_class ); ?>">
								<?php echo esc_html( ucfirst( $topic->status ) ); ?>
							</span>
						</td>
						<td class="hp-ai-col-priority">
							<?php if ( $topic->priority ) : ?>
								<span class="hp-ai-badge hp-ai-badge--error"><?php esc_html_e( 'High', 'hitprice-content-engine' ); ?></span>
							<?php else : ?>
								<span class="description"><?php esc_html_e( 'Normal', 'hitprice-content-engine' ); ?></span>
							<?php endif; ?>
						</td>
						<td class="hp-ai-col-month"><?php echo $topic->month_target ? esc_html( $topic->month_target ) : '—'; ?></td>
						<td class="hp-ai-col-date"><?php echo esc_html( wp_date( 'M j, Y', strtotime( $topic->created_at ) ) ); ?></td>
						<td class="hp-ai-col-actions">
							<?php if ( 'pending' === $topic->status ) : ?>
								<button type="button" class="button button-small hp-ai-skip-topic" data-id="<?php echo esc_attr( $topic->id ); ?>">
									<?php esc_html_e( 'Skip', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>
							<button type="button" class="button button-small button-link-delete hp-ai-delete-topic" data-id="<?php echo esc_attr( $topic->id ); ?>">
								<?php esc_html_e( 'Delete', 'hitprice-content-engine' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					printf(
						/* translators: %s: number of items */
						esc_html( _n( '%s item', '%s items', $total_items, 'hitprice-content-engine' ) ),
						esc_html( number_format_i18n( $total_items ) )
					);
					?>
				</span>
				<span class="pagination-links">
					<?php
					echo wp_kses_post( paginate_links( array(
						'base'    => add_query_arg( 'paged', '%#%' ),
						'format'  => '',
						'current' => $paged,
						'total'   => $total_pages,
						'type'    => 'plain',
					) ) );
					?>
				</span>
			</div>
		</div>
	<?php endif; ?>
</div>
