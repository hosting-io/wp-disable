var Optimisationio_PluginSettingsTabs;

var OptimisationioAddons = (function($){

	"use strict";

	var settings_import_export = function(){

		var export_clipboard,
			$el = {
				import: {
					textarea: null,
					btn: null,
					clear_btn: null,
				},
				export: {
					addons_fields: null,
					textarea: null,
					btn: null,
					copy_btn: null,
				},
			};

		function imp_exp_ajax_request(action, data){
			$.ajax({
	            type: 'post',
	            url: ajaxurl,
	            data:{
	            	action: action,
	            	data: data,
	            	nonce: $('#optimisationio_import_export_nonce').val(),
	            },
	            dataType: 'json',
	            success: function (data, textStatus, XMLHttpRequest) {
	            	if( 0 === data.error ){
	            		if( 'optimisationio_export_addons_settings' === action ){
	            			$el.export.textarea.val(data.export);
	            			$el.export.copy_btn.removeClass('hidden');
	            		}
	            		else{
	            			$el.import.textarea.val('');
	            			$el.import.clear_btn.addClass('hidden');
	            			alert(data.msg);
	            		}
	            	}
	            	else if( 'undefined' !== typeof data.msg ) {
	        			alert( data.msg );
	        		}
	            },
	            error: function (data, textStatus, XMLHttpRequest) {
	            	console.log(data);
	            }
	        });
		}

		function on_addon_field_click(ev) {
			var checked_addons = [];
			$el.export.addons_fields.each(function(i, input){
				if( input.checked ){ checked_addons.push(input.value); }
			});
			$el.export.btn.prop("disabled", checked_addons.length ? false : true);
		}

		function on_import_textarea_change(ev) {
			if( '' === $el.import.textarea.val().trim() ){
				$el.import.clear_btn.addClass('hidden');
				$el.import.btn.prop("disabled", true);
			}
			else{
				$el.import.clear_btn.removeClass('hidden');
				$el.import.btn.prop("disabled", false);
			}
		}

		function on_import_btn_click() {
			var imported = $el.import.textarea.val().trim();
			if( '' !== imported ){
				imp_exp_ajax_request('optimisationio_import_addons_settings', imported);
			}
		}

		function on_export_btn_click() {
			var export_slugs = [];
			$el.export.addons_fields.each(function(i, input){
				if( input.checked ){ export_slugs.push(input.value); }
			});
			if( export_slugs.length ){
				imp_exp_ajax_request('optimisationio_export_addons_settings', export_slugs);
			}
		}

		function on_clear_import_btn_click() {
			$el.import.textarea.val('');
		}

		function init(){
			$el = {
				import: {
					textarea: $('#import_settings_tarea'),
					btn: $('.import-btn'),
					clear_btn: $('.clear-import-btn'),
				},
				export: {
					addons_fields: $('input[name="export_addons[]"]'),
					textarea: $('#export_settings_tarea'),
					btn: $('.export-btn'),
					copy_btn: $('.copy-export-btn'),
				},
			};
			
			export_clipboard = new Clipboard('.copy-export-btn', {
				text: function(trigger) {
					return $el.export.textarea.val();
				}
			});
			
			$el.export.addons_fields.on('click', on_addon_field_click);
			$el.import.textarea.on('change, keyup', on_import_textarea_change);
			$el.import.btn.on('click', function(e){ e.preventDefault(); on_import_btn_click(); });
			$el.export.btn.on('click', function(e){ e.preventDefault(); on_export_btn_click(); });
			$el.import.clear_btn.on('click', function(e){ e.preventDefault(); on_clear_import_btn_click(); });

			export_clipboard.on('success', function(e){ alert("Exported settings copied to clipboard"); });
		}

		$(function () {
			init();
		});

		return {
			init: init
		};
	};

	var install_activate_deactivate = function(){
		
		var running_processes = 0,
			on_deactivate_process = false,
			deactivates_queue = [];
		
		function confirm_page_leave(ev){
			var msg;
		    if (0 !== running_processes) {
		        msg = "Changes you made may not be saved.";
		        ev.returnValue = msg;
		        return msg;
		    }
		}

		function on_action_click( $btn, action ){

			var slug, file, link, $parent;

			if( ! $btn.hasClass("disabled") ){
				$btn.addClass("disabled");

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
					addons_ajax_request( action, slug, file, link, $btn );
				}
			}
		}

		function addons_ajax_request(action, slug, file, link, $btn){

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

	            	var to_activate, next, $el;

	            	if( 0 !== data.error ){
	            		if("undefined" !== typeof data.type && "deny-disable" === data.type ){
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
	            		switch(action){
	            			case 'install':
	            				to_activate = 'activate';
	            				break;
							case 'activate':
								to_activate = 'deactivate';
								break;
							case 'deactivate':

								on_deactivate_process = false;

								if( deactivates_queue.length ){
									next = deactivates_queue.pop();
									addons_ajax_request(next.action, next.slug, next.file, next.link, next['$btn']);
								}

								to_activate = 'activate';
								break;
	            		}

	            		if( to_activate ){
	                		$btn.parent('.addon-buttons').find('.' + to_activate + '-addon').removeClass('hidden').removeClass("disabled");
	                		$btn.addClass("hidden");
	                		running_processes--;
	                	}

	                	if( 'undefined' !== typeof data.measurements_content_replace ){
	                		// @note: Used on addon activation.
	                		$('.statistics-measurements').replaceWith( data.measurements_content_replace );
	                	}

	                	if( 'undefined' !== typeof data.sidebar_tabs_content ){
	                		// @note: Used on "WP Disable" activation.
	                		$('.sidebar-tabs-section').html(data.sidebar_tabs_content);
	                		setTimeout(function(){
	                			SidebarTabs.default_active_tab();
            					SidebarTabs.bind_events();
            					SettingsImportExport.init();
            				}, 100);
	                	}

	                	switch(slug){
                			case 'wp-disable':
                				$el = $( '.statistics-tab-content[data-tab="disable"]' );
                				if( $el.length ){
	                				$el.html( data.plugin_settings_content );
	                				setTimeout(function(){
	                					Optimisationio_PluginSettingsTabs.bind_events($el.find('.addon-settings'), slug);
	                					if( 'undefined' !== typeof Optimisationio_Dashbord_WP_Disable ){
		                					Optimisationio_Dashbord_WP_Disable.init();
		                				}
	                				}, 100);
	                			}
                				break;
                			case 'cache-performance':
                				$el = $( '.statistics-tab-content[data-tab="cache"]' );
                				if( $el.length ){
	                				$el.html( data.plugin_settings_content );
	                				setTimeout(function(){
	                					Optimisationio_PluginSettingsTabs.bind_events($el.find('.addon-settings'), slug);
	                					if( 'undefined' !== typeof Optimisationio_Dashboard_Cache_Performance ){
		                					Optimisationio_Dashboard_Cache_Performance.init();
		                				}
	                				}, 100);
	                			}
	                			break;
	                		case 'wp-image-compression':
                				$el = $( '.statistics-tab-content[data-tab="images"]' );
                				if( $el.length ){
	                				$('.sidebar-cloudinary-api-wrap').html(data.cloudinary_api_settings_content);
	                				$el.html( data.plugin_settings_content );
	                				setTimeout(function(){
	                					Optimisationio_PluginSettingsTabs.bind_events($el.find('.addon-settings'), slug);
	                					if( 'undefined' !== typeof Optimisationio_Dashboard_Image_Compression ){
		                					Optimisationio_Dashboard_Image_Compression.init();
		                				}
	                				}, 100);
	                			}
                				break;
                		}
	            	}
	            },
	            error: function (data, textStatus, XMLHttpRequest) {
	            	console.error("ERROR: ", slug, action);
	            	$btn.text("ERROR");
	            	running_processes--;
	            }
	        });
		}

		$(function () {
			$('.install-addon').on('click', function(){ on_action_click( $(this), 'install' ); });
			$('.activate-addon').on('click', function(){ on_action_click( $(this), 'activate' ); });
			$('.deactivate-addon').on('click', function(){ on_action_click( $(this), 'deactivate' ); });
			
			$(window).bind('beforeunload', confirm_page_leave);
		});
	};

	var main_tabs = function(){
		
		var active_tab_id = localStorage.getItem( 'optimisationio_stats_tab' );

		active_tab_id = active_tab_id ? active_tab_id : 'disable';

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

		$(function () {	
			if( active_tab_id ){
				update_active_tab( active_tab_id );
				$('.statistics-tabs-nav li[data-tab="' + active_tab_id + '"]').addClass('active');
			}
			$('.statistics-tabs-nav li').on('click', function(){ on_tabs_nav_li_click( $(this) ); });
		});
	};

	var sidebar_tabs = function(){

		var active_side_tab_id = localStorage.getItem( 'optimisationio_sidebar_tab' );

		function on_sidebar_tab_click($tab){
			var tab_id = $tab.data('tab-id');
			if( ! $tab.hasClass('active') ){
				update_active_sidebar_tab( tab_id );
				$tab.addClass('active');
				localStorage.setItem( 'optimisationio_sidebar_tab', tab_id );
			}
		}

		function update_active_sidebar_tab(tab_id){
			$('.sidebar-tabs-section ul li, .sidebar-tabs-content ul li').removeClass('active');
			$('.sidebar-tabs-content ul li[data-tab-id="' + tab_id + '"]').addClass('active');
		}

		function bind_events(){
			$('.sidebar-tabs-section ul li').on('click', function(){ on_sidebar_tab_click( $(this) ); });
		}

		function default_active_tab(){
			var $activeEl;

			active_side_tab_id = active_side_tab_id ? active_side_tab_id : ( $('.sidebar-tabs-section ul li[data-tab-id="ga"]').length ? "ga" : "imp" );
			
			if( active_side_tab_id ){
			
				$activeEl = $('.sidebar-tabs-section ul li[data-tab-id="' + active_side_tab_id + '"]');

				if( ! $activeEl.length ){
					active_side_tab_id = "imp"
					$activeEl = $('.sidebar-tabs-section ul li[data-tab-id="' + active_side_tab_id + '"]');
				}
				update_active_sidebar_tab( active_side_tab_id );
				$activeEl.addClass('active');
			}
		}

		$(function () {
			default_active_tab();
			bind_events();
		});

		return {
			bind_events: bind_events,
			default_active_tab: default_active_tab
		};
	};

	var plugin_settings_tabs = function(){

		function set_initial_active_tab($wrap){
			var plugin_slug = $wrap.data('sett-group'), active_plugin_tab;
			if( plugin_slug ){
				active_plugin_tab = localStorage.getItem( 'optimisationio_addon_active_tab[' + plugin_slug + ']' );
				if( ! active_plugin_tab || ! $wrap.find('.addon-settings-tabs ul li[data-tab-setting="' + active_plugin_tab + '"]').length ){
					active_plugin_tab = $( $wrap.find('.addon-settings-tabs ul li')[0] ).data('tab-setting');
				}
				update_active_tab($wrap, active_plugin_tab);
			}
		}

		function init(){
			var $addon_settings = $('.addon-settings');
			if( $addon_settings.length ){
				$addon_settings.each(function(i, el){
					var $el = $(el), plugin_slug = $el.data('sett-group');
					bind_events($el, plugin_slug);
					set_initial_active_tab( $el );
				});
			}
		}

		function update_active_tab($wrap, tab_id){
			$wrap.find('.addon-settings-tabs ul li.active, .addon-settings-content.active').removeClass('active');
			$wrap.find('.addon-settings-tabs ul li[data-tab-setting="'+tab_id+'"], .addon-settings-content[data-tab-setting="'+tab_id+'"]').addClass('active');
		}

		function on_tab_click(ev){
			var tab_id = $(this).data('tab-setting');
			update_active_tab(ev.data.$wrap, tab_id);
			localStorage.setItem( 'optimisationio_addon_active_tab[' + ev.data.plugin_slug + ']', tab_id );
		}

		function bind_events($wrap, plugin_slug){
			var $tabs = $wrap.find('.addon-settings-tabs ul li');
			if( $tabs.length ){ $tabs.on('click', { '$wrap' : $wrap, plugin_slug: plugin_slug }, on_tab_click); }
		}

		$(function () {
			init();
		});

		return {
			bind_events: bind_events
		};
	};

	main_tabs();
	install_activate_deactivate();

	var SidebarTabs = sidebar_tabs();
	var SettingsImportExport = settings_import_export();
	
	Optimisationio_PluginSettingsTabs = plugin_settings_tabs();

}(jQuery));

