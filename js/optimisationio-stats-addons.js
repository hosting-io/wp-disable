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

		$(function () {

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
		});
	};

	var install_activate_deactivate = function(){
		
		var running_processes = 0;
		var on_deactivate_process = false;
		var deactivates_queue = [];
		
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

	            	var to_activate, next;

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

		$(function () {
			
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

			$('.sidebar-tabs-section ul li').on('click', function(){ on_sidebar_tab_click( $(this) ); });
		});
	};

	var plugin_settings_tabs = function(){

		function on_tab_click(ev){
			var id = $(this).data('tab-setting');
			ev.data.$wrap.find('.addon-settings-tabs ul li.active, .addon-settings-content.active').removeClass('active');
			ev.data.$wrap.find('.addon-settings-tabs ul li[data-tab-setting="'+id+'"], .addon-settings-content[data-tab-setting="'+id+'"]').addClass('active');
		}

		function bind_plugin_events($wrap){
			var $tabs = $wrap.find('.addon-settings-tabs ul li');
			if( $tabs.length ){ $tabs.on('click', { '$wrap' : $wrap }, on_tab_click); }
		}

		$(function () {
			var $addon_settings = $('.addon-settings');
			if( $addon_settings.length ){
				$addon_settings.each(function(i, el){
					bind_plugin_events($(el));
				});
			}
		});
	};

	main_tabs();
	sidebar_tabs();
	plugin_settings_tabs();
	settings_import_export();
	install_activate_deactivate();

}(jQuery));