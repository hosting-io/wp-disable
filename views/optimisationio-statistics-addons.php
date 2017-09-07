<?php
$addons = Optimisationio_Stats_And_Addons::$addons;
?>
<div class="wrap">

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

				<?php Optimisationio_Stats_And_Addons::display_stats__measurements(); ?>

				<ul class="statistics-tabs-nav">
					<li data-tab="disable"><?php _e('Remove Excess Bloat', 'optimisationio');?></li>
					<li data-tab="images"><?php _e('Compress Images', 'optimisationio');?></li>
					<li data-tab="cache"><?php _e('Cache & Database', 'optimisationio');?></li>
				</ul>
				
				<div class="statistics-tabs-content-wrap">

					<div data-tab="disable" class="statistics-tab-content"> <?php
						if( ! $addons['wp-disable']['activated'] ){ 
							Optimisationio_Stats_And_Addons::display_addons__activation_section('wp-disable');
						}
						else{
							Optimisationio_Stats_And_Addons::display_addons__settings('wp-disable');
						}
						?>
					</div>
					
					<div data-tab="images" class="statistics-tab-content"> <?php
						// if( ! $addons['wp-image-compression']['activated'] ){
							Optimisationio_Stats_And_Addons::display_addons__activation_section('wp-image-compression');
						// }
						// else{

						// }
						?>
					</div>

					<div data-tab="cache" class="statistics-tab-content"> <?php
						// if( ! $addons['cache-performance']['activated'] ){
							Optimisationio_Stats_And_Addons::display_addons__activation_section('cache-performance');
						// }
						// else{

						// }
						?>
					</div>

				</div>
		
			</div>

		</div><!-- // .statistics-wrap -->

		<div class="sidebar-section">

			<div class="sidebar-section-inner">

				<div class="cdn-comming-soon">
					<div><strong>CDN</strong><?php esc_html_e("Coming Soon"); ?></div>
				</div> 

				<div class="sidebar-tabs-section">

					<div class="sidebar-tabs-nav">
						<ul>
							<?php if( $addons['wp-disable']['activated'] ){ ?>
								<li data-tab-id="ga"><?php esc_html_e('Offload Google Analytics', 'optimisationio'); ?></li>
							<?php } ?>
							<li data-tab-id="imp"><?php esc_html_e('Import', 'optimisationio'); ?></li>
							<li data-tab-id="exp"><?php esc_html_e('Export', 'optimisationio'); ?></li>
						</ul>
					</div>

					<div class="sidebar-tabs-content">
						<ul>
							<?php if( $addons['wp-disable']['activated'] ){ ?>
								<li data-tab-id="ga">
									<?php WpPerformance_Admin::offload_google_analytics_settings(); ?>		
								</li>
							<?php } ?>
							<li data-tab-id="imp">
								<p><?php esc_html_e("Copy into textarea the encoded string of add-ons settings you have exported", "optimisationio"); ?></p>
								<div class="textarea-wrap">
									<textarea id="import_settings_tarea"></textarea>
								</div>
								
								<button class="import-btn button button-primary button-large" disabled><?php esc_html_e( "Import settings", "optimisationio" ); ?></button>
								
								<button class="clear-import-btn button button-large hidden"><?php esc_html_e( "Clear", "optimisationio" ); ?></button>
							</li>
							<li data-tab-id="exp">
								
								<p><?php esc_html_e("Select the add-οns whose settings you want to include in the exported data", "optimisationio"); ?></p>

								<div class="export-addons-list-options">
									<?php foreach ($addons as $key => $val) { 
										if( $val['activated'] ){ ?>
										<label><input type="checkbox" name="export_addons[]" value="<?php echo $val['slug']; ?>" checked /><?php echo $val['title']; ?></label>
									<?php }
									} ?>
								</div>
								
								<div class="textarea-wrap">
									<textarea id="export_settings_tarea" readonly></textarea>
								</div>
								
								<button class="export-btn button button-primary button-large"><?php esc_html_e( "Export current settings", "optimisationio" ); ?></button>

								<button class="copy-export-btn button button-large hidden"><?php esc_html_e( "Copy to clipboard", "optimisationio" ); ?></button>
							</li>
						</ul>
					</div>
				</div>
			</div>

		</div><!-- // .sidebar-section -->

	</div>

	<?php wp_nonce_field( 'optimisationio-addons-nonce', 'optimisationio_addons_nonce' ); ?>
	<?php wp_nonce_field( 'optimisationio-import-export-nonce', 'optimisationio_import_export_nonce' ); ?>
</div>