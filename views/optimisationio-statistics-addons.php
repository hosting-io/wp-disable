<?php
$str_i18n = array(
	'n/a'	=> __( 'n/a', 'optimisationio' ),
	'install' => __( 'Install', 'optimisationio' ),
	'activate' => __( 'Activate', 'optimisationio' ),
	'deactivate' => __( 'Deactivate', 'optimisationio' ),
	'page_title' => __( '%1$s Optimisation.io %2$s', 'optimisationio' ),
	'changes_may_not_saved' => __('Changes you made may not be saved.', 'optimisationio'),
);

$disable_slug = 'wp-disable';
$cache_slug = 'cache-performance';
$img_compress_slug = 'wp-image-compression';

$addons = Optimisationio_Stats_And_Addons::$addons;

$active_addons_number = Optimisationio_Stats_And_Addons::active_addons_number();

if ( $addons[$img_compress_slug]['activated'] ) {
	global $wpdb;
	$image_compress_info = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "image_compression_settings", ARRAY_A);
}

$cache_info = $addons[$cache_slug]['activated'] ? Optimisationio_CacheEnabler::get_optimisation_info() : null;

$loading_gif = '<img src="' . admin_url('images/wpspin_light.gif') . '" alt="" />';
?>

<div class="wrap wp-disable">

	<div class="wrap-main">
	
		<div class="statistics-wrap">

			<div class="statistics-wrap-inner">
			
				<div class="statistics-top">
					<div class="statistics-top-cell logo">
						<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/logo-optimisation.png' ); ?>" alt="" />
					</div>
					<div class="statistics-top-cell manual-optimisation">
						<a href="https://optimisation.io/contact-us/" title="" target="_blank"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/logo-optimisation-line.png' ); ?>" alt="" /><?php _e( 'Request manual optimisation' , 'optimisationio' ); ?></a>
					</div>
					<div class="statistics-top-cell support">
						<a href="https://optimisation.io/contact-us/" title="" target="_blank"><img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'images/icon-support.png' ); ?>" alt="" /><?php _e( 'Support' , 'optimisationio' ); ?></a>
					</div>
				</div>

				<div class="statistics-tabs">
				
					<div class="statistics-tabs-content-wrap">
						
						<div data-tab="disable" class="statistics-tab-content">
							<ul>
								<li><?php esc_html_e( 'External requests saved', '' ); ?></li>
								<li><?php echo $addons[$disable_slug]['activated'] ? WpPerformance::saved_external_requests() : $str_i18n['n/a']; ?></li>
							</ul>
						</div>
						
						<div data-tab="images" class="statistics-tab-content">
							<ul>
								<li><?php esc_html_e( 'Pages average load time', 'optimisationio' ); ?></li>
								<li><?php echo $addons[$cache_slug]['activated'] ? Optimisationio::average_pages_load_time() : $str_i18n['n/a']; ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Cache size', 'optimisationio' ); ?></li>
								<li><?php Optimisationio_Stats_And_Addons::echo_stats_size( $addons[$cache_slug]['activated'], $addons[$cache_slug]['activated'] ? Optimisationio_CacheEnabler::get_cache_size() : 0 ); ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Database size', 'optimisationio' ); ?></li>
								<li><?php Optimisationio_Stats_And_Addons::echo_stats_size( $addons[$cache_slug]['activated'], $addons[$cache_slug]['activated'] ? $cache_info->optimised_size : 0 ); ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Database before cleanups', 'optimisationio' ); ?></li>
								<li><?php Optimisationio_Stats_And_Addons::echo_stats_size( $addons[$cache_slug]['activated'], $addons[$cache_slug]['activated'] ? $cache_info->size : 0 ); ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Database size saved', 'optimisationio' ); ?></li>
								<li><?php Optimisationio_Stats_And_Addons::echo_stats_size( $addons[$cache_slug]['activated'], $addons[$cache_slug]['activated'] ? $cache_info->saving : 0 ); ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Gravatars cache', 'optimisationio' ); ?></li>
								<li><?php echo $addons[$cache_slug]['activated'] ? Optimisationio_Admin::cache_gravatars_number() : $str_i18n['n/a']; ?></li>
							</ul>
						</div>
						
						<div data-tab="cache" class="statistics-tab-content">
							<ul>
								<li><?php esc_html_e( 'Compressed images', 'optimisationio' ); ?></li>
							    <li><?php echo $addons[$img_compress_slug]['activated'] ? $image_compress_info['total_image_optimized'] : $str_i18n['n/a']; ?></li>
							</ul>
							<ul>
								<li><?php esc_html_e( 'Size saved', 'optimisationio' ); ?></li>
							    <li><?php Optimisationio_Stats_And_Addons::echo_stats_size( $addons[$img_compress_slug]['activated'], $addons[$img_compress_slug]['activated'] ? 1000 * $image_compress_info['total_size_optimized'] : 0 ); ?></li>
							</ul>
						</div>

					</div>
				
					<ul class="statistics-tabs-nav">
						<li data-tab="disable"><?php _e( 'Disable' , 'optimisationio' ); ?></li>
						<li data-tab="images"><?php _e( 'Images' , 'optimisationio' ); ?></li>
						<li data-tab="cache"><?php _e( 'Cache' , 'optimisationio' ); ?></li>
					</ul>
				
				</div>

			</div>
		</div>

		<div class="add-ons-list">

			<div class="add-ons-list-inner">				

				<?php foreach( $addons as $k => $v ){ ?>

					<div class="add-on">
						
						<div class="add-on-title">
							<?php /* ?>
							<span class="state <?php Optimisationio_Stats_And_Addons::echo_addon_state_color( $v['activated'], $v['installed'] ); ?>"></span>
							<?php */ ?>
							<h3><?php echo $v['title']; ?></h3>
							<span class="on-process hidden"><?php echo $loading_gif; ?></span>
						</div>
						
						<div class="add-on-content">

							<div class="add-on-thumb">
								<a href="<?php echo esc_url( $v['homepage'] ); ?>" target="_blank" style="background-image:url(<?php echo esc_url( $v['thumb'] ); ?>);"></a>
							</div>
							
							<div class="add-on-descr">
								<p><?php echo $v['description']; ?></p>

								<div class="addon-buttons"
									 data-slug="<?php echo esc_attr($k); ?>"
									 data-file="<?php echo esc_attr($v['file']); ?>"
									 data-link="<?php echo esc_attr($v['download_link']); ?>">

									<button class="button button-primary install-addon <?php echo ! $v['installed'] ? '' : 'hidden'; ?>">
										<?php echo $str_i18n['install'] ?>
									</button>

									<button class="button button-primary activate-addon <?php echo $v['installed'] && ! $v['activated'] ? '' : 'hidden'; ?>"><?php echo $str_i18n['activate'] ?></button>
									<?php

									if( $v['installed'] && $v['activated'] ){
										// $cn = 1 === $active_addons_number ? "disabled" : "";
										$cn = "";
									}
									else{
										$cn = "hidden";
									}
									?>

									<button class="button deactivate-addon <?php echo $cn; ?>"><?php echo $str_i18n['deactivate'] ?></button>

								</div>
							</div>
						</div>

					</div>

				<?php } ?>

			</div>

		</div>

	</div>

	<?php wp_nonce_field( 'optimisationio-addons-nonce', 'optimisationio_addons_nonce' ); ?>

