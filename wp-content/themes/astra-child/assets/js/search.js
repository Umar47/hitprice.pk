( function () {
	'use strict';

	/* -----------------------------------------------------------------------
	 * Config — values injected via wp_localize_script( 'hitprice-search', 'hpSearchConfig', ... )
	 * --------------------------------------------------------------------- */
	var cfg   = window.hpSearchConfig || {};
	var REST  = ( cfg.restUrl || '/wp-json/hp/v1/search' ).replace( /\/$/, '' );
	var NONCE = cfg.nonce || '';

	var DEBOUNCE      = 160;  // ms debounce on keystrokes
	var MIN_CHARS     = 2;    // minimum chars before firing suggest
	var LOADING_DELAY = 280;  // ms before showing loading indicator (fast responses feel instant)

	/* -----------------------------------------------------------------------
	 * Elements
	 * --------------------------------------------------------------------- */
	var overlay      = document.querySelector( '[data-hp-search-overlay]' );
	if ( ! overlay ) return;

	var triggers     = document.querySelectorAll( '[data-hp-search-trigger]' );
	var closeBtns    = overlay.querySelectorAll( '[data-hp-search-close]' );
	var input        = overlay.querySelector( '[data-hp-search-input]' );
	var clearBtn     = overlay.querySelector( '[data-hp-search-clear]' );
	var trendingSec  = overlay.querySelector( '[data-hp-search-trending]' );
	var trendingList = overlay.querySelector( '[data-hp-search-trending-list]' );
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
	var cache         = new Map(); // cacheKey → response data (LRU, capped at 40)

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

	/* -----------------------------------------------------------------------
	 * Open / Close
	 * --------------------------------------------------------------------- */
	function openOverlay() {
		if ( isOpen ) return;
		isOpen = true;
		// Remove hidden so CSS can take over; add open class to keep display:block
		// during both open AND close transitions (see CSS .hp-search-is-open rule).
		overlay.removeAttribute( 'hidden' );
		overlay.classList.add( 'hp-search-is-open' );
		// Double rAF: first frame switches display to block, second fires transition.
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
		// Trigger CSS close transition (backdrop fades, panel slides down).
		overlay.setAttribute( 'aria-hidden', 'true' );
		document.body.classList.remove( 'hp-search-open' );
		// Wait for transition (220ms max) then fully hide.
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
		hide( resultsSec );
		hide( loadingEl );
		hide( emptyEl );
		hide( errorEl );
		var hasChips = trendingList && trendingList.children.length > 0;
		if ( hasChips ) {
			show( trendingSec );
		} else {
			hide( trendingSec );
		}
	}

	function showLoading() {
		hide( trendingSec );
		hide( resultsSec );
		hide( emptyEl );
		hide( errorEl );
		show( loadingEl );
	}

	function showEmpty() {
		hide( trendingSec );
		hide( resultsSec );
		hide( loadingEl );
		hide( errorEl );
		show( emptyEl );
	}

	function showError() {
		hide( trendingSec );
		hide( resultsSec );
		hide( loadingEl );
		hide( emptyEl );
		show( errorEl );
	}

	function showResults( data ) {
		hide( loadingEl );
		hide( trendingSec );
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

		var html = '';
		for ( var i = 0; i < products.length; i++ ) {
			var p = products[ i ];

			var imgHtml = p.image
				? '<img src="' + escHtml( p.image ) + '" alt="" width="56" height="56" loading="lazy">'
				: '';

			var skuHtml = p.sku
				? '<p class="hp-search-overlay__product-sub">SKU: ' + escHtml( p.sku ) + '</p>'
				: '';

			// p.price is wp_kses()-filtered WooCommerce HTML — safe as innerHTML.
			html +=
				'<li role="option">' +
				'<a href="' + escHtml( p.url ) +
				'" class="hp-search-overlay__product"' +
				' data-hp-product-id="' + parseInt( p.id, 10 ) + '"' +
				' aria-label="' + escHtml( p.title ) + '">' +
				'<span class="hp-search-overlay__product-image">' + imgHtml + '</span>' +
				'<span class="hp-search-overlay__product-meta">' +
				'<p class="hp-search-overlay__product-title">' + escHtml( p.title ) + '</p>' +
				skuHtml +
				'</span>' +
				'<span class="hp-search-overlay__product-price">' + p.price + '</span>' +
				'</a></li>';
		}
		productsList.innerHTML = html;

		// Update "View all results" link.
		if ( viewAllLink && input ) {
			var q = encodeURIComponent( input.value.trim() );
			viewAllLink.href        = '/?s=' + q + '&post_type=product';
			viewAllLink.textContent = 'View all results for “' + input.value.trim() + '”';
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

		// Show loading indicator only after LOADING_DELAY — hides flash for fast responses.
		loadingTimer = setTimeout( showLoading, LOADING_DELAY );
		controller   = new AbortController();

		fetch( REST + '/suggest?q=' + encodeURIComponent( term ), {
			signal:  controller.signal,
			headers: { 'X-WP-Nonce': NONCE },
		} )
		.then( function ( res ) {
			clearTimeout( loadingTimer );
			if ( ! res.ok ) {
				showError();
				return null;
			}
			return res.json();
		} )
		.then( function ( data ) {
			if ( ! data ) return;
			// LRU eviction — keep cache lean.
			if ( cache.size >= 40 ) {
				cache.delete( cache.keys().next().value );
			}
			cache.set( cacheKey, data );
			showResults( data );
		} )
		.catch( function ( err ) {
			clearTimeout( loadingTimer );
			if ( err.name !== 'AbortError' ) showError();
		} );
	}

	function fetchTrending() {
		fetch( REST + '/trending', { headers: { 'X-WP-Nonce': NONCE } } )
		.then( function ( res ) {
			return res.ok ? res.json() : null;
		} )
		.then( function ( data ) {
			if ( ! data || ! data.terms || ! data.terms.length ) return;
			renderTrendingChips( data.terms );
			// If overlay is already open and idle, refresh display.
			if ( isOpen && input && input.value.trim().length < MIN_CHARS ) {
				showIdle();
			}
		} )
		.catch( function () {} );
	}

	// Fire-and-forget click tracking via sendBeacon (won't delay navigation).
	function trackClick( productId ) {
		var id = parseInt( productId, 10 );
		if ( ! currentLogId || ! id ) return;

		var payload = JSON.stringify( { log_id: currentLogId, product_id: id } );

		if ( navigator.sendBeacon ) {
			navigator.sendBeacon(
				REST + '/click',
				new Blob( [ payload ], { type: 'application/json' } )
			);
		} else {
			fetch( REST + '/click', {
				method:    'POST',
				headers:   { 'Content-Type': 'application/json', 'X-WP-Nonce': NONCE },
				body:      payload,
				keepalive: true,
			} ).catch( function () {} );
		}
	}

	/* -----------------------------------------------------------------------
	 * Events
	 * --------------------------------------------------------------------- */

	// Open on any [data-hp-search-trigger] click (header input field or search icon).
	for ( var ti = 0; ti < triggers.length; ti++ ) {
		( function ( el ) {
			el.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				openOverlay();
			} );
		} )( triggers[ ti ] );
	}

	// Close buttons (backdrop, back arrow, cancel).
	for ( var ci = 0; ci < closeBtns.length; ci++ ) {
		closeBtns[ ci ].addEventListener( 'click', closeOverlay );
	}

	// Escape key.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && isOpen ) closeOverlay();
	} );

	// Input: debounce + toggle clear button + aria-expanded.
	if ( input ) {
		input.addEventListener( 'input', function () {
			var val = input.value.trim();
			if ( val.length > 0 ) {
				show( clearBtn );
			} else {
				hide( clearBtn );
			}
			input.setAttribute( 'aria-expanded', val.length >= MIN_CHARS ? 'true' : 'false' );
			clearTimeout( debounceTimer );
			debounceTimer = setTimeout( function () {
				fetchSuggestions( val );
			}, DEBOUNCE );
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

	// Delegated: chip clicks + product click tracking.
	overlay.addEventListener( 'click', function ( e ) {
		// Trending / suggestion chip — fill input and search.
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

		// Product link — track click before navigation.
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

	// Prefetch trending on first hover/focus of the header search trigger —
	// so the overlay opens with fresh data instantly.
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
