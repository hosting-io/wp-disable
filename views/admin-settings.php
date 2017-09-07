<?php
$active_tab = isset( $_GET['tab'] ) ? esc_attr( $_GET['tab'] ) : 'requests';	// Input var okay.
$active_tab = ! $active_tab || '' === $active_tab ? 'requests' : $active_tab;
$public_post_types = get_post_types( array( 'public' => true ) );
?>
<div class="wrap wp-disable">
	
	<h2><?php echo sprintf( "WP Disable", '<strong>', '</strong>' ); ?></h2>

	<br/>

	<div class="container" style="padding:0;">
		<form method="post" id="wp-disable-form" action="<?php echo admin_url( 'tools.php?page=updatewpperformance-settings' ); ?>" style="display:inline-block;width:100%;">

			<div class="tab-wrap">

				<ul class="tabs">
					<li class="tab-link <?php echo 'requests' === $active_tab ? ' current' : '' ?>" data-tab="requests"><?php esc_html_e( 'Requests', 'wpperformance' ); ?></li>
					
					<?php if( WpPerformance::is_woocommerce_enabled() ) { ?>
					<li class="tab-link <?php echo 'woocommerce' === $active_tab ? ' current' : '' ?>" data-tab="woocommerce"><?php esc_html_e( 'WooCommerce', 'wpperformance' ); ?></li>
					<?php } ?>

					<li class="tab-link <?php echo 'tags' === $active_tab ? ' current' : '' ?>" data-tab="tags"><?php esc_html_e( 'Tags', 'wpperformance' ); ?></li>
					<li class="tab-link <?php echo 'admin' === $active_tab ? ' current' : '' ?>" data-tab="admin"><?php esc_html_e( 'Admin', 'wpperformance' ); ?></li>
					<li class="tab-link <?php echo 'others' === $active_tab ? ' current' : '' ?>" data-tab="others"><?php esc_html_e( 'Others', 'wpperformance' ); ?></li>
				</ul>

				<div id="requests" class="tab-content  <?php echo 'requests' === $active_tab ? ' current' : '' ?>">
					<div class="form">
						<div class="disable-form disable_settings">

							<div class="form-group">
								<span><?php esc_html_e( 'Disable Emojis', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_emoji" type="checkbox" id="disable_emoji" <?php if ( isset( $settings['disable_emoji'] ) && 1 === $settings['disable_emoji'] ) { echo 'checked="checked"'; } ?> value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Remove Querystrings', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_querystrings" <?php if ( isset( $settings['remove_querystrings'] ) && 1 === $settings['remove_querystrings'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_querystrings" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable Embeds', 'wpperformance' ); ?></span>
								<label class="switch">
									<td style="width:300px; text-align:left;"><input name="disable_embeds" type="checkbox" id="disable_embeds" <?php if ( isset( $settings['disable_embeds'] ) && 1 === $settings['disable_embeds'] ) { echo 'checked="checked"'; } ?> value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							
							<div class="form-group">
								<span><?php esc_html_e( 'Disable Google Maps', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_google_maps" <?php if ( isset( $settings['disable_google_maps'] ) && 1 === $settings['disable_google_maps'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_google_maps" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

							<div class="form-group disable-google-maps-group">
								<span><?php esc_html_e( 'Exclude pages from "Disable Google Maps"', 'wpperformance' ); ?></span>
								<br/>
								<input type="text" name="exclude_from_disable_google_maps" value="<?php if ( isset( $settings['exclude_from_disable_google_maps'] ) ) { echo $settings['exclude_from_disable_google_maps']; } ?>" style="width:100%; padding:10px 10px 12px" />
								<br/>
								<p><span><small><?php esc_html_e('Post or Pages IDs separated by a', 'wpperformance' ); ?> <code>,</code></small></span></p>
							</div>
							
							<div class="form-group">
								<span><?php esc_html_e( 'Disable Referral Spam', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_referral_spam" type="checkbox" id="disable_referral_spam" <?php if ( isset( $settings['disable_referral_spam'] ) && 1 === $settings['disable_referral_spam'] ) { echo 'checked="checked"'; } ?> value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

							<div class="form-group">
								<span><?php printf( __( 'Minimize requests and load %1$sGoogle Fonts%2$s asynchronous', 'wpperformance' ), '<strong>', '</strong>' ); ?></span>
								<label class="switch">
									<input name="lazy_load_google_fonts" <?php if ( isset( $settings['lazy_load_google_fonts'] ) && 1 === $settings['lazy_load_google_fonts'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="lazy_load_google_fonts" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

							<div class="form-group">	
								<span><?php printf( __( 'Minimize requests and load %1$sFont Awesome%2$s asynchronous', 'wpperformance' ), '<strong>', '</strong>' ); ?></span>
								<label class="switch">
									<input name="lazy_load_font_awesome" <?php if ( isset( $settings['lazy_load_font_awesome'] ) && 1 === $settings['lazy_load_font_awesome'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="lazy_load_font_awesome" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>


							<div class="form-group">
								<span><?php esc_html_e( 'Disable WordPress password strength meter js on non related pages', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_wordpress_password_meter" <?php if ( isset( $settings['disable_wordpress_password_meter'] ) && 1 === $settings['disable_wordpress_password_meter'] ) { echo 'checked="checked"'; } ?>  type="checkbox" id="disable_wordpress_password_meter" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>


							<div class="form-group">
								<span><?php esc_html_e( 'Disable Dashicons when user disables admin toolbar when viewing site', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_front_dashicons_when_disabled_toolbar" <?php if ( isset( $settings['disable_front_dashicons_when_disabled_toolbar'] ) && 1 === $settings['disable_front_dashicons_when_disabled_toolbar'] ) { echo 'checked="checked"'; } ?>  type="checkbox" id="disable_front_dashicons_when_disabled_toolbar" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

						</div>
					</div>
				</div>
				
				<?php if( WpPerformance::is_woocommerce_enabled() ) { ?>

				<div id="woocommerce" class="tab-content <?php echo 'woocommerce' === $active_tab ? ' current' : '' ?>">
					<div class="form">
						<div class="disable-form disable_settings">
							<div class="form-group">
								<span><?php esc_html_e( 'Disable WooCommerce scripts and CSS on non WooCommerce pages', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_woocommerce_non_pages" <?php if ( isset( $settings['disable_woocommerce_non_pages'] ) && 1 === $settings['disable_woocommerce_non_pages'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_woocommerce_non_pages" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable WooCommerce Reviews', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_woocommerce_reviews" <?php if ( isset( $settings['disable_woocommerce_reviews'] ) && 1 === $settings['disable_woocommerce_reviews'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_woocommerce_reviews" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Defer WooCommerce Cart Fragments', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_woocommerce_cart_fragments" <?php if ( isset( $settings['disable_woocommerce_cart_fragments'] ) && 1 === $settings['disable_woocommerce_cart_fragments'] ) { echo 'checked="checked"'; } ?>  type="checkbox" id="disable_woocommerce_cart_fragments" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable WooCommerce password strength meter js on non related pages', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_woocommerce_password_meter" <?php if ( isset( $settings['disable_woocommerce_password_meter'] ) && 1 === $settings['disable_woocommerce_password_meter'] ) { echo 'checked="checked"'; } ?>  type="checkbox" id="disable_woocommerce_password_meter" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
						</div>
					</div>
				</div>

				<?php } ?>

				<div id="tags" class="tab-content <?php echo 'tags' === $active_tab ? ' current' : '' ?>">
					<div class="form">
						<div class="disable-form disable_settings">
							<div class="form-group">
								<span><?php esc_html_e( 'Remove RSD (Really Simple Discovery) tag', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_rsd" <?php if ( isset( $settings['remove_rsd'] ) && 1 === $settings['remove_rsd'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_rsd" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Remove Shortlink Tag', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_shortlink_tag" <?php if ( isset( $settings['remove_shortlink_tag'] ) && 1 === $settings['remove_shortlink_tag'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_shortlink_tag" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Remove Wordpress API from header', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_wordpress_api_from_header" <?php if ( isset( $settings['remove_wordpress_api_from_header'] ) && 1 === $settings['remove_wordpress_api_from_header'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_wordpress_api_from_header" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Remove Windows Live Writer tag', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_windows_live_writer" <?php if ( isset( $settings['remove_windows_live_writer'] ) && 1 === $settings['remove_windows_live_writer'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_windows_live_writer" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Remove Wordpress Generator Tag', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_wordpress_generator_tag" <?php if ( isset( $settings['remove_wordpress_generator_tag'] ) && 1 === $settings['remove_wordpress_generator_tag'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_wordpress_generator_tag" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
						</div>
					</div>
				</div>

				<div id="admin" class="tab-content <?php echo 'admin' === $active_tab ? ' current' : '' ?>">
					<div class="form">
						<div class="disable-form disable_settings">
							<div class="form-group">
								<span><?php esc_html_e( 'Posts revisions number', 'wpperformance' ); ?></span>
								<label class="switch" style="width:auto;">
									<?php
									$revisions_num = array(
										'default' => __( 'WordPress default', 'wpperformance' ),
										'0' => 0,
										'3' => 3,
										'5' => 5,
										'10' => 10,
									);
									$selected_val = 'default';
									if ( isset( $settings['disable_revisions'] ) ) {

										if ( 0 === $settings['disable_revisions'] ) {
											$selected_val = 'default';	// @note: Cover older plugin's version possible value.
										} elseif ( 1 === $settings['disable_revisions'] ) {
											$selected_val = 0;	// @note: Cover older plugin's version possible value.
										} else {
											$selected_val = isset( $revisions_num[ $settings['disable_revisions'] ] ) ? $settings['disable_revisions'] : 'default';
										}
									}
									?>
									<select name="disable_revisions" style="height:100%;border-color:#dedede;border-radius:2px;">
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
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable Autosave', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_autosave" <?php if ( isset( $settings['disable_autosave'] ) && 1 === $settings['disable_autosave'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_autosave" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable admin notices', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_admin_notices" <?php if ( isset( $settings['disable_admin_notices'] ) && 1 === $settings['disable_admin_notices'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_autosave" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable author pages', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_author_pages" <?php if ( isset( $settings['disable_author_pages'] ) && 1 === $settings['disable_author_pages'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_author_pages" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable all comments', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_all_comments" <?php if ( isset( $settings['disable_all_comments'] ) && 1 === $settings['disable_all_comments'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_all_comments" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group comments-group">
								<span><?php esc_html_e( 'Disable comments on certain post types', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_comments_on_certain_post_types" <?php if ( isset( $settings['disable_comments_on_certain_post_types'] ) && 1 === $settings['disable_comments_on_certain_post_types'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_comments_on_certain_post_types" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<?php
							foreach ( $public_post_types as $key => $value ) { ?>
								<div class="form-group comments-group certain-posts-comments-group">
									<span><?php printf( __( 'Disable comments on post type "%1$s%2$s%3$s"', 'wpperformance' ), '<strong>', $value, '</strong>' ); ?></span>
									<label class="switch">
										<input name="disable_comments_on_post_types[<?php echo $value; ?>]" type="checkbox" value="1" <?php echo isset( $settings['disable_comments_on_post_types'][ $value ] ) && 1 === (int) $settings['disable_comments_on_post_types'][ $value ] ? 'checked="checked"': ''; ?> />
										<div class="slider round"></div>
									</label>
								</div> <?php
							} ?>
							<div class="form-group comments-group">
								<span><?php esc_html_e( 'Close comments after 28 days', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="close_comments" <?php if ( isset( $settings['close_comments'] ) && 1 === $settings['close_comments'] && get_option( 'close_comments_for_old_posts' ) && 28 === (int) get_option( 'close_comments_days_old' ) ) { echo 'checked="checked"'; } ?> type="checkbox" id="close_comments" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group comments-group">
								<span><?php esc_html_e( 'Paginate comments at 20', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="paginate_comments" <?php if ( isset( $settings['paginate_comments'] ) && 1 === $settings['paginate_comments'] && get_option( 'page_comments' ) && 20 === (int) get_option( 'comments_per_page' ) ) { echo 'checked="checked"'; } ?> type="checkbox" id="paginate_comments" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group comments-group">
								<span><?php esc_html_e( 'Remove links from comments', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="remove_comments_links" <?php if ( isset( $settings['remove_comments_links'] ) && 1 === $settings['remove_comments_links'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="remove_comments_links" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Heartbeat frequency', 'wpperformance' ); ?></span>
								<label class="switch" style="width:auto">
									<?php
									$seconds = ' ' . __( 'seconds', 'wpperformance' );
									$heartbeat_frequencies = array(
										'default' => __( 'WordPress default', 'wpperformance' ),
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
									if ( isset( $settings['heartbeat_frequency'] ) ) {
										$selected_val = isset( $heartbeat_frequencies[ $settings['heartbeat_frequency'] ] ) ? $settings['heartbeat_frequency'] : 'default';
									}
									?>
									<select name="heartbeat_frequency" style="height:100%;border-color:#dedede;border-radius:2px;">
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
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Heartbeat locations', 'wpperformance' ); ?></span>
								<label class="switch" style="width:auto">
									<?php
									$heartbeat_location = array(
										'default' => __( 'WordPress default', 'wpperformance' ),
										'disable_everywhere' => __( 'Disable everywhere', 'wpperformance' ),
										'disable_on_dashboard_page' => __( 'Disable on dashboard page', 'wpperformance' ),
										'allow_only_on_post_edit_pages' => __( 'Allow only on post edit pages', 'wpperformance' ),
									);
									$selected_val = 'default';
									if ( isset( $settings['heartbeat_location'] ) ) {
										$selected_val = isset( $heartbeat_location[ $settings['heartbeat_location'] ] ) ? $settings['heartbeat_location'] : 'default';
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
								</label>
							</div>
						</div>
					</div>
				</div>

				<div id="others" class="tab-content <?php echo 'others' === $active_tab ? ' current' : '' ?>">
					<div class="form">
						<div class="disable-form disable_settings">
							<div class="form-group">
								<span><?php esc_html_e( 'Disable Gravatars', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_gravatars" <?php if ( ( isset( $settings['disable_gravatars'] ) && 1 === $settings['disable_gravatars'] ) || 0 === (int) get_option( 'show_avatars' ) ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_gravatars" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable pingbacks and trackbacks', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="default_ping_status" <?php if ( isset( $settings['default_ping_status'] ) && 1 === $settings['default_ping_status'] && 'close' === get_option( 'default_ping_status' ) ) { echo 'checked="checked"'; } ?> type="checkbox" id="default_ping_status" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>							
							<div class="form-group">
								<span><?php esc_html_e( 'Disable feeds', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_rss" <?php if ( isset( $settings['disable_rss'] ) && 1 === $settings['disable_rss'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_rss" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group feeds-group">
								<label>
									<input type="radio" name="disabled_feed_behaviour" value="redirect" <?php echo isset( $settings['disabled_feed_behaviour'] ) && '404_error' !== $settings['disabled_feed_behaviour'] ? 'checked="checked"' : ''; ?> /> <span><?php esc_html_e( 'Redirect feed requests to corresponding HTML content', 'wpperformance' ); ?></span>
								</label>
								<br/>
								<label>
									<input type="radio" name="disabled_feed_behaviour" value="404_error" <?php echo isset( $settings['disabled_feed_behaviour'] ) && '404_error' === $settings['disabled_feed_behaviour'] ? 'checked="checked"' : ''; ?> /> <span><?php esc_html_e( 'Issue a Page Not Found (404) error for feed requests', 'wpperformance' ); ?></span>
								</label>
							</div>
							<div class="form-group feeds-group">
								<span><?php printf( __( 'Do not disable the %1$sglobal post feed%2$s and %3$sglobal comment feed%4$s', 'wpperformance' ), '<strong>', '</strong>', '<strong>', '</strong>' ); ?></span>
								<label class="switch">
									<input name="not_disable_global_feeds" <?php if ( isset( $settings['not_disable_global_feeds'] ) && 1 === $settings['not_disable_global_feeds'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="not_disable_global_feeds" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>
							<div class="form-group">
								<span><?php esc_html_e( 'Disable XML-RPC', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="disable_xmlrpc" <?php if ( isset( $settings['disable_xmlrpc'] ) && 1 === $settings['disable_xmlrpc'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="disable_xmlrpc" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

							<div class="form-group comments-group">
								<span><?php esc_html_e( 'Enable spam comments cleaner', 'wpperformance' ); ?></span>
								<label class="switch">
									<input name="spam_comments_cleaner" <?php if ( isset( $settings['spam_comments_cleaner'] ) && 1 === $settings['spam_comments_cleaner'] ) { echo 'checked="checked"'; } ?> type="checkbox" id="spam_comments_cleaner" value="1"/>
									<div class="slider round"></div>
								</label>
							</div>

							<div class="form-group delete-spam-comments-group comments-group">
								<span><?php esc_html_e( 'Delete spam comments', 'wpperformance' ); ?></span>
								<label class="switch" style="width:auto;">
									<?php

									$options = array(
										'hourly' => __( 'Once Hourly', 'wpperformance' ),
										'daily' => __( 'Once Daily', 'wpperformance' ),
										'twicedaily' => __( 'Twice Daily', 'wpperformance' ),
										'weekly' => __( 'Once Weekly', 'wpperformance' ),
										'twicemonthly' => __( 'Twice Monthly', 'wpperformance' ),
										'monthly' => __( 'Once Monthly', 'wpperformance' ),
									);
									$selected_val = 'daily';
									if ( isset( $settings['delete_spam_comments'] ) && isset( $options[ $settings['delete_spam_comments'] ] ) ) {
										$selected_val = $settings['delete_spam_comments'];
									}
									?>
									<select name="delete_spam_comments" style="height:100%;border-color:#dedede;border-radius:2px;">
										<?php
										foreach ( $options as $key => $val ) {
											echo '<option value="' . esc_attr( $key ) . '" ' . ($selected_val === $key ? ' selected' : '') . '>' . $val . '</option>';
										}
										?>
									</select>
								</label>
							</div>

							<div class="form-group delete-spam-comments-group comments-group">
								<span>
								<?php
									$next_scheduled = wp_next_scheduled( 'delete_spam_comments' );
								if ( $next_scheduled ) {
									echo '<small>';
									printf( __( 'Next spam delete: %s', 'wpperformance' ), '<strong>' . date( 'l, F j, Y @ h:i a',( $next_scheduled ) ) . '</strong>' );
									echo '</small>';
								}
								?>	
								</span>	
								<span style="float:right;">
									<?php echo submit_button( __( 'Delete spam comments Now', 'wpperformance' ) , 'large submit', 'delete_spam_comments_now', false ); ?>
								</span>
								
							</div>

						</div>
					</div>
				</div>

				<div><?php echo submit_button( __( 'Update', 'wpperformance' ) , 'btn-submit', 'submit', false ); ?></div>

			</div>

			<input type="hidden" id="active_tab" name="active_tab" value="<?php echo $active_tab; ?>" />

			<?php wp_nonce_field( 'wpperformance-admin-nonce', 'wpperformance_admin_settings_nonce' ); ?>

		</form>
	</div>
</div>
<script type="text/javascript">
	(function ($) {

		"use strict";

		var $disableCommentsInput = $('input[name="disable_all_comments"]'),
			$disableCertainPostsCommentsInput = $('input[name="disable_comments_on_certain_post_types"]'),
			$disableFeedsInput = $('input[name="disable_rss"]'),
			$spamCommentsCleaner = $('input[name="spam_comments_cleaner"]'),
			$disableGoogleMapsInput = $('input[name="disable_google_maps"]');

		function on_tab_click( $tab ){
			var tab_id = $tab.attr('data-tab');
			$('ul.tabs li, .tab-content').removeClass('current');
			$tab.addClass('current');
			$("#" + tab_id ).addClass('current');
			$('#active_tab').val( tab_id );
		}
		
		function on_change_disable_all_comments(){
			var isChecked = $disableCommentsInput.is(":checked");
			$('.comments-group').css('display', isChecked ? 'none' : '');
			on_change_disable_certain_post_types_comments();
		}

		function on_change_disable_certain_post_types_comments(){
			$('.certain-posts-comments-group').css('display', ! $disableCertainPostsCommentsInput.is(":checked") ? 'none' : ( $disableCommentsInput.is(":checked") ? 'none' : '' ) );
		}

		function on_change_disable_feeds(){
			$('.feeds-group').css('display', $disableFeedsInput.is(":checked") ? '' : 'none');
		}

		function on_change_spam_comments_cleaner(){
			var isChecked = ! $disableCommentsInput.is(":checked") && $spamCommentsCleaner.is(":checked");
			$('.delete-spam-comments-group').css('display', isChecked ? '' : 'none');
		}

		function on_change_disable_google_maps(){
			$('.disable-google-maps-group').css('display', $disableGoogleMapsInput.is(":checked") ? '' : 'none');
		}

		$(function () {

			// Bind events.
			$('ul.tabs li').on('click', function(){ on_tab_click( $(this) ); });
			$disableCommentsInput.on('change', function(){ on_change_disable_all_comments(); });
			$disableCertainPostsCommentsInput.on('change', function(){ on_change_disable_certain_post_types_comments(); });
			$disableFeedsInput.on('change', function(){ on_change_disable_feeds(); });
			$spamCommentsCleaner.on('change', function(){ on_change_spam_comments_cleaner(); });
			$disableGoogleMapsInput.on('change', function(){ on_change_disable_google_maps(); });

			// Run initial checks.
			on_change_disable_all_comments();
			on_change_disable_certain_post_types_comments();
			on_change_disable_feeds();
			on_change_spam_comments_cleaner();
			on_change_disable_google_maps();
		});
	}(jQuery));
</script>
