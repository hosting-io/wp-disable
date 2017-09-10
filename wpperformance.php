<?php
/**
 * Plugin Name: WP Disable
 * Plugin URI: https://optimisation.io
 * Description: Improve WordPress performance by disabling unused items.
 * Author: pigeonhut, Jody Nesbitt, optimisation.io
 * Author URI:https://optimisation.io
 * Version: 1.5.1
 *
 * Copyright (C) 2017 Optimisation.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

define( 'OPTIMISATIONIO_WP_DISABLE_ADDON', true);

require_once 'lib/class-wpperformance.php';
require_once 'lib/class-wpperformance-view.php';
require_once 'lib/class-wpperformance-admin.php';

/**
 * On plugin activation.
 */
function wpperformance_on_activate() {

	WpPerformance::check_spam_comments_delete();

	wp_clear_scheduled_hook( 'update_local_ga' );

	if ( ! wp_next_scheduled( 'update_local_ga' ) ) {
		$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
		if( isset( $settings['caos_remove_wp_cron'] ) && $settings['caos_remove_wp_cron'] &&
			'on' !== esc_attr( $settings['caos_remove_wp_cron'] )
		){
			wp_schedule_event( time(), 'daily', 'update_local_ga' );
		}
	}
}

/**
 * On plugin deactivation.
 */
function wpperformance_on_deactivate() {

	WpPerformance::delete_transients();
	WpPerformance::unschedule_spam_comments_delete();

	if ( wp_next_scheduled( 'update_local_ga' ) ) {
		wp_clear_scheduled_hook( 'update_local_ga' );
	}
}

// Register hook to schedule script in wp_cron().
register_activation_hook( __FILE__, 'wpperformance_on_activate' );

// Remove script from wp_cron upon plugin deactivation.
register_deactivation_hook( __FILE__, 'wpperformance_on_deactivate' );

/**
 * Include file 'includes/update-local-ga.php'.
 */
function wpperformance_update_local_ga_script() {
	include( 'includes/update-local-ga.php' );
}

// Load update script to schedule in wp_cron.
add_action( 'update_local_ga', 'wpperformance_update_local_ga_script' );

/**
 * Initialize WP_Filesystem if hasn't inited yet.
 */
function wpperformance_init_wp_filesystem() {
	global $wp_filesystem;
	if ( null === $wp_filesystem ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		WP_Filesystem();
	}
}

/**
 * Disable Google maps ob_end.
 */
function wpperformance_disable_google_maps_ob_end( $html ) {
	global $post;
	$exclude_ids = [];
	$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );
	if ( isset( $settings['exclude_from_disable_google_maps'] ) && '' !== $settings['exclude_from_disable_google_maps'] ) {
		$exclude_ids = array_map( 'intval', explode(',', $settings['exclude_from_disable_google_maps']) );
	}
	if( $post && ! in_array( $post->ID, $exclude_ids, true ) ){
		$html = preg_replace( '/<script[^<>]*\/\/maps.(googleapis|google|gstatic).com\/[^<>]*><\/script>/i', '', $html );
	}
	return $html;
}

/**
 * Generate tracking code and add to header/footer (default is header).
 */
function wpperformance_add_ga_header_script() {

	$settings = get_option( WpPerformance::OPTION_KEY . '_settings', array() );

	$ds_track_admin = isset( $settings['ds_track_admin'] ) && $settings['ds_track_admin'] ? esc_attr( $settings['ds_track_admin'] ) : false;

	// If user is admin we don't want to render the tracking code, when option is disabled.
	if ( current_user_can( 'manage_options' ) && ( ! $ds_track_admin) ) { return; }

	$ds_tracking_id = isset( $settings['ds_tracking_id'] ) && $settings['ds_tracking_id'] ? esc_attr( $settings['ds_tracking_id'] ) : '';

	$ds_adjusted_bounce_rate = isset( $settings['ds_adjusted_bounce_rate'] ) && $settings['ds_adjusted_bounce_rate'] ? esc_attr( $settings['ds_adjusted_bounce_rate'] ) : 0;

	$ds_anonymize_ip = isset( $settings['ds_anonymize_ip'] ) && $settings['ds_anonymize_ip'] ? esc_attr( $settings['ds_anonymize_ip'] ) : null;

	$caos_disable_display_features = isset( $settings['caos_disable_display_features'] ) && $settings['caos_disable_display_features'] ? esc_attr( $settings['caos_disable_display_features'] ) : 'off';

	echo '<!-- Google Analytics Local by Optimisation.io -->';

	echo "<script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" . plugin_dir_url( __FILE__ ) . "cache/local-ga.js','ga');";

	echo "ga('create', '" . $ds_tracking_id . "', 'auto');";

	echo 'on' === $caos_disable_display_features ? "ga('set', 'displayFeaturesTask', null);" : '';

	echo 'on' === $ds_anonymize_ip ? "ga('set', 'anonymizeIp', true);" : '';

	echo "ga('send', 'pageview');";

	echo $ds_adjusted_bounce_rate ? 'setTimeout("ga(' . "'send','event','adjusted bounce rate','" . $ds_adjusted_bounce_rate . " seconds')" . '"' . ',' . $ds_adjusted_bounce_rate * 1000 . ');' : '';

	echo '</script>';
}

function wpperformance_cron_additions( $schedules ) {

	$schedules['weekly'] = array(
		'interval' => 86400 * 7,
		'display' => __( 'Once Weekly' ),
	);

	$schedules['twicemonthly'] = array(
		'interval' => 86400 * 14,
		'display' => __( 'Twice Monthly' ),
	);

	$schedules['monthly'] = array(
		'interval' => 86400 * 30,
		'display' => __( 'Once Monthly' ),
	);

	return $schedules;
}

add_filter( 'cron_schedules', 'wpperformance_cron_additions' );

add_action( 'plugins_loaded', array( 'WpPerformance', 'get_instance' ) );
