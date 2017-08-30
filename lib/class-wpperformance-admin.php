<?php
class WpPerformance_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ), 9 );
		add_action( 'init', array( $this, 'wp_performance_disable_emojis' ) );
		add_action( 'init', array( $this, 'wp_performance_speed_stop_loading_wp_embed' ) );
		add_filter( 'script_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_filter( 'style_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_action( 'init', array( $this, 'wp_performace_disable_woo_stuffs' ) );
		add_action( 'init', array( $this, 'wp_performance_optimize_cleanups' ) );
		add_action( 'wp_loaded', array( $this, 'wp_performance_disable_google_maps' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_performance_dequeue_woocommerce_cart_fragments' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_performance_admin_script' ) );
		$this->heartbeat_handler();
	}

	public function wp_performance_admin_script($hook){
		if ( preg_match( '/optimisationio-wp-disable/i', $hook ) ) {
			wp_enqueue_style( 'wp-disable-style', plugin_dir_url( dirname( __FILE__ ) ) . 'css/wp-disable-style.css' );
		}
	}

	public function menu() {
		
		add_submenu_page( 
			'optimisationio-statistics-and-addons', 
			__( 'WP Disable', 'wpperformance' ), 
			__( 'WP Disable', 'wpperformance' ), 
			'manage_options', 
			'optimisationio-wp-disable', 
			array( $this, 'addsettings' ) 
		);

		add_submenu_page( 
			'', 
			__( 'Update Settings', 'wpperformance' ), 
			__( 'Update Settings', 'wpperformance' ), 
			'manage_options', 
			'updatewpperformance-settings', 
			array( $this, 'updatesettings' ) 
		);
	}

	public function addsettings() {
		$default_array = array(
			'disable_gravatars' 					=> 0,
			'disable_referral_spam'					=> 0,
			'disable_emoji'                         => 0,
			'disable_embeds'                        => 0,
			'remove_querystrings'                   => 0,
			'lazy_load_google_fonts' 				=> 0,
			'lazy_load_font_awesome' 				=> 0,
			'default_ping_status'                   => 0,
			'disable_all_comments' 				 	=> 0,
			'disable_author_pages'					=> 0,
			'disable_comments_on_certain_post_types' => 0,
			'disable_comments_on_post_types' 	 	=> array(),
			'close_comments'                        => 0,
			'paginate_comments'                     => 0,
			'remove_comments_links'					=> 0,
			'heartbeat_frequency'					=> 'default',
			'heartbeat_location'					=> 'default',
			'disable_woocommerce_non_pages'         => 0,
			'disable_woocommerce_cart_fragments' 	=> 0,
			'remove_rsd'                            => 0,
			'remove_windows_live_writer'            => 0,
			'remove_wordpress_generator_tag'        => 0,
			'remove_shortlink_tag'                  => 0,
			'remove_wordpress_api_from_header'      => 0,
			'disable_rss'                           => 0,
			'disabled_feed_behaviour'				=> 'redirect',
			'not_disable_global_feeds'				=> 0,
			'disable_xmlrpc'                        => 0,
			'spam_comments_cleaner'					=> 0,
			'delete_spam_comments'					=> 'daily',
			'disable_autosave'                      => 0,
			'disable_admin_notices'					=> 0,
			'disable_revisions'                     => 'default',
			'disable_woocommerce_reviews'           => 0,
			'disable_google_maps'                   => 0,
			'ds_tracking_id'                        => null,
			'ds_anonymize_ip'                       => 'off',
			'ds_script_position'                    => null,
			'caos_disable_display_features'         => 'off',
			'ds_track_admin'                        => 'off',
			'caos_remove_wp_cron'                   => 'off',
			'disable_woocommerce_password_meter'    => 0,
			'disable_wordpress_password_meter'		=> 0,
			'disable_front_dashicons_when_disabled_toolbar' => 0,
		);
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', $default_array );
		$data     = array(
			'settings' => $settings,
		);
		echo WpPerformance_View::render( 'admin-settings', $data );
	}

	public function updatesettings() {

		$post_req = $_POST; // Input var okay.

		if ( ! isset( $post_req['wpperformance_admin_settings_nonce'] ) || ! wp_verify_nonce( $post_req['wpperformance_admin_settings_nonce'], 'wpperformance-admin-nonce' ) ) {
			$this->redirect_url( admin_url( 'admin.php?page=optimisationio-wp-disable' ) );
			exit;
		}

		$options = array(
			'disable_gravatars'                  => isset( $post_req['disable_gravatars'] ) ? 1 : 0,
			'disable_referral_spam' 			 => isset( $post_req['disable_referral_spam'] ) ? 1 : 0,
			'disable_emoji'                      => isset( $post_req['disable_emoji'] ) ? 1 : 0,
			'disable_embeds'                     => isset( $post_req['disable_embeds'] ) ? 1 : 0,
			'remove_querystrings'                => isset( $post_req['remove_querystrings'] ) ? 1 : 0,
			'lazy_load_google_fonts'             => isset( $post_req['lazy_load_google_fonts'] ) ? 1 : 0,
			'lazy_load_font_awesome' 			 => isset( $post_req['lazy_load_font_awesome'] ) ? 1 : 0,
			'default_ping_status'                => isset( $post_req['default_ping_status'] ) ? 1 : 0,
			'disable_all_comments' 				 => isset( $post_req['disable_all_comments'] ) ? 1 : 0,
			'disable_author_pages' 				 => isset( $post_req['disable_author_pages'] ) ? 1 : 0,
			'disable_comments_on_certain_post_types' => isset( $post_req['disable_comments_on_certain_post_types'] ) ? 1 : 0,
			'disable_comments_on_post_types' 	 => isset( $post_req['disable_comments_on_post_types'] ) ? $post_req['disable_comments_on_post_types'] : array(),
			'close_comments'                     => isset( $post_req['close_comments'] ) ? 1 : 0,
			'paginate_comments'                  => isset( $post_req['paginate_comments'] ) ? 1 : 0,
			'remove_comments_links' 			 => isset( $post_req['remove_comments_links'] ) ? 1 : 0,
			'heartbeat_frequency'				 => isset( $post_req['heartbeat_frequency'] ) ? $post_req['heartbeat_frequency'] : 'default',
			'heartbeat_location'				 => isset( $post_req['heartbeat_location'] ) ? $post_req['heartbeat_location'] : 'default',
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
			'delete_spam_comments' 			 	 => isset( $post_req['delete_spam_comments'] ) ? $post_req['delete_spam_comments'] : 'daily',
			'disable_autosave'                   => isset( $post_req['disable_autosave'] ) ? 1 : 0,
			'disable_admin_notices' 			 => isset( $post_req['disable_admin_notices'] ) ? 1 : 0,
			'disable_revisions'                  => isset( $post_req['disable_revisions'] ) ? $post_req['disable_revisions'] : 'default',
			'disable_woocommerce_non_pages'      => isset( $post_req['disable_woocommerce_non_pages'] ) ? 1 : 0,
			'disable_woocommerce_cart_fragments' => isset( $post_req['disable_woocommerce_cart_fragments'] ) ? 1 : 0,
			'disable_woocommerce_reviews'        => isset( $post_req['disable_woocommerce_reviews'] ) ? 1 : 0,
			'disable_woocommerce_password_meter' => isset( $post_req['disable_woocommerce_password_meter'] ) ? 1 : 0,
			'disable_wordpress_password_meter' => isset( $post_req['disable_wordpress_password_meter'] ) ? 1 : 0,
			'disable_front_dashicons_when_disabled_toolbar' => isset( $post_req['disable_front_dashicons_when_disabled_toolbar'] ) ? 1 : 0,
			'disable_google_maps'                => isset( $post_req['disable_google_maps'] ) ? 1 : 0,
			'ds_tracking_id'                     => sanitize_text_field( (isset( $post_req['ds_tracking_id'] ) && $post_req['ds_tracking_id']) ? $post_req['ds_tracking_id'] : null ),
			'ds_adjusted_bounce_rate'            => sanitize_text_field( (isset( $post_req['ds_adjusted_bounce_rate'] ) && $post_req['ds_adjusted_bounce_rate']) ? $post_req['ds_adjusted_bounce_rate'] : 0 ),
			'ds_enqueue_order'                   => sanitize_text_field( (isset( $post_req['ds_enqueue_order'] ) && $post_req['ds_enqueue_order']) ? $post_req['ds_enqueue_order'] : 0 ),
			'ds_anonymize_ip'                    => sanitize_text_field( (isset( $post_req['ds_anonymize_ip'] ) && $post_req['ds_anonymize_ip']) ? $post_req['ds_anonymize_ip'] : null ),
			'ds_script_position'                 => sanitize_text_field( (isset( $post_req['ds_script_position'] ) && $post_req['ds_script_position']) ? $post_req['ds_script_position'] : null ),
			'caos_disable_display_features'      => sanitize_text_field( (isset( $post_req['caos_disable_display_features'] ) && $post_req['caos_disable_display_features']) ? $post_req['caos_disable_display_features'] : null ),
			'ds_track_admin'                     => sanitize_text_field( (isset( $post_req['ds_track_admin'] ) && $post_req['ds_track_admin']) ? $post_req['ds_track_admin'] : null ),
			'caos_remove_wp_cron'                => sanitize_text_field( (isset( $post_req['caos_remove_wp_cron'] ) && $post_req['caos_remove_wp_cron']) ? $post_req['caos_remove_wp_cron'] : null ),
		);

		WpPerformance::synchronize_discussion_data( $post_req );

		$settings = update_option( WpPerformance::OPTION_KEY . '_settings', $options );

		WpPerformance::delete_transients();

		if ( isset( $post_req['delete_spam_comments_now'] ) ) {
			WpPerformance::delete_spam_comments();
			WpPerformance::check_spam_comments_delete( true );
		} else {
			WpPerformance::check_spam_comments_delete( false );
		}

		$this->add_message( 'Settings updated successfully' );

		$tab_attr = isset( $post_req['active_tab'] ) ? '&tab=' . $post_req['active_tab'] : '';
		$this->redirect_url( admin_url( 'admin.php?page=optimisationio-wp-disable' ) . $tab_attr );
	}

	private function add_message( $msg, $type = 'success' ) {
		if ( 'success' === $type ) {
			printf( "<div class='updated'><p><strong>%s</strong></p></div>", $msg );
		} else {
			printf( "<div class='error'><p><strong>%s</strong></p></div>", $msg );
		}
	}

	private function redirect_url( $url ) {
		echo '<script>window.location.href="' . $url . '";</script>';
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
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		
		// disable remove query strings for users who are not able to edit pages/posts or admin panel
		if ( !(current_user_can('edit_page') || current_user_can('edit_post') || is_admin()) &&  isset( $settings['remove_querystrings'] ) && 1 === (int) $settings['remove_querystrings'] ) {
			$parts = explode( '?ver', $src );
			return $parts[0];
		} else {
			return $src;
		}
	}

	public function wp_performace_disable_woo_stuffs() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['disable_woocommerce_non_pages'] ) && 1 === (int) $settings['disable_woocommerce_non_pages'] ) {
			add_action( 'wp_print_scripts', array( $this, 'wp_performance_woocommerce_de_script' ), 100 );
			add_action( 'wp_enqueue_scripts', array( $this, 'wp_performance_remove_woocommerce_generator' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'child_manage_woocommerce_css' ) );
		}
	}

	public function wp_performance_remove_woocommerce_generator() {
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() ) {
				// if we're not on a woo page, remove the generator tag.
				remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
			}
		}
	}

	public function child_manage_woocommerce_css() {
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
				// this adds the styles back on woocommerce pages. If you're using a custom script, you could remove these and enter in the path to your own CSS file (if different from your basic style.css file).
				wp_dequeue_style( 'woocommerce-layout' );
				wp_dequeue_style( 'woocommerce-smallscreen' );
				wp_dequeue_style( 'woocommerce-general' );
			}
		}
	}

	public function wp_performance_woocommerce_de_script() {
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() && ! is_account_page() ) {
				// if we're not on a Woocommerce page, dequeue all of these scripts.
				wp_dequeue_script( 'wc-add-to-cart' );
				wp_dequeue_script( 'jquery-blockui' );
				wp_dequeue_script( 'jquery-placeholder' );
				wp_dequeue_script( 'woocommerce' );
				wp_dequeue_script( 'jquery-cookie' );
				wp_dequeue_script( 'wc-cart-fragments' );
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

		if ( isset( $settings['disable_woocommerce_reviews'] ) && $settings['disable_woocommerce_reviews'] ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'wcs_woo_remove_reviews_tab' ), 98 );
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
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['disable_google_maps'] ) && 1 === (int) $settings['disable_google_maps'] ) {
			ob_start( 'wpperformance_disable_google_maps_ob_end' );
		}
	}

	public function wp_performance_dequeue_woocommerce_cart_fragments() {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if ( isset( $settings['disable_woocommerce_cart_fragments'] ) && 1 === (int) $settings['disable_woocommerce_cart_fragments'] ) {
			if ( is_front_page() ) {
				wp_dequeue_script( 'wc-cart-fragments' );
			}
		}
	}

	public function heartbeat_stop(){
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		switch( $settings['heartbeat_location'] ){
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
		$args['interval'] = (int) $settings['heartbeat_frequency'];
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
}
