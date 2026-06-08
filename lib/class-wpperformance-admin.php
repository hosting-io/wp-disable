<?php
defined( 'ABSPATH' ) || exit; // Prevent direct access.
class WpPerformance_Admin {

	public function __construct() {
		add_action( 'init', array( $this, 'wp_performance_yoast_seo_settings' ) );
		add_action( 'init', array( $this, 'wp_performance_disable_emojis' ) );
		add_action( 'init', array( $this, 'wp_performance_speed_stop_loading_wp_embed' ) );
		add_filter( 'script_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_filter( 'style_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_action( 'init', array( $this, 'wp_performace_disable_woo_stuffs' ) );
		add_action( 'init', array( $this, 'wp_performance_optimize_cleanups' ) );
		add_action( 'wp_loaded', array( $this, 'wp_performance_disable_google_maps' ) );
		add_action( 'wp_default_scripts', array( $this, 'remove_jquery_migrate' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_performance_dequeue_woocommerce_cart_fragments' ), 11 );
		add_action( 'wp_loaded', array( $this, 'wp_performance_save_dashboard_settings' ) );
		$this->heartbeat_handler();
	}

	public function wp_performance_yoast_seo_settings(){

		if ( defined( 'WPSEO_VERSION' ) ){

			$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

			if ( isset( $settings['remove_yoast_comment'] ) && 1 === (int) $settings['remove_yoast_comment'] ) {
				add_action( 'get_header', array( $this, 'remove_yoast_comment' ) );
				add_action( 'wp_head', array( $this, 'remove_yoast_comment_complete' ), 999 );
			}

			if ( isset( $settings['remove_yoast_breadcrumbs_duplicates'] ) && 1 === (int) $settings['remove_yoast_breadcrumbs_duplicates'] ) {
				add_filter('wpseo_breadcrumb_single_link', array( $this, 'remove_yoast_breadcrumb_last_link' ) );
			}
		}
	}

	public function remove_yoast_comment() {
		ob_start( array( $this, 'remove_yoast_comment_replace' ) );
	}

	public function remove_yoast_comment_replace($html) {
		return preg_replace( '/^<!--.*?[Y]oast.*?-->$/mi', '', $html );
	}

	public function remove_yoast_comment_complete() {
		ob_end_flush();
	}

	public function remove_yoast_breadcrumb_last_link($link_output) {
		return false !== strpos( $link_output, 'breadcrumb_last' ) ? '' : $link_output;
	}

	public function disable_emojis_tinymce( $plugins ) {
		$ret = is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
		return $ret;
	}

	public function wp_performance_disable_emojis() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['disable_emoji'] ) && 1 === (int) $settings['disable_emoji'] ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			add_filter( 'tiny_mce_plugins', array( $this, 'disable_emojis_tinymce' ) );
		}
	}