var Optimisationio_Dashbord_WP_Disable = (function($){

	var $toogle_el = {
		feeds: null,
		comments: null,
		googleMaps: null,
		spamCommentsCleaner: null,
		certainPostsComments: null,
	};

	function on_change_feeds(ev){
		$('.feeds-group').css('display', $toogle_el.feeds.is(":checked") ? '' : 'none');
	}
	
	function on_change_comments(ev){
		var isChecked = $toogle_el.comments.is(":checked");
		$('.comments-group').css('display', isChecked ? 'none' : '');
		on_change_certainPostsComments();
	}
	
	function on_change_googleMaps(ev){
		$('.disable-google-maps-group').css('display', $toogle_el.googleMaps.is(":checked") ? '' : 'none');
	}
	
	function on_change_spamCommentsCleaner(ev){
		var isChecked = ! $toogle_el.comments.is(":checked") && $toogle_el.spamCommentsCleaner.is(":checked");
		$('.delete-spam-comments-group').css('display', isChecked ? '' : 'none');
	}

	function on_change_certainPostsComments(ev){
		$('.certain-posts-comments-group').css('display', ! $toogle_el.certainPostsComments.is(":checked") ? 'none' : ( $toogle_el.comments.is(":checked") ? 'none' : '' ) );
	}

	function on_change_dnsPrefetch(ev){
		$('.dns-prefetch-group').css('display', $toogle_el.dnsPrefetch.is(":checked") ? '' : 'none');
	}

	function init(){
		$toogle_el = {
			feeds: $('input[name="disable_rss"]'),
			comments: $('input[name="disable_all_comments"]'),
			googleMaps: $('input[name="disable_google_maps"]'),
			spamCommentsCleaner: $('input[name="spam_comments_cleaner"]'),
			certainPostsComments: $('input[name="disable_comments_on_certain_post_types"]'),
			dnsPrefetch: $('input[name="dns_prefetch"]'),
		};
		
		if( $toogle_el.feeds.length ){
			$toogle_el.feeds.on('change', on_change_feeds);
			on_change_feeds();
		}

		if( $toogle_el.comments.length ){
			$toogle_el.comments.on('change', on_change_comments);
			on_change_comments();
		}

		if( $toogle_el.googleMaps ){
			$toogle_el.googleMaps.on('change', on_change_googleMaps);
			on_change_googleMaps();
		}

		if( $toogle_el.spamCommentsCleaner ){
			$toogle_el.spamCommentsCleaner.on('change', on_change_spamCommentsCleaner);
			on_change_spamCommentsCleaner();
		}

		if( $toogle_el.certainPostsComments.length ){
			$toogle_el.certainPostsComments.on('change', on_change_certainPostsComments);
			on_change_certainPostsComments();
		}

		if( $toogle_el.dnsPrefetch.length ){
			$toogle_el.dnsPrefetch.on('change', on_change_dnsPrefetch);
			on_change_dnsPrefetch();
		}
	}

	return {
		init: init,
	}
}(jQuery));

