/**
 * Dark Mode for WP Dashboard — admin bar toggle.
 *
 * Vanilla JavaScript, no jQuery. Configuration arrives as the global
 * darkModeDashboard (wp_localize_script).
 */
( function () {
	'use strict';

	var cfg = window.darkModeDashboard;

	if ( ! cfg ) {
		return;
	}

	var node = document.getElementById( 'wp-admin-bar-dark-mode-dashboard' );
	var toggle = node ? node.querySelector( '.ab-item' ) : null;

	if ( ! node || ! toggle ) {
		return;
	}

	var live = node.querySelector( '.dm-live' );
	var body = document.body;
	var saved = cfg.preference; // What the server currently has stored.
	var initialPref = cfg.preference; // What the server pre-painted this page for.
	var inFlight = false;
	var queued = null;
	var stylesLoaded = ! cfg.lazyStyles || ! Object.keys( cfg.lazyStyles ).length;

	function isDark() {
		return body.classList.contains( 'dark-mode' );
	}

	function announce( message ) {
		if ( live ) {
			live.textContent = message;
		}
	}

	/**
	 * Reflect the current state in the icon and the switch semantics.
	 *
	 * role/aria-checked are applied from here rather than from PHP because the
	 * control does nothing at all without this script running.
	 */
	function paintState() {
		var dark = isDark();

		node.classList.toggle( 'dm-light', ! dark );
		toggle.setAttribute( 'role', 'switch' );
		toggle.setAttribute( 'aria-checked', dark ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', dark ? cfg.i18n.switchToLight : cfg.i18n.switchToDark );
	}

	// ---------------------------------------------------------------------
	// Lazy stylesheet loading
	// ---------------------------------------------------------------------

	/**
	 * Fetch the theme stylesheets a light-mode user never downloaded.
	 *
	 * Resolves once they have loaded, or after a short grace period, so the
	 * class flip does not land on a page with no rules behind it.
	 *
	 * @return {Promise} Resolves when the sheets are in the document.
	 */
	function loadStyles() {
		if ( stylesLoaded ) {
			return Promise.resolve();
		}

		stylesLoaded = true;

		var pending = Object.keys( cfg.lazyStyles ).map( function ( handle ) {
			return new Promise( function ( resolve ) {
				var id = handle + '-css';

				if ( document.getElementById( id ) ) {
					resolve();
					return;
				}

				var link = document.createElement( 'link' );

				link.id = id;
				link.rel = 'stylesheet';
				link.href = cfg.lazyStyles[ handle ];
				link.addEventListener( 'load', resolve );
				link.addEventListener( 'error', resolve );
				document.head.appendChild( link );
			} );
		} );

		return Promise.race( [
			Promise.all( pending ),
			new Promise( function ( resolve ) {
				window.setTimeout( resolve, 600 );
			} ),
		] );
	}

	// ---------------------------------------------------------------------
	// Editor canvases
	// ---------------------------------------------------------------------

	var CANVAS_STYLE_ID = 'dm-canvas-baseline';
	var TINYMCE_STYLE_ID = 'dm-toggle-override';

	function setDocStyle( doc, id, css ) {
		var existing = doc.getElementById( id );

		if ( ! css ) {
			if ( existing ) {
				existing.parentNode.removeChild( existing );
			}
			return;
		}

		if ( existing ) {
			existing.textContent = css;
			return;
		}

		var style = doc.createElement( 'style' );

		style.id = id;
		style.textContent = css;
		( doc.head || doc.documentElement ).appendChild( style );
	}

	// ---------------------------------------------------------------------
	// Coloured blocks inside the canvas
	// ---------------------------------------------------------------------

	/*
	 * The dark canvas paints text light. A block the author gave a light
	 * background but no text colour of its own — a white Group, a cream
	 * paragraph, a light Cover — then rendered light-on-light, at around
	 * 1.1:1. Which backgrounds are light cannot be known from CSS (custom
	 * colours are inline hex, theme presets have arbitrary names), so each one
	 * is measured here and tagged, and editor-canvas.scss gives the tagged
	 * block a text colour that reads on it:
	 *
	 *   data-dm-ink="light"   light background → dark text
	 *   data-dm-ink="dark"    dark background → the canvas' light text
	 *   data-dm-ink="author"  the author picked a text colour → leave it, and
	 *                         let the block's content inherit it
	 *
	 * A data attribute rather than a class: React owns the block's className
	 * and rewrites it on every selection change; it leaves attributes it never
	 * set alone.
	 */
	var INK_ATTR = 'data-dm-ink';
	var INK_SCAN = '.has-background,.has-text-color,.wp-block-cover,[style*="color"],[' + INK_ATTR + ']';

	// Background luminance at which the canvas' light text and dark text are
	// equally legible (both about 3.8:1). Above it, dark text reads better.
	var INK_THRESHOLD = 0.19;

	/**
	 * Relative luminance of a computed colour, or null when it is transparent
	 * enough not to count, or in a format this does not parse.
	 *
	 * @param {string} colour Computed colour value.
	 * @return {?number} Luminance between 0 and 1.
	 */
	function luminance( colour ) {
		var srgb = /^color\(srgb/.test( colour || '' );
		var parts = ( colour || '' ).match( /[\d.]+/g );

		if ( ! parts || parts.length < 3 || ( ! /^(rgb|color\(srgb)/.test( colour ) ) ) {
			return null;
		}

		if ( parts.length > 3 && Number( parts[ 3 ] ) < 0.5 ) {
			return null;
		}

		var c = parts.slice( 0, 3 ).map( function ( v ) {
			v = srgb ? Number( v ) : Number( v ) / 255;
			return v <= 0.03928 ? v / 12.92 : Math.pow( ( v + 0.055 ) / 1.055, 2.4 );
		} );

		return 0.2126 * c[ 0 ] + 0.7152 * c[ 1 ] + 0.0722 * c[ 2 ];
	}

	function inkFor( el, view ) {
		if ( el.classList.contains( 'has-text-color' ) || el.style.color ) {
			return 'author';
		}

		// A Cover paints its colour on a child overlay, not on itself; the
		// editor already works out whether that overlay is light.
		if ( el.classList.contains( 'wp-block-cover' ) ) {
			return el.classList.contains( 'is-light' ) ? 'light' : 'dark';
		}

		var lum = luminance( view.getComputedStyle( el ).backgroundColor );

		if ( null === lum ) {
			return '';
		}

		return lum > INK_THRESHOLD ? 'light' : 'dark';
	}

	function markInk( doc ) {
		var view = doc.defaultView;

		if ( ! view ) {
			return;
		}

		Array.prototype.forEach.call( doc.querySelectorAll( INK_SCAN ), function ( el ) {
			var ink = inkFor( el, view );

			if ( ink ) {
				if ( el.getAttribute( INK_ATTR ) !== ink ) {
					el.setAttribute( INK_ATTR, ink );
				}
			} else if ( el.hasAttribute( INK_ATTR ) ) {
				el.removeAttribute( INK_ATTR );
			}
		} );
	}

	/**
	 * Keep the tags current while the author works: new blocks, a background
	 * picked from the sidebar, a block converted to a Group.
	 *
	 * Only class and style changes are watched, so tagging never re-triggers
	 * itself. The pass runs synchronously in the observer callback: that is a
	 * microtask straight after the editor's DOM commit, so it always lands
	 * before the next paint. Deferring it to requestAnimationFrame let a block
	 * that was just given a light background paint at least one frame of
	 * unreadable text. The observer already batches every mutation of a commit
	 * into one call, and the pass only visits coloured blocks.
	 *
	 * @param {Document} doc Canvas document.
	 */
	function watchInk( doc ) {
		var view = doc.defaultView;

		if ( ! view || ! doc.body || doc.body.dmInkWatched ) {
			return;
		}

		doc.body.dmInkWatched = true;

		new view.MutationObserver( function () {
			markInk( doc );
		} ).observe( doc.body, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: [ 'class', 'style' ],
		} );

		markInk( doc );
	}

	/**
	 * Push the current state into every editor canvas on the page.
	 *
	 * Idempotent on purpose: the block editor remounts its iframe while
	 * loading, and a canvas that remounts after the last sync would otherwise
	 * keep whatever it was born with.
	 *
	 * @param {boolean} dark Whether the canvas should be dark.
	 */
	function syncCanvases( dark ) {
		var frames = document.querySelectorAll( 'iframe[name="editor-canvas"]' );

		Array.prototype.forEach.call( frames, function ( frame ) {
			try {
				var doc = frame.contentDocument;
				var canvas = doc && doc.body;

				if ( ! canvas ) {
					return;
				}

				canvas.classList.toggle( 'dark-mode', dark );
				canvas.classList.toggle( 'dark-mode-off', ! dark );

				// Belt and braces for the one case the server could not
				// pre-paint: a user whose stored preference is light, turning
				// dark inside the editor. Without this the canvas would depend
				// entirely on the class landing before the next frame.
				setDocStyle( doc, CANVAS_STYLE_ID, dark && 'disabled' === initialPref ? cfg.canvasDark : '' );

				watchInk( doc );
			} catch ( e ) {
				// Cross-origin canvas; nothing we can do, and nothing broken.
			}
		} );
	}

	// ---------------------------------------------------------------------
	// Classic editors (TinyMCE)
	// ---------------------------------------------------------------------

	/**
	 * The state TinyMCE documents were last explicitly put in, or null while
	 * the server-rendered content_style is still in charge. That one follows
	 * "auto" through its own media query, so nothing is injected until the
	 * user actually toggles.
	 *
	 * @type {?boolean}
	 */
	var mceState = null;

	/**
	 * Every TinyMCE content document on the page.
	 *
	 * Previously only #content_ifr was handled, so WooCommerce's product short
	 * description, ACF WYSIWYG fields and every other wp_editor() kept the old
	 * state after a toggle. The body check keeps this to real TinyMCE content
	 * documents and away from any other iframe whose id happens to end in _ifr.
	 *
	 * @return {Document[]} Content documents.
	 */
	function mceDocs() {
		var docs = [];

		Array.prototype.forEach.call( document.querySelectorAll( 'iframe[id$="_ifr"]' ), function ( frame ) {
			try {
				var doc = frame.contentDocument;

				if ( doc && doc.body && doc.body.classList.contains( 'mce-content-body' ) ) {
					docs.push( doc );
				}
			} catch ( e ) {}
		} );

		return docs;
	}

	function syncTinyMCE() {
		if ( ! cfg.tinymce ) {
			return;
		}

		mceDocs().forEach( function ( doc ) {
			var css = '';

			if ( null !== mceState ) {
				css = mceState ? cfg.tinymceDark : cfg.tinymceLight;
			}

			setDocStyle( doc, TINYMCE_STYLE_ID, css );
		} );
	}

	/**
	 * Editors initialised after a toggle — an ACF repeater row, a field that
	 * only builds its editor when shown — are brought in line as they appear.
	 * TinyMCE itself loads after this script, so the hook is attached once it
	 * exists.
	 *
	 * @return {boolean} Whether TinyMCE was there to hook into.
	 */
	function watchTinyMCE() {
		if ( ! window.tinymce || ! window.tinymce.on ) {
			return false;
		}

		window.tinymce.on( 'AddEditor', function ( event ) {
			event.editor.on( 'init', syncTinyMCE );
		} );

		return true;
	}

	/**
	 * Watch for canvases appearing or remounting.
	 *
	 * Only ever started on an editor screen, and scoped to the narrowest
	 * container that exists, rather than observing every DOM insertion in the
	 * whole admin for the lifetime of the tab.
	 */
	function watchCanvases() {
		var scheduled = false;

		function schedule() {
			if ( scheduled ) {
				return;
			}

			scheduled = true;
			window.requestAnimationFrame( function () {
				scheduled = false;
				syncCanvases( isDark() );
			} );
		}

		var target = document.querySelector( '.editor-visual-editor' )
			|| document.querySelector( '.interface-interface-skeleton__content' )
			|| document.getElementById( 'editor' )
			|| document.getElementById( 'wpbody-content' )
			|| document.body;

		new MutationObserver( schedule ).observe( target, { childList: true, subtree: true } );

		document.addEventListener( 'load', function ( event ) {
			if ( event.target && 'editor-canvas' === event.target.name ) {
				schedule();
			}
		}, true );

		schedule();

		// Fast while the editor mounts, then a cheap heartbeat: the canvas can
		// still remount an hour into a writing session, and re-applying a class
		// that is already there costs nothing.
		var ticks = 0;
		var timer = window.setInterval( function () {
			schedule();

			if ( ++ticks === 40 ) {
				window.clearInterval( timer );
				window.setInterval( schedule, 2000 );
			}
		}, 150 );
	}

	// ---------------------------------------------------------------------
	// State changes
	// ---------------------------------------------------------------------

	/**
	 * Apply a state to the document without touching the server.
	 *
	 * @param {boolean} dark Target state.
	 */
	function applyState( dark ) {
		body.classList.toggle( 'dark-mode', dark );

		// An explicit click always wins over "auto": drop the marker class so
		// the OS-change listener stops second-guessing the user until reload.
		body.classList.remove( 'dark-mode-auto' );

		paintState();

		if ( cfg.editorCanvas ) {
			syncCanvases( dark );
		}

		mceState = dark;
		syncTinyMCE();
	}

	/**
	 * Serial number of the most recent intent to change state.
	 *
	 * A click that has to fetch stylesheets first applies its state later, in a
	 * promise callback. If the save has failed by then, that callback would
	 * otherwise reinstate the state the rollback just undid — the page ending up
	 * dark while the message says the previous setting was restored. Bumping
	 * this invalidates any apply still in flight.
	 */
	var applyToken = 0;

	/**
	 * Put the document back to whatever the server still has stored.
	 *
	 * 'auto' is a third state, not a synonym for dark. The old rollback asked
	 * ( 'disabled' !== saved ), which is true for 'auto', so a failed save left
	 * an auto user on a light OS looking at a dark admin their preference never
	 * asked for. Restoring auto also puts the marker class back, which is what
	 * lets the OS-change listener resume speaking for them.
	 */
	function restoreSaved() {
		if ( 'auto' === saved ) {
			var media = window.matchMedia && window.matchMedia( '(prefers-color-scheme: dark)' );
			var dark = !! ( media && media.matches );

			body.classList.toggle( 'dark-mode', dark );
			body.classList.add( 'dark-mode-auto' );
			paintState();

			if ( cfg.editorCanvas ) {
				syncCanvases( dark );
			}

			// Back to the server's content_style, which follows the OS itself.
			mceState = null;
			syncTinyMCE();

			return;
		}

		applyState( 'enabled' === saved );
	}

	/**
	 * Persist a preference, putting the UI back if the server refuses it.
	 *
	 * The old handler was fire-and-forget: an expired nonce, a WAF block or a
	 * failed metadata write left the page showing a state that was never
	 * stored, and the next reload silently undid it.
	 *
	 * @param {string} preference One of 'enabled' or 'disabled'.
	 */
	function persist( preference ) {
		if ( inFlight ) {
			queued = preference;
			return;
		}

		inFlight = true;

		var data = new FormData();

		data.append( 'action', 'dark_mode_dashboard_toggle' );
		data.append( 'security', cfg.nonce );
		data.append( 'preference', preference );

		window.fetch( cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: data,
		} ).then( function ( response ) {
			return response.json().catch( function () {
				return { success: false };
			} );
		} ).then( function ( result ) {
			if ( ! result || ! result.success ) {
				throw new Error( 'rejected' );
			}

			saved = preference;
			announce( 'enabled' === preference ? cfg.i18n.nowDark : cfg.i18n.nowLight );
		} ).catch( function () {
			// A newer click is still the user's intended choice and must be sent
			// after this failed request. Only roll back when that choice agrees
			// with the last saved value, or when no newer click is waiting.
			if ( null === queued || queued === saved ) {
				// Invalidate any apply still waiting on loadStyles(), so it cannot
				// reinstate the failed state after the rollback.
				applyToken++;
				restoreSaved();
				announce( cfg.i18n.saveFailed );
			}
		} ).then( function () {
			inFlight = false;

			// Clicks that arrived while the request was open: send the last one
			// so the stored value matches what the user is looking at, instead
			// of letting responses land out of order.
			if ( null !== queued ) {
				var next = queued;

				queued = null;

				if ( next !== saved ) {
					persist( next );
				}
			}
		} );
	}

	toggle.addEventListener( 'click', function ( event ) {
		event.preventDefault();

		var dark = ! isDark();
		var preference = dark ? 'enabled' : 'disabled';
		var token = ++applyToken;

		if ( dark && ! stylesLoaded ) {
			loadStyles().then( function () {
				// A failed save, or a newer click, has moved on without us. The
				// stylesheets are loaded either way — only the state change is
				// abandoned, so a later click still applies instantly.
				if ( token !== applyToken ) {
					return;
				}

				applyState( true );
			} );
		} else {
			applyState( dark );
		}

		persist( preference );
	} );

	// The resolver script flips the body class when the OS scheme changes under
	// an "auto" user; keep the icon and the switch state with it.
	document.addEventListener( 'dark-mode-dashboard-change', function () {
		paintState();

		if ( cfg.editorCanvas ) {
			syncCanvases( isDark() );
		}
	} );

	paintState();

	if ( cfg.editorCanvas ) {
		watchCanvases();
	}

	if ( cfg.tinymce && ! watchTinyMCE() ) {
		window.addEventListener( 'load', watchTinyMCE );
	}
} )();
