/*!
 * Folium UI — shared front-end behaviour for Folium Studio plugins.
 * Provides FL.icon(name) (inline SVG set) and the plugin-switcher dropdown.
 * Canonical: FoliumStudio/folium-ui. Do not edit vendored copies.
 */
( function () {
	'use strict';

	var P = {
		gauge: '<path d="M12 13a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="m13.4 10.6 3.1-3.1"/><path d="M5 18a8 8 0 1 1 14 0"/>',
		sparkle: '<path d="M12 3v4M12 17v4M3 12h4M17 12h4"/><path d="M6.3 6.3 9 9M15 15l2.7 2.7M17.7 6.3 15 9M9 15l-2.7 2.7"/>',
		broom: '<path d="M13 3 7.5 12.5"/><path d="M3 21s1.5-4 5-6 6.5-1 6.5-1"/><path d="m11 9 5 5"/><path d="M19 21c1-3-1-6-4-7"/>',
		comment: '<path d="M21 12a8 8 0 0 1-11.5 7.2L4 20l1-4.5A8 8 0 1 1 21 12Z"/>',
		database: '<ellipse cx="12" cy="5.5" rx="7" ry="2.8"/><path d="M5 5.5v6c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8v-6"/><path d="M5 11.5v6c0 1.5 3.1 2.8 7 2.8s7-1.3 7-2.8v-6"/>',
		cart: '<circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/><path d="M3 4h2l2.2 11.2a1.5 1.5 0 0 0 1.5 1.2h8.1a1.5 1.5 0 0 0 1.5-1.2L21 8H6"/>',
		tools: '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.2L4 16.8 7.2 20l5.3-5.3a4 4 0 0 0 5.2-5.4l-2.5 2.5-2.3-.4-.4-2.3 2.2-2.8Z"/>',
		shield: '<path d="M12 3 5 6v5c0 4.5 3 7.6 7 9 4-1.4 7-4.5 7-9V6l-7-3Z"/><path d="m9.5 12 1.8 1.8L15 10"/>',
		search: '<circle cx="11" cy="11" r="6"/><path d="m20 20-3.2-3.2"/>',
		bolt: '<path d="M13 3 5 13h6l-1 8 8-10h-6l1-8Z"/>',
		download: '<path d="M12 4v10"/><path d="m8 11 4 4 4-4"/><path d="M5 19h14"/>',
		upload: '<path d="M12 16V6"/><path d="m8 9 4-4 4 4"/><path d="M5 19h14"/>',
		check: '<path d="m5 12 4 4L19 7"/>',
		info: '<circle cx="12" cy="12" r="8.5"/><path d="M12 11v5M12 8h.01"/>',
		warn: '<path d="M12 4 3 19h18L12 4Z"/><path d="M12 10v4M12 17h.01"/>',
		clock: '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
		refresh: '<path d="M4 12a8 8 0 0 1 13.7-5.6L20 8"/><path d="M20 4v4h-4"/><path d="M20 12a8 8 0 0 1-13.7 5.6L4 16"/><path d="M4 20v-4h4"/>',
		plug: '<path d="M9 3v5M15 3v5"/><path d="M7 8h10v3a5 5 0 0 1-10 0V8Z"/><path d="M12 16v5"/>',
		leaf: '<path d="M4 20c0-8 6-15 16-15 0 10-7 16-16 15Z"/><path d="M4 20C7 15 11 12 16 10"/>',
		save: '<path d="M5 4h11l3 3v13H5V4Z"/><path d="M8 4v5h7"/><path d="M8 20v-6h8v6"/>',
		chevron: '<path d="m9 6 6 6-6 6"/>',
		chevdown: '<path d="m6 9 6 6 6-6"/>',
		dot: '<circle cx="12" cy="12" r="4"/>',
		grid: '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>',
		list: '<path d="M8 6h12M8 12h12M8 18h12"/><path d="M4 6h.01M4 12h.01M4 18h.01"/>',
		table: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><path d="M4 10h16M10 10v9"/>',
		external: '<path d="M14 5h5v5"/><path d="M19 5l-7 7"/><path d="M19 13v6H5V5h6"/>',
		code: '<path d="m9 8-4 4 4 4M15 8l4 4-4 4"/>',
		feed: '<path d="M5 19a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"/><path d="M5 12a7 7 0 0 1 7 7"/><path d="M5 5a14 14 0 0 1 14 14"/>',
		image: '<rect x="4" y="5" width="16" height="14" rx="1.5"/><circle cx="9" cy="10" r="1.5"/><path d="m5 17 4-4 3 3 3-3 4 4"/>',
		eye: '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
		star: '<path d="m12 4 2.3 4.8 5.2.7-3.8 3.6.9 5.1L12 16l-4.6 2.4.9-5.1L4.5 9.7l5.2-.7L12 4Z"/>',
		arrowup: '<path d="M12 19V5M6 11l6-6 6 6"/>',
		arrowdown: '<path d="M12 5v14M6 13l6 6 6-6"/>',
		phone: '<path d="M6 3h3l1.5 5-2 1.5a12 12 0 0 0 5.5 5.5l1.5-2 5 1.5v3a2 2 0 0 1-2.2 2A17 17 0 0 1 4 5.2 2 2 0 0 1 6 3Z"/>',
		send: '<path d="M4 12 20 4l-6 16-3-7-7-1Z"/>',
		x: '<path d="M6 6l12 12M18 6 6 18"/>',
		chat: '<path d="M21 12a8 8 0 0 1-11.5 7.2L4 20l1-4.5A8 8 0 1 1 21 12Z"/>'
	};

	function icon( name, opts ) {
		var o = opts || {};
		var sw = o.stroke || 1.6;
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' + sw + '" stroke-linecap="round" stroke-linejoin="round">' + ( P[ name ] || P.dot ) + '</svg>';
	}

	// Hydrate any <span class="fl-i" data-ic="name"> placeholders in the DOM.
	function hydrate( root ) {
		var nodes = ( root || document ).querySelectorAll( '.fl-i[data-ic]' );
		for ( var i = 0; i < nodes.length; i++ ) {
			var n = nodes[ i ];
			if ( n.getAttribute( 'data-fl-done' ) ) { continue; }
			n.innerHTML = icon( n.getAttribute( 'data-ic' ) );
			n.setAttribute( 'data-fl-done', '1' );
		}
	}

	window.FL = window.FL || {};
	window.FL.icon = icon;
	window.FL.icons = P;
	window.FL.hydrate = hydrate;

	function init() {
		hydrate( document );
	}
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