	public function wp_performance_speed_stop_loading_wp_embed() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['disable_embeds'] ) && 1 === (int) $settings['disable_embeds'] ) {
			if ( ! is_admin() ) {
				wp_deregister_script( 'wp-embed' );
			}
		}
	}

	public function wp_performance_remove_script_version( $src ) {
		if ( ! is_admin() && ! current_user_can('administrator') && ! current_user_can('editor') ) {
			$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
			if ( isset( $settings['remove_querystrings'] ) && 1 === (int) $settings['remove_querystrings'] ) {
				$parts = explode( '?ver', $src );
				return $parts[0];
			}
		}
		return $src;
	}

	public function wp_performace_disable_woo_stuffs() {
		if( WpPerformance::is_woocommerce_enabled() ){
			$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
			if ( isset( $settings['disable_woocommerce_non_pages'] ) && 1 === (int) $settings['disable_woocommerce_non_pages'] ) {
				add_action( 'wp_print_scripts', array( $this, 'wp_performance_woocommerce_de_script' ), 100 );
				add_action( 'wp_enqueue_scripts', array( $this, 'wp_performance_remove_woocommerce_generator' ), 99 );
				add_action( 'wp_enqueue_scripts', array( $this, 'child_manage_woocommerce_css' ) );
			}
		}
	}

	public function wp_performance_remove_woocommerce_generator() {
		if( WpPerformance::is_woocommerce_enabled() ){
			if ( function_exists( 'is_woocommerce' ) ) {
				if ( ! is_woocommerce() ) {
					// if we're not on a woo page, remove the generator tag.
					remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
				}
			}
		}
	}

	public function child_manage_woocommerce_css() {
		if( WpPerformance::is_woocommerce_enabled() ){
			if ( function_exists( 'is_woocommerce' ) ) {
				if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
					// this adds the styles back on woocommerce pages. If you're using a custom script, you could remove these and enter in the path to your own CSS file (if different from your basic style.css file).
					wp_dequeue_style( 'woocommerce-layout' );
					wp_dequeue_style( 'woocommerce-smallscreen' );
					wp_dequeue_style( 'woocommerce-general' );
				}
			}
		}
	}

	public function wp_performance_woocommerce_de_script() {
		if( WpPerformance::is_woocommerce_enabled() ){
			if ( function_exists( 'is_woocommerce' ) ) {
				if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
					// if we're not on a Woocommerce page, dequeue all of these scripts.
					wp_dequeue_script( 'wc-add-to-cart' );
					wp_dequeue_script( 'woocommerce' );
					wp_dequeue_script( 'wc-cart-fragments' );
				}
			}
		}
	}

	public function disabler_kill_autosave() {
		wp_deregister_script( 'autosave' );
	}

	public function wcs_woo_remove_reviews_tab( $tabs ) {
		unset( $tabs['reviews'] );
		return $tabs;
	}

	public function wp_performance_optimize_cleanups() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

		if ( isset( $settings['rsd_clean'] ) && $settings['rsd_clean'] ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( isset( $settings['remove_rsd'] ) && $settings['remove_rsd'] ) {
			remove_action ('wp_head', 'rsd_link');
		}

		if ( isset( $settings['remove_windows_live_writer'] ) && $settings['remove_windows_live_writer'] ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( isset( $settings['remove_wordpress_generator_tag'] ) && $settings['remove_wordpress_generator_tag'] ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		if ( isset( $settings['remove_shortlink_tag'] ) && $settings['remove_shortlink_tag'] ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		if ( isset( $settings['remove_wordpress_api_from_header'] ) && $settings['remove_wordpress_api_from_header'] ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head' );
		}

		if ( isset( $settings['disable_revisions'] ) ) {

			switch ( $settings['disable_revisions'] ) {
				case 'default':
					$this->wp_config_remove_post_revisions();
					break;
				default:
					$this->wp_config_set_post_revisions( (int) $settings['disable_revisions'] );
			}
		}

		if ( isset( $settings['disable_xmlrpc'] ) && $settings['disable_xmlrpc'] ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}

		if ( isset( $settings['disable_autosave'] ) && $settings['disable_autosave'] ) {
			add_action( 'wp_print_scripts', array( $this, 'disabler_kill_autosave' ) );
		}

		if( WpPerformance::is_woocommerce_enabled() ){
			if ( isset( $settings['disable_woocommerce_reviews'] ) && $settings['disable_woocommerce_reviews'] ) {
				add_filter( 'woocommerce_product_tabs', array( $this, 'wcs_woo_remove_reviews_tab' ), 98 );
			}
		}
	}

	private function wp_config_set_post_revisions( $revisions_num = null ) {

		if ( null !== $revisions_num ) {

			wpperformance_init_wp_filesystem();

			global $wp_filesystem;

			if ( $wp_filesystem ) {

				$revisions_num = (int) $revisions_num;

				$revisions_str = sprintf( "define('WP_POST_REVISIONS', %s); // Added by WP Disable\r\n", $revisions_num );

				$file = $this->wp_config_filepath();

				if ( $file ) {

					$contents = $wp_filesystem->get_contents_array( $file );

					if ( $contents ) {

						$exists = false;
						$need_update = true;

						foreach ( $contents as $key => $line ) {

							$found = preg_match( '/^define\(\s*\'([A-Z_]+)\',(.*)\)/', $line, $match );

							if ( $found && 'WP_POST_REVISIONS' === $match[1] ) {

								if ( $revisions_num === (int) $match[2] ) {
									$need_update = false;
								} else {
									$exists = true;
									$contents[ $key ] = $revisions_str;
								}
								break;
							}
						}

						if ( $need_update ) {

							if ( ! $exists ) {
								array_shift( $contents );
								array_unshift( $contents, "<?php\r\n", $revisions_str );
							}

							$wp_filesystem->put_contents(
								$file,
								implode( '', $contents ),
								FS_CHMOD_FILE // predefined mode settings for WP files
							);
						}

						unset( $contents );
					}// End if().
				}// End if().
			}// End if().
		}// End if().
	}

	private function wp_config_remove_post_revisions() {

		wpperformance_init_wp_filesystem();

		global $wp_filesystem;

		if ( $wp_filesystem ) {

			$file = $this->wp_config_filepath();

			if ( $file ) {

				$contents = $wp_filesystem->get_contents_array( $file );
				$contents_new = array();

				if ( $contents ) {

					$exists = false;

					foreach ( $contents as $key => $line ) {

						$found = preg_match( '/^define\(\s*\'([A-Z_]+)\',(.*)\)/', $line, $match );

						if ( $found && 'WP_POST_REVISIONS' === $match[1] ) {
							$exists = true;
						} else {
							$contents_new[] = $line;
						}
					}

					unset( $contents );

					if ( $exists ) {
						$wp_filesystem->put_contents(
							$file,
							implode( '', $contents_new ),
							FS_CHMOD_FILE // predefined mode settings for WP files
						);
					}

					unset( $contents_new );
				}
			}
		}// End if().
	}

	private function wp_config_filepath() {

		wpperformance_init_wp_filesystem();

		global $wp_filesystem;

		$config_file = ABSPATH . 'wp-config.php';
		$config_file_alt = dirname( ABSPATH ) . '/wp-config.php';

		if ( $wp_filesystem->exists( $config_file ) ) {
			return $config_file;
		} elseif ( $wp_filesystem->exists( $config_file_alt ) ) {
			return $config_file_alt;
		}

		// No writable file found
		return false;
	}

	public function wp_performance_disable_google_maps() {
		if ( !is_admin() ) {
			$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
			if ( isset( $settings['disable_google_maps'] ) && 1 === (int) $settings['disable_google_maps'] ) {
				ob_start( 'wpperformance_disable_google_maps_ob_end' );
			}
		}
	}

	public function remove_jquery_migrate( $scripts ) {
		if ( is_admin() ) {
			return;
		}

		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

		if ( isset( $settings['remove_jquery_migrate'] ) && 1 === (int) $settings['remove_jquery_migrate'] ) {
			// Drop jquery-migrate from the 'jquery' meta-handle's dependencies.
			// The old code re-registered jquery pinned to 1.12.4, which DOWNGRADED
			// core jQuery on modern WP (5.6+ ships jQuery 3.x) and broke sites.
			if ( ! empty( $scripts->registered['jquery'] ) && is_array( $scripts->registered['jquery']->deps ) ) {
				$scripts->registered['jquery']->deps = array_diff(
					$scripts->registered['jquery']->deps,
					array( 'jquery-migrate' )
				);
			}
		}
	}

	public function wp_performance_dequeue_woocommerce_cart_fragments() {
		if( WpPerformance::is_woocommerce_enabled() ){
			$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
			if ( isset( $settings['disable_woocommerce_cart_fragments'] ) && 1 === (int) $settings['disable_woocommerce_cart_fragments'] ) {
				if ( is_front_page() ) {
					wp_dequeue_script( 'wc-cart-fragments' );
				}
			}
		}
	}

	public function heartbeat_stop(){
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		$location = isset( $settings['heartbeat_location'] ) ? $settings['heartbeat_location'] : 'default';
		switch( $location ){
			case 'disable_everywhere':
				wp_deregister_script('heartbeat');
				break;
			case 'disable_on_dashboard_page':
				global $pagenow;
				if ( 'index.php' === $pagenow ){
					wp_deregister_script('heartbeat');
				}
				break;
			case 'allow_only_on_post_edit_pages':
				global $pagenow;
				if ( 'post.php' !== $pagenow && 'post-new.php' !== $pagenow ){
					wp_deregister_script('heartbeat');
				}
				break;
		}
	}

	public function heartbeat_frequency( $args ){
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['heartbeat_frequency'] ) ) {
			$args['interval'] = (int) $settings['heartbeat_frequency'];
		}
		return $args;
	}

	public function heartbeat_handler(){
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['heartbeat_frequency'] ) && $settings['heartbeat_frequency'] ) {
			if ( 0 < (int) $settings['heartbeat_frequency'] ) {
				add_filter( 'heartbeat_settings', array( $this, 'heartbeat_frequency' ) );
			}
		}
		if ( isset( $settings['heartbeat_location'] ) && 'default' !== $settings['heartbeat_location'] ) {
			add_action( 'init', array( $this, 'heartbeat_stop' ), 1 );
		}
	}

	public function wp_performance_save_dashboard_settings(){
		if ( is_admin() && current_user_can( 'manage_options' ) ) {

			$post_req = wp_unslash( $_POST );

			if( isset( $post_req['wpperformance_admin_settings_nonce'] ) ){

				if( wp_verify_nonce( sanitize_text_field( $post_req['wpperformance_admin_settings_nonce'] ), 'wpperformance-admin-nonce' ) ) {

					self::persist_settings( $post_req );
				}

			}
		}
	}

	/**
	 * Build, sanitise and persist the settings option from a request array.
	 *
	 * Shared by the legacy admin-form handler (above) and the Folium UI ajax
	 * save endpoint (Optimisationio_Dashboard::ajax_app_save). Callers MUST gate
	 * capability + nonce before calling this. Input uses the same checkbox
	 * semantics as the form: a toggle key present means "on", absent means "off".
	 *
	 * @param array $post_req Unslashed request data (real option field names).
	 */
	public static function persist_settings( array $post_req ) {

					$options = array(
						'disable_gravatars'                  => isset( $post_req['disable_gravatars'] ) ? 1 : 0,
						'disable_referral_spam' 			 => isset( $post_req['disable_referral_spam'] ) ? 1 : 0,
						'remove_jquery_migrate'				 => isset( $post_req['remove_jquery_migrate'] ) ? 1 : 0,
						'dns_prefetch'						 => isset( $post_req['dns_prefetch'] ) ? 1 : 0,
						'dns_prefetch_host_list'			 => isset( $post_req['dns_prefetch_host_list'] ) ? sanitize_textarea_field( $post_req['dns_prefetch_host_list'] ) : '',
						'disable_emoji'                      => isset( $post_req['disable_emoji'] ) ? 1 : 0,
						'disable_embeds'                     => isset( $post_req['disable_embeds'] ) ? 1 : 0,
						'remove_querystrings'                => isset( $post_req['remove_querystrings'] ) ? 1 : 0,
						'lazy_load_google_fonts'             => isset( $post_req['lazy_load_google_fonts'] ) ? 1 : 0,
						'lazy_load_font_awesome' 			 => isset( $post_req['lazy_load_font_awesome'] ) ? 1 : 0,
						'remove_yoast_comment'                => isset( $post_req['remove_yoast_comment'] ) ? 1 : 0,
						'remove_yoast_breadcrumbs_duplicates' => isset( $post_req['remove_yoast_breadcrumbs_duplicates'] ) ? 1 : 0,
						'default_ping_status'                => isset( $post_req['default_ping_status'] ) ? 1 : 0,
						'disable_all_comments' 				 => isset( $post_req['disable_all_comments'] ) ? 1 : 0,
						'disable_author_pages' 				 => isset( $post_req['disable_author_pages'] ) ? 1 : 0,
						'disable_comments_on_certain_post_types' => isset( $post_req['disable_comments_on_certain_post_types'] ) ? 1 : 0,
						'disable_comments_on_post_types' 	 => isset( $post_req['disable_comments_on_post_types'] ) && is_array( $post_req['disable_comments_on_post_types'] ) ? array_map( 'intval', $post_req['disable_comments_on_post_types'] ) : array(),
						'close_comments'                     => isset( $post_req['close_comments'] ) ? 1 : 0,
						'paginate_comments'                  => isset( $post_req['paginate_comments'] ) ? 1 : 0,
						'remove_comments_links' 			 => isset( $post_req['remove_comments_links'] ) ? 1 : 0,
						'heartbeat_frequency'				 => isset( $post_req['heartbeat_frequency'] ) ? sanitize_text_field( $post_req['heartbeat_frequency'] ) : 'default',
						'heartbeat_location'				 => isset( $post_req['heartbeat_location'] ) ? sanitize_text_field( $post_req['heartbeat_location'] ) : 'default',
						'remove_rsd'                         => isset( $post_req['remove_rsd'] ) ? 1 : 0,
						'remove_windows_live_writer'         => isset( $post_req['remove_windows_live_writer'] ) ? 1 : 0,
						'remove_wordpress_generator_tag'     => isset( $post_req['remove_wordpress_generator_tag'] ) ? 1 : 0,
						'remove_shortlink_tag'               => isset( $post_req['remove_shortlink_tag'] ) ? 1 : 0,
						'remove_wordpress_api_from_header'   => isset( $post_req['remove_wordpress_api_from_header'] ) ? 1 : 0,
						'disable_rss'                        => isset( $post_req['disable_rss'] ) ? 1 : 0,
						'not_disable_global_feeds' 			 => isset( $post_req['not_disable_global_feeds'] ) ? 1 : 0,
						'disabled_feed_behaviour'			 => isset( $post_req['disabled_feed_behaviour'] ) && '404_error' === $post_req['disabled_feed_behaviour'] ? '404_error' : 'redirect',
						'disable_xmlrpc'                     => isset( $post_req['disable_xmlrpc'] ) ? 1 : 0,
						'spam_comments_cleaner' 			 => isset( $post_req['spam_comments_cleaner'] ) ? 1 : 0,
						'delete_spam_comments' 			 	 => isset( $post_req['delete_spam_comments'] ) ? sanitize_text_field( $post_req['delete_spam_comments'] ) : 'daily',
						'disable_autosave'                   => isset( $post_req['disable_autosave'] ) ? 1 : 0,
						'disable_admin_notices' 			 => isset( $post_req['disable_admin_notices'] ) ? 1 : 0,
						'disable_revisions'                  => isset( $post_req['disable_revisions'] ) ? sanitize_text_field( $post_req['disable_revisions'] ) : 'default',
						'disable_woocommerce_non_pages'      => isset( $post_req['disable_woocommerce_non_pages'] ) ? 1 : 0,
						'disable_woocommerce_cart_fragments' => isset( $post_req['disable_woocommerce_cart_fragments'] ) ? 1 : 0,
						'disable_woocommerce_reviews'        => isset( $post_req['disable_woocommerce_reviews'] ) ? 1 : 0,
						'disable_woocommerce_password_meter' => isset( $post_req['disable_woocommerce_password_meter'] ) ? 1 : 0,
						'disable_wordpress_password_meter' => isset( $post_req['disable_wordpress_password_meter'] ) ? 1 : 0,
						'disable_front_dashicons_when_disabled_toolbar' => isset( $post_req['disable_front_dashicons_when_disabled_toolbar'] ) ? 1 : 0,
						'disable_google_maps'                => isset( $post_req['disable_google_maps'] ) ? 1 : 0,
						'exclude_from_disable_google_maps'   => isset( $post_req['exclude_from_disable_google_maps'] ) ? sanitize_text_field( trim( $post_req['exclude_from_disable_google_maps'] ) ) : '',
					);

					WpPerformance::synchronize_discussion_data( $post_req );

					$settings = update_option( WpPerformance::OPTION_KEY . '_settings', $options );

					WpPerformance::delete_transients();

					if ( isset( $post_req['delete_spam_comments_now'] ) ) {
						WpPerformance::delete_spam_comments();
						WpPerformance::check_spam_comments_delete( true );
					}
					else {
						WpPerformance::check_spam_comments_delete( false );
					}
	}

}
