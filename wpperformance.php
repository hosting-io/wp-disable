<?php
/**
 * Plugin Name: WP Disable
 * Plugin URI: https://optimisation.io
 * Description: Improve WordPress performance by disabling unused items. <a href="admin.php?page=optimisationio-dashboard">Open Settings</a>
 * Version: 2.0.2
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Author: optimisation.io, hosting.io
 * Author URI: https://optimisation.io
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-disable
 * Domain Path: /lang
 *
 * Copyright (C) 2017-2026 Optimisation.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) { die; }

define( 'OPTIMISATIONIO_WP_DISABLE_ADDON', true);

require_once 'lib/class-wpperformance.php';
require_once 'lib/class-wpperformance-admin.php';

/**
 * On plugin activation.
 */
function wpperformance_on_activate() {

	WpPerformance::check_spam_comments_delete();

	// Clear the legacy Universal Analytics offload cron if it lingers from
	// a pre-2.0 install (the GA feature has been removed).
	wp_clear_scheduled_hook( 'update_local_ga' );
}

/**
 * On plugin deactivation.
 */
function wpperformance_on_deactivate() {

	WpPerformance::delete_transients();
	WpPerformance::unschedule_spam_comments_delete();

	// Clean up the legacy GA offload cron, if present.
	wp_clear_scheduled_hook( 'update_local_ga' );
}

// Register hook to schedule script in wp_cron().
register_activation_hook( __FILE__, 'wpperformance_on_activate' );

// Remove script from wp_cron upon plugin deactivation.
register_deactivation_hook( __FILE__, 'wpperformance_on_deactivate' );

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
