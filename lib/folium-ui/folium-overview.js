/*!
 * Folium Studio — suite overview (the shared "Folium" landing).
 * Ported from the Folium UI standalone. Data-driven: reads window.FoliumStudio
 * (the GLOBAL catalog, localized by Folium_UI from folium-ui — edit it there and
 * every plugin that ships the updated folium-ui shows it). Registers as the
 * 'studio' app with the Folium frame and mounts into #wpd-main.
 */
( function () {
	var FS = ( window.FStudio = window.FStudio || {} );
	var $ = function ( s, r ) { return ( r || document ).querySelector( s ); };
	var ICON = function ( n ) { return window.FL.icon( n ); };
	var DATA = window.FoliumStudio || { plugins: [], stats: [] };
	FS.PLUGINS = DATA.plugins || [];

	function pill( s ) {
		if ( s === 'active' ) { return '<span class="fl-pill fl-pill--good"><span class="fl-dot"></span> Active</span>'; }
		if ( s === 'inactive' ) { return '<span class="fl-pill"><span class="fl-dot"></span> Inactive</span>'; }
		if ( s === 'coming-soon' ) { return '<span class="fl-pill fl-pill--info"><span class="fl-dot"></span> Coming soon</span>'; }
		return '<span class="fl-pill"><span class="fl-dot"></span> Not installed</span>';
	}

	function footIcon( s ) {
		if ( s === 'inactive' ) { return 'plug'; }
		if ( s === 'coming-soon' ) { return 'clock'; }
		return 'download';
	}

	function esc( s ) { var d = document.createElement( 'div' ); d.textContent = s == null ? '' : s; return d.innerHTML; }

	function card( p ) {
		var stats = ( p.stats || [] ).map( function ( s ) {
			return '<div class="fs-cstat"><span class="v">' + esc( s[0] ) + '</span><span class="k">' + esc( s[1] ) + '</span></div>';
		} ).join( '' );
		var foot = p.open
			? '<span class="fs-open">Open ' + esc( p.name ) + ' <span class="fl-i" data-ic="chevron"></span></span><span class="fl-meta">' + esc( p.ver || '' ) + '</span>'
			: '<span class="fs-open">' + esc( p.note || 'Install' ) + ' <span class="fl-i" data-ic="' + footIcon( p.status ) + '"></span></span>';
		var search = ( ( p.name || '' ) + ' ' + ( p.tag || '' ) + ' ' + ( p.desc || '' ) ).toLowerCase();
		return '<button class="fs-card ' + ( p.open ? '' : 'is-soft' ) + '" data-fs-open="' + esc( p.id ) + '" data-fs-url="' + esc( p.url || '' ) + '" ' + ( p.open ? '' : 'data-fs-soft="1"' ) + ' data-fssearch="' + esc( search ) + '">' +
			'<div class="fs-card-top"><span class="fs-mark">' + esc( p.mark ) + '</span><div class="fs-card-id"><b>' + esc( p.name ) + '</b><span>Folium Studio · ' + esc( p.tag ) + '</span></div>' + pill( p.status ) + '</div>' +
			'<p class="fs-card-desc">' + esc( p.desc ) + '</p>' +
			'<div class="fs-card-stats">' + stats + '</div>' +
			'<div class="fs-card-foot">' + foot + '</div>' +
		'</button>';
	}

	FS.render = function () {
		var main = $( '#wpd-main' );
		if ( ! main ) { return; }
		var active = FS.PLUGINS.filter( function ( p ) { return p.status === 'active'; } ).length;
		var hs = DATA.stats || [];
		var heroStats = hs.length
			? hs.map( function ( s ) { return '<div class="fs-stat"><span class="v">' + esc( s[0] ) + ( s[2] ? '<span class="u">' + esc( s[2] ) + '</span>' : '' ) + '</span><span class="k">' + esc( s[1] ) + '</span></div>'; } ).join( '' )
			: '<div class="fs-stat"><span class="v">' + active + '</span><span class="k">Active plugins</span></div>';

		main.innerHTML = '<div class="fs-home" data-screen-label="Folium Studio">' +
			'<div class="fs-hero">' +
				'<span class="fl-eyebrow"><span class="fl-num">00</span> — FOLIUM STUDIO</span>' +
				'<h1 class="fl-display">A calmer WordPress, <span class="fl-ital">one plugin</span> at a time.</h1>' +
				'<p class="fl-lead">Your Folium suite, in one place. Pick a plugin to tune — every one shares the same account, the same design, and the same calm.</p>' +
				'<div class="fs-stats">' + heroStats + '</div>' +
			'</div>' +
			'<div class="fs-sectionlabel"><span class="fl-eyebrow">YOUR PLUGINS</span><span class="fl-meta">' + FS.PLUGINS.length + ' listed · click to open</span></div>' +
			'<div class="fs-grid" id="fs-grid">' + FS.PLUGINS.map( card ).join( '' ) + '</div>' +
		'</div>';
		main.querySelectorAll( '[data-ic]' ).forEach( function ( el ) { el.innerHTML = ICON( el.getAttribute( 'data-ic' ) ); } );
	};

	FS.filter = function ( q ) {
		q = ( q || '' ).trim().toLowerCase();
		var any = false;
		document.querySelectorAll( '#fs-grid .fs-card' ).forEach( function ( c ) {
			var hit = ! q || c.getAttribute( 'data-fssearch' ).indexOf( q ) !== -1;
			c.style.display = hit ? '' : 'none';
			if ( hit ) { any = true; }
		} );
		var empty = $( '#fs-empty' );
		if ( ! any ) {
			if ( ! empty ) { empty = document.createElement( 'div' ); empty.id = 'fs-empty'; empty.className = 'fs-empty'; var g = $( '#fs-grid' ); if ( g ) { g.after( empty ); } }
			empty.textContent = 'No plugins match “' + q + '”.';
			empty.style.display = '';
		} else if ( empty ) { empty.style.display = 'none'; }
	};

	FS.wireOnce = function () {
		if ( FS._wired ) { return; } FS._wired = true;
		var root = $( '#wpd' );
		if ( ! root ) { return; }
		root.addEventListener( 'click', function ( e ) {
			if ( window.__activePlugin !== 'studio' ) { return; }
			var c = e.target.closest( '[data-fs-open]' );
			if ( ! c ) { return; }
			var url = c.getAttribute( 'data-fs-url' );
			if ( url ) { window.location.href = url; return; }
			var id = c.getAttribute( 'data-fs-open' );
			if ( c.getAttribute( 'data-fs-soft' ) ) {
				window.WPD && WPD.toast && WPD.toast( 'That plugin is not available here.' );
				return;
			}
			window.Folium && window.Folium.activate && window.Folium.activate( id );
		} );
	};

	if ( window.Folium && window.Folium.registerApp ) {
		window.Folium.registerApp( 'studio', {
			mount: function () { FS.render(); FS.wireOnce(); },
			filter: function ( q ) { FS.filter( q ); }
		} );
	}
} )();
