<?php
/**
 * Admin menu, page routing, and asset loading.
 *
 * Registers the top-level menu and subpages, enqueues admin
 * CSS/JS only on plugin pages, and routes page output.
 *
 * @package HitPrice_Content_Engine
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HP_AI_Admin {

	/**
	 * Menu slug prefix.
	 *
	 * @var string
	 */
	const MENU_SLUG = 'hp-ai-content';

	/**
	 * Required capability to access the plugin.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Hook into WordPress.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Register admin menu and subpages.
	 *
	 * @return void
	 */
	public function register_menu() {

		add_menu_page(
			__( 'HitPrice AI', 'hitprice-content-engine' ),
			__( 'HitPrice AI', 'hitprice-content-engine' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_dashboard' ),
			'dashicons-edit-large',
			30
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'hitprice-content-engine' ),
			__( 'Dashboard', 'hitprice-content-engine' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Topics', 'hitprice-content-engine' ),
			__( 'Topics', 'hitprice-content-engine' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-topics',
			array( $this, 'render_topics' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Drafts', 'hitprice-content-engine' ),
			__( 'Drafts', 'hitprice-content-engine' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-drafts',
			array( $this, 'render_drafts' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'hitprice-content-engine' ),
			__( 'Settings', 'hitprice-content-engine' ),
			self::CAPABILITY,
			self::MENU_SLUG . '-settings',
			array( $this, 'render_settings' )
		);
	}

	/**
	 * Enqueue admin CSS and JS only on plugin pages.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {

		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'hp-ai-admin',
			HP_AI_PLUGIN_URL . 'admin/css/hp-ai-admin.css',
			array(),
			HP_AI_VERSION
		);

		wp_enqueue_script(
			'hp-ai-admin',
			HP_AI_PLUGIN_URL . 'admin/js/hp-ai-admin.js',
			array(),
			HP_AI_VERSION,
			true
		);

		wp_localize_script( 'hp-ai-admin', 'hpAiAdmin', array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'hp_ai_admin_nonce' ),
			'contentTypes' => HP_AI_Topic::CONTENT_TYPES,
			'i18n'         => array(
				'confirm_delete'  => __( 'Are you sure you want to delete this?', 'hitprice-content-engine' ),
				'generating'      => __( 'Generating...', 'hitprice-content-engine' ),
				'error'           => __( 'Something went wrong.', 'hitprice-content-engine' ),
				'no_topics'       => __( 'No topics found.', 'hitprice-content-engine' ),
				'no_drafts'       => __( 'No drafts found.', 'hitprice-content-engine' ),
			),
		) );
	}

	/**
	 * Check if the current admin page belongs to this plugin.
	 *
	 * @param string $hook_suffix The current page hook suffix.
	 * @return bool
	 */
	private function is_plugin_page( $hook_suffix ) {

		$plugin_pages = array(
			'toplevel_page_' . self::MENU_SLUG,
			'hitprice-ai_page_' . self::MENU_SLUG . '-topics',
			'hitprice-ai_page_' . self::MENU_SLUG . '-drafts',
			'hitprice-ai_page_' . self::MENU_SLUG . '-settings',
		);

		return in_array( $hook_suffix, $plugin_pages, true );
	}

	/**
	 * Render the dashboard page.
	 *
	 * @return void
	 */
	public function render_dashboard() {

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hitprice-content-engine' ) );
		}

		require_once HP_AI_PLUGIN_DIR . 'admin/views/dashboard.php';
	}

	/**
	 * Render the topics page.
	 *
	 * @return void
	 */
	public function render_topics() {

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hitprice-content-engine' ) );
		}

		require_once HP_AI_PLUGIN_DIR . 'admin/views/topics.php';
	}

	/**
	 * Render the drafts page.
	 *
	 * @return void
	 */
	public function render_drafts() {

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hitprice-content-engine' ) );
		}

		require_once HP_AI_PLUGIN_DIR . 'admin/views/drafts.php';
	}

	/**
	 * Render the settings page.
	 *
	 * @return void
	 */
	public function render_settings() {

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'hitprice-content-engine' ) );
		}

		require_once HP_AI_PLUGIN_DIR . 'admin/views/settings.php';
	}
}
