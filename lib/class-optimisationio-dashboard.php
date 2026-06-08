<?php
defined( 'ABSPATH' ) || exit; // Prevent direct access.

/**
 * Featherweight's bridge to the shared Folium UI frame.
 *
 * Registers the plugin under the single "Folium" admin menu, enqueues its JS
 * app, and handles the app's save/reset ajax. The pre-Folium standalone
 * dashboard and the cross-plugin add-on installer were removed in 2.2.0 —
 * Folium UI owns the admin shell and the JS app owns the settings screen.
 */
class Optimisationio_Dashboard {

	private static $instance = null;

	public function __construct() {

		// folium-ui is vendored and loaded unconditionally from the main plugin
		// file (plugins_loaded:4), so Folium_UI is always defined by the time this
		// constructs (plugins_loaded:10). The class_exists guard is belt-and-braces.
		if ( ! class_exists( 'Folium_UI' ) ) {
			return;
		}

		// Nest Featherweight under the single shared "Folium" admin menu and open
		// in-frame at admin.php?page=wp-disable. No own top-level item. The slug
		// stays wp-disable (the permanent wp.org slug); only the brand changes.
		Folium_UI::register_plugin( array(
			'slug'     => 'wp-disable',
			'name'     => __( 'Featherweight', 'wp-disable' ),
			'tagline'  => __( 'Bloat & request removal', 'wp-disable' ),
			'icon'     => 'F',
			'icon_url' => plugin_dir_url( dirname( __FILE__ ) ) . 'images/featherweight-icon.png',
		) );
		add_action( 'folium_ui_enqueue', array( $this, 'on_folium_enqueue' ) );
		add_action( 'wp_ajax_wp_disable_app_save', array( $this, 'ajax_app_save' ) );
		add_action( 'wp_ajax_wp_disable_app_reset', array( $this, 'ajax_app_reset' ) );
		// Redirect the legacy top-level slug to the in-frame Folium page.
		add_action( 'admin_init', array( $this, 'redirect_legacy_slug' ) );
	}

	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
	}

	/**
	 * Enqueue the Featherweight Folium app (JS + CSS) and inject real settings
	 * when the shared frame renders the wp-disable screen. Hooked to
	 * folium_ui_enqueue.
	 *
	 * @param string $slug Plugin slug being rendered by Folium UI.
	 */
	public function on_folium_enqueue( $slug ) {
		if ( 'wp-disable' !== $slug ) {
			return;
		}
		$url  = plugin_dir_url( dirname( __FILE__ ) );
		$base = plugin_dir_path( dirname( __FILE__ ) );
		$css  = $base . 'assets/css/wp-disable-app.css';
		$js   = $base . 'assets/js/wp-disable-app.js';

		// filemtime versioning so CDN/browser caches never mask a pushed edit
		// (switch to the version constant at wp.org release — see PUBLISHING.md).
		$css_ver = file_exists( $css ) ? (string) filemtime( $css ) : ( defined( 'WP_DISABLE_VERSION' ) ? WP_DISABLE_VERSION : '2.2.0' );
		$js_ver  = file_exists( $js )  ? (string) filemtime( $js )  : ( defined( 'WP_DISABLE_VERSION' ) ? WP_DISABLE_VERSION : '2.2.0' );

		wp_enqueue_style( 'wp-disable-app', $url . 'assets/css/wp-disable-app.css', array( 'folium-ui' ), $css_ver );
		wp_enqueue_script( 'wp-disable-app', $url . 'assets/js/wp-disable-app.js', array( 'folium-ui', 'folium-app' ), $js_ver, true );
		wp_localize_script( 'wp-disable-app', 'WPDisableData', $this->app_data() );
	}

	/**
	 * Real data injected into the Featherweight Folium app.
	 *
	 * @return array
	 */
	private function app_data() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		$post_types = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $pt ) {
			$label        = isset( $pt->labels->singular_name ) && $pt->labels->singular_name ? $pt->labels->singular_name : $pt->name;
			$post_types[] = array( 'name' => $pt->name, 'label' => $label );
		}

		return array(
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'nonce'     => wp_create_nonce( 'wp_disable_app' ),
			'actions'   => array(
				'save'  => 'wp_disable_app_save',
				'reset' => 'wp_disable_app_reset',
			),
			'settings'  => $settings,
			'postTypes' => $post_types,
			'flags'     => array(
				'woo' => WpPerformance::is_woocommerce_enabled(),
				'seo' => WpPerformance::should_show_seo_tab(),
			),
		);
	}

	/**
	 * Ajax: persist settings from the Folium app. Reuses the exact same
	 * sanitisation as the legacy form (WpPerformance_Admin::persist_settings).
	 */
	public function ajax_app_save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}
		check_ajax_referer( 'wp_disable_app', 'nonce' );

		$raw = isset( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : array(); // phpcs:ignore WordPress.Security.ValidationSanitization.InputNotValidated
		if ( ! is_array( $raw ) ) {
			$raw = array();
		}
		WpPerformance_Admin::persist_settings( $raw );
		wp_send_json_success();
	}

	/**
	 * Ajax: reset settings to defaults (an empty request writes every default).
	 */
	public function ajax_app_reset() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}
		check_ajax_referer( 'wp_disable_app', 'nonce' );

		WpPerformance_Admin::persist_settings( array() );
		wp_send_json_success();
	}

	/**
	 * Redirect the retired top-level slug (admin.php?page=optimisationio-dashboard)
	 * to the in-frame Folium page so old bookmarks / the plugin row link resolve.
	 */
	public function redirect_legacy_slug() {
		if ( isset( $_GET['page'] ) && 'optimisationio-dashboard' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( admin_url( 'admin.php?page=wp-disable' ) );
			exit;
		}
	}
}
