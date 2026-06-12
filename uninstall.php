<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
require_once 'lib/class-wpperformance.php';
WpPerformance::delete_options();
WpPerformance::delete_transients();
WpPerformance::unschedule_spam_comments_delete();

// Clean up legacy add-on download-link transients from the pre-Folium era
// (the cross-plugin installer that set these was removed in 2.2.0).
foreach ( array( 'wp-disable', 'cache-performance', 'wp-image-compression' ) as $wp_disable_legacy_slug ) {
	delete_transient( 'optimisaitionio_addon_download_link[' . $wp_disable_legacy_slug . ']' );
}

// Clean up the legacy Universal Analytics offload cron + transient, if present.
wp_clear_scheduled_hook( 'update_local_ga' );
delete_transient( 'wpperformance_ds_tracking_id' );
