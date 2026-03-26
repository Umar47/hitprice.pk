<?php
/**
 * Plugin deactivation handler.
 *
 * Clears scheduled cron events. Does NOT delete data —
 * data is only removed on full plugin deletion via uninstall.php.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Deactivator {

	/**
	 * Run on plugin deactivation.
	 *
	 * @return void
	 */
	public static function deactivate() {

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$timestamp = wp_next_scheduled( 'hp_ai_daily_generation' );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'hp_ai_daily_generation' );
		}
	}
}
