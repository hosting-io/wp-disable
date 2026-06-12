<?php
defined( 'ABSPATH' ) || exit; // Prevent direct access.
class WpPerformance {

	private static $instance = false;

	const MIN_PHP_VERSION = '7.4';
	const MIN_WP_VERSION = '6.4';
	const TEXT_DOMAIN = 'wp-disable';
	const OPTION_KEY = 'wpperformance_rev3a';

	// Internal schema version, bumped when a one-time data migration is needed.
	// v2: removed the obsolete Universal Analytics "local GA" offload feature.
	const DB_VERSION = 2;
	const DB_VERSION_KEY = 'wpperformance_db_version';

	private $plugin_settings = null;

	private static $enabled_woocommerce = null;

	/**
	 * Constructor.
	 * Initializes the plugin by setting localization, filters, and
	 * administration functions.
	 */
	private function __construct() {

		if ( ! $this->test_host() ) { return; }

		$this->maybe_upgrade();

		if( ! class_exists('Optimisationio_Dashboard') ){
			require_once 'class-optimisationio-dashboard.php';
		}

		Optimisationio_Dashboard::init();

		new WpPerformance_Admin;		

		$this->apply_settings();
	}

	/**
	 * Singleton class instance.
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Delete plugin's transient values.
	 */
	public static function delete_transients() {
		delete_transient( self::OPTION_KEY . '_referalls_spam_blacklist' );
	}

	/**
	 * Delete plugin's options values.
	 */
	public static function delete_options() {
		delete_option( self::OPTION_KEY . '_settings' );
		delete_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		delete_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
		delete_option( self::DB_VERSION_KEY );
	}

	/**
	 * One-time data migrations, keyed off DB_VERSION_KEY.
	 */
	private function maybe_upgrade() {

		$installed = (int) get_option( self::DB_VERSION_KEY, 1 );

		if ( $installed >= self::DB_VERSION ) {
			return;
		}

		// v2: tear down the removed Universal Analytics "local GA" offload.
		wp_clear_scheduled_hook( 'update_local_ga' );
		delete_transient( 'wpperformance_ds_tracking_id' );

		$settings = get_option( self::OPTION_KEY . '_settings', array() );
		if ( is_array( $settings ) ) {
			$ga_keys = array(
				'ds_tracking_id', 'ds_adjusted_bounce_rate', 'ds_enqueue_order',
				'ds_anonymize_ip', 'ds_script_position', 'ds_track_admin',
				'caos_disable_display_features', 'caos_remove_wp_cron',
			);
			foreach ( $ga_keys as $ga_key ) {
				unset( $settings[ $ga_key ] );
			}
			update_option( self::OPTION_KEY . '_settings', $settings );
		}

		$local_ga = dirname( dirname( __FILE__ ) ) . '/cache/local-ga.js';
		if ( file_exists( $local_ga ) ) {
			wp_delete_file( $local_ga );
		}

		update_option( self::DB_VERSION_KEY, self::DB_VERSION );
	}

	public static function delete_spam_comments() {
		$spam_comments_id_arr = get_comments(
			array(
				'status' => 'spam',
				'fields' => 'ids',
				'number' => 0,
			)
		);
		if ( ! empty( $spam_comments_id_arr ) ) {
			$spam_comments_id_arr = array_map( 'intval', $spam_comments_id_arr );
			foreach ( $spam_comments_id_arr as $comment_id ) {
				wp_delete_comment( $comment_id, true );
			}
		}
	}

	public static function schedule_spam_comments_delete( $schedule, $reschedule = false ) {

		$pre_schedule = wp_get_schedule( 'delete_spam_comments' );

		if ( $reschedule || ( $pre_schedule && $pre_schedule !== $schedule ) || ! wp_next_scheduled( 'delete_spam_comments' ) ) {
			self::unschedule_spam_comments_delete();
			wp_schedule_event( time(), $schedule, 'delete_spam_comments' );
		}
	}

	public static function unschedule_spam_comments_delete() {
		wp_clear_scheduled_hook( 'delete_spam_comments' );
	}

	// -------------------------------------------------------------------------
	// Environment Checks
	// -------------------------------------------------------------------------

