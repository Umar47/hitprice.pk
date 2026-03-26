<?php
/**
 * Dashboard admin page.
 *
 * Shows live generation stats, quick generate action, and recent activity.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$topic_counts   = HP_AI_Topic::get_status_counts();
$social_counts  = HP_AI_Draft::get_social_status_counts();
$blog_counts    = HP_AI_Draft::get_blog_status_counts();
$posted_month   = HP_AI_Draft::get_monthly_posted_count();
$generated_month = HP_AI_Draft::get_monthly_count();
$monthly_target = (int) get_option( 'hp_ai_monthly_target', 30 );
$drafts_ready   = $social_counts['draft'] + $blog_counts['draft'];
$pending_topics = HP_AI_Topic::get_pending_for_select();
$current_mode   = get_option( 'hp_ai_mode', 'mock' );
$recent_logs    = HP_AI_Logger::get_recent( 10 );
?>

<div class="wrap hp-ai-wrap">
	<h1><?php esc_html_e( 'HitPrice AI — Dashboard', 'hitprice-content-engine' ); ?></h1>

	<!-- Mode indicator -->
	<div class="hp-ai-mode-bar hp-ai-mode-bar--<?php echo esc_attr( $current_mode ); ?>">
		<?php if ( 'mock' === $current_mode ) : ?>
			<?php esc_html_e( 'Mock Mode — No API calls will be made', 'hitprice-content-engine' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'Live Mode — AI API is active', 'hitprice-content-engine' ); ?>
		<?php endif; ?>
	</div>

	<!-- Stats -->
	<div class="hp-ai-stats-row">
		<div class="hp-ai-stat-card">
			<span class="hp-ai-stat-number"><?php echo esc_html( $topic_counts['pending'] ); ?></span>
			<span class="hp-ai-stat-label"><?php esc_html_e( 'Topics Pending', 'hitprice-content-engine' ); ?></span>
		</div>
		<div class="hp-ai-stat-card">
			<span class="hp-ai-stat-number"><?php echo esc_html( $drafts_ready ); ?></span>
			<span class="hp-ai-stat-label"><?php esc_html_e( 'Drafts Ready', 'hitprice-content-engine' ); ?></span>
		</div>
		<div class="hp-ai-stat-card">
			<span class="hp-ai-stat-number"><?php echo esc_html( $posted_month ); ?></span>
			<span class="hp-ai-stat-label"><?php esc_html_e( 'Posted This Month', 'hitprice-content-engine' ); ?></span>
		</div>
		<div class="hp-ai-stat-card">
			<span class="hp-ai-stat-number"><?php echo esc_html( $generated_month ); ?> / <?php echo esc_html( $monthly_target ); ?></span>
			<span class="hp-ai-stat-label"><?php esc_html_e( 'Monthly Target', 'hitprice-content-engine' ); ?></span>
		</div>
	</div>

	<!-- Progress bar -->
	<?php
	$progress = $monthly_target > 0 ? min( 100, round( ( $generated_month / $monthly_target ) * 100 ) ) : 0;
	?>
	<div class="hp-ai-section">
		<h2><?php esc_html_e( 'Monthly Progress', 'hitprice-content-engine' ); ?></h2>
		<div class="hp-ai-progress-bar">
			<div class="hp-ai-progress-fill" style="width: <?php echo esc_attr( $progress ); ?>%;">
				<span><?php echo esc_html( $progress ); ?>%</span>
			</div>
		</div>
	</div>

	<!-- Quick Generate -->
	<div class="hp-ai-section">
		<h2><?php esc_html_e( 'Quick Generate', 'hitprice-content-engine' ); ?></h2>

		<?php if ( empty( $pending_topics ) ) : ?>
			<p class="description"><?php esc_html_e( 'No pending topics available. Add topics first.', 'hitprice-content-engine' ); ?></p>
		<?php else : ?>
			<div class="hp-ai-generate-form" id="hp-ai-generate-form">
				<div class="hp-ai-generate-row">
					<label for="hp-ai-gen-topic"><?php esc_html_e( 'Topic', 'hitprice-content-engine' ); ?></label>
					<select id="hp-ai-gen-topic" class="hp-ai-select">
						<option value=""><?php esc_html_e( '— Select Topic —', 'hitprice-content-engine' ); ?></option>
						<?php foreach ( $pending_topics as $topic ) : ?>
							<option value="<?php echo esc_attr( $topic->id ); ?>">
								<?php echo esc_html( $topic->title ); ?>
								(<?php echo esc_html( HP_AI_Topic::CONTENT_TYPES[ $topic->content_type ] ?? $topic->content_type ); ?>)
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="hp-ai-generate-row">
					<label for="hp-ai-gen-type"><?php esc_html_e( 'Output', 'hitprice-content-engine' ); ?></label>
					<select id="hp-ai-gen-type" class="hp-ai-select">
						<option value="social"><?php esc_html_e( 'Social Post', 'hitprice-content-engine' ); ?></option>
						<option value="blog"><?php esc_html_e( 'Blog Article', 'hitprice-content-engine' ); ?></option>
					</select>
				</div>
				<div class="hp-ai-generate-row">
					<button type="button" id="hp-ai-generate-btn" class="button button-primary button-hero">
						<?php esc_html_e( 'Generate Now', 'hitprice-content-engine' ); ?>
					</button>
				</div>
				<div id="hp-ai-generate-result" class="hp-ai-generate-result" style="display:none;"></div>
			</div>
		<?php endif; ?>
	</div>

	<!-- Recent Activity -->
	<div class="hp-ai-section">
		<h2><?php esc_html_e( 'Recent Activity', 'hitprice-content-engine' ); ?></h2>

		<?php if ( empty( $recent_logs ) ) : ?>
			<p class="description"><?php esc_html_e( 'No activity yet.', 'hitprice-content-engine' ); ?></p>
		<?php else : ?>
			<table class="widefat striped hp-ai-log-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Action', 'hitprice-content-engine' ); ?></th>
						<th><?php esc_html_e( 'Type', 'hitprice-content-engine' ); ?></th>
						<th><?php esc_html_e( 'Details', 'hitprice-content-engine' ); ?></th>
						<th><?php esc_html_e( 'User', 'hitprice-content-engine' ); ?></th>
						<th><?php esc_html_e( 'Date', 'hitprice-content-engine' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_logs as $log ) : ?>
						<?php
						$user    = get_userdata( $log->user_id );
						$details = json_decode( $log->details, true );
						$detail_str = '';
						if ( is_array( $details ) ) {
							$parts = array();
							foreach ( $details as $k => $v ) {
								$parts[] = esc_html( $k ) . ': ' . esc_html( $v );
							}
							$detail_str = implode( ', ', $parts );
						}
						?>
						<tr>
							<td><span class="hp-ai-badge hp-ai-badge--info"><?php echo esc_html( $log->action ); ?></span></td>
							<td><?php echo esc_html( $log->object_type ); ?> #<?php echo esc_html( $log->object_id ); ?></td>
							<td><?php echo esc_html( $detail_str ); ?></td>
							<td><?php echo $user ? esc_html( $user->display_name ) : '—'; ?></td>
							<td><?php echo esc_html( $log->created_at ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
