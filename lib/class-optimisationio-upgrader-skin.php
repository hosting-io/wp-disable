<?php
defined( 'ABSPATH' ) || exit; // Prevent direct access.

include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

class Optimisationio_Upgrader_Skin extends WP_Upgrader_Skin {
	public function feedback( $string ) {
		// @note: Keep it empty.
	}
}