	/**
	 * Checks PHP and WordPress versions.
	 */
	private function test_host() {
		// Check if PHP is too old.
		if ( version_compare( PHP_VERSION, self::MIN_PHP_VERSION, '<' ) ) {
			// Display notice.
			add_action( 'admin_notices', array( &$this, 'php_version_error' ) );
			return false;
		}

		// Check if WordPress is too old.
		global $wp_version;
		if ( version_compare( $wp_version, self::MIN_WP_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( &$this, 'wp_version_error' ) );
			return false;
		}
		return true;
	}

	/**
	 * Displays a warning when installed on an old PHP version.
	 */
	public function php_version_error() {
		echo '<div class="error"><p><strong>';
		printf(
			/* translators: 1: required PHP version, 2: installed PHP version, 3: plugin name. */
			esc_html__( 'Error: %3$s requires PHP version %1$s or greater. Your installed PHP version: %2$s', 'wp-disable' ),
			esc_html( self::MIN_PHP_VERSION ),
			esc_html( PHP_VERSION ),
			esc_html( $this->get_plugin_name() )
		);
		echo '</strong></p></div>';
	}

	/**
	 * Displays a warning when installed in an old WordPress version.
	 */
	public function wp_version_error() {
		echo '<div class="error"><p><strong>';
		printf(
			/* translators: 1: required WordPress version, 2: plugin name. */
			esc_html__( 'Error: %2$s requires WordPress version %1$s or greater.', 'wp-disable' ),
			esc_html( self::MIN_WP_VERSION ),
			esc_html( $this->get_plugin_name() )
		);
		echo '</strong></p></div>';
	}

	/**
	 * Get the name of this plugin.
	 *
	 * @return string The plugin name.
	 */
	private function get_plugin_name() {
		// get_plugin_data() lives in wp-admin and must be pointed at the MAIN
		// plugin file (this is the class file). Guard both so the version-error
		// notices never fatal.
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$main_file = dirname( __FILE__, 2 ) . '/wpperformance.php';
		$data = get_plugin_data( $main_file, false, false );
		return ! empty( $data['Name'] ) ? $data['Name'] : 'Featherweight';
	}

	// -------------------------------------------------------------------------
	// Apply settings values
	// -------------------------------------------------------------------------
	
	private function apply_settings() {

		$this->check_referral_spam_disable();

		if ( ! is_admin() ) {
			$this->check_pages_disable();
			$this->check_dns_prefetch();
		}
		else{
			$this->check_admin_notices_display();
		}

		$this->check_comments_disable();
		$this->check_feeds_disable();

		add_action( 'wp_print_styles', array( $this, 'enqueue_scripts' ), -1 );
		add_action( 'wp_print_styles', array( $this, 'dequeue_styles'), -1 );
		add_action( 'wp_print_scripts', array( $this, 'dequeue_scripts' ), 100 );

	}

	private function get_settings_values() {
		$this->plugin_settings = null === $this->plugin_settings ? get_option( WpPerformance::OPTION_KEY . '_settings', array() ) : $this->plugin_settings;
		return $this->plugin_settings;
	}

	public function enqueue_scripts() {
		$async_links = $this->check_googlefonts_fontawesome_styles();
		if ( ! empty( $async_links ) ) {
			$script_path = dirname( __DIR__ ) . '/js/css-lazy-load.js';
			$script_ver  = file_exists( $script_path ) ? (string) filemtime( $script_path ) : WP_DISABLE_VERSION;
			wp_enqueue_script( 'wp-disable-css-lazy-load', plugin_dir_url( dirname( __FILE__ ) ) . 'js/css-lazy-load.js', array(), $script_ver, true );
			wp_localize_script( 'wp-disable-css-lazy-load', 'WpDisableAsyncLinks', $async_links );
		}
	}

	public function dequeue_styles(){

		$settings = $this->get_settings_values();

		if( ! is_admin() &&
			! is_admin_bar_showing() && 
			! is_customize_preview() &&
			isset( $settings['disable_front_dashicons_when_disabled_toolbar'] ) && 
			$settings['disable_front_dashicons_when_disabled_toolbar'] ){
			wp_deregister_style('dashicons');
		}
	}

