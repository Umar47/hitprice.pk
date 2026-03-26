<?php
/**
 * Drafts listing admin page.
 *
 * Shows generated social posts and blog drafts in separate tabs
 * with status filters and quick actions.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'social';
if ( ! in_array( $active_tab, array( 'social', 'blog' ), true ) ) {
	$active_tab = 'social';
}

$filter_status = isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '';
$paged         = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;

// Fetch data based on active tab.
if ( 'social' === $active_tab ) {
	$result        = HP_AI_Draft::get_social_list( array( 'status' => $filter_status, 'page' => $paged ) );
	$status_counts = HP_AI_Draft::get_social_status_counts();
	$all_statuses  = array( 'draft', 'approved', 'posted', 'rejected' );
} else {
	$result        = HP_AI_Draft::get_blog_list( array( 'status' => $filter_status, 'page' => $paged ) );
	$status_counts = HP_AI_Draft::get_blog_status_counts();
	$all_statuses  = array( 'draft', 'approved', 'published', 'rejected' );
}

$items       = $result['items'];
$total_items = $result['total'];
$total_pages = ceil( $total_items / 20 );
$total_count = array_sum( $status_counts );

$base_url = admin_url( 'admin.php?page=hp-ai-content-drafts' );
?>

<div class="wrap hp-ai-wrap">
	<h1><?php esc_html_e( 'Generated Drafts', 'hitprice-content-engine' ); ?></h1>

	<!-- Tabs -->
	<nav class="nav-tab-wrapper hp-ai-tab-wrapper">
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'social', 'status' => '', 'paged' => 1 ), $base_url ) ); ?>"
		   class="nav-tab <?php echo 'social' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Social Posts', 'hitprice-content-engine' ); ?>
		</a>
		<a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'blog', 'status' => '', 'paged' => 1 ), $base_url ) ); ?>"
		   class="nav-tab <?php echo 'blog' === $active_tab ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Blog Articles', 'hitprice-content-engine' ); ?>
		</a>
	</nav>

	<!-- Status Filters -->
	<ul class="subsubsub">
		<li>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $active_tab, 'status' => '', 'paged' => 1 ), $base_url ) ); ?>"
			   class="<?php echo empty( $filter_status ) ? 'current' : ''; ?>">
				<?php esc_html_e( 'All', 'hitprice-content-engine' ); ?>
				<span class="count">(<?php echo esc_html( $total_count ); ?>)</span>
			</a> |
		</li>
		<?php foreach ( $all_statuses as $i => $s ) : ?>
			<li>
				<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $active_tab, 'status' => $s, 'paged' => 1 ), $base_url ) ); ?>"
				   class="<?php echo $filter_status === $s ? 'current' : ''; ?>">
					<?php echo esc_html( ucfirst( $s ) ); ?>
					<span class="count">(<?php echo esc_html( $status_counts[ $s ] ); ?>)</span>
				</a>
				<?php echo $i < count( $all_statuses ) - 1 ? '|' : ''; ?>
			</li>
		<?php endforeach; ?>
	</ul>

	<br class="clear" />

	<?php if ( empty( $items ) ) : ?>

		<div class="hp-ai-section hp-ai-empty-state">
			<p>
				<?php
				if ( 'social' === $active_tab ) {
					esc_html_e( 'No social post drafts found. Generate content from the Dashboard.', 'hitprice-content-engine' );
				} else {
					esc_html_e( 'No blog drafts found. Generate content from the Dashboard.', 'hitprice-content-engine' );
				}
				?>
			</p>
		</div>

	<?php elseif ( 'social' === $active_tab ) : ?>

		<!-- Social Posts Cards -->
		<div class="hp-ai-drafts-grid" id="hp-ai-drafts-grid">
			<?php foreach ( $items as $item ) : ?>
				<div class="hp-ai-draft-card" id="hp-ai-draft-<?php echo esc_attr( $item->id ); ?>">
					<div class="hp-ai-draft-header">
						<?php
						$s_class = 'info';
						if ( 'draft' === $item->status ) {
							$s_class = 'warning';
						} elseif ( 'approved' === $item->status ) {
							$s_class = 'info';
						} elseif ( 'posted' === $item->status ) {
							$s_class = 'success';
						} elseif ( 'rejected' === $item->status ) {
							$s_class = 'error';
						}
						?>
						<span class="hp-ai-badge hp-ai-badge--<?php echo esc_attr( $s_class ); ?>">
							<?php echo esc_html( ucfirst( $item->status ) ); ?>
						</span>
						<?php if ( ! empty( $item->content_type ) ) : ?>
							<span class="hp-ai-badge hp-ai-badge--info">
								<?php echo esc_html( HP_AI_Topic::CONTENT_TYPES[ $item->content_type ] ?? $item->content_type ); ?>
							</span>
						<?php endif; ?>
						<?php if ( ! empty( $item->platform ) ) : ?>
							<span class="hp-ai-draft-platform"><?php echo esc_html( ucfirst( $item->platform ) ); ?></span>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $item->topic_title ) ) : ?>
						<div class="hp-ai-draft-topic"><?php echo esc_html( $item->topic_title ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item->hook_line ) ) : ?>
						<div class="hp-ai-draft-hook"><?php echo esc_html( $item->hook_line ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item->caption ) ) : ?>
						<div class="hp-ai-draft-caption"><?php echo esc_html( wp_trim_words( $item->caption, 30 ) ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item->hashtags ) ) : ?>
						<div class="hp-ai-draft-hashtags"><?php echo esc_html( wp_trim_words( $item->hashtags, 10 ) ); ?></div>
					<?php endif; ?>

					<?php if ( ! empty( $item->image_text ) ) : ?>
						<div class="hp-ai-draft-meta">
							<strong><?php esc_html_e( 'Image Text:', 'hitprice-content-engine' ); ?></strong>
							<?php echo esc_html( $item->image_text ); ?>
						</div>
					<?php endif; ?>

					<div class="hp-ai-draft-footer">
						<span class="hp-ai-draft-date"><?php echo esc_html( wp_date( 'M j, Y', strtotime( $item->created_at ) ) ); ?></span>
						<div class="hp-ai-draft-actions">
							<?php if ( ! empty( $item->caption ) ) : ?>
								<button type="button" class="button button-small hp-ai-copy-btn" data-copy="<?php echo esc_attr( $item->caption . "\n\n" . $item->hashtags ); ?>">
									<?php esc_html_e( 'Copy', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( 'draft' === $item->status ) : ?>
								<button type="button" class="button button-small button-primary hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="social" data-status="approved">
									<?php esc_html_e( 'Approve', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( 'approved' === $item->status ) : ?>
								<button type="button" class="button button-small button-primary hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="social" data-status="posted">
									<?php esc_html_e( 'Mark Posted', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( in_array( $item->status, array( 'draft', 'approved' ), true ) ) : ?>
								<button type="button" class="button button-small hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="social" data-status="rejected">
									<?php esc_html_e( 'Reject', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<button type="button" class="button button-small button-link-delete hp-ai-delete-draft-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="social">
								<?php esc_html_e( 'Delete', 'hitprice-content-engine' ); ?>
							</button>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

	<?php else : ?>

		<!-- Blog Drafts Table -->
		<table class="widefat striped hp-ai-blog-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Title', 'hitprice-content-engine' ); ?></th>
					<th><?php esc_html_e( 'Topic', 'hitprice-content-engine' ); ?></th>
					<th><?php esc_html_e( 'Focus Keyword', 'hitprice-content-engine' ); ?></th>
					<th><?php esc_html_e( 'Status', 'hitprice-content-engine' ); ?></th>
					<th><?php esc_html_e( 'Created', 'hitprice-content-engine' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'hitprice-content-engine' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $items as $item ) : ?>
					<?php
					$s_class = 'info';
					if ( 'draft' === $item->status ) {
						$s_class = 'warning';
					} elseif ( 'approved' === $item->status ) {
						$s_class = 'info';
					} elseif ( 'published' === $item->status ) {
						$s_class = 'success';
					} elseif ( 'rejected' === $item->status ) {
						$s_class = 'error';
					}
					?>
					<tr id="hp-ai-blog-row-<?php echo esc_attr( $item->id ); ?>">
						<td>
							<strong><?php echo esc_html( $item->title ); ?></strong>
							<?php if ( ! empty( $item->excerpt ) ) : ?>
								<br><span class="description"><?php echo esc_html( wp_trim_words( $item->excerpt, 12 ) ); ?></span>
							<?php endif; ?>
						</td>
						<td><?php echo ! empty( $item->topic_title ) ? esc_html( $item->topic_title ) : '—'; ?></td>
						<td><?php echo ! empty( $item->focus_keyword ) ? esc_html( $item->focus_keyword ) : '—'; ?></td>
						<td><span class="hp-ai-badge hp-ai-badge--<?php echo esc_attr( $s_class ); ?>"><?php echo esc_html( ucfirst( $item->status ) ); ?></span></td>
						<td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $item->created_at ) ) ); ?></td>
						<td>
							<?php if ( 'draft' === $item->status ) : ?>
								<button type="button" class="button button-small button-primary hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="blog" data-status="approved">
									<?php esc_html_e( 'Approve', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( 'approved' === $item->status ) : ?>
								<button type="button" class="button button-small button-primary hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="blog" data-status="published">
									<?php esc_html_e( 'Publish', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<?php if ( in_array( $item->status, array( 'draft', 'approved' ), true ) ) : ?>
								<button type="button" class="button button-small hp-ai-draft-status-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="blog" data-status="rejected">
									<?php esc_html_e( 'Reject', 'hitprice-content-engine' ); ?>
								</button>
							<?php endif; ?>

							<button type="button" class="button button-small button-link-delete hp-ai-delete-draft-btn" data-id="<?php echo esc_attr( $item->id ); ?>" data-type="blog">
								<?php esc_html_e( 'Delete', 'hitprice-content-engine' ); ?>
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

	<?php endif; ?>

	<!-- Pagination -->
	<?php if ( $total_pages > 1 ) : ?>
		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					printf(
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
