<?php
/**
 * Folium UI — versioned implementation (the design-system runtime).
 *
 * Loaded by loader.php for the single newest active copy. Owns:
 *   - one shared "By Folium" top-level admin menu (so plugins don't each litter
 *     the sidebar with their own top-level item),
 *   - the plugin registry + switcher dropdown (the in-page selector),
 *   - asset enqueueing (CSS / JS / bundled fonts) on Folium screens.
 *
 * A Folium plugin registers itself (with a render callback) and DOES NOT call
 * add_menu_page itself:
 *
 *     Folium_UI::register_plugin( array(
 *         'slug'    => 'sitewise',
 *         'name'    => 'Sitewise',
 *         'tagline' => 'Grounded on-page chat',
 *         'icon'    => 'S',
 *         'render'  => array( $admin, 'render_page' ),
 *     ) );
 *     // page-specific assets:
 *     add_action( 'folium_ui_enqueue', function ( $slug ) { if ( 'sitewise' === $slug ) { ... } } );
 *     // at the top of render_page(): echo Folium_UI::render_switcher( 'sitewise' );
 *
 * Registration must happen before admin_menu priority 9 (e.g. in the plugin's
 * admin constructor on plugins_loaded).
 *
 * @package FoliumUI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Folium_UI' ) ) {
	return;
}

class Folium_UI {

	const VERSION     = '1.0.3';
	const MENU_SLUG   = 'folium';
	const STUDIO_SLUG = 'studio'; // the suite-overview landing (folium-ui's own "app").

	/** @var string Absolute path to the active folium-ui.php. */
	private static $file = '';

	/** @var string Base URL of the active copy (trailing slash). */
	private static $url = '';

	/** @var bool Whether assets have been enqueued this request. */
	private static $enqueued = false;

	/** @var array<string,array> Registered plugins, keyed by slug (insertion order kept). */
	private static $plugins = array();

	/** @var array<string,string> Admin page hook suffix per plugin slug. */
	private static $page_hooks = array();

	/** @var string Hook suffix of the top-level "By Folium" page. */
	private static $top_hook = '';

	/** @var string Slug rendered on the top-level "By Folium" landing. */
	private static $default_slug = '';

	/**
	 * Called once by the loader for the winning copy. Wires the shared menu and
	 * the enqueue hook.
	 *
	 * @param string $file    Path to this folium-ui.php.
	 * @param string $version Resolved version.
	 */
	public static function init( $file, $version = self::VERSION ) {
		self::$file = $file;
		self::$url  = trailingslashit( plugins_url( '', $file ) );

		add_action( 'admin_menu', array( __CLASS__, 'build_menu' ), 9 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
		add_action( 'admin_footer', array( __CLASS__, 'maybe_mark_menu_current_script' ) );
		add_filter( 'parent_file', array( __CLASS__, 'maybe_mark_parent_menu' ) );
		add_filter( 'custom_menu_order', array( __CLASS__, 'use_custom_menu_order' ) );
		add_filter( 'menu_order', array( __CLASS__, 'order_admin_menu' ) );
	}

	/**
	 * @return string Active design-system version.
	 */
	public static function version() {
		return self::VERSION;
	}

	/**
	 * Register a plugin into the Folium menu + switcher. Idempotent per slug.
	 *
	 * @param array $args slug, name, tagline, icon (1-2 char badge), render
	 *                    (callable), capability, menu (page slug; defaults to slug).
	 */
	public static function register_plugin( array $args ) {
		if ( empty( $args['slug'] ) ) {
			return;
		}
		self::$plugins[ $args['slug'] ] = wp_parse_args(
			$args,
			array(
				'name'       => $args['slug'],
				'tagline'    => '',
				'icon'       => strtoupper( substr( $args['slug'], 0, 1 ) ),
				'icon_url'   => '',
				'menu'       => $args['slug'],
				'capability' => 'manage_options',
				'render'     => null,
				'render_mode' => 'app',
			)
		);
	}

	/**
	 * Build the single "By Folium" top-level menu and hidden plugin pages. Runs
	 * on admin_menu priority 9.
	 */
	public static function build_menu() {
		if ( empty( self::$plugins ) ) {
			return;
		}

		$first              = reset( self::$plugins );
		self::$default_slug = key( self::$plugins );

		// Single top-level "By Folium" item. It renders the landing (the default
		// plugin for now; a universal home view later).
		self::$top_hook = add_menu_page(
			'By Folium',
			'By Folium',
			$first['capability'],
			self::MENU_SLUG,
			array( __CLASS__, 'render_landing' ),
			self::menu_icon(),
			2
		);

		// Register plugin pages as hidden admin pages. The in-page switcher owns
		// suite navigation, so the WP sidebar stays as one clean "By Folium" item.
		foreach ( self::$plugins as $slug => $p ) {
			$render = function () use ( $slug ) {
				Folium_UI::render_app( $slug );
			};
			$hook = add_submenu_page(
				'',
				$p['name'] . ' — By Folium',
				$p['name'],
				$p['capability'],
				$p['menu'],
				$render
			);
			if ( $hook ) {
				self::$page_hooks[ $slug ] = $hook;
			}
		}
	}

	/**
	 * Render the top-level "Folium" landing — the suite overview.
	 */
	public static function render_landing() {
		self::render_app( self::STUDIO_SLUG );
	}

	/**
	 * Enable a deterministic admin menu order so the shared suite entry sits
	 * directly below Dashboard even when another plugin claims an early position.
	 *
	 * @param bool $enabled Current custom menu order flag.
	 * @return bool
	 */
	public static function use_custom_menu_order( $enabled ) {
		return true;
	}

	/**
	 * Move the shared "By Folium" top-level menu directly after Dashboard.
	 *
	 * @param array $menu_order Ordered menu slugs.
	 * @return array
	 */
	public static function order_admin_menu( $menu_order ) {
		if ( ! is_array( $menu_order ) ) {
			return $menu_order;
		}

		$menu_order = array_values( array_diff( $menu_order, array( self::MENU_SLUG ) ) );
		$dashboard  = array_search( 'index.php', $menu_order, true );
		$insert_at  = false === $dashboard ? 0 : $dashboard + 1;

		array_splice( $menu_order, $insert_at, 0, array( self::MENU_SLUG ) );
		return $menu_order;
	}

	/**
	 * Plugin pages should highlight the shared "By Folium" sidebar item when
	 * they are open.
	 *
	 * @param string $parent_file Current WordPress parent menu file.
	 * @return string
	 */
	public static function maybe_mark_parent_menu( $parent_file ) {
		global $plugin_page;

		$current_page = $plugin_page;
		if ( empty( $current_page ) && isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin menu highlighting.
			$current_page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin menu highlighting.
		}

		if ( self::MENU_SLUG === $current_page ) {
			return self::MENU_SLUG;
		}

		foreach ( self::$plugins as $plugin ) {
			if ( ! empty( $plugin['menu'] ) && $plugin['menu'] === $current_page ) {
				return self::MENU_SLUG;
			}
		}

		return $parent_file;
	}

	/**
	 * Hidden admin pages do not get WordPress' native current-menu classes.
	 * Add them client-side so direct plugin pages still look anchored under
	 * the single "By Folium" sidebar item, without exposing a WP submenu.
	 */
	public static function maybe_mark_menu_current_script() {
		global $plugin_page;

		$current_page = $plugin_page;
		if ( empty( $current_page ) && isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin menu highlighting.
			$current_page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin menu highlighting.
		}

		$mark_current = false;
		foreach ( self::$plugins as $plugin ) {
			if ( ! empty( $plugin['menu'] ) && $plugin['menu'] === $current_page ) {
				$mark_current = true;
				break;
			}
		}

		if ( ! $mark_current ) {
			return;
		}
		?>
		<script>
		(function () {
			var item = document.getElementById('toplevel_page_folium');
			if (!item) { return; }
			item.classList.remove('wp-not-current-submenu');
			item.classList.add('current', 'menu-top');
			var link = item.querySelector('a.menu-top');
			if (!link) { return; }
			link.classList.remove('wp-not-current-submenu');
			link.classList.add('current', 'menu-top');
			link.setAttribute('aria-current', 'page');
		}());
		</script>
		<?php
	}

	/**
	 * Enqueue Folium UI + fire the per-plugin asset hook, but only on a
	 * registered Folium plugin screen.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public static function maybe_enqueue( $hook ) {
		// Top-level "Folium" landing = the suite overview (folium-ui's own app).
		if ( $hook === self::$top_hook ) {
			self::enqueue();
			self::enqueue_overview();
			return;
		}
		$slug = array_search( $hook, self::$page_hooks, true );
		if ( false === $slug ) {
			return;
		}
		self::enqueue();
		/**
		 * Fires when a Folium plugin's admin page assets should load. Plugins
		 * hook this to enqueue their own page-specific CSS/JS.
		 *
		 * @param string $slug The plugin slug being rendered.
		 */
		do_action( 'folium_ui_enqueue', $slug );
	}

	/**
	 * Enqueue the suite-overview app + localize the global catalog.
	 */
	private static function enqueue_overview() {
		wp_enqueue_script( 'folium-overview', self::$url . 'folium-overview.js', array( 'folium-ui', 'folium-app' ), self::ver( 'folium-overview.js' ), true );
		wp_localize_script( 'folium-overview', 'FoliumStudio', self::overview_data() );
	}

	/**
	 * Enqueue the design-system CSS (+ bundled fonts) and JS. Idempotent.
	 */
	public static function enqueue() {
		if ( self::$enqueued || '' === self::$url ) {
			return;
		}
		self::$enqueued = true;

		wp_enqueue_style( 'folium-ui', self::$url . 'folium-ui.css', array(), self::ver( 'folium-ui.css' ) );

		$fonts_css = @file_get_contents( self::dir() . 'fonts.css' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents, WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $fonts_css ) {
			$fonts_css = str_replace( '%%FONTS%%', untrailingslashit( self::$url . 'fonts' ), $fonts_css );
			wp_add_inline_style( 'folium-ui', $fonts_css );
		}

		wp_enqueue_script( 'folium-ui', self::$url . 'folium-ui.js', array(), self::ver( 'folium-ui.js' ), true );
		wp_enqueue_script( 'folium-app', self::$url . 'folium-app.js', array( 'folium-ui' ), self::ver( 'folium-app.js' ), true );
	}

	/**
	 * @return string Filesystem dir of the active copy (trailing slash).
	 */
	private static function dir() {
		return trailingslashit( dirname( self::$file ) );
	}

	/**
	 * Cache-busting asset version = file mtime (falls back to VERSION). Ensures
	 * edits aren't masked by aggressive CDN/browser caching.
	 *
	 * @param string $filename Asset filename in the lib dir.
	 * @return string
	 */
	private static function ver( $filename ) {
		$path = self::dir() . $filename;
		return file_exists( $path ) ? (string) filemtime( $path ) : self::VERSION;
	}

	/**
	 * Data-URI menu icon (Folium leaf mark — "folium" is Latin for leaf).
	 * Single-colour; WP tints it.
	 *
	 * @return string
	 */
	private static function menu_icon() {
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M6.05 8.05c-2.73 2.73-2.73 7.15-.02 9.88 1.47-3.4 4.09-6.24 7.36-7.93-2.77 2.34-4.71 5.61-5.39 9.32 2.6 1.23 5.8.78 7.95-1.37C19.43 14.47 20 4 20 4S9.53 4.57 6.05 8.05z"/></svg>';
		return 'data:image/svg+xml;base64,' . base64_encode( $svg );
	}

	/**
	 * Render the shared Folium app shell (#wpd): top bar with the plugin
	 * switcher, filter, dirty/saved indicators and Reset/Save, then the tab
	 * bar + main panel the active plugin's JS app renders into.
	 *
	 * @param string $current_slug Slug of the plugin being shown.
	 */
	public static function render_app( $current_slug = '' ) {
		$plugins = self::$plugins;

		if ( self::STUDIO_SLUG === $current_slug ) {
			// Suite-overview landing: a synthetic "Folium Studio" header.
			$current = array(
				'name'               => 'Folium Studio',
				'icon'               => 'F',
				'icon_url'           => '',
				'version'            => '',
				'active_chip'        => '<span class="fl-dot"></span> Suite',
				'search_placeholder' => 'Filter plugins…',
			);
		} else {
			$current = isset( $plugins[ $current_slug ] ) ? $plugins[ $current_slug ] : reset( $plugins );
			if ( ! $current ) {
				return;
			}
			$current_slug = $current['slug'] ?? key( $plugins );
		}
		?>
		<div class="wrap fl-root" style="margin:10px 20px 0 0;">
		<section id="wpd" class="folium-app" data-layout="tabs" data-accent="green" data-active="<?php echo esc_attr( $current_slug ); ?>" aria-label="By Folium">
			<header class="wpd-bar">
				<div class="wpd-switcher">
					<button class="wpd-switch" id="wpd-switch" type="button" aria-haspopup="menu" aria-expanded="false">
						<?php echo self::badge( $current, 'wpd-mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge(). ?>
						<span class="wpd-switch-text">
							<span class="nm"><?php echo esc_html( $current['name'] ); ?> <span class="fl-i wpd-caret" data-ic="chevdown"></span></span>
							<span class="by">Folium Studio</span>
						</span>
					</button>
					<div class="wpd-menu" id="wpd-menu" role="menu" hidden>
						<div class="wpd-menu-head"><span class="fl-meta" style="letter-spacing:.11em;text-transform:uppercase">Installed plugins</span></div>
						<?php foreach ( self::nav_plugins() as $p ) : ?>
							<a class="wpd-menu-item<?php echo $p['id'] === $current_slug ? ' is-active' : ''; ?>" role="menuitem" href="<?php echo esc_url( $p['url'] ); ?>">
								<?php echo self::badge( $p, 'wpd-mini-mark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in badge(). ?>
								<span class="wpd-menu-t"><b><?php echo esc_html( $p['name'] ); ?></b><span><?php echo esc_html( $p['tagline'] ); ?></span></span>
								<?php if ( $p['id'] === $current_slug ) : ?><span class="fl-i wpd-menu-check" data-ic="check"></span><?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php $ver = $current['version'] ?? ( 'v' . self::VERSION ); ?>
				<?php if ( $ver ) : ?>
					<span class="fl-pill" id="wpd-ver" style="margin-left:4px"><span class="fl-dot" style="background:var(--fl-accent)"></span> <?php echo esc_html( $ver ); ?></span>
				<?php endif; ?>
				<div class="grow"></div>
				<div class="fl-search wpd-search">
					<span class="fl-i" data-ic="search"></span>
					<input id="wpd-search" class="fl-input" placeholder="<?php echo esc_attr( $current['search_placeholder'] ?? 'Filter…' ); ?>" aria-label="Filter">
					<span class="fl-kbd">/</span>
				</div>
				<div class="wpd-barsep"></div>
				<?php if ( ! empty( $current['active_chip'] ) ) : ?>
					<span class="fl-pill fl-pill--good" id="wpd-active-chip"><?php echo wp_kses_post( $current['active_chip'] ); ?></span>
				<?php endif; ?>
				<span class="fl-pill fl-pill--warn" id="wpd-dirty" hidden><span class="fl-dot"></span> Unsaved</span>
				<span class="fl-meta" id="wpd-saved-note" hidden><span class="fl-i" data-ic="check"></span> Saved</span>
				<button class="fl-btn fl-btn--ghost fl-btn--sm" id="wpd-reset" type="button"><span class="fl-i" data-ic="refresh"></span> Reset</button>
				<button class="fl-btn fl-btn--primary fl-btn--sm" id="wpd-save" type="button" disabled><span class="fl-i" data-ic="save"></span> Save changes</button>
			</header>
			<div class="wpd-body">
				<aside id="wpd-nav" hidden></aside>
				<div class="wpd-maincol">
					<div id="wpd-tabsbar" hidden></div>
					<div id="wpd-main">
						<?php
						if (
							self::STUDIO_SLUG !== $current_slug
							&& ! empty( $current['render'] )
							&& is_callable( $current['render'] )
							&& 'php' === ( $current['render_mode'] ?? 'app' )
						) {
							call_user_func( $current['render'] );
						}
						?>
					</div>
				</div>
			</div>
			<div id="wpd-toast" class="fl-card fl-card-pad" hidden></div>
		</section>
		</div>
		<?php
	}

	/**
	 * 1-2 char badge passes through; longer values fall back to an initial.
	 *
	 * @param string $icon Icon hint.
	 * @return string
	 */
	private static function icon_or_initial( $icon ) {
		return ( is_string( $icon ) && strlen( $icon ) <= 2 ) ? $icon : strtoupper( substr( (string) $icon, 0, 1 ) );
	}

	/**
	 * A plugin badge: an icon image when registered, else the letter mark.
	 *
	 * @param array  $p     Plugin registry entry.
	 * @param string $class Badge wrapper class.
	 * @return string HTML.
	 */
	private static function badge( $p, $class ) {
		if ( ! empty( $p['icon_url'] ) ) {
			return '<span class="' . esc_attr( $class ) . ' has-img"><img src="' . esc_url( $p['icon_url'] ) . '" alt="" /></span>';
		}
		return '<span class="' . esc_attr( $class ) . '">' . esc_html( self::icon_or_initial( $p['icon'] ) ) . '</span>';
	}

	/**
	 * THE GLOBAL FOLIUM CATALOG. Single source of truth for the suite overview —
	 * edit here (in the canonical folium-ui) to add a plugin or change a
	 * description; re-sync into the plugins and every one that ships the updated
	 * folium-ui shows it. Each entry:
	 *   id    folium page slug if the plugin is on the Folium frame, else wp.org slug
	 *   mark  letter badge · name · tag · desc
	 *   stats default headline stats [ [value,label], … ] (live stats override via registry)
	 *   file  plugin main file for install/active detection (folder/main.php)
	 *   admin admin URL for an installed-but-not-on-Folium plugin's own screen
	 *   wporg / home links
	 *
	 * @return array<int,array>
	 */
	public static function catalog() {
		$catalog = array(
			array(
				'id'    => 'wp-disable', // matches the Folium page slug + permanent wp.org slug.
				'mark'  => 'F',
				'name'  => 'Featherweight',
				'tag'   => 'Performance',
				'desc'  => 'Strip the scripts, meta tags and requests WordPress injects by default — lighter pages, fewer round-trips.',
				'stats' => array( array( '25', 'optimisations' ), array( '214 KB', 'saved / page' ), array( '86', 'perf score' ) ),
				'file'  => 'wp-disable/wpperformance.php',
				'wporg' => 'https://wordpress.org/plugins/wp-disable/',
				'home'  => 'https://foliumstudio.co.uk/plugins/featherweight/',
			),
			array(
				'id'    => 'sitewise', // matches the Folium page slug it registers.
				'mark'  => 'S',
				'name'  => 'Sitewise',
				'tag'   => 'AI · Chat',
				'desc'  => 'A grounded on-page chat assistant that answers only from your own content and routes anything else to contact.',
				'stats' => array( array( '—', 'messages / mo' ), array( '—', 'from corpus' ), array( '—', 'pages indexed' ) ),
				'file'  => 'wp-call-me-back/sitewise.php',
				'wporg' => 'https://wordpress.org/plugins/wp-call-me-back/',
				'home'  => 'https://foliumstudio.co.uk/plugins/sitewise/',
			),
			array(
				'id'     => 'cache-performance',
				'mark'   => 'C',
				'name'   => 'Cache by Folium',
				'tag'    => 'Performance',
				'desc'   => 'Page and object caching with smart purge rules and edge support.',
				'stats'  => array( array( '—', 'cached' ), array( '—', 'TTFB' ) ),
				'file'   => 'cache-performance/optimisationio.php',
				'wporg'  => 'https://wordpress.org/plugins/cache-performance/',
				'home'   => 'https://foliumstudio.co.uk/plugins/folium-cache/',
			),
		);

		/**
		 * Filter the Folium suite catalog (e.g. to inject a private/EDD plugin).
		 *
		 * @param array $catalog The catalog entries.
		 */
		return apply_filters( 'folium_ui_catalog', $catalog );
	}

	/**
	 * Build the overview payload (catalog merged with live install/active state
	 * and any live stats registered by adopted plugins).
	 *
	 * @return array{plugins:array,stats:array}
	 */
	public static function overview_data() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed = function_exists( 'get_plugins' ) ? get_plugins() : array();

		$plugins = array();
		foreach ( self::catalog() as $c ) {
			$id       = $c['id'];
			$file     = $c['file'] ?? '';
			$on_fol   = isset( self::$plugins[ $id ] );             // adopted into the Folium frame.
			$has_file = $file && isset( $installed[ $file ] );
			$active   = $on_fol || ( $file && is_plugin_active( $file ) );
			$coming   = ! empty( $c['coming'] ) && ! $has_file;     // unreleased, not yet installed.
			$menu     = $on_fol ? ( self::$plugins[ $id ]['menu'] ?? $id ) : $id;

			if ( $active ) {
				$status = 'active';
			} elseif ( $has_file ) {
				$status = 'inactive';
			} elseif ( $coming ) {
				$status = 'coming-soon';
			} else {
				$status = 'not-installed';
			}

			// Where the card click goes + the foot label.
			$open = false;
			$note = '';
			if ( $on_fol ) {
				$url  = admin_url( 'admin.php?page=' . $menu );
				$open = true;
			} elseif ( $active && ! empty( $c['admin'] ) ) {
				$url  = admin_url( $c['admin'] );
				$open = true;
			} elseif ( $has_file ) {
				$url  = admin_url( 'plugins.php' );
				$note = __( 'Activate', 'wp-disable' );
			} elseif ( $coming ) {
				$url  = '';
				$note = __( 'Coming soon', 'wp-disable' );
			} else {
				$url  = $c['wporg'] ?? ( $c['home'] ?? '' );
				$note = __( 'Install', 'wp-disable' );
			}

			$ver = $has_file && ! empty( $installed[ $file ]['Version'] ) ? 'v' . $installed[ $file ]['Version'] : '';

			// Live stats from an adopted plugin override the catalog defaults.
			$stats = $c['stats'] ?? array();
			if ( $on_fol && ! empty( self::$plugins[ $id ]['stats'] ) ) {
				$stats = self::$plugins[ $id ]['stats'];
			}

			$plugins[] = array(
				'id'       => $id,
				'mark'     => $c['mark'],
				'icon_url' => $on_fol && ! empty( self::$plugins[ $id ]['icon_url'] ) ? self::$plugins[ $id ]['icon_url'] : '',
				'name'     => $c['name'],
				'tag'      => $c['tag'],
				'desc'     => $c['desc'],
				'stats'    => $stats,
				'status'   => $status,
				'open'     => $open,
				'url'      => $url,
				'note'     => $note,
				'ver'      => $ver,
			);
		}

		$active_count = count(
			array_filter(
				$plugins,
				function ( $p ) {
					return 'active' === $p['status'];
				}
			)
		);

		$cache = array(
			'plugins' => $plugins,
			'stats'   => array(
				array( (string) $active_count, 'Active plugins' ),
			),
		);
		return $cache;
	}

	/**
	 * Plugins for the switcher dropdown: installed (active or inactive) Folium
	 * plugins from the catalog, so non-adopted ones (e.g. Cache by Folium) appear too.
	 *
	 * @return array<int,array>
	 */
	public static function nav_plugins() {
		$items = array();
		foreach ( self::overview_data()['plugins'] as $p ) {
			if ( ! in_array( $p['status'], array( 'active', 'inactive' ), true ) ) {
				continue;
			}
			$items[] = array(
				'id'       => $p['id'],
				'name'     => $p['name'],
				'tagline'  => $p['tag'],
				'icon'     => $p['mark'],
				'icon_url' => $p['icon_url'],
				'url'      => $p['url'],
			);
		}
		return $items;
	}
}