	public function dequeue_scripts() {

		$settings = $this->get_settings_values();

		$invalid_disable = is_page('lost_password');

		$wc_invalid_disable = ! WpPerformance::is_woocommerce_enabled() || $invalid_disable || is_account_page() || is_checkout();

		if ( ! $wc_invalid_disable && isset( $settings['disable_woocommerce_password_meter'] ) && $settings['disable_woocommerce_password_meter'] ) {

			if ( wp_script_is( 'zxcvbn-async', 'enqueued' ) ) {
				wp_dequeue_script( 'zxcvbn-async' );
			}

			if ( wp_script_is( 'password-strength-meter', 'enqueued' ) ) {
				wp_dequeue_script( 'password-strength-meter' );
			}

			if ( wp_script_is( 'wc-password-strength-meter', 'enqueued' ) ) {
				wp_dequeue_script( 'wc-password-strength-meter' );
			}
		}

		if ( ! $invalid_disable && isset( $settings['disable_wordpress_password_meter'] ) && $settings['disable_wordpress_password_meter'] ) {

			if ( wp_script_is( 'zxcvbn-async', 'enqueued' ) ) {
				wp_dequeue_script( 'zxcvbn-async' );
			}

			if ( wp_script_is( 'password-strength-meter', 'enqueued' ) ) {
				wp_dequeue_script( 'password-strength-meter' );
			}
		}
	}

	private function check_googlefonts_fontawesome_styles() {
		global $wp_styles;
		$ret = array();
		if ( isset( $wp_styles ) && ! empty( $wp_styles ) ) {

			$settings = $this->get_settings_values();

			$load_google_fonts = isset( $settings['lazy_load_google_fonts'] ) && $settings['lazy_load_google_fonts'];
			$load_font_awesome = isset( $settings['lazy_load_font_awesome'] ) && $settings['lazy_load_font_awesome'];

			if ( $load_google_fonts || $load_font_awesome ) {

				$gfonts_base_url = 'fonts.googleapis.com/css';
				$gfonts_links = array();

				$font_awesome_slug = 'font-awesome';
				$font_awesome_slug_alt = 'fontawesome';
				$font_awesome_links = array(
					'external' => array(),
					'internal' => array(),
				);

				foreach ( $wp_styles->queue as $handle ) {
					if ( $load_google_fonts && false !== strpos( $wp_styles->registered[ $handle ]->src, $gfonts_base_url ) ) {
						$gfonts_links[] = urldecode( str_replace( array( '&amp;' ), array( '&' ), $wp_styles->registered[ $handle ]->src ) );
						wp_dequeue_style( $handle );
					} elseif ( $load_font_awesome && false !== strpos( $wp_styles->registered[ $handle ]->src, $font_awesome_slug ) || false !== strpos( $wp_styles->registered[ $handle ]->src, $font_awesome_slug_alt ) ) {

						wp_dequeue_style( $handle );

						$font_awesome_links[ false !== strpos( $wp_styles->registered[ $handle ]->src, site_url() ) ? 'internal' : 'external' ][] = array(
							'ver' => $wp_styles->registered[ $handle ]->ver ? $wp_styles->registered[ $handle ]->ver : false,
							'link' => $wp_styles->registered[ $handle ]->src,
						);
					}
				}

				$saved_font_awesome_requests = 0;
				$saved_google_fonts_requests = 0;

				if ( $load_font_awesome && ( ! empty( $font_awesome_links['internal'] ) || ! empty( $font_awesome_links['external'] ) ) ) {

					// @note: Prioritize external links.
					$fa_links = ! empty( $font_awesome_links['external'] ) ? $font_awesome_links['external'] : $font_awesome_links['internal'];

					$selected_fa_link = $fa_links[0];

					$links_count = count( $fa_links );
					if ( 1 < $links_count ) {
						for ( $i = 1; $i < $links_count; $i++ ) {
							if ( false !== $fa_links[ $i ]['ver'] &&
								( false === $selected_fa_link['ver'] || version_compare( $selected_fa_link['ver'], $fa_links[ $i ]['ver'], '<' ) )
							) {
								$selected_fa_link = $fa_links[ $i ];
							}
						}
					}

					$ret['wp-disable-font-awesome'] = esc_url( $selected_fa_link['link'] );
					
					$this->update_saved_font_awesome_requests( count( $font_awesome_links['internal'] ) + count( $font_awesome_links['external'] ) );
				}
				else{
					$this->update_saved_font_awesome_requests(0);
				}

				if ( $load_google_fonts && ! empty( $gfonts_links ) ) {
					
					$ret['wp-disable-google-fonts'] = esc_url( $this->combine_google_fonts_links( $gfonts_links ) );

					$this->update_saved_google_fonts_request( count( $gfonts_links ) );
				}
				else{
					$this->update_saved_google_fonts_request(0);
				}
			}
			else{
				$this->update_saved_font_awesome_requests(0);
				$this->update_saved_google_fonts_request(0);
			}
		}// End if().

		return $ret;
	}

