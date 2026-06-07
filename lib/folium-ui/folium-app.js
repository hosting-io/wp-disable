/*!
 * Folium app frame — shared host shell behaviour for Folium plugins.
 *
 * The PHP-rendered #wpd shell provides the top bar (switcher, filter, Reset/Save)
 * and an empty #wpd-tabsbar + #wpd-main. A plugin ships its own app and calls
 * window.Folium.registerApp(slug, api). The frame mounts the active app (read
 * from #wpd[data-active]) and routes the shared controls to it.
 *
 * App api: { mount(), save(), reset(), filter(str) } — all optional except mount.
 */
( function () {
	'use strict';

	var apps = {};
	var mounted = false;

	function $( sel ) { return document.querySelector( sel ); }

	function activeSlug() {
		var root = $( '#wpd' );
		return root ? root.getAttribute( 'data-active' ) : '';
	}
	function activeApi() { return apps[ activeSlug() ] || null; }

	/* ---- toast ------------------------------------------------------------ */
	var toastTimer = null;
	function toast( msg ) {
		var t = $( '#wpd-toast' );
		if ( ! t ) { return; }
		t.innerHTML = '<span class="fl-i" data-ic="check" style="color:var(--fl-accent)"></span> ' + escapeHtml( msg );
		if ( window.FL && FL.hydrate ) { FL.hydrate( t ); }
		t.hidden = false;
		// reflow then show
		void t.offsetWidth;
		t.classList.add( 'show' );
		clearTimeout( toastTimer );
		toastTimer = setTimeout( function () {
			t.classList.remove( 'show' );
			setTimeout( function () { t.hidden = true; }, 250 );
		}, 2600 );
	}

	function escapeHtml( s ) { var d = document.createElement( 'div' ); d.textContent = s; return d.innerHTML; }

	/* ---- switcher dropdown ------------------------------------------------- */
	function wireSwitcher() {
		var btn = $( '#wpd-switch' );
		var menu = $( '#wpd-menu' );
		if ( ! btn || ! menu ) { return; }
		btn.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
			var open = menu.hidden;
			menu.hidden = ! open;
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! menu.contains( e.target ) && ! btn.contains( e.target ) ) {
				menu.hidden = true;
				btn.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	/* ---- shared top-bar controls ------------------------------------------ */
	function wireControls() {
		var save = $( '#wpd-save' );
		if ( save ) {
			save.addEventListener( 'click', function () {
				var api = activeApi();
				if ( api && api.save ) { api.save(); }
			} );
		}
		var reset = $( '#wpd-reset' );
		if ( reset ) {
			reset.addEventListener( 'click', function () {
				var api = activeApi();
				if ( api && api.reset ) { api.reset(); }
			} );
		}
		var search = $( '#wpd-search' );
		if ( search ) {
			search.addEventListener( 'input', function () {
				var api = activeApi();
				if ( api && api.filter ) { api.filter( search.value ); }
			} );
			document.addEventListener( 'keydown', function ( e ) {
				if ( e.key === '/' && document.activeElement !== search && ! /input|textarea/i.test( ( document.activeElement || {} ).tagName || '' ) ) {
					e.preventDefault();
					search.focus();
				}
			} );
		}
	}

	/* ---- registry + mount ------------------------------------------------- */
	function mountActive() {
		var slug = activeSlug();
		window.__activePlugin = slug;
		var api = apps[ slug ];
		if ( api && api.mount && ! mounted ) {
			mounted = true;
			api.mount();
		}
	}

	function registerApp( slug, api ) {
		apps[ slug ] = api;
		// If the frame is already up and this is the active app, mount now.
		if ( document.getElementById( 'wpd' ) && slug === activeSlug() ) {
			mountActive();
		}
	}

	// Navigate to a plugin's Folium page (used by the suite overview).
	function activate( id ) {
		window.location.href = 'admin.php?page=' + encodeURIComponent( id );
	}

	// Public API. WPD is kept as an alias so design-bundle apps work unchanged.
	window.Folium = { registerApp: registerApp, toast: toast, activeSlug: activeSlug, activate: activate };
	window.WPD = window.WPD || {};
	window.WPD.toast = toast;

	function init() {
		if ( ! $( '#wpd' ) ) { return; }
		wireSwitcher();
		wireControls();
		mountActive();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
