<?php
/**
 * Settings admin page.
 *
 * Handles API configuration, mode toggle, and generation settings.
 * Settings are saved via admin-post.php with nonce verification.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle settings save.
if ( isset( $_POST['hp_ai_save_settings'] ) ) {

	if ( ! isset( $_POST['hp_ai_settings_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['hp_ai_settings_nonce'] ) ), 'hp_ai_save_settings' ) ) {
		wp_die( esc_html__( 'Security check failed.', 'hitprice-content-engine' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to change settings.', 'hitprice-content-engine' ) );
	}

	$allowed_modes     = array( 'mock', 'live' );
	$allowed_providers = array( 'claude', 'gpt' );

	$mode = isset( $_POST['hp_ai_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['hp_ai_mode'] ) ) : 'mock';
	if ( ! in_array( $mode, $allowed_modes, true ) ) {
		$mode = 'mock';
	}

	$provider = isset( $_POST['hp_ai_provider'] ) ? sanitize_text_field( wp_unslash( $_POST['hp_ai_provider'] ) ) : 'claude';
	if ( ! in_array( $provider, $allowed_providers, true ) ) {
		$provider = 'claude';
	}

	$model = isset( $_POST['hp_ai_model'] ) ? sanitize_text_field( wp_unslash( $_POST['hp_ai_model'] ) ) : '';

	$monthly_target = isset( $_POST['hp_ai_monthly_target'] ) ? absint( $_POST['hp_ai_monthly_target'] ) : 30;
	if ( $monthly_target < 1 || $monthly_target > 100 ) {
		$monthly_target = 30;
	}

	$default_hashtags = isset( $_POST['hp_ai_default_hashtags'] ) ? sanitize_textarea_field( wp_unslash( $_POST['hp_ai_default_hashtags'] ) ) : '';

	update_option( 'hp_ai_mode', $mode );
	update_option( 'hp_ai_provider', $provider );
	update_option( 'hp_ai_model', $model );
	update_option( 'hp_ai_monthly_target', $monthly_target );
	update_option( 'hp_ai_default_hashtags', $default_hashtags );

	HP_AI_Logger::log( 'settings_update', 'system', 0, array(
		'mode'     => $mode,
		'provider' => $provider,
	) );

	$saved = true;
}

$current_mode     = get_option( 'hp_ai_mode', 'mock' );
$current_provider = get_option( 'hp_ai_provider', 'claude' );
$current_model    = get_option( 'hp_ai_model', 'claude-sonnet-4-6' );
$monthly_target   = get_option( 'hp_ai_monthly_target', 30 );
$default_hashtags = get_option( 'hp_ai_default_hashtags', '' );
$has_api_key      = defined( 'HP_AI_API_KEY' ) && ! empty( HP_AI_API_KEY );
?>

<div class="wrap hp-ai-wrap">
	<h1><?php esc_html_e( 'Settings', 'hitprice-content-engine' ); ?></h1>

	<?php if ( ! empty( $saved ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'hitprice-content-engine' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'hp_ai_save_settings', 'hp_ai_settings_nonce' ); ?>

		<table class="form-table" role="presentation">

			<!-- Mode Toggle -->
			<tr>
				<th scope="row">
					<label for="hp_ai_mode"><?php esc_html_e( 'Mode', 'hitprice-content-engine' ); ?></label>
				</th>
				<td>
					<select name="hp_ai_mode" id="hp_ai_mode">
						<option value="mock" <?php selected( $current_mode, 'mock' ); ?>><?php esc_html_e( 'Mock (no API calls)', 'hitprice-content-engine' ); ?></option>
						<option value="live" <?php selected( $current_mode, 'live' ); ?>><?php esc_html_e( 'Live (real AI)', 'hitprice-content-engine' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Mock mode returns sample data without calling the AI API.', 'hitprice-content-engine' ); ?></p>
				</td>
			</tr>

			<!-- AI Provider -->
			<tr>
				<th scope="row">
					<label for="hp_ai_provider"><?php esc_html_e( 'AI Provider', 'hitprice-content-engine' ); ?></label>
				</th>
				<td>
					<select name="hp_ai_provider" id="hp_ai_provider">
						<option value="claude" <?php selected( $current_provider, 'claude' ); ?>>Claude (Anthropic)</option>
						<option value="gpt" <?php selected( $current_provider, 'gpt' ); ?>>GPT (OpenAI)</option>
					</select>
				</td>
			</tr>

			<!-- Model -->
			<tr>
				<th scope="row">
					<label for="hp_ai_model"><?php esc_html_e( 'Model', 'hitprice-content-engine' ); ?></label>
				</th>
				<td>
					<input type="text" name="hp_ai_model" id="hp_ai_model" value="<?php echo esc_attr( $current_model ); ?>" class="regular-text" />
					<p class="description"><?php esc_html_e( 'e.g., claude-sonnet-4-6, gpt-4o', 'hitprice-content-engine' ); ?></p>
				</td>
			</tr>

			<!-- API Key Status -->
			<tr>
				<th scope="row"><?php esc_html_e( 'API Key', 'hitprice-content-engine' ); ?></th>
				<td>
					<?php if ( $has_api_key ) : ?>
						<span class="hp-ai-badge hp-ai-badge--success"><?php esc_html_e( 'Configured', 'hitprice-content-engine' ); ?></span>
						<p class="description"><?php esc_html_e( 'API key is set in wp-config.php as HP_AI_API_KEY.', 'hitprice-content-engine' ); ?></p>
					<?php else : ?>
						<span class="hp-ai-badge hp-ai-badge--error"><?php esc_html_e( 'Not configured', 'hitprice-content-engine' ); ?></span>
						<p class="description">
							<?php esc_html_e( 'Add this line to your wp-config.php:', 'hitprice-content-engine' ); ?>
							<br><code>define( 'HP_AI_API_KEY', 'your-api-key-here' );</code>
						</p>
					<?php endif; ?>
				</td>
			</tr>

			<!-- Monthly Target -->
			<tr>
				<th scope="row">
					<label for="hp_ai_monthly_target"><?php esc_html_e( 'Monthly Target', 'hitprice-content-engine' ); ?></label>
				</th>
				<td>
					<input type="number" name="hp_ai_monthly_target" id="hp_ai_monthly_target" value="<?php echo esc_attr( $monthly_target ); ?>" min="1" max="100" class="small-text" />
					<p class="description"><?php esc_html_e( 'Number of posts to generate per month.', 'hitprice-content-engine' ); ?></p>
				</td>
			</tr>

			<!-- Default Hashtags -->
			<tr>
				<th scope="row">
					<label for="hp_ai_default_hashtags"><?php esc_html_e( 'Default Hashtags', 'hitprice-content-engine' ); ?></label>
				</th>
				<td>
					<textarea name="hp_ai_default_hashtags" id="hp_ai_default_hashtags" rows="3" class="large-text"><?php echo esc_textarea( $default_hashtags ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Hashtags to always include with generated content.', 'hitprice-content-engine' ); ?></p>
				</td>
			</tr>

		</table>

		<?php submit_button( __( 'Save Settings', 'hitprice-content-engine' ), 'primary', 'hp_ai_save_settings' ); ?>
	</form>
</div>