</div>

<script type="text/javascript">
	(function ($) {

		"use strict";

		var activated_addons_num = <?php echo $active_addons_number; ?>;
		var running_processes = 0;
		var on_deactivate_process = false;
		var deactivates_queue = [];

		function confirm_page_leave(event){
		    if (0 !== running_processes) {
		        var msg = "<?php echo $str_i18n['changes_may_not_saved']; ?>";
		        event.returnValue = msg;
		        return msg;
		    }
		}

		function on_action_click( $btn, action ){

			var slug, file, link, $parent;

			if( ! $btn.hasClass("disabled") ){
				$btn.addClass("disabled");
				$btn.parents('.add-on').find('.on-process').removeClass('hidden');

				$parent = $btn.parent('.addon-buttons');
				slug = $parent.data('slug');
				file = $parent.data('file');
				link = $parent.data('link');

				if( on_deactivate_process ){
					deactivates_queue.push({
						action: action,
						slug: slug,
						file: file,
						link: link,
						'$btn': $btn
					});
				}
				else{
					ajax_request( action, slug, file, link, $btn );
				}
			}
		}

		function on_tabs_nav_li_click($tab){
			var tab_id = $tab.data('tab');
			if( ! $tab.hasClass('active') ){
				update_active_tab( tab_id );
				$tab.addClass('active');
				localStorage.setItem( 'optimisationio_stats_tab', tab_id );
			}
		}

		function update_active_tab(tab_id){
			$('.statistics-tabs-nav li, .statistics-tab-content').removeClass('active');
			$('.statistics-tab-content[data-tab="' + tab_id + '"]').addClass('active');
		}

		function ajax_request(action, slug, file, link, $btn){

			running_processes++;

			on_deactivate_process = 'deactivate' === action;

			$.ajax({
                type: 'post',
                url: ajaxurl,
                data:{
                	action: 'optimisationio_' + action + '_addon',
                	slug: slug,
                	file: file,
                	link: link,
                	nonce: $('#optimisationio_addons_nonce').val(),
                },
                dataType: 'json',
                success: function (data, textStatus, XMLHttpRequest) {

                	var to_activate, $state_light, next;

                	if( 0 !== data.error ){
                		if("undefined" !== typeof data.type && "deny-disable" === data.type ){
                			$btn.parents('.add-on').find('.on-process').addClass('hidden');
                			alert(data.msg);
                			on_deactivate_process = false;
                			running_processes--;
                			$btn.removeClass("disabled");
                		}
                		else{
                			console.error(data.msg);
                		}
                	}
                	else{

                		$state_light = $btn.parents('.add-on').find('.add-on-title .state');

                		switch(action){
                			case 'install':
                				to_activate = 'activate';
                				$state_light.removeClass('red').addClass('orange');
                				break;
							case 'activate':
								to_activate = 'deactivate';
								$state_light.removeClass('orange').addClass('green');
								break;
							case 'deactivate':

								on_deactivate_process = false;

								if( deactivates_queue.length ){
									next = deactivates_queue.pop();
									ajax_request(next.action, next.slug, next.file, next.link, next['$btn']);
								}

								to_activate = 'activate';
								$state_light.removeClass('green').addClass('orange');
								break;
                		}

                		if( to_activate ){
	                		$btn.parent('.addon-buttons').find('.' + to_activate + '-addon').removeClass('hidden').removeClass("disabled");
	                		$btn.parents('.add-on').find('.on-process').addClass('hidden');
	                		$btn.addClass("hidden");
	                		running_processes--;
	                	}
                	}
                },
                error: function (data, textStatus, XMLHttpRequest) {
                	console.error("ERROR: ", slug, action);
                	$btn.parents('.add-on').find('.on-process').addClass('hidden');
                	$btn.text("ERROR");
                	running_processes--;
                }
            });
		}

		$(function () {
			$('.install-addon').on('click', function(){ on_action_click( $(this), 'install' ); });
			$('.activate-addon').on('click', function(){ on_action_click( $(this), 'activate' ); });
			$('.deactivate-addon').on('click', function(){ on_action_click( $(this), 'deactivate' ); });
			$('.statistics-tabs-nav li').on('click', function(){ on_tabs_nav_li_click( $(this) ); });
			window.onbeforeunload = confirm_page_leave;
		});

		var active_tab_id = localStorage.getItem( 'optimisationio_stats_tab' );
		active_tab_id = active_tab_id ? active_tab_id : 'disable';
		if( active_tab_id ){
			update_active_tab( active_tab_id );
			$('.statistics-tabs-nav li[data-tab="' + active_tab_id + '"]').addClass('active');
		}
	}(jQuery));
</script>