	private function update_saved_google_fonts_request( $count ) {
		$count = ! isset( $count ) ? 0 : (int) $count;
		$old_val = get_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		if( false === $old_val || ( false !== $old_val && $count > (int) $old_val ) ){
			update_option( self::OPTION_KEY . '_combined_google_fonts_requests_number', $count );
		}
	}

	private function update_saved_font_awesome_requests( $count ) {
		$count = ! isset( $count ) ? 0 : (int) $count;
		$old_val = get_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
		if( false === $old_val || ( false !== $old_val && $count > (int)  $old_val ) ){
			update_option( self::OPTION_KEY . '_combined_font_awesome_requests_number', $count );
		}
	}

	public static function saved_external_requests(){
		$google_fonts = (int) get_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		$font_awesome = (int) get_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
		$google_fonts_saved = 1 < $google_fonts ? $google_fonts - 1 : 0;
		$font_awesome_saved = 1 < $font_awesome ? $font_awesome - 1 : 0;
		return $google_fonts_saved + $font_awesome_saved;
	}

	/**
	 * Combine multiple Google Fonts links into one.
	 *
	 * @param array $links An array of the different Google Fonts links. Default array().
	 * @return string The compined Google Fonts link.
	 */
	private function combine_google_fonts_links( $links = array() ) {

		if ( ! is_array( $links ) ) {
			return $links;
		}

		$links = array_unique( $links );

		if ( 1 === count( $links ) ) {
			return $links[0];
		}

		$protocol = 'https';
		$base_url   = '//fonts.googleapis.com/css';
		$family_arg = 'family';
		$subset_arg = 'subset';

		$base_url_len = strlen( $base_url );
		$family_arg_len = strlen( $family_arg );

		$fonts = array();
		$cnt = 0;

		$clean_links = array();
		foreach ( $links as $k => $v ) {

			$base_url_pos = strrpos( $v, $base_url );

			$args_str = trim( substr( $v, ($base_url_len + $base_url_pos), strlen( $v ) ) );

			if ( substr( $args_str, 0, $family_arg_len + 2 ) === '?' . $family_arg . '=' ) {
				$args_str = substr( $args_str, $family_arg_len + 2, strlen( $args_str ) );
			}

			$tmp = explode( '|', $args_str );
			$tmp_count = count( $tmp );
			for ( $i = 0; $i < $tmp_count; $i++ ) {
				$clean_links[] = $tmp[ $i ];
			}
		}

		foreach ( $clean_links as $k => $v ) {

			$expl = explode( '&' . $subset_arg, $v );

			if ( isset( $expl[0] ) && ! empty( $expl[0] ) ) {

				$tmp = explode( ':', $expl[0] );

				if ( isset( $tmp[0] ) && ! empty( $tmp[0] ) ) {

					// Has font family name.
					$font_name = str_replace( ' ', '+', $tmp[0] );

					if ( ! isset( $fonts[ $font_name ] ) ) {
						$fonts[ $font_name ] = array(
							'sizes' => array(),
							'subsets' => array(),
						);
					}

					if ( isset( $tmp[1] ) && ! empty( $tmp[1] ) ) {

						// Has font sizes.
						$x = explode( ',', $tmp[1] );
						$xc = count( $x );

						foreach ( $x as $xk => $xv ) {
							if ( ! in_array( $xv, $fonts[ $font_name ]['sizes'], true ) && ( 400 !== (int) $xv || $xc > 1) ) {
								$fonts[ $font_name ]['sizes'][] = $xv;
							}
						}
					}

					if ( isset( $expl[1] ) && ! empty( $expl[1] ) ) {

						// Has subsets.
						$y = explode( ',', $expl[1] );
						$yc = count( $y );

						foreach ( $y as $yk => $yv ) {

							if ( '=' === substr( $yv, 0, 1 ) ) {
								$yv = substr( $yv, 1, strlen( $yv ) );
							}

							if ( ! in_array( $yv, $fonts[ $font_name ]['subsets'], true ) && ('latin' !== $yv || $yc > 1) ) {
								$fonts[ $font_name ]['subsets'][] = $yv;
							}
						}
					}
				}// End if().
			}// End if().
		}// End foreach().

		$ret = '';

		if ( ! empty( $fonts ) ) {

			$ret .= $protocol . ':' . $base_url;
			$i = 0;
			$subsets = array();

			foreach ( $fonts as $key => $val ) {

				if ( 0 === $i ) {
					$ret .= '?' . $family_arg . '=';
				} else {
					$ret .= '|';
				}

				$ret .= $key;

				if ( ! empty( $val['sizes'] ) ) {
					$ret .= ':' . implode( ',', $val['sizes'] );
				}

				if ( ! empty( $val['subsets'] ) ) {
					$subsets = array_merge( $subsets, $val['subsets'] );
				}

				$i++;
			}

			if ( ! empty( $subsets ) ) {
				$ret .= '&' . $subset_arg . '=' . implode( ',', $subsets );
			}
		}

		return $ret;
	}

