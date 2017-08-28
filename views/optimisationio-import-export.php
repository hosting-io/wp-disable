<?php
$active_export = isset($_GET) && isset($_GET['export']);
$str_i18n = array( 'page_title' => __( '%1$s Optimisation.io - Import/Export add-ons settings %2$s', 'optimisationio' ) );
$base_curr_url = admin_url('admin.php?page=optimisationio-import-export');
$addons = Optimisationio_Stats_And_Addons::$addons;
?>
<div class="wrap">
	
	<h2><?php echo sprintf( $str_i18n['page_title'], '<strong>', '</strong>' ); ?></h2>
	
	<br/>

	<div class="wrap-main <?php echo $active_export ? 'view-export': 'view-import'; ?>">

		<ul class="wrap-imp-exp-nav">
			<li class="t-import <?php echo ! $active_export ? 'active': ''; ?>"><a href="<?php echo esc_url( $base_curr_url ); ?>" title=""><?php esc_html_e("Import", "optimisationio"); ?></a></li>
			<li class="t-export <?php echo $active_export ? 'active': ''; ?>"><a href="<?php echo esc_url( $base_curr_url . '&export' ); ?>" title=""><?php esc_html_e("Export", "optimisationio"); ?></a></li>
		</ul>

		<div class="wrap-imp-exp-content">
			<div class="wrap-imp-exp-inner">
				<div class="wrap-imp-exp-main c-import">
					<div class="imp-exp-options">
						<p><?php esc_html_e("Copy into textarea the encoded string of add-ons settings you have exported", "optimisationio"); ?></p>
					</div>
					<div class="textarea-wrap"><textarea></textarea></div>
				</div>
				<div class="wrap-imp-exp-main c-export">
					<div class="imp-exp-options">
						<p><?php esc_html_e("Select the add-οns whose settings you want to include in the exported data", "optimisationio"); ?></p>
						<?php foreach ($addons as $key => $val) { 
							if( $val['activated'] ){ ?>
							<label><input type="checkbox" name="export_addons[]" value="<?php echo $val['slug']; ?>" checked /><?php echo $val['title']; ?></label>
						<?php }
						} ?>
					</div>
					<div class="textarea-wrap"><textarea id="exported_settings_tarea" readonly></textarea></div>
				</div>
			</div>
		</div>

		<button class="import-btn button button-primary button-large" disabled><?php esc_html_e( "Import settings", "optimisationio" ); ?></button>
		<button class="export-btn button button-primary button-large"><?php esc_html_e( "Export current settings", "optimisationio" ); ?></button>

		<button class="clear-import-btn button button-large hidden"><?php esc_html_e( "Clear", "optimisationio" ); ?></button>
		<button class="copy-export-btn button button-large hidden"><?php esc_html_e( "Copy to clipboard", "optimisationio" ); ?></button>

	</div>
	
	<?php wp_nonce_field( 'optimisationio-import-export-nonce', 'optimisationio_import_export_nonce' ); ?>
</div>

<script type="text/javascript">
	(function ($) {

		"use strict";

		var curr_tab = <?php echo $active_export ? '"export"' : '"import"'?>,
			local_storage = 'undefined' !== typeof window.localStorage ? window.localStorage : false,
			local_storage_tab_key = 'Optimisation.io[admin][import_export_tab]',
			$addons_checkboxes, $tab_nav, $textarea, $action_btn,
			$wrapMain = $('.wrap-main'),
			export_clipboard;

		function on_addon_checkbox_click(ev) {
			var checked_addons = [];

			$addons_checkboxes.each(function(i, input){
				if( input.checked ){ checked_addons.push(input.value); }
			});

			if( checked_addons.length ){
				$action_btn.export.prop("disabled", false);
			}
			else{
				$action_btn.export.prop("disabled", true);
			}
		}

		function on_import_tab_click() {

			if( 'import' !== curr_tab ){

				$wrapMain.removeClass('view-export');
				$wrapMain.addClass('view-import');
				$tab_nav.export.removeClass('active');
				$tab_nav.import.addClass('active');
				
				curr_tab = 'import';

				if( local_storage ){ local_storage.setItem( local_storage_tab_key, curr_tab); }

				$textarea.export.val('');
				$action_btn.copy_export.addClass('hidden');
			}
		}

		function on_export_tab_click() {

			if( 'export' !== curr_tab ){

				$wrapMain.addClass('view-export');
				$wrapMain.removeClass('view-import');
				$tab_nav.import.removeClass('active');
				$tab_nav.export.addClass('active');
			
				curr_tab = 'export';
			
				if( local_storage ){ local_storage.setItem(local_storage_tab_key, curr_tab); }
			}
		}

		function on_import_textarea_change(ev) {
			if( '' === $textarea.import.val().trim() ){
				$action_btn.clear_import.addClass('hidden');
				$action_btn.import.prop("disabled", true);
			}
			else{
				$action_btn.clear_import.removeClass('hidden');
				$action_btn.import.prop("disabled", false);
			}
		}

		function on_import_btn_click() {
			var imported = $textarea.import.val().trim();
			if( '' !== imported ){
				ajax_request('optimisationio_import_addons_settings', imported);
			}
		}

		function on_export_btn_click() {
			var export_slugs = [];
			$addons_checkboxes.each(function(i, input){
				if( input.checked ){ export_slugs.push(input.value); }
			});
			if( export_slugs.length ){
				ajax_request('optimisationio_export_addons_settings', export_slugs);
			}
		}

		function on_clear_import_btn_click() {
			$textarea.import.val('');
		}

		function ajax_request(action, data){
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
                			$textarea.export.val(data.export);
                			$action_btn.copy_export.removeClass('hidden');
                		}
                		else{
                			$textarea.import.val('');
                			$action_btn.clear_import.addClass('hidden');
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

		$(function () {
			
			var saved_active_tab = local_storage.getItem( local_storage_tab_key );

			$addons_checkboxes = $('input[name="export_addons[]"]');

			$tab_nav = { 
				import: $('.t-import'),
				export: $('.t-export')
			};

			$textarea = { 
				import: $('.c-import textarea'),
				export: $('.c-export textarea')
			};

			$action_btn = {
				import: $('.import-btn'),
				export: $('.export-btn'),
				clear_import: $('.clear-import-btn'),
				copy_export: $('.copy-export-btn'),
			};

			$addons_checkboxes.on('click', on_addon_checkbox_click);
			$tab_nav.import.on('click', function(ev){ ev.preventDefault(); on_import_tab_click(); });
			$tab_nav.export.on('click', function(ev){ ev.preventDefault(); on_export_tab_click(); });
			$textarea.import.on('change, keyup', on_import_textarea_change);
			$action_btn.import.on('click', function(ev){ ev.preventDefault(); on_import_btn_click(); });
			$action_btn.export.on('click', function(ev){ ev.preventDefault(); on_export_btn_click(); });
			$action_btn.clear_import.on('click', function(ev){ ev.preventDefault(); on_clear_import_btn_click(); });

			var export_clipboard = new Clipboard('.copy-export-btn', {
			    text: function(trigger) {
			        return $textarea.export.val();
			    }
			});

			export_clipboard.on('success', function(e){
				alert('<?php esc_html_e( "Exported settings copied to clipboard", "optimisationio" ); ?>')
			});

			if( 'undefined' !== typeof saved_active_tab && saved_active_tab !== curr_tab ){
				if( 'export' === saved_active_tab ){
					on_export_tab_click();
				}
				else{
					on_import_tab_click();
				}
			}

		});
	}(jQuery));
</script>