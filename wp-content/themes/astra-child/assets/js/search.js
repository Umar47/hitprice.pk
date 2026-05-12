( function () {
	'use strict';

	/* -----------------------------------------------------------------------
	 * Config — values injected via wp_localize_script
	 * --------------------------------------------------------------------- */
	var cfg   = window.hpSearchConfig || {};
	var REST  = ( cfg.restUrl || '/wp-json/hp/v1/search' ).replace( /\/$/, '' );
	var NONCE = cfg.nonce || '';

	var DEBOUNCE      = 250;  // ms — balanced: fast enough, not too aggressive
	var MIN_CHARS     = 2;
	var LOADING_DELAY = 280;  // ms before spinner appears (masks fast responses)
	var RECENT_KEY    = 'hp_recent_searches';
	var RECENT_MAX    = 5;

	/* -----------------------------------------------------------------------
	 * Elements
	 * --------------------------------------------------------------------- */
	var overlay      = document.querySelector( '[data-hp-search-overlay]' );
	if ( ! overlay ) return;

	var triggers     = document.querySelectorAll( '[data-hp-search-trigger]' );
	var closeBtns    = overlay.querySelectorAll( '[data-hp-search-close]' );
	var overlayForm  = overlay.querySelector( '[data-hp-search-overlay-form]' );
	var input        = overlay.querySelector( '[data-hp-search-input]' );
	var clearBtn     = overlay.querySelector( '[data-hp-search-clear]' );
	var trendingSec  = overlay.querySelector( '[data-hp-search-trending]' );
	var trendingList = overlay.querySelector( '[data-hp-search-trending-list]' );
	var recentSec    = overlay.querySelector( '[data-hp-search-recent]' );
	var recentList   = overlay.querySelector( '[data-hp-search-recent-list]' );
	var featuredSec  = overlay.querySelector( '[data-hp-search-featured]' );
	var resultsSec   = overlay.querySelector( '[data-hp-search-results]' );
	var termsSec     = overlay.querySelector( '[data-hp-search-terms]' );
	var termsList    = overlay.querySelector( '[data-hp-search-terms-list]' );
	var productsSec  = overlay.querySelector( '[data-hp-search-products]' );
	var productsList = overlay.querySelector( '[data-hp-search-products-list]' );
	var viewAllLink  = overlay.querySelector( '[data-hp-search-view-all]' );
	var loadingEl    = overlay.querySelector( '[data-hp-search-loading]' );
	var emptyEl      = overlay.querySelector( '[data-hp-search-empty]' );
	var errorEl      = overlay.querySelector( '[data-hp-search-error]' );

	/* -----------------------------------------------------------------------
	 * State
	 * --------------------------------------------------------------------- */
	var isOpen        = false;
	var currentLogId  = 0;
	var controller    = null;
	var debounceTimer = null;
	var loadingTimer  = null;
	var cache         = new Map(); // LRU capped at 40

	/* -----------------------------------------------------------------------
	 * Helpers
	 * --------------------------------------------------------------------- */
	function show( el ) {
		if ( el ) el.removeAttribute( 'hidden' );
	}

	function hide( el ) {
		if ( el ) el.setAttribute( 'hidden', '' );
	}

	function escHtml( str ) {
		var d = document.createElement( 'div' );
		d.textContent = String( str );
		return d.innerHTML;
	}

	/**
	 * Wrap occurrences of `term` inside `text` with <mark> for highlighting.
	 * Case-insensitive, safe — text is already escaped before calling.
	 */
	function highlightTerm( escapedText, rawTerm ) {
		if ( ! rawTerm ) return escapedText;
		// Escape special regex chars in the search term.
		var safe = rawTerm.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
		// Also build alternatives from each word so multi-word queries highlight each word.
		var words  = safe.split( /\s+/ ).filter( Boolean );
		var pattern = words.length > 1 ? words.join( '|' ) : safe;
		try {
			var re = new RegExp( '(' + pattern + ')', 'gi' );
			return escapedText.replace( re, '<mark class="hp-search-highlight">$1</mark>' );
		} catch ( e ) {
			return escapedText;
		}
	}

	/* -----------------------------------------------------------------------
	 * Recent searches (localStorage)
	 * --------------------------------------------------------------------- */
	function getRecentSearches() {
		try {
			return JSON.parse( localStorage.getItem( RECENT_KEY ) || '[]' );
		} catch ( e ) {
			return [];
		}
	}

	function saveRecentSearch( term ) {
		term = term.trim();
		if ( term.length < MIN_CHARS ) return;
		var list = getRecentSearches().filter( function ( t ) {
			return t.toLowerCase() !== term.toLowerCase();
		} );
		list.unshift( term );
		list = list.slice( 0, RECENT_MAX );
		try {
			localStorage.setItem( RECENT_KEY, JSON.stringify( list ) );
		} catch ( e ) {}
	}

	function clearRecentSearches() {
		try {
			localStorage.removeItem( RECENT_KEY );
		} catch ( e ) {}
	}

	function renderRecentSearches() {
		if ( ! recentSec || ! recentList ) return;
		var list = getRecentSearches();
		if ( ! list.length ) {
			hide( recentSec );
			return;
		}
		var html = '';
		for ( var i = 0; i < list.length; i++ ) {
			html +=
				'<li>' +
				'<button type="button" class="hp-search-overlay__chip hp-search-overlay__chip--recent" data-hp-search-term="' +
				escHtml( list[ i ] ) + '">' +
				'<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.36"/></svg>' +
				escHtml( list[ i ] ) + '</button></li>';
		}
		recentList.innerHTML = html;
		show( recentSec );
	}

	/* -----------------------------------------------------------------------
	 * Open / Close
	 * --------------------------------------------------------------------- */
	function openOverlay() {
		if ( isOpen ) return;
		isOpen = true;
		overlay.removeAttribute( 'hidden' );
		overlay.classList.add( 'hp-search-is-open' );
		requestAnimationFrame( function () {
			overlay.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'hp-search-open' );
			requestAnimationFrame( function () {
				if ( input ) input.focus();
			} );
		} );
		fetchTrending();
	}

	function closeOverlay() {
		if ( ! isOpen ) return;
		isOpen = false;
		overlay.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'hp-search-open' );
		setTimeout( function () {
			overlay.classList.remove( 'hp-search-is-open' );
			overlay.setAttribute( 'hidden', '' );
		}, 260 );
		cancelInflight();
		resetInput();
		showIdle();
	}

	function resetInput() {
		if ( ! input ) return;
		input.value = '';
		input.setAttribute( 'aria-expanded', 'false' );
		hide( clearBtn );
	}

	/* -----------------------------------------------------------------------
	 * View states
	 * --------------------------------------------------------------------- */
	function showIdle() {
		// Hide search-specific sections.
		hide( resultsSec );
		hide( loadingEl );
		hide( emptyEl );
		hide( errorEl );
		// Show idle-state sections.
		show( featuredSec );
		renderRecentSearches();
		var hasChips = trendingList && trendingList.children.length > 0;
		if ( hasChips ) {
			show( trendingSec );
		} else {
			hide( trendingSec );
		}
	}

	function showLoading() {
		hide( trendingSec );
		hide( recentSec );
		hide( featuredSec );
		hide( resultsSec );
		hide( emptyEl );
		hide( errorEl );
		show( loadingEl );
	}

	function showEmpty() {
		hide( trendingSec );
		hide( recentSec );
		hide( featuredSec );
		hide( resultsSec );
		hide( loadingEl );
		hide( errorEl );
		show( emptyEl );
	}

	function showError() {
		hide( trendingSec );
		hide( recentSec );
		hide( featuredSec );
		hide( resultsSec );
		hide( loadingEl );
		hide( emptyEl );
		show( errorEl );
	}

	function showResults( data ) {
		hide( loadingEl );
		hide( trendingSec );
		hide( recentSec );
		hide( featuredSec );
		hide( emptyEl );
		hide( errorEl );

		var hasProducts = data.products && data.products.length;
		var hasTerms    = data.terms    && data.terms.length;

		if ( ! hasProducts && ! hasTerms ) {
			showEmpty();
			return;
		}

		if ( hasTerms ) {
			renderTerms( data.terms );
			show( termsSec );
		} else {
			hide( termsSec );
		}

		if ( hasProducts ) {
			renderProducts( data.products, data.log_id );
			show( productsSec );
		} else {
			hide( productsSec );
		}

		show( resultsSec );
	}

	/* -----------------------------------------------------------------------
	 * Renderers
	 * --------------------------------------------------------------------- */
	function renderTerms( terms ) {
		if ( ! termsList ) return;
		var html = '';
		for ( var i = 0; i < terms.length; i++ ) {
			html +=
				'<li><button type="button" class="hp-search-overlay__chip" data-hp-search-term="' +
				escHtml( terms[ i ] ) + '">' + escHtml( terms[ i ] ) + '</button></li>';
		}
		termsList.innerHTML = html;
	}

	function renderProducts( products, logId ) {
		if ( ! productsList ) return;
		currentLogId = logId || 0;

		var currentTerm = input ? input.value.trim() : '';
		var html = '';

		for ( var i = 0; i < products.length; i++ ) {
			var p = products[ i ];

			var imgHtml = p.image
				? '<img src="' + escHtml( p.image ) + '" alt="" width="64" height="64" loading="lazy" decoding="async">'
				: '<span class="hp-search-overlay__product-image-placeholder" aria-hidden="true"></span>';

			// Highlight matched term in title.
			var titleEscaped = escHtml( p.title );
			var titleHtml    = highlightTerm( titleEscaped, currentTerm );

			// Category label.
			var categoryHtml = p.category
				? '<span class="hp-search-overlay__product-cat">' + escHtml( p.category ) + '</span>'
				: '';

			// Stock badge.
			var stockHtml = ( p.in_stock === false )
				? '<span class="hp-search-overlay__stock hp-search-overlay__stock--out">' +
				  'Out of stock</span>'
				: '';

			// p.price is wp_kses()-filtered WC HTML — safe as innerHTML.
			html +=
				'<li role="option">' +
				'<a href="' + escHtml( p.url ) +
				'" class="hp-search-overlay__product"' +
				' data-hp-product-id="' + parseInt( p.id, 10 ) + '"' +
				' aria-label="' + escHtml( p.title ) + '">' +
				'<span class="hp-search-overlay__product-image">' + imgHtml + '</span>' +
				'<span class="hp-search-overlay__product-meta">' +
				'<span class="hp-search-overlay__product-top">' + categoryHtml + stockHtml + '</span>' +
				'<p class="hp-search-overlay__product-title">' + titleHtml + '</p>' +
				'</span>' +
				'<span class="hp-search-overlay__product-price">' + p.price + '</span>' +
				'</a></li>';
		}
		productsList.innerHTML = html;

		// Update "View all results" link.
		if ( viewAllLink && input ) {
			var q = encodeURIComponent( currentTerm );
			viewAllLink.href        = '/?s=' + q + '&post_type=product';
			viewAllLink.textContent = 'View all results for "' + currentTerm + '"';
		}
	}

	function renderTrendingChips( terms ) {
		if ( ! trendingList || ! terms.length ) return;
		var html = '';
		for ( var i = 0; i < terms.length; i++ ) {
			html +=
				'<li><button type="button" class="hp-search-overlay__chip" data-hp-search-term="' +
				escHtml( terms[ i ] ) + '">' + escHtml( terms[ i ] ) + '</button></li>';
		}
		trendingList.innerHTML = html;
		show( trendingSec );
	}

	/* -----------------------------------------------------------------------
	 * API
	 * --------------------------------------------------------------------- */
	function cancelInflight() {
		if ( controller ) {
			controller.abort();
			controller = null;
		}
		clearTimeout( debounceTimer );
		clearTimeout( loadingTimer );
	}

	function fetchSuggestions( term ) {
		cancelInflight();

		if ( term.length < MIN_CHARS ) {
			showIdle();
			return;
		}

		var cacheKey = term.toLowerCase();
		if ( cache.has( cacheKey ) ) {
			showResults( cache.get( cacheKey ) );
			return;
		}

		loadingTimer = setTimeout( showLoading, LOADING_DELAY );
		controller   = new AbortController();

		fetch( REST + '/suggest?q=' + encodeURIComponent( term ), {
			signal:  controller.signal,
			headers: { 'X-WP-Nonce': NONCE },
		} )
		.then( function ( res ) {
			clearTimeout( loadingTimer );
			if ( ! res.ok ) { showError(); return null; }
			return res.json();
		} )
		.then( function ( data ) {
			if ( ! data ) return;
			if ( cache.size >= 40 ) cache.delete( cache.keys().next().value );
			cache.set( cacheKey, data );
			showResults( data );
			// Save to recent searches on success.
			if ( data.products && data.products.length ) {
				saveRecentSearch( term );
			}
		} )
		.catch( function ( err ) {
			clearTimeout( loadingTimer );
			if ( err.name !== 'AbortError' ) showError();
		} );
	}

	function fetchTrending() {
		fetch( REST + '/trending', { headers: { 'X-WP-Nonce': NONCE } } )
		.then( function ( res ) { return res.ok ? res.json() : null; } )
		.then( function ( data ) {
			if ( ! data || ! data.terms || ! data.terms.length ) return;
			renderTrendingChips( data.terms );
			if ( isOpen && input && input.value.trim().length < MIN_CHARS ) showIdle();
		} )
		.catch( function () {} );
	}

	function trackClick( productId ) {
		var id = parseInt( productId, 10 );
		if ( ! currentLogId || ! id ) return;
		var payload = JSON.stringify( { log_id: currentLogId, product_id: id } );
		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( REST + '/click', new Blob( [ payload ], { type: 'application/json' } ) );
		} else {
			fetch( REST + '/click', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
				body: payload,
				keepalive: true,
			} ).catch( function () {} );
		}
	}

	/* -----------------------------------------------------------------------
	 * Events
	 * --------------------------------------------------------------------- */

	// Open on click or focus of any [data-hp-search-trigger] element.
	for ( var ti = 0; ti < triggers.length; ti++ ) {
		( function ( el ) {
			el.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				openOverlay();
			} );
			// Keyboard users focusing the header input should also open the overlay.
			if ( el.tagName === 'INPUT' ) {
				el.addEventListener( 'focus', function () {
					openOverlay();
				} );
			}
		} )( triggers[ ti ] );
	}

	// Close buttons (backdrop, back arrow, cancel).
	for ( var ci = 0; ci < closeBtns.length; ci++ ) {
		closeBtns[ ci ].addEventListener( 'click', closeOverlay );
	}

	// ESC closes overlay.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && isOpen ) closeOverlay();
	} );

	// Input: debounce + clear button + aria-expanded.
	if ( input ) {
		input.addEventListener( 'input', function () {
			var val = input.value.trim();
			if ( val.length > 0 ) { show( clearBtn ); } else { hide( clearBtn ); }
			input.setAttribute( 'aria-expanded', val.length >= MIN_CHARS ? 'true' : 'false' );
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( function () {
				fetchSuggestions( val );
			}, DEBOUNCE );
		} );

		// Enter key: submit the overlay form (belt-and-suspenders alongside native submit).
		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key !== 'Enter' ) return;
			var val = input.value.trim();
			if ( val.length >= MIN_CHARS ) {
				saveRecentSearch( val );
			}
			// Let the native form submit happen — it redirects to /?s=TERM&post_type=product.
			// No preventDefault here intentionally.
		} );
	}

	// Clear button.
	if ( clearBtn ) {
		clearBtn.addEventListener( 'click', function () {
			if ( input ) {
				input.value = '';
				input.setAttribute( 'aria-expanded', 'false' );
				input.focus();
			}
			hide( clearBtn );
			cancelInflight();
			showIdle();
		} );
	}

	// Overlay form submit (Enter in overlay input, or explicit submit).
	// Ensure the "view all" redirect includes post_type=product.
	if ( overlayForm ) {
		overlayForm.addEventListener( 'submit', function () {
			var val = input ? input.value.trim() : '';
			if ( val.length >= MIN_CHARS ) saveRecentSearch( val );
			// Let the browser navigate — form has method="get" action="/" with
			// name="s" and hidden post_type=product inputs.
		} );
	}

	// Delegated: chip clicks + clear recent + product click tracking.
	overlay.addEventListener( 'click', function ( e ) {
		// Clear recent searches button.
		if ( e.target.closest( '[data-hp-search-clear-recent]' ) ) {
			clearRecentSearches();
			hide( recentSec );
			return;
		}

		var chip = e.target.closest( '[data-hp-search-term]' );
		if ( chip ) {
			var term = chip.dataset.hpSearchTerm;
			if ( input ) {
				input.value = term;
				show( clearBtn );
				input.setAttribute( 'aria-expanded', 'true' );
			}
			fetchSuggestions( term );
			return;
		}

		var product = e.target.closest( '[data-hp-product-id]' );
		if ( product ) {
			trackClick( product.dataset.hpProductId );
		}
	} );

	// Arrow-key navigation through product results.
	overlay.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'ArrowDown' && e.key !== 'ArrowUp' ) return;
		var items = Array.prototype.slice.call(
			productsList ? productsList.querySelectorAll( '[data-hp-product-id]' ) : []
		);
		if ( ! items.length ) return;
		e.preventDefault();
		var current = overlay.querySelector( '[data-hp-product-id][aria-selected="true"]' );
		var idx     = current ? items.indexOf( current ) : -1;
		var next    = e.key === 'ArrowDown'
			? items[ ( idx + 1 ) % items.length ]
			: items[ ( idx - 1 + items.length ) % items.length ];
		if ( current ) current.removeAttribute( 'aria-selected' );
		next.setAttribute( 'aria-selected', 'true' );
		next.focus();
	} );

	// Prefetch trending on first hover/focus of the header trigger.
	var prefetched = false;
	function maybePrefetch() {
		if ( prefetched ) return;
		prefetched = true;
		fetchTrending();
	}
	for ( var pi = 0; pi < triggers.length; pi++ ) {
		triggers[ pi ].addEventListener( 'mouseenter', maybePrefetch );
		triggers[ pi ].addEventListener( 'focus', maybePrefetch );
	}

}() );