	public static function check_spam_comments_delete( $reschedule = false ) {

		// This method is static; $this is never available here (the old
		// isset( $this ) branch was dead code that also errored on PHP 8).
		$settings = get_option( self::OPTION_KEY . '_settings', array() );

		if ( isset( $settings['spam_comments_cleaner'] ) && 1 === (int) $settings['spam_comments_cleaner'] && isset( $settings['delete_spam_comments'] ) && $settings['delete_spam_comments'] ) {
			self::schedule_spam_comments_delete( $settings['delete_spam_comments'], $reschedule );
		} else {
			self::unschedule_spam_comments_delete();
		}
	}

	public static function synchronize_discussion_data($settings){
		if ( isset( $settings['disable_gravatars'] ) && 1 === (int) $settings['disable_gravatars'] ) {
			update_option( 'show_avatars', false );
		} else {
			update_option( 'show_avatars', true );
		}

		if ( isset( $settings['default_ping_status'] ) && 1 === (int) $settings['default_ping_status'] ) {
			update_option( 'default_ping_status', 'close' );
		} else {
			update_option( 'default_ping_status', 'open' );
		}

		if ( isset( $settings['close_comments'] ) && 1 === (int) $settings['close_comments'] ) {
			update_option( 'close_comments_for_old_posts', true );
			update_option( 'close_comments_days_old', 28 );
		} else {
			update_option( 'close_comments_for_old_posts', false );
			update_option( 'close_comments_days_old', 14 );
		}

		if ( isset( $settings['paginate_comments'] ) && 1 === (int) $settings['paginate_comments'] ) {
			update_option( 'page_comments', true );
			update_option( 'comments_per_page', 20 );
		} else {
			update_option( 'page_comments', false );
			update_option( 'comments_per_page', 50 );
		}
	}

	private function check_admin_notices_display(){
		$settings = $this->get_settings_values();
		if ( isset( $settings['disable_admin_notices'] ) && $settings['disable_admin_notices'] ) {
			add_action('admin_print_scripts', array($this, 'disable_admin_notices') );
		}
	}

	public function disable_admin_notices(){
		global $wp_filter;
		if (is_user_admin()) {
			if (isset($wp_filter['user_admin_notices'])) {
				unset($wp_filter['user_admin_notices']);
			}
		} elseif (isset($wp_filter['admin_notices'])) {
			unset($wp_filter['admin_notices']);
		}
		if (isset($wp_filter['all_admin_notices'])) {
			unset($wp_filter['all_admin_notices']);
		}
	}

