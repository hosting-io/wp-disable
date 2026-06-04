<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
require_once 'lib/class-wpperformance.php';
require_once 'lib/class-optimisationio-dashboard.php';
WpPerformance::delete_options();
WpPerformance::delete_transients();
WpPerformance::unschedule_spam_comments_delete();
Optimisationio_Dashboard::delete_transients();

// Clean up the legacy Universal Analytics offload cron + transient, if present.
wp_clear_scheduled_hook( 'update_local_ga' );
delete_transient( 'wpperformance_ds_tracking_id' );