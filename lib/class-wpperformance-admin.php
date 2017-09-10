<?php
class WpPerformance_Admin {

	public function __construct() {
		add_action( 'init', array( $this, 'wp_performance_disable_emojis' ) );
		add_action( 'init', array( $this, 'wp_performance_speed_stop_loading_wp_embed' ) );
		add_filter( 'script_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_filter( 'style_loader_src', array( $this, 'wp_performance_remove_script_version' ), 15, 1 );
		add_action( 'init', array( $this, 'wp_performace_disable_woo_stuffs' ) );
		add_action( 'init', array( $this, 'wp_performance_optimize_cleanups' ) );
		add_action( 'wp_loaded', array( $this, 'wp_performance_disable_google_maps' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_performance_dequeue_woocommerce_cart_fragments' ), 11 );
		add_action( 'wp_loaded', array( $this, 'wp_performance_save_dashboard_settings' ) );
		$this->heartbeat_handler();
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
					wp_dequeue_script( 'jquery-blockui' );
					wp_dequeue_script( 'jquery-placeholder' );
					wp_dequeue_script( 'woocommerce' );
					wp_dequeue_script( 'jquery-cookie' );
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

	public function wp_performance_save_dashboard_settings(){
		if ( is_admin() && current_user_can( 'manage_options' ) ) {

			$post_req = $_POST;

			if( isset( $post_req['wpperformance_g_analytics_settings_nonce'] ) ){

				if( wp_verify_nonce( $post_req['wpperformance_g_analytics_settings_nonce'], 'wpperformance-g-analytics-settings-nonce' ) ) {

					$options = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

					$options['ds_tracking_id'] = isset( $post_req['ds_tracking_id'] ) ? sanitize_text_field( $post_req['ds_tracking_id'] ) : null;
					$options['ds_adjusted_bounce_rate'] = isset( $post_req['ds_adjusted_bounce_rate'] ) ? sanitize_text_field( $post_req['ds_adjusted_bounce_rate']) : 0;
					$options['ds_enqueue_order'] = isset( $post_req['ds_enqueue_order'] ) ? sanitize_text_field( $post_req['ds_enqueue_order'] ) : 0;
					$options['ds_anonymize_ip'] = isset( $post_req['ds_anonymize_ip'] ) ? sanitize_text_field( $post_req['ds_anonymize_ip'] ) : null;
					
					$options['ds_script_position'] = isset( $post_req['ds_script_position'] ) ? sanitize_text_field( $post_req['ds_script_position'] ) : null;
					$options['caos_disable_display_features'] = isset( $post_req['caos_disable_display_features'] ) ? sanitize_text_field( $post_req['caos_disable_display_features'] ) : null;					
					$options['ds_track_admin'] = isset( $post_req['ds_track_admin'] ) ? sanitize_text_field( $post_req['ds_track_admin'] ) : null;
					$options['caos_remove_wp_cron'] = isset( $post_req['caos_remove_wp_cron'] ) ? sanitize_text_field( $post_req['caos_remove_wp_cron'] ) : null;

					$settings = update_option( WpPerformance::OPTION_KEY . '_settings', $options );

					WpPerformance::delete_transients();
				}
			}

			if( isset( $post_req['wpperformance_admin_settings_nonce'] ) ){

				if( wp_verify_nonce( $post_req['wpperformance_admin_settings_nonce'], 'wpperformance-admin-nonce' ) ) {

					$prev_settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

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
						'exclude_from_disable_google_maps'   => isset( $post_req['exclude_from_disable_google_maps'] ) ? trim( $post_req['exclude_from_disable_google_maps'] ) : '',
					);

					$options['ds_tracking_id'] = isset($prev_settings['ds_tracking_id']) ? $prev_settings['ds_tracking_id'] : null;
					$options['ds_adjusted_bounce_rate'] = isset($prev_settings['ds_adjusted_bounce_rate']) ? $prev_settings['ds_adjusted_bounce_rate'] : 0;
					$options['ds_enqueue_order'] = isset($prev_settings['ds_enqueue_order']) ? $prev_settings['ds_enqueue_order'] : 0;
					$options['ds_anonymize_ip'] = isset($prev_settings['ds_anonymize_ip']) ? $prev_settings['ds_anonymize_ip'] : null;
					$options['ds_script_position'] = isset($prev_settings['ds_script_position']) ? $prev_settings['ds_script_position'] : null;
					$options['caos_disable_display_features'] = isset($prev_settings['caos_disable_display_features']) ? $prev_settings['caos_disable_display_features'] : null;
					$options['ds_track_admin'] = isset($prev_settings['ds_track_admin']) ? $prev_settings['ds_track_admin'] : null;
					$options['caos_remove_wp_cron'] = isset($prev_settings['caos_remove_wp_cron']) ? $prev_settings['caos_remove_wp_cron'] : null;

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
		}
	}

	public static function offload_google_analytics_settings($settings = array()){
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		?>
		<form action="<?php echo esc_url( admin_url( 'admin.php?page=optimisationio-dashboard' ) ); ?>" method="post" class="offload-g-analytics-form">
			<div class="form-group">
				<label><?php esc_html_e( 'GA Code', 'wpperformance' ); ?></label>
				<input type="text" name="ds_tracking_id" value="<?php echo (isset( $settings['ds_tracking_id'] ))?$settings['ds_tracking_id']:''; ?>" />
			</div>
			<div class="form-group">
				<label><?php esc_html_e( 'Save GA in (please ensure you remove any other GA tracking)', 'wpperformance' ); ?></label>
				<?php
				$sgal_script_position = array( 'header', 'footer' );
				if ( ! isset( $settings['ds_script_position'] ) || ( 'header' !== $settings['ds_script_position'] && 'footer' !== $settings['ds_script_position'] ) ) {
					$settings['ds_script_position'] = 'header';
				}
				foreach ( $sgal_script_position as $option ) {
					echo "<input type='radio' name='ds_script_position' value='" . $option . "' " . ( $option === $settings['ds_script_position'] ? ' checked="checked"' : '' ) . ' /> <span>' . esc_html( ucfirst( $option ) ) . '</span>&nbsp;&nbsp;';
				} ?>
			</div>
			<div class="form-group">
				<label><?php esc_html_e( 'Use adjusted bounce rate?', 'wpperformance' ); ?></label>
				<input type="number" name="ds_adjusted_bounce_rate" min="0" max="60" value="<?php echo isset( $settings['ds_adjusted_bounce_rate'] )?$settings['ds_adjusted_bounce_rate']:0; ?>" />
			</div>
			<div class="form-group">
				<label><?php esc_html_e( 'Change enqueue order? (Default = 0)', 'wpperformance' ); ?></label>
				<input type="number" name="ds_enqueue_order" min="0" value="<?php echo isset( $settings['ds_enqueue_order'] )?$settings['ds_enqueue_order']:0; ?>" />
			</div>
			<div class="form-group">
				<input type="checkbox" name="caos_disable_display_features" <?php if ( isset( $settings['caos_disable_display_features'] ) && 'on' === $settings['caos_disable_display_features'] ) { echo 'checked = "checked"';} ?> />  Disable all <a href="https://developers.google.com/analytics/devguides/collection/analyticsjs/display-features" target="_blank">display features functionality</a>?
			</div>
			<div class="form-group">
				<input type="checkbox" name="ds_anonymize_ip" <?php if ( isset( $settings['ds_anonymize_ip'] ) && 'on' === $settings['ds_anonymize_ip'] ) { echo 'checked = "checked"';} ?> />  Use <a href="https://support.google.com/analytics/answer/2763052?hl=en" target="_blank">Anonymize IP</a>? (Required by law for some countries)
			</div>
			<div class="form-group">
				<input type="checkbox" name="ds_track_admin" <?php if ( isset( $settings['ds_track_admin'] ) && 'on' === $settings['ds_track_admin'] ) { echo 'checked = "checked"';} ?> /> <?php esc_html_e( 'Track logged in Administrators?', 'wpperformance' ); ?>
			</div>
			<div class="form-group">
				<input type="checkbox" name="caos_remove_wp_cron" <?php if ( isset( $settings['caos_remove_wp_cron'] ) && 'on' === $settings['caos_remove_wp_cron'] ) { echo 'checked="checked"'; } ?> /> <?php esc_html_e( 'Remove script from wp-cron?', 'wpperformance' ); ?>
			</div>
			<br/>
			<input type="submit" class="button button-primary button-large" value="<?php echo esc_attr("Save", "wpperformance"); ?>" />

			<?php wp_nonce_field( 'wpperformance-g-analytics-settings-nonce', 'wpperformance_g_analytics_settings_nonce' ); ?>
		</form>
		<?php		
	}

	public static function addon_settings(){ 
		
		$default_values = array(
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
			'disable_woocommerce_reviews'           => 0,
			'disable_woocommerce_password_meter'    => 0,
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
			'disable_google_maps'                   => 0,
			'exclude_from_disable_google_maps'		=> '',
			'ds_tracking_id'                        => null,
			'ds_anonymize_ip'                       => 'off',
			'ds_script_position'                    => null,
			'caos_disable_display_features'         => 'off',
			'ds_track_admin'                        => 'off',
			'caos_remove_wp_cron'                   => 'off',
			'disable_wordpress_password_meter'		=> 0,
			'disable_front_dashicons_when_disabled_toolbar' => 0,
		);
		
		$sett = get_option( WpPerformance::OPTION_KEY . '_settings', $default_values );

		$public_post_types = get_post_types( array( 'public' => true ) );
		?>
		<div class="addon-settings" data-sett-group="wp-disable">
			
			<form action="<?php echo esc_url( admin_url( 'admin.php?page=optimisationio-dashboard' ) ); ?>" method="post">

				<div class="addon-settings-tabs">
					<ul>
						<li data-tab-setting="requests" class="active"><?php esc_html_e('Requests', 'optimisationio'); ?></li>
						<li data-tab-setting="tags"><?php esc_html_e('Tags', 'optimisationio'); ?></li>
						<li data-tab-setting="admin"><?php esc_html_e('Admin', 'optimisationio'); ?></li>
						<li data-tab-setting="others"><?php esc_html_e('Others', 'optimisationio'); ?></li>
					</ul>
				</div>

				<div class="addon-settings-section">

					<div data-tab-setting="requests" class="addon-settings-content auto-table-layout active">
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Emojis', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_emoji', isset( $sett['disable_emoji'] ) && 1 === (int) $sett['disable_emoji']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove Querystrings', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_querystrings', isset( $sett['remove_querystrings'] ) && 1 === (int) $sett['remove_querystrings']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Embeds', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_embeds', isset( $sett['disable_embeds'] ) && 1 === (int) $sett['disable_embeds']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Google Maps', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_google_maps', isset( $sett['disable_google_maps'] ) && 1 === (int) $sett['disable_google_maps']); ?></div>
						</div>
						<div class="field sub-field disable-google-maps-group">
							<div class="field-left" style="vertical-align:top;"><?php printf( __( 'Exclude pages from %1$s Disable Google Maps %2$s filter', 'optimisationio' ), '<strong>', '</strong>' ); ?></div>
							<div class="field-right">
								<input type="text" name="exclude_from_disable_google_maps" value="<?php if ( isset( $sett['exclude_from_disable_google_maps'] ) ) { echo $sett['exclude_from_disable_google_maps']; } ?>" /><br/>
								<small style="display:inline-block; padding-top:5px;"><?php printf('%s Posts %s or %s Pages IDs %s separated by a', '<strong>', '</strong>', '<strong>', '</strong>' ); ?> <code>,</code></small>
							</div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Referral Spam', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_referral_spam', isset( $sett['disable_referral_spam'] ) && 1 === (int) $sett['disable_referral_spam']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php printf( __( 'Minimize requests and load %1$sGoogle Fonts%2$s asynchronous', 'optimisationio' ), '<strong>', '</strong>' ); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('lazy_load_google_fonts', isset( $sett['lazy_load_google_fonts'] ) && 1 === (int) $sett['lazy_load_google_fonts']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php printf( __( 'Minimize requests and load %1$sFont Awesome%2$s asynchronous', 'optimisationio' ), '<strong>', '</strong>' ); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('lazy_load_font_awesome', isset( $sett['lazy_load_font_awesome'] ) && 1 === (int) $sett['lazy_load_font_awesome']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable WordPress password strength meter js on non related pages', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_wordpress_password_meter', isset( $sett['disable_wordpress_password_meter'] ) && 1 === (int) $sett['disable_wordpress_password_meter']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Dashicons when user disables admin toolbar when viewing site', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_front_dashicons_when_disabled_toolbar', isset( $sett['disable_front_dashicons_when_disabled_toolbar'] ) && 1 === (int) $sett['disable_front_dashicons_when_disabled_toolbar']); ?></div>
						</div>
					</div>

					<div data-tab-setting="tags" class="addon-settings-content auto-table-layout">
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove RSD (Really Simple Discovery) tag', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_rsd', isset( $sett['remove_rsd'] ) && 1 === (int) $sett['remove_rsd']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove Shortlink Tag', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_shortlink_tag', isset( $sett['remove_shortlink_tag'] ) && 1 === (int) $sett['remove_shortlink_tag']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove Wordpress API from header', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_wordpress_api_from_header', isset( $sett['remove_wordpress_api_from_header'] ) && 1 === (int) $sett['remove_wordpress_api_from_header']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove Windows Live Writer tag', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_windows_live_writer', isset( $sett['remove_windows_live_writer'] ) && 1 === (int) $sett['remove_windows_live_writer']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Remove Wordpress Generator Tag', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_wordpress_generator_tag', isset( $sett['remove_wordpress_generator_tag'] ) && 1 === (int) $sett['remove_wordpress_generator_tag']); ?></div>
						</div>
					</div>
					
					<div data-tab-setting="admin" class="addon-settings-content auto-table-layout">
						<div class="field">
							<div class="field-left"><?php esc_html_e( 'Posts revisions number', 'optimisationio'); ?></div>
							<div class="field-right">
								<?php
									
									$revisions_num = array(
										'default' => __( 'WordPress default', 'optimisationio' ),
										'0' => 0,
										'1' => 1,
										'2' => 2,
										'3' => 3,
										'4' => 4,
										'5' => 5,
										'10' => 10,
										'15' => 15,
										'20' => 20,
										'25' => 25,
										'30' => 30,
									);
									
									$selected_val = 'default';

									if ( isset( $sett['disable_revisions'] ) ) {
										if ( 0 === $sett['disable_revisions'] ) {
											$selected_val = 'default';	// @note: Cover older plugin's version possible value.
										} elseif ( 1 === $sett['disable_revisions'] ) {
											$selected_val = 0;	// @note: Cover older plugin's version possible value.
										} else {
											$selected_val = isset( $revisions_num[ $sett['disable_revisions'] ] ) ? $sett['disable_revisions'] : 'default';
										}
									}
									?>
									<select name="disable_revisions">
										<?php
										foreach ( $revisions_num as $key => $val ) {
											if ( 'default' === $selected_val ) {
												$is_selected = $selected_val === $key;
											} else {
												$is_selected = (int) $selected_val === (int) $key;
											}
											echo '<option value="' . esc_attr( $key ) . '" ' . ( $is_selected ? ' selected' : '' ) . '>' . $val . '</option>';
										}
										?>
									</select>
							</div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Autosave', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_autosave', isset( $sett['disable_autosave'] ) && 1 === (int) $sett['disable_autosave']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable admin notices', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_admin_notices', isset( $sett['disable_admin_notices'] ) && 1 === (int) $sett['disable_admin_notices']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable author pages', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_author_pages', isset( $sett['disable_author_pages'] ) && 1 === (int) $sett['disable_author_pages']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable all comments', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_all_comments', isset( $sett['disable_all_comments'] ) && 1 === (int) $sett['disable_all_comments']); ?></div>
						</div>
						<div class="field sub-field comments-group">
							<div class="field-left"><?php esc_attr_e('Disable comments on certain post types', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_comments_on_certain_post_types', isset( $sett['disable_comments_on_certain_post_types'] ) && 1 === (int) $sett['disable_comments_on_certain_post_types']); ?></div>
						</div>
						
						<?php
						foreach ( $public_post_types as $key => $value ) { ?>
							<div class="field sub-sub-field certain-posts-comments-group">
								<div class="field-left"><?php printf( __( 'Disable comments on post type "%1$s%2$s%3$s"', 'optimisationio' ), '<strong>', $value, '</strong>' ); ?></div>

								<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_comments_on_post_types['.$value.']', isset($sett['disable_comments_on_post_types'][$value]) && 1 === (int) $sett['disable_comments_on_post_types'][$value] ); ?></div>
							</div> <?php
						} ?>

						<div class="field sub-field comments-group">
							<div class="field-left"><?php esc_attr_e('Close comments after 28 days', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('close_comments', isset( $sett['close_comments'] ) && 1 === (int) $sett['close_comments'] && get_option('close_comments_for_old_posts') && 28 === (int) get_option('close_comments_days_old') ); ?></div>
						</div>
						<div class="field sub-field comments-group">
							<div class="field-left"><?php esc_attr_e('Paginate comments at 20', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('paginate_comments', isset( $sett['paginate_comments'] ) && 1 === (int) $sett['paginate_comments'] && get_option('page_comments') && 20 === (int) get_option('comments_per_page') ); ?></div>
						</div>
						<div class="field sub-field comments-group">
							<div class="field-left"><?php esc_attr_e('Remove links from comments', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('remove_comments_links', isset( $sett['remove_comments_links'] ) && 1 === (int) $sett['remove_comments_links']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_html_e( 'Heartbeat frequency', 'optimisationio'); ?></div>
							<div class="field-right">
								<?php
								$seconds = ' ' . __( 'seconds', 'optimisationio' );
								$heartbeat_frequencies = array(
									'default' => __( 'WordPress default', 'optimisationio' ),
									'15' => 15 . $seconds,
									'20' => 20 . $seconds,
									'25' => 25 . $seconds,
									'30' => 30 . $seconds,
									'35' => 35 . $seconds,
									'40' => 40 . $seconds,
									'45' => 45 . $seconds,
									'50' => 50 . $seconds,
									'55' => 55 . $seconds,
									'60' => 60 . $seconds,
								);
								$selected_val = 'default';
								if ( isset( $sett['heartbeat_frequency'] ) ) {
									$selected_val = isset( $heartbeat_frequencies[ $sett['heartbeat_frequency'] ] ) ? $sett['heartbeat_frequency'] : 'default';
								}
								?>
								<select name="heartbeat_frequency">
									<?php
									foreach ( $heartbeat_frequencies as $key => $val ) {
										if ( 'default' === $selected_val ) {
											$is_selected = $selected_val === $key;
										} else {
											$is_selected = (int) $selected_val === (int) $key;
										}
										echo '<option value="' . esc_attr( $key ) . '" ' . ( $is_selected ? ' selected' : '' ) . '>' . $val . '</option>';
									}
									?>
								</select>
							</div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_html_e( 'Heartbeat locations', 'optimisationio'); ?></div>
							<div class="field-right">
								<?php
								$heartbeat_location = array(
									'default' => __( 'WordPress default', 'optimisationio' ),
									'disable_everywhere' => __( 'Disable everywhere', 'optimisationio' ),
									'disable_on_dashboard_page' => __( 'Disable on dashboard page', 'optimisationio' ),
									'allow_only_on_post_edit_pages' => __( 'Allow only on post edit pages', 'optimisationio' ),
								);
								$selected_val = 'default';
								if ( isset( $sett['heartbeat_location'] ) ) {
									$selected_val = isset( $heartbeat_location[ $sett['heartbeat_location'] ] ) ? $sett['heartbeat_location'] : 'default';
								}
								?>
								<select name="heartbeat_location" style="height:100%;border-color:#dedede;border-radius:2px;">
									<?php
									foreach ( $heartbeat_location as $key => $val ) {
										$is_selected = $selected_val === $key;
										echo '<option value="' . esc_attr( $key ) . '" ' . ( $is_selected ? ' selected' : '' ) . '>' . $val . '</option>';
									}
									?>
								</select>
							</div>
						</div>
					</div>
					
					<div data-tab-setting="others" class="addon-settings-content auto-table-layout">
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable pingbacks and trackbacks', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('default_ping_status', isset( $sett['default_ping_status'] ) && 1 === (int) $sett['default_ping_status']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable feeds', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_rss', isset( $sett['disable_rss'] ) && 1 === (int) $sett['disable_rss']); ?></div>
						</div>
						<div class="field sub-field feeds-group">
							<div class="field-left">
								<label>
									<input type="radio" name="disabled_feed_behaviour" value="redirect" <?php echo isset( $sett['disabled_feed_behaviour'] ) && '404_error' !== $sett['disabled_feed_behaviour'] ? 'checked="checked"' : ''; ?> /> <span><?php esc_html_e( 'Redirect feed requests to corresponding HTML content', 'optimisationio' ); ?></span>
								</label>
								<br/>
								<br/>
								<label>
									<input type="radio" name="disabled_feed_behaviour" value="404_error" <?php echo isset( $sett['disabled_feed_behaviour'] ) && '404_error' === $sett['disabled_feed_behaviour'] ? 'checked="checked"' : ''; ?> /> <span><?php esc_html_e( 'Issue a "Page Not Found (404)" error for feed requests', 'optimisationio' ); ?></span>
								</label>
							</div>
							<div class="field-right"></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable XML-RPC', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_xmlrpc', isset( $sett['disable_xmlrpc'] ) && 1 === (int) $sett['disable_xmlrpc']); ?></div>
						</div>
						<div class="field">
							<div class="field-left"><?php esc_attr_e('Disable Gravatars', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('disable_gravatars', isset( $sett['disable_gravatars'] ) && 1 === (int) $sett['disable_gravatars']); ?></div>
						</div>
						<div class="field comments-group">
							<div class="field-left"><?php esc_attr_e('Enable spam comments cleaner', 'optimisationio'); ?></div>
							<div class="field-right"><?php Optimisationio_Dashboard::checkbox_component('spam_comments_cleaner', isset( $sett['spam_comments_cleaner'] ) && 1 === (int) $sett['spam_comments_cleaner']); ?></div>
						</div>
						<div class="field sub-field delete-spam-comments-group comments-group">
							<div class="field-left"><?php esc_html_e( 'Delete spam comments', 'optimisationio'); ?></div>
							<div class="field-right">
								<?php
								$options = array(
									'hourly' => __( 'Once Hourly', 'optimisationio' ),
									'twicedaily' => __( 'Twice Daily', 'optimisationio' ),
									'daily' => __( 'Once Daily', 'optimisationio' ),
									'weekly' => __( 'Once Weekly', 'optimisationio' ),
									'twicemonthly' => __( 'Twice Monthly', 'optimisationio' ),
									'monthly' => __( 'Once Monthly', 'optimisationio' ),
								);
								
								$selected_val = 'daily';
								
								if ( isset( $sett['delete_spam_comments'] ) && isset( $options[ $sett['delete_spam_comments'] ] ) ) {
									$selected_val = $sett['delete_spam_comments'];
								} ?>
								
								<select name="delete_spam_comments"> <?php
									foreach ( $options as $key => $val ) {
										echo '<option value="' . esc_attr( $key ) . '" ' . ($selected_val === $key ? ' selected' : '') . '>' . $val . '</option>';
									} ?>
								</select>
							</div>
						</div>
						<div class="field sub-field delete-spam-comments-group comments-group">
							<div class="field-left"> <?php
								$next_scheduled = wp_next_scheduled( 'delete_spam_comments' );
								if ( $next_scheduled ) {
									printf( __( 'Next spam delete: %s', 'optimisationio' ), '<br/><strong><i>' . date( 'l, F j, Y @ h:i a',( $next_scheduled ) ) . '</i></strong>' );
								} ?>
							</div>
							<div class="field-right"> 
								<?php echo submit_button( __( 'Delete spam comments now', 'optimisationio' ) , 'large submit', 'delete_spam_comments_now', false ); ?>
							</div>
						</div>
					</div>
				</div>
				
				<div class="addon-settings-actions-section">
					<input type="submit" class="button button-primary button-large" name="" value="<?php echo esc_attr("Save settings", "optimisationio"); ?>" />
				</div>

				<?php wp_nonce_field( 'wpperformance-admin-nonce', 'wpperformance_admin_settings_nonce' ); ?>

			</form>
		</div>
		<?php
	}
}
