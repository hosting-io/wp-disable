<?php
class WpPerformance {

	private static $instance = false;

	const MIN_PHP_VERSION = '5.2.4';
	const MIN_WP_VERSION = '4.3';
	const TEXT_DOMAIN = 'wpperformance';
	const OPTION_KEY = 'wpperformance_rev3a';

	private $plugin_settings = null;

	/**
	 * Constructor.
	 * Initializes the plugin by setting localization, filters, and
	 * administration functions.
	 */
	private function __construct() {

		if ( ! $this->test_host() ) { return; }

		if( ! class_exists('Optimisationio_Stats_And_Addons') ){
			require_once 'class-optimisationio-stats-and-addons.php';
		}

		Optimisationio_Stats_And_Addons::init();

		new WpPerformance_Admin;		

		add_action( 'init', array( $this, 'text_domain' ) );

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
	 * Loads the plugin text domain for translation
	 */
	public function text_domain() {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			dirname( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR
		);
	}

	/**
	 * Delete plugin's transient values.
	 */
	public static function delete_transients() {
		delete_transient( 'wpperformance_ds_tracking_id' );
		delete_transient( self::OPTION_KEY . '_referalls_spam_blacklist' );
	}
	
	/**
	 * Delete plugin's options values.
	 */
	public static function delete_options() {
		delete_option( self::OPTION_KEY . '_settings' );
		delete_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		delete_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
	}

	public static function delete_spam_comments() {
		global $wpdb;

		$spam_comments_id_arr = $wpdb->get_col( "SELECT comment_id FROM {$wpdb->comments} WHERE comment_approved = 'spam'" );
		if ( ! empty( $spam_comments_id_arr ) ) {
			$spam_comments_ids = implode( ', ', array_map( 'intval', $spam_comments_id_arr ) );

			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->comments} WHERE comment_id IN ( %s )", $spam_comments_ids ) );
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->commentmeta} WHERE comment_id IN ( %s )", $spam_comments_ids ) );

			$wpdb->query( "OPTIMIZE TABLE $wpdb->comments" );
			$wpdb->query( "OPTIMIZE TABLE $wpdb->commentmeta" );
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
			'Error: %3$s requires PHP version %1$s or greater.<br/>' .
			'Your installed PHP version: %2$s',
			self::MIN_PHP_VERSION,
			PHP_VERSION,
			$this->get_plugin_name()
		);
		echo '</strong></p></div>';
	}

	/**
	 * Displays a warning when installed in an old Wordpress version.
	 */
	public function wp_version_error() {
		echo '<div class="error"><p><strong>';
		printf(
			'Error: %2$s requires WordPress version %1$s or greater.',
			self::MIN_WP_VERSION,
			$this->get_plugin_name()
		);
		echo '</strong></p></div>';
	}

	/**
	 * Get the name of this plugin.
	 *
	 * @return string The plugin name.
	 */
	private function get_plugin_name() {
		$data = get_plugin_data( __FILE__ );
		return $data['Name'];
	}

	// -------------------------------------------------------------------------
	// Apply settings values
	// -------------------------------------------------------------------------
	
	private function apply_settings() {

		$this->caos_remove_wp_cron();

		$this->check_referral_spam_disable();

		if ( ! is_admin() ) {
			$this->add_ga_header_script();
			$this->check_pages_disable();
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
			wp_enqueue_script( 'wp-disable-css-lazy-load',  plugin_dir_url( dirname( __FILE__ ) ) . 'js/css-lazy-load.min.js' );
			wp_localize_script( 'wp-disable-css-lazy-load', 'WpDisableAsyncLinks', $async_links );
		}
	}

	public function dequeue_styles(){

		$settings = $this->get_settings_values();

		if( ! is_admin() && ! is_admin_bar_showing() && isset( $settings['disable_front_dashicons_when_disabled_toolbar'] ) && $settings['disable_front_dashicons_when_disabled_toolbar'] ){
			wp_deregister_style('dashicons');
		}
	}

	public function dequeue_scripts() {

		$settings = $this->get_settings_values();

		$enabled_woocommerce = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );

		$invalid_disable = is_page('lost_password');

		$wc_invalid_disable = !$enabled_woocommerce || $invalid_disable || is_account_page() || is_checkout();

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
		$old_val = get_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
		if( false === $old_val || ( false !== $old_val && $count > (int) $old_val ) ){
			update_option( self::OPTION_KEY . '_combined_font_awesome_requests_number', $count );
		}
	}

	private function update_saved_font_awesome_requests( $count ) {
		$count = ! isset( $count ) ? 0 : (int) $count;
		$old_val = get_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		if( false === $old_val || ( false !== $old_val && $count > (int)  $old_val ) ){
			update_option( self::OPTION_KEY . '_combined_google_fonts_requests_number', $count );
		}
	}

	public static function saved_external_requests(){
		$google_fonts = (int) get_option( self::OPTION_KEY . '_combined_google_fonts_requests_number' );
		$font_awesome = (int) get_option( self::OPTION_KEY . '_combined_font_awesome_requests_number' );
		$google_fonts_saved = 1 < $google_fonts ? $google_fonts - 1 : 0;
		$font_awesome_saved = 1 < $font_awesome ? $font_awesome - 1 : 0;
		return $google_fonts_saved + $font_awesome_saved;
	}

	private function reset_saved_google_fonts_request() {

	}

	private function reset_saved_font_awesome_requests() {

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

		if ( isset( $this ) ) {
			$settings = $this->get_settings_values();
		} else {
			$settings = get_option( self::OPTION_KEY . '_settings', array() );
		}

		if ( isset( $settings['spam_comments_cleaner'] ) && 1 === $settings['spam_comments_cleaner'] && isset( $settings['delete_spam_comments'] ) && $settings['delete_spam_comments'] ) {
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
			add_action( 'template_redirect', array( $this, 'redirect_athor_pages' ) );
		}
	}

	public function redirect_athor_pages(){
		if( get_query_var( 'author' ) || get_query_var( 'author_name' ) ){
			wp_redirect( home_url(), 307 );
			exit;
		}
	}

	public function comment_admin_menu_remove(){
		remove_menu_page('edit-comments.php');
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
		$content = preg_replace( '/<a[^>]*href=[^>]*>|<\/[^a]*a[^>]*>/i','',$content );
		echo $content;
	}

	public function disable_comments_authors_links( $author_link ) {
		return strip_tags( $author_link );
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
			if ( isset( $_GET['feed'] ) ) {
				wp_redirect( esc_url_raw( remove_query_arg( 'feed' ) ), 301 );
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
			$requested_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$new_url = preg_replace( '#' . $struct . '/?$#', '', $requested_url );

			if ( $new_url !== $requested_url ) {
				wp_redirect( $new_url, 301 );
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

	private function caos_remove_wp_cron() {

		$settings = $this->get_settings_values();

		if ( isset( $settings['caos_remove_wp_cron'] ) ){
			// Remove script from wp_cron if option is selected.
			if( 'on' === esc_attr( $settings['caos_remove_wp_cron'] ) ){
				wp_clear_scheduled_hook( 'update_local_ga' );
			}
			else if( ! wp_next_scheduled( 'update_local_ga' ) ) {
				wp_schedule_event( time(), 'daily', 'update_local_ga' );
			}
		}
	}

	private function add_ga_header_script() {

		$ds_tracking_id = get_transient( 'wpperformance_ds_tracking_id' );

		if ( false === $ds_tracking_id ) {

			$settings = $this->get_settings_values();

			$ds_tracking_id = isset( $settings['ds_tracking_id'] ) && $settings['ds_tracking_id'] ? esc_attr( $settings['ds_tracking_id'] ) : '';
			set_transient( 'wpperformance_ds_tracking_id', $ds_tracking_id, 60 * 60 * 24 );	// Keep transient for one day.
		}

		if ( '' !== $ds_tracking_id ) {

			$local_ga_file = dirname( dirname( __FILE__ ) ) . '/cache/local-ga.js';
			// If file is not created yet, create now!
			if( ! file_exists( $local_ga_file ) ){
				ob_start();
				do_action('update_local_ga');
				ob_end_clean();
			}

			$ds_script_position = isset( $settings['ds_script_position'] ) && $settings['ds_script_position'] ? esc_attr( $settings['ds_script_position'] ) : null;

			if ( isset( $settings['ds_enqueue_order'] ) && $settings['ds_enqueue_order'] ) {
				$ds_enqueue_order = esc_attr( $settings['ds_enqueue_order'] );
				$ds_enqueue_order = $ds_enqueue_order ? $ds_enqueue_order : 0;
			} else {
				$ds_enqueue_order = 0;
			}

			switch ( $ds_script_position ) {
				case 'footer':
					add_action( 'wp_footer', 'wpperformance_add_ga_header_script', $ds_enqueue_order );
					break;
				default:
					add_action( 'wp_head', 'wpperformance_add_ga_header_script', $ds_enqueue_order );
			}
		}
	}

	public function check_referral_spam_disable(){
		$settings = $this->get_settings_values();
		if ( isset( $settings['disable_referral_spam'] ) && 1 === $settings['disable_referral_spam'] ) {

			add_filter('request', array($this, 'filter_referral_spam_requests'), 0);
		}
	}

	public function filter_referral_spam_requests($request){
		global $wp_query;
		
		$referrer = wp_get_referer() !== false ? wp_get_referer() : $_SERVER['HTTP_REFERER'];	// Input var okay.		
		
		if ( empty( $referrer ) ) {
			return $request;
		}

		$referrer = parse_url($referrer, PHP_URL_HOST);
		
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
            get_template_part(404);
            exit();
        }

        return $request;
	}

	private function referrals_blacklist(){

		$ret = get_transient( self::OPTION_KEY . '_referalls_spam_blacklist' );
		
		if( false === $ret ){
		
			$response = wp_remote_get('http://wielo.co/referrer-spam.php');
			
			if ($response instanceof WP_Error) {
	            error_log('Unable to get referrals spam blacklist: ' . $response->get_error_message());
	            return;
	        }

	        $ret = $response['body'];

	        if (empty($ret)) {
	            error_log('Invalid referrals spam blacklist response');
	            return;
	        }

	        $ret = json_decode($ret, true);

	        if (null === $ret) {
	            error_log('Invalid referrals spam blacklist data');
	            return;
	        }

	        set_transient( self::OPTION_KEY . '_referalls_spam_blacklist', $ret, DAY_IN_SECONDS );	// Refresh daily.
	    }

        return $ret;
	}
}
