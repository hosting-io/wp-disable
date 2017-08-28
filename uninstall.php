<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }
require_once 'lib/class-wpperformance.php';
require_once 'lib/class-optimisationio-stats-and-addons.php';
WpPerformance::delete_options();
WpPerformance::delete_transients();
WpPerformance::unschedule_spam_comments_delete();
Optimisationio_Stats_And_Addons::delete_transients();