	private function check_pages_disable(){
		$settings = $this->get_settings_values();
		if ( isset( $settings['disable_author_pages'] ) && $settings['disable_author_pages'] ) {
			add_action( 'template_redirect', array( $this, 'redirect_author_pages' ) );
		}
	}

	public function redirect_author_pages(){
		if( get_query_var( 'author' ) || get_query_var( 'author_name' ) ){
			wp_safe_redirect( home_url(), 307 );
			exit;
		}
	}

	public function comment_admin_menu_remove(){
		remove_menu_page('edit-comments.php');
	}

	private function check_dns_prefetch(){

		$settings = $this->get_settings_values();

		if( ! isset( $settings['dns_prefetch'] ) || ! $settings['dns_prefetch'] ) {
			return;
		}

		// Defer the actual output to wp_head. Echoing here (plugins_loaded time)
		// emits body output before headers are sent — which on REST/AJAX/feed
		// requests triggers "headers already sent". wp_head only fires while
		// rendering a front-end page, which is where dns-prefetch hints belong.
		add_action( 'wp_head', array( $this, 'print_dns_prefetch' ), 0 );
	}

	public function print_dns_prefetch(){

		$settings = $this->get_settings_values();

		$list      = array();
		$host_list = isset( $settings['dns_prefetch_host_list'] ) ? $settings['dns_prefetch_host_list'] : '';
		$host_list = '' !== $host_list ? explode("\n", $host_list) : array();

		if( ! empty( $host_list ) ){
			foreach ($host_list as $key => $val) {
				$val = str_replace( 'http:', '', str_replace( 'https:', '', esc_url( $val ) ) );
				if( $val && ! in_array($val, $list, true) ){
					$list[] = $val;
				}
			}
		}

		if( ! empty( $list ) ){
			foreach ($list as $key => $val) {
				echo '<link rel="dns-prefetch" href="' . esc_url( $val ) . '" />' . "\n";
			}
		}
	}

	private function check_comments_disable() {
		$settings = $this->get_settings_values();
		$disable_all_comments = isset( $settings['disable_all_comments'] ) && 1 === $settings['disable_all_comments'];
		if( $disable_all_comments ){
			if( ! is_admin() ){
				add_filter( 'feed_links_show_comments_feed', '__return_false' );
				add_action( 'wp_footer', array( $this, 'hide_meta_widget_link' ), 100 );
				add_action( 'template_redirect', array( $this, 'check_comments_template' ) );
			}
			else{
				add_action('admin_menu', array( $this, 'comment_admin_menu_remove' ) );	
			}
		}
	}

	public function check_comments_template() {

		$settings = $this->get_settings_values();

		$disable_settings = false;

		if ( isset( $settings['disable_all_comments'] ) && 1 === $settings['disable_all_comments'] ) {
			$disable_settings = true;
		} elseif ( isset( $settings['disable_comments_on_certain_post_types'] ) && 1 === $settings['disable_comments_on_certain_post_types'] ) {

			$current_post_type = get_post_type();

			if ( $current_post_type &&
				isset( $settings['disable_comments_on_post_types'] ) &&
				is_array( $settings['disable_comments_on_post_types'] ) &&
				isset( $settings['disable_comments_on_post_types'][ $current_post_type ] ) &&
				1 === (int) $settings['disable_comments_on_post_types'][ $current_post_type ] ) {
				$disable_settings = true;
			}
		}

		if ( $disable_settings ) {

			// Replace comments template with empty file.
			add_action( 'comments_template', array( $this, 'empty_comments_template' ) );

			// Remove comment-reply script for themes that include it indiscriminately.
			wp_deregister_script( 'comment-reply' );
		} else {
			$this->check_comments_authors_links();
		}
	}

	public function hide_meta_widget_link() {
		if ( is_active_widget( false, false, 'meta', true ) && wp_script_is( 'jquery', 'enqueued' ) ) {
			echo '<script> jQuery(function($){ $(".widget_meta a[href=\'' . esc_url( get_bloginfo( 'comments_rss2_url' ) ) . '\']").parent().remove(); }); </script>';
		}
	}

	public function empty_comments_template() {
		return dirname( dirname( __FILE__ ) ) . '/includes/empty-comments-template.php';
	}

	private function check_comments_authors_links() {
		$settings = $this->get_settings_values();
		if ( isset( $settings['remove_comments_links'] ) && 1 === $settings['remove_comments_links'] ) {
			add_filter( 'comment_form_default_fields', array( $this, 'filter_comments_fields' ), 10 );
			add_filter( 'get_comment_author_link', array( $this, 'disable_comments_authors_links' ), 10 );
			add_filter( 'comment_text', array( $this, 'disable_comments_content_links' ), 10 );
		}
	}

	public function filter_comments_fields( $fields ) {
		if ( isset( $fields['url'] ) ) {
			unset( $fields['url'] );
		}
		return $fields;
	}

	public function disable_comments_content_links( $content = '' ) {
		// This is a 'comment_text' filter: it must RETURN the value. The old
		// echo printed the stripped content and returned null, blanking comments.
		return preg_replace( '/<a[^>]*href=[^>]*>|<\/[^a]*a[^>]*>/i', '', $content );
	}

	public function disable_comments_authors_links( $author_link ) {
		return wp_strip_all_tags( $author_link );
	}

	public function check_feeds_disable() {

		$settings = $this->get_settings_values();

		if ( isset( $settings['disable_rss'] ) && 1 === $settings['disable_rss'] ) {
			add_action( 'wp_loaded', array( $this, 'remove_feed_links' ) );
			add_action( 'template_redirect', array( $this, 'filter_feeds' ), 1 );
			add_filter( 'bbp_request', array( $this, 'filter_bbp_feeds' ), 9 );
		}
	}

	public function remove_feed_links() {
		remove_action( 'wp_head', 'feed_links', 2 );
		remove_action( 'wp_head', 'feed_links_extra', 3 );
	}

	public function filter_feeds() {

		if ( ! is_feed() || is_404() ) {
			return;
		}

		$settings = $this->get_settings_values();

		if ( isset( $settings['not_disable_global_feeds'] ) && 1 === $settings['not_disable_global_feeds'] ) {
			if ( ! ( is_singular() || is_archive() || is_date() || is_author() || is_category() || is_tag() || is_tax() || is_search() ) ) {
				return;
			}
		}

		$this->disabled_feed_behaviour();
	}

	public function disabled_feed_behaviour() {

		global $wp_rewrite, $wp_query;

		$settings = $this->get_settings_values();

		if ( isset( $settings['disabled_feed_behaviour'] ) && '404_error' === $settings['disabled_feed_behaviour'] ) {
			$wp_query->is_feed = false;
			$wp_query->set_404();
			status_header( 404 );
			// Override the xml+rss header set by WP in send_headers
			header( 'Content-Type: ' . get_option( 'html_type' ) . '; charset=' . get_option( 'blog_charset' ) );
		} else {
			$uri           = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$request_query = wp_parse_url( $uri, PHP_URL_QUERY );
			$query_args    = array();
			if ( $request_query ) {
				wp_parse_str( $request_query, $query_args );
			}

			if ( isset( $query_args['feed'] ) ) {
				wp_safe_redirect( esc_url_raw( remove_query_arg( 'feed' ) ), 301 );
				exit;
			}

			if ( 'old' !== get_query_var( 'feed' ) ) {	// WP redirects these anyway, and removing the query var will confuse it thoroughly
				set_query_var( 'feed', '' );
			}

			redirect_canonical();	// Let WP figure out the appropriate redirect URL.

			// Still here? redirect_canonical failed to redirect, probably because of a filter. Try the hard way.
			$struct = ( ! is_singular() && is_comment_feed() ) ? $wp_rewrite->get_comment_feed_permastruct() : $wp_rewrite->get_feed_permastruct();
			$struct = preg_quote( $struct, '#' );
			$struct = str_replace( '%feed%', '(\w+)?', $struct );
			$struct = preg_replace( '#/+#', '/', $struct );
			$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$requested_url = ( is_ssl() ? 'https://' : 'http://' ) . $host . $uri;
			$new_url = preg_replace( '#' . $struct . '/?$#', '', $requested_url );

			if ( $new_url !== $requested_url ) {
				wp_safe_redirect( $new_url, 301 );
				exit;
			}
		}
	}

	/**
	 * BBPress feed detection sourced from bbp_request_feed_trap() in BBPress Core.
	 *
	 * @param  [type] $query_vars [description].
	 * @return [type]             [description]
	 */
	public function filter_bbp_feeds( $query_vars ) {
		// Looking at a feed
		if ( isset( $query_vars['feed'] ) ) {

			// Forum/Topic/Reply Feed
			if ( isset( $query_vars['post_type'] ) ) {

				// Matched post type
				$post_type = false;

				// Post types to check
				$post_types = array(
					bbp_get_forum_post_type(),
					bbp_get_topic_post_type(),
					bbp_get_reply_post_type(),
				);

				// Cast query vars as array outside of foreach loop
				$qv_array = (array) $query_vars['post_type'];

				// Check if this query is for a bbPress post type
				foreach ( $post_types as $bbp_pt ) {
					if ( in_array( $bbp_pt, $qv_array, true ) ) {
						$post_type = $bbp_pt;
						break;
					}
				}

				// Looking at a bbPress post type
				if ( ! empty( $post_type ) ) {
					$this->disabled_feed_behaviour();
				}
			}
		}

		// No feed so continue on
		return $query_vars;
	}

	public function check_referral_spam_disable(){
		$settings = $this->get_settings_values();
		if ( isset( $settings['disable_referral_spam'] ) && 1 === $settings['disable_referral_spam'] ) {

			add_filter('request', array($this, 'filter_referral_spam_requests'), 0);
		}
	}

	public function filter_referral_spam_requests($request){
		global $wp_query;
		
		$referrer = wp_get_referer() !== false ? wp_get_referer() : ( isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '' );

		if ( empty( $referrer ) ) {
			return $request;
		}

		$referrer = wp_parse_url($referrer, PHP_URL_HOST);
		
		$referrers_blacklist = $this->referrals_blacklist();

		if( empty( $referrers_blacklist ) ){
			return $request;
		}

		$is_blacklisted = false;

		foreach ($referrers_blacklist as $blist_ref) {
            if (false !== stripos($referrer, $blist_ref)) {
            	$is_blacklisted = true;
            	break;
            }
        }

        if( $is_blacklisted ){
        	status_header(404);
        	$wp_query->set_404();
            get_template_part('404');
            exit();
        }

        return $request;
	}

	private function referrals_blacklist(){

		$ret = get_transient( self::OPTION_KEY . '_referalls_spam_blacklist' );
		
		if( false === $ret ){
		
			$response = wp_remote_get( 'https://wielo.co/referrer-spam.php', array( 'timeout' => 5 ) );
			
			if ( $response instanceof WP_Error ) {
				do_action( 'wp_disable_referral_spam_blacklist_error', 'request_failed', $response );
				return;
			}

			$ret = $response['body'];

			if ( empty( $ret ) ) {
				do_action( 'wp_disable_referral_spam_blacklist_error', 'empty_response', $response );
				return;
			}

			$ret = json_decode( $ret, true );

			if ( null === $ret ) {
				do_action( 'wp_disable_referral_spam_blacklist_error', 'invalid_json', $response );
				return;
			}

			set_transient( self::OPTION_KEY . '_referalls_spam_blacklist', $ret, DAY_IN_SECONDS );	// Refresh daily.
	    }

        return $ret;
	}

	public static function is_woocommerce_enabled(){
		if( null === WpPerformance::$enabled_woocommerce ){
			WpPerformance::$enabled_woocommerce = class_exists( 'WooCommerce' ) || function_exists( 'WC' );
		}
		return WpPerformance::$enabled_woocommerce;
	}

	/**
	 * Whether to show the SEO settings tab.
	 *
	 * True only when a supported SEO plugin is active (currently Yoast SEO);
	 * the SEO options are no-ops otherwise. Kept abstracted so other SEO
	 * plugins can be added here later. Props @JeroenSormani.
	 */
	public static function should_show_seo_tab(){
		return defined( 'WPSEO_VERSION' );
	}
}
