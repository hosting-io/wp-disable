<?php
$addons = Optimisationio_Dashboard::$addons;
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

				<?php Optimisationio_Dashboard::display_stats__measurements(); ?>

				<ul class="statistics-tabs-nav">
					<li data-tab="disable"><?php _e('Remove Excess Bloat', 'optimisationio');?></li>
					<li data-tab="images"><?php _e('Compress Images', 'optimisationio');?></li>
					<li data-tab="cache"><?php _e('Cache & Database', 'optimisationio');?></li>
				</ul>
				
				<div class="statistics-tabs-content-wrap">

					<div data-tab="disable" class="statistics-tab-content"> <?php
						if( ! $addons['wp-disable']['activated'] ){ 
							Optimisationio_Dashboard::display_addons__activation_section('wp-disable');
						}
						else{
							Optimisationio_Dashboard::display_addons__settings('wp-disable');
						}
						?>
					</div>
					
					<div data-tab="images" class="statistics-tab-content"> <?php
						// if( ! $addons['wp-image-compression']['activated'] ){
							Optimisationio_Dashboard::display_addons__activation_section('wp-image-compression');
						// }
						// else{

						// }
						?>
					</div>

					<div data-tab="cache" class="statistics-tab-content"> <?php
						// if( ! $addons['cache-performance']['activated'] ){
							Optimisationio_Dashboard::display_addons__activation_section('cache-performance');
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
					<?php Optimisationio_Dashboard::sidebar_tabs_section_content(); ?>
				</div>
			</div>

		</div><!-- // .sidebar-section -->

	</div>

	<?php wp_nonce_field( 'optimisationio-addons-nonce', 'optimisationio_addons_nonce' ); ?>
	<?php wp_nonce_field( 'optimisationio-import-export-nonce', 'optimisationio_import_export_nonce' ); ?>
</div>