var Optimisationio_Dashboard_Cache_Performance = (function($){

	var $toogle_el = {};
	var $btn_el = {};

	function on_change_auto_optimise(){
		$('.auto-optimise-group').css('display', $toogle_el.auto_optimise.is(":checked") ? '' : 'none');
	}

	function on_change_gravatars_cache(){
		$('.gravatars-cache-group').css('display', $toogle_el.gravatars_cache.is(":checked") ? '' : 'none');
	}

	function on_click_optimise_db(ev){
		
		ev.preventDefault();
		ev.stopPropagation();

		var btn = this,
			data = {
		        action: 'optimise_db_ajx',
		        clean_draft_posts: $('input[name="clean_draft_posts"]').is(':checked') ? 1 : 0,
		        clean_auto_draft_posts: $('input[name="clean_auto_draft_posts"]').is(':checked') ? 1 : 0,
		        clean_trash_posts: $('input[name="clean_trash_posts"]').is(':checked') ? 1 : 0,
		        clean_post_revisions: $('input[name="clean_post_revisions"]').is(':checked') ? 1 : 0,
		        clean_transient_options: $('input[name="clean_transient_options"]').is(':checked') ? 1 : 0,
		        clean_trash_comments: $('input[name="clean_trash_comments"]').is(':checked') ? 1 : 0,
		        clean_spam_comments: $('input[name="clean_spam_comments"]').is(':checked') ? 1 : 0,
		        clean_post_meta: $('input[name="clean_post_meta"]').is(':checked') ? 1 : 0,
		        auto_optimise: $('input[name="auto_optimise"]').is(':checked') ? 1 : 0,
		        optimise_schedule_type: $('select[name="optimise_schedule_type"]').val(),
		        optimisationio_cache_preformance_settings: $('input[name="optimisationio_cache_preformance_settings"]').val(),
		    };

		$('.optimising-db-overlay').fadeIn(300);

		btn.setAttribute('disabled', 'disabled');

	    jQuery.post(ajaxurl, data, function(data){
	    	if ('success' === data.status) {
	    		if( 'undefined' !== typeof data.optimise_db_fields_content ){
		    		$('div[data-tab-setting="optimise-db"]').html( data.optimise_db_fields_content );
		    		setTimeout(function(){
		    			init_optimise_db_content();
		    		}, 100);
		    	}
	      	}
	      	else{
	      		console.error(data);
	      	}

	      	$(btn).removeAttr('disabled');

	    },'json');
	}

	function on_click_gravatars_clear(ev){
		
		ev.preventDefault();
		ev.stopPropagation();

		var btn = this;
		
		$('.clearing-gravatars-cache-overlay').fadeIn(300);

		btn.setAttribute('disabled', 'disabled');

		$.ajax({
            type: 'post',
            url: ajaxurl,
            data:{
            	action: 'optimisationio_clear_gravatar_cache',
            	nonce: $('input[name="optimisationio_cache_preformance_settings"]').val(),
            },
            dataType: 'json',
            success: function (data, textStatus, XMLHttpRequest) {
            	if( data.error ){
            		console.error( data );
            	}
            	else{
            		$('.cashed-gravatars-num').html('0');
            	}
            	$(btn).removeAttr('disabled');
            	$('.clearing-gravatars-cache-overlay').fadeOut(300);
            },
            error: function (data, textStatus, XMLHttpRequest) {
            	console.error("ERROR: ", data);
            	$(btn).removeAttr('disabled');
            	$('.clearing-gravatars-cache-overlay').fadeOut(300);
            }
        });
	}

	function init_optimise_db_content(){

		$btn_el.optimise_db = $('.optimise-db-now');
		$toogle_el.auto_optimise = $('input[name="auto_optimise"]');

		if( $btn_el.optimise_db ){
			$btn_el.optimise_db.on('click', on_click_optimise_db);
		}

		if( $toogle_el.auto_optimise.length ){
			$toogle_el.auto_optimise.on('change', on_change_auto_optimise);
			on_change_auto_optimise();
		}
	}

	function init_gravatars_cache_content(){
		
		$btn_el.clear_gravatars = $('.clear-now-gravatars-cache');
		$toogle_el.gravatars_cache = $('input[name="enable_cache_gravatars"]');

		if( $btn_el.clear_gravatars.length ) {
			$btn_el.clear_gravatars.on('click', on_click_gravatars_clear);
		}

		if( $toogle_el.gravatars_cache.length ){
			$toogle_el.gravatars_cache.on('change', on_change_gravatars_cache);
			on_change_gravatars_cache();
		}
	}

	function init(){
		init_optimise_db_content();
		init_gravatars_cache_content();
	}

	return {
		init: init,
	}
}(jQuery));

