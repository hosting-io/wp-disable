<?php
/**
 * Folium UI — bootstrap / version negotiator.
 *
 * Each Folium plugin bundles its OWN copy of folium-ui at lib/folium-ui/ and
 * `require_once`s this file from its main plugin file. Every active copy
 * registers its version; on `plugins_loaded` the single highest-versioned copy
 * is loaded and defines the Folium_UI class. This means:
 *
 *   - No hard dependency: every plugin works standalone (it has its own copy).
 *   - One runtime instance: only the newest active copy enqueues assets.
 *   - Safe removal: disable/delete any plugin and the next-newest takes over.
 *
 * Keep THIS file stable across versions — it is the part that may run from an
 * older copy. All version-specific behaviour lives in folium-ui.php.
 *
 * @package FoliumUI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register this copy. (Version is stamped to match folium-ui.php's constant.)
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Shared Folium UI registry must use one stable global across bundled plugin copies.
if ( ! isset( $GLOBALS['folium_ui_registry'] ) ) {
	$GLOBALS['folium_ui_registry'] = array();
}
$GLOBALS['folium_ui_registry'][] = array(
	'version' => '1.0.3',
	'path'    => __DIR__ . '/folium-ui.php',
);
// phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Schedule the resolver exactly once, regardless of which copy runs first.
if ( ! function_exists( 'folium_ui_boot' ) ) {

	/**
	 * Pick the newest registered copy and load it.
	 */
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Shared Folium UI loader function must be stable across bundled copies.
	function folium_ui_boot() {
		$registry = isset( $GLOBALS['folium_ui_registry'] ) ? $GLOBALS['folium_ui_registry'] : array();
		if ( empty( $registry ) ) {
			return;
		}
		usort(
			$registry,
			function ( $a, $b ) {
				return version_compare( $b['version'], $a['version'] );
			}
		);
		$winner = $registry[0];
		if ( ! empty( $winner['path'] ) && is_readable( $winner['path'] ) ) {
			require_once $winner['path'];          // defines Folium_UI (guarded).
			if ( class_exists( 'Folium_UI' ) ) {
				Folium_UI::init( $winner['path'], $winner['version'] );
			}
		}
	}

	// Priority 4: before plugins' own priority-5+ init that registers screens.
	add_action( 'plugins_loaded', 'folium_ui_boot', 4 );
}