var Optimisationio_Dashboard_Image_Compression = (function($){

	var $toogle_el = {
		quality_auto: null,
		custom_cloudinary_account: null,
	};

	function on_quality_auto_change(){
		$('.manual-quality-group').css('display', 'manual' === $toogle_el.quality_auto.val() ? '' : 'none');
	}

	function on_custom_cloudinary_account_change(){
		var isChecked = $toogle_el.custom_cloudinary_account.is(":checked");
		$('.custom-cloudinary-group').css('display', isChecked ? '' : 'none');
		$('.auto-cloudinary-group').css('display', isChecked ? 'none' : '');
	}

	function init(){
		$toogle_el = {
			quality_auto: $('select[name="wpimages_quality_auto"]'),
			custom_cloudinary_account: $('input[name="custom_cloudinary[enabled]"]')
		};

		if( $toogle_el.quality_auto.length ){
			$toogle_el.quality_auto.on('change', on_quality_auto_change);
			on_quality_auto_change();
		}

		if( $toogle_el.custom_cloudinary_account.length ){
			$toogle_el.custom_cloudinary_account.on('change', on_custom_cloudinary_account_change);
			on_custom_cloudinary_account_change();
		}

		$('.close-cloudinary-api-msg').on('click', function(){
			$(this).parent().parent().fadeOut(300, function(){
				$(this).remove();
			});
		});
	}

	return {
		init: init,
	}
}(jQuery));

(function ($) {

	"use strict";

	$(function () {
		Optimisationio_Dashbord_WP_Disable.init();
		Optimisationio_Dashboard_Cache_Performance.init();
		Optimisationio_Dashboard_Image_Compression.init();
	});
	
}(jQuery));