/**
 * Single Product Page — Custom Gallery, Variation Swatches, Sticky Bar
 *
 * @package HitPrice
 */
(function () {
	'use strict';

	/* ====================================================================
	 * CUSTOM GALLERY — Sliding track with transition
	 * ==================================================================== */
	function initGallery() {
		var wooGallery = document.querySelector('.hp-product-layout .woocommerce-product-gallery');
		if (!wooGallery) return;

		var wooImages = wooGallery.querySelectorAll('.woocommerce-product-gallery__image');
		if (!wooImages.length) return;

		// Build image data from WooCommerce DOM.
		var galleryImages = [];
		wooImages.forEach(function (slide) {
			var img = slide.querySelector('img');
			var link = slide.querySelector('a');
			if (!img) return;
			galleryImages.push({
				src: img.getAttribute('src') || '',
				srcset: img.getAttribute('srcset') || '',
				sizes: img.getAttribute('sizes') || '',
				full: link ? link.getAttribute('href') : (img.getAttribute('data-large_image') || img.getAttribute('src') || ''),
				alt: img.getAttribute('alt') || '',
				title: img.getAttribute('title') || ''
			});
		});

		if (!galleryImages.length) return;

		var originalImages = galleryImages.slice();
		var activeIndex = 0;
		var isAnimating = false;

		// ---- Build gallery DOM ----
		var galleryEl = document.createElement('div');
		galleryEl.className = 'hp-gallery';

		// Viewport clips the track.
		var viewport = document.createElement('div');
		viewport.className = 'hp-gallery__viewport';

		// Track holds all slides side-by-side, shifted via translateX.
		var track = document.createElement('div');
		track.className = 'hp-gallery__track';
		track.style.width = (galleryImages.length * 100) + '%';

		var slideEls = [];
		galleryImages.forEach(function (imgData) {
			var slide = document.createElement('div');
			slide.className = 'hp-gallery__slide';
			slide.style.width = (100 / galleryImages.length) + '%';

			var img = document.createElement('img');
			img.className = 'hp-gallery__img';
			setImgSrc(img, imgData);
			img.setAttribute('alt', imgData.alt);
			img.setAttribute('draggable', 'false');

			slide.appendChild(img);
			track.appendChild(slide);
			slideEls.push({ el: slide, img: img });
		});

		viewport.appendChild(track);
		galleryEl.appendChild(viewport);

		// Arrows.
		if (galleryImages.length > 1) {
			var prevBtn = createArrow('prev', 'Previous image', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>');
			var nextBtn = createArrow('next', 'Next image', '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>');

			prevBtn.addEventListener('click', function () { slideTo(activeIndex - 1, 'right'); });
			nextBtn.addEventListener('click', function () { slideTo(activeIndex + 1, 'left'); });

			viewport.appendChild(prevBtn);
			viewport.appendChild(nextBtn);
		}

		// Thumbnails.
		var thumbsWrap = null;
		if (galleryImages.length > 1) {
			thumbsWrap = document.createElement('div');
			thumbsWrap.className = 'hp-gallery__thumbs';

			galleryImages.forEach(function (imgData, i) {
				var thumb = document.createElement('button');
				thumb.type = 'button';
				thumb.className = 'hp-gallery__thumb' + (i === 0 ? ' is-active' : '');
				thumb.setAttribute('aria-label', 'View image ' + (i + 1));

				var thumbImg = document.createElement('img');
				thumbImg.setAttribute('src', imgData.src);
				thumbImg.setAttribute('alt', imgData.alt);
				thumb.appendChild(thumbImg);

				thumb.addEventListener('click', function () {
					var dir = i > activeIndex ? 'left' : 'right';
					slideTo(i, dir);
				});
				thumbsWrap.appendChild(thumb);
			});

			galleryEl.appendChild(thumbsWrap);
		}

		// Replace WooCommerce gallery innards.
		wooGallery.innerHTML = '';
		wooGallery.appendChild(galleryEl);
		wooGallery.classList.add('hp-gallery-active');

		// Position track at first slide.
		positionTrack(0, false);

		// ---- Touch / swipe support ----
		var touchStartX = 0;
		var touchDeltaX = 0;
		var isSwiping = false;

		viewport.addEventListener('touchstart', function (e) {
			if (isAnimating || galleryImages.length <= 1) return;
			touchStartX = e.touches[0].clientX;
			touchDeltaX = 0;
			isSwiping = true;
			track.style.transition = 'none';
		}, { passive: true });

		viewport.addEventListener('touchmove', function (e) {
			if (!isSwiping) return;
			touchDeltaX = e.touches[0].clientX - touchStartX;
			var baseOffset = -(activeIndex * (100 / galleryImages.length));
			var dragPercent = (touchDeltaX / viewport.offsetWidth) * (100 / galleryImages.length);
			track.style.transform = 'translateX(' + (baseOffset + dragPercent) + '%)';
		}, { passive: true });

		viewport.addEventListener('touchend', function () {
			if (!isSwiping) return;
			isSwiping = false;
			track.style.transition = '';

			var threshold = viewport.offsetWidth * 0.15;
			if (touchDeltaX < -threshold && activeIndex < galleryImages.length - 1) {
				slideTo(activeIndex + 1, 'left');
			} else if (touchDeltaX > threshold && activeIndex > 0) {
				slideTo(activeIndex - 1, 'right');
			} else {
				positionTrack(activeIndex, true);
			}
		});

		// ---- Slide navigation ----
		function slideTo(index, direction) {
			if (isAnimating) return;
			if (index < 0) index = galleryImages.length - 1;
			if (index >= galleryImages.length) index = 0;
			if (index === activeIndex) return;

			isAnimating = true;
			activeIndex = index;
			positionTrack(index, true);
			updateThumbs(index);

			track.addEventListener('transitionend', function handler() {
				isAnimating = false;
				track.removeEventListener('transitionend', handler);
			});

			// Safety fallback in case transitionend doesn't fire.
			setTimeout(function () { isAnimating = false; }, 400);
		}

		function positionTrack(index, animate) {
			var offset = -(index * (100 / galleryImages.length));
			if (!animate) {
				track.style.transition = 'none';
				track.style.transform = 'translateX(' + offset + '%)';
				// Force reflow then restore transition.
				track.offsetHeight; // eslint-disable-line no-unused-expressions
				track.style.transition = '';
			} else {
				track.style.transform = 'translateX(' + offset + '%)';
			}
		}

		function updateThumbs(index) {
			if (!thumbsWrap) return;
			var thumbs = thumbsWrap.querySelectorAll('.hp-gallery__thumb');
			thumbs.forEach(function (t, i) {
				t.classList.toggle('is-active', i === index);
			});
			if (thumbs[index]) {
				thumbs[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
			}
		}

		// ---- Variation image swap ----
		if (typeof jQuery !== 'undefined') {
			var $form = jQuery('.variations_form');
			if ($form.length) {
				$form.on('found_variation', function (event, variation) {
					if (!variation || !variation.image || !variation.image.src || variation.image.src === '') return;

					var vi = variation.image;
					var currentSrc = slideEls[0].img.getAttribute('src');
					if (currentSrc === vi.src) return;

					// Update first slide to variation image.
					var newData = {
						src: vi.src,
						srcset: vi.srcset || '',
						sizes: vi.sizes || '',
						full: vi.full_src || vi.src,
						alt: vi.alt || '',
						title: vi.title || ''
					};
					setImgSrc(slideEls[0].img, newData);
					slideEls[0].img.setAttribute('alt', newData.alt);
					galleryImages[0] = newData;

					// Update first thumbnail.
					if (thumbsWrap) {
						var firstThumbImg = thumbsWrap.querySelector('.hp-gallery__thumb:first-child img');
						if (firstThumbImg) {
							firstThumbImg.setAttribute('src', vi.gallery_thumbnail_src || vi.thumb_src || vi.src);
						}
					}

					// Slide to first image.
					if (activeIndex !== 0) {
						slideTo(0, 'right');
					}
				});

				$form.on('reset_data', function () {
					galleryImages = originalImages.slice();

					// Restore all slide images.
					galleryImages.forEach(function (imgData, i) {
						if (slideEls[i]) {
							setImgSrc(slideEls[i].img, imgData);
							slideEls[i].img.setAttribute('alt', imgData.alt);
						}
					});

					// Restore all thumbnails.
					if (thumbsWrap) {
						var thumbImgs = thumbsWrap.querySelectorAll('.hp-gallery__thumb img');
						galleryImages.forEach(function (imgData, i) {
							if (thumbImgs[i]) {
								thumbImgs[i].setAttribute('src', imgData.src);
							}
						});
					}

					if (activeIndex !== 0) {
						slideTo(0, 'right');
					} else {
						positionTrack(0, false);
					}
					updateThumbs(0);
				});
			}
		}
	}

	function createArrow(dir, label, svg) {
		var btn = document.createElement('button');
		btn.type = 'button';
		btn.className = 'hp-gallery__arrow hp-gallery__arrow--' + dir;
		btn.setAttribute('aria-label', label);
		btn.innerHTML = svg;
		return btn;
	}

	function setImgSrc(img, data) {
		img.setAttribute('src', data.src);
		if (data.srcset) { img.setAttribute('srcset', data.srcset); } else { img.removeAttribute('srcset'); }
		if (data.sizes) { img.setAttribute('sizes', data.sizes); } else { img.removeAttribute('sizes'); }
		if (data.full) { img.setAttribute('data-large_image', data.full); }
	}


	/* ====================================================================
	 * VARIATION SWATCH OVERLAY
	 * ==================================================================== */
	function initSwatches() {
		var form = document.querySelector('.variations_form');
		if (!form) return;

		// Build per-attribute variation image maps from WooCommerce variation JSON.
		var variationImages = buildVariationImageMap(form);

		var rows = form.querySelectorAll('.variations tr');
		if (!rows.length) return;

		rows.forEach(function (row) {
			var select = row.querySelector('select');
			if (!select) return;

			var options = select.querySelectorAll('option');
			if (options.length <= 1) return;

			var attrName = select.getAttribute('data-attribute_name') || select.getAttribute('name') || '';
			var isColorAttr = /color/i.test(attrName);
			var imgMap = isColorAttr ? (variationImages[attrName] || {}) : {};

			var container = document.createElement('div');
			container.className = 'hp-swatches';

			options.forEach(function (option) {
				if (!option.value) return;

				var text = option.textContent.trim();
				var imgSrc = imgMap[option.value.toLowerCase().trim()] || '';

				var swatch = document.createElement('button');
				swatch.type = 'button';
				swatch.setAttribute('data-value', option.value);
				swatch.setAttribute('aria-label', text);
				swatch.setAttribute('title', text);

				if (imgSrc) {
					// Image-based swatch.
					swatch.className = 'hp-swatch hp-swatch--image';
					var img = document.createElement('img');
					img.src = imgSrc;
					img.alt = text;
					img.width = 56;
					img.height = 56;
					img.loading = 'lazy';
					var label = document.createElement('span');
					label.className = 'hp-swatch-label';
					label.textContent = text;
					swatch.appendChild(img);
					swatch.appendChild(label);
				} else {
					// Text swatch fallback (e.g. Storage/RAM).
					swatch.className = 'hp-swatch';
					swatch.textContent = text;
				}

				if (select.value === option.value) {
					swatch.classList.add('is-active');
				}

				swatch.addEventListener('click', function () {
					select.value = option.value;
					if (typeof jQuery !== 'undefined') {
						jQuery(select).trigger('change');
					} else {
						select.dispatchEvent(new Event('change', { bubbles: true }));
					}
					updateActiveSwatches(container, option.value);
				});

				container.appendChild(swatch);
			});

			var selectCell = row.querySelector('td.value');
			if (selectCell) {
				selectCell.insertBefore(container, selectCell.firstChild);
			}
		});

		form.classList.add('hp-has-swatches');

		if (typeof jQuery !== 'undefined') {
			jQuery(form).on('reset_data', function () {
				form.querySelectorAll('.hp-swatch.is-active').forEach(function (s) {
					s.classList.remove('is-active');
				});
			});
		}
	}

	function updateActiveSwatches(container, value) {
		container.querySelectorAll('.hp-swatch').forEach(function (s) {
			s.classList.toggle('is-active', s.getAttribute('data-value') === value);
		});
	}

	/**
	 * Build per-attribute variation image maps from WooCommerce variation JSON.
	 * Maps { attribute_name: { attribute_value_lowercase: thumb_src } }.
	 *
	 * Uses the first thumb found per value; if a value maps to different images
	 * across variations it keeps the first (WooCommerce picks first match too).
	 *
	 * @param {Element} form The .variations_form element.
	 * @return {Object}
	 */
	function buildVariationImageMap(form) {
		var raw = form.getAttribute('data-product_variations');
		var variations;
		try { variations = JSON.parse(raw); } catch (e) { return {}; }
		if (!variations || !Array.isArray(variations)) return {};

		var maps = {};

		variations.forEach(function (v) {
			var src = v.image && (v.image.thumb_src || v.image.src);
			if (!src) return;
			var attrs = v.attributes || {};

			Object.keys(attrs).forEach(function (attrName) {
				var value = (attrs[attrName] || '').toLowerCase().trim();
				if (!value) return;
				if (!maps[attrName]) maps[attrName] = {};
				// First image wins per value.
				if (!maps[attrName][value]) {
					maps[attrName][value] = src;
				}
			});
		});

		return maps;
	}



	/* ====================================================================
	 * STICKY BAR
	 * ==================================================================== */
	function initStickyBar() {
		var bar = document.getElementById('hp-sticky-bar');
		if (!bar) return;

		var cartBtn = document.querySelector('.hp-product-layout .single_add_to_cart_button') || document.querySelector('.hp-product-layout .cart');
		if (!cartBtn) return;

		var priceEl = document.getElementById('hp-sticky-bar-price');
		var shown = false;

		function checkScroll() {
			var rect = cartBtn.getBoundingClientRect();
			var shouldShow = rect.bottom < 0;
			if (shouldShow !== shown) {
				shown = shouldShow;
				bar.classList.toggle('hp-sticky-bar--hidden', !shown);
				bar.setAttribute('aria-hidden', String(!shown));
			}
		}

		window.addEventListener('scroll', checkScroll, { passive: true });
		checkScroll();

		if (typeof jQuery === 'undefined') return;

		var $form = jQuery('.variations_form');
		if (!$form.length || !priceEl) return;

		$form.on('found_variation', function (event, variation) {
			if (variation && variation.price_html) {
				priceEl.innerHTML = variation.price_html;
			}
			if (variation && !variation.is_in_stock) {
				var cta = bar.querySelector('.hp-sticky-bar__cta');
				if (cta) cta.style.display = 'none';
				var oos = bar.querySelector('.hp-sticky-bar__out-of-stock');
				if (!oos) {
					oos = document.createElement('span');
					oos.className = 'hp-sticky-bar__out-of-stock';
					oos.textContent = 'Out of stock';
					bar.querySelector('.hp-sticky-bar__actions').appendChild(oos);
				}
				oos.style.display = '';
			} else {
				var cta2 = bar.querySelector('.hp-sticky-bar__cta');
				if (cta2) cta2.style.display = '';
				var oos2 = bar.querySelector('.hp-sticky-bar__out-of-stock');
				if (oos2) oos2.style.display = 'none';
			}
		});

		$form.on('reset_data', function () {
			var product = document.querySelector('.product');
			var origPrice = product ? product.querySelector('.summary .price') : null;
			if (origPrice && priceEl) {
				priceEl.innerHTML = origPrice.innerHTML;
			}
			var cta = bar.querySelector('.hp-sticky-bar__cta');
			if (cta) cta.style.display = '';
			var oos = bar.querySelector('.hp-sticky-bar__out-of-stock');
			if (oos) oos.style.display = 'none';
		});
	}


	/* ====================================================================
	 * VIEWERS COUNT — seeded pseudo-random, consistent per product per day
	 * ==================================================================== */
	function initViewers() {
		var nodes = document.querySelectorAll( '.hp-stock-viewers[data-product-id]' );
		if ( ! nodes.length ) return;

		var first     = nodes[0];
		var productId = parseInt( first.getAttribute( 'data-product-id' ), 10 ) || 1;
		var min       = parseInt( first.getAttribute( 'data-viewers-min' ), 10 ) || 12;
		var max       = parseInt( first.getAttribute( 'data-viewers-max' ), 10 ) || 48;

		// Seed: product ID + current date parts (changes daily, consistent within day).
		var d    = new Date();
		var seed = productId * 1000 + d.getFullYear() * 400 + ( d.getMonth() + 1 ) * 31 + d.getDate();

		// Simple deterministic hash.
		seed = ( ( seed ^ ( seed >>> 16 ) ) * 0x45d9f3b ) & 0xffffffff;
		seed = ( ( seed ^ ( seed >>> 16 ) ) * 0x45d9f3b ) & 0xffffffff;
		seed = ( seed ^ ( seed >>> 16 ) ) >>> 0;

		var count = min + ( seed % ( max - min + 1 ) );

		nodes.forEach( function ( node ) {
			var span = node.querySelector( '.hp-viewers-count' );
			if ( span ) {
				span.textContent = count;
				span.setAttribute( 'aria-label', count + ' people' );
			}
		} );
	}

	/* ====================================================================
	 * QUANTITY +/- BUTTONS
	 * ==================================================================== */
	function initQtyButtons() {
		var qty = document.querySelector( '.hp-product-layout .summary .cart .quantity .qty' );
		if ( ! qty ) return;

		var wrap = document.createElement( 'div' );
		wrap.className = 'hp-qty-wrap';

		var minus = document.createElement( 'button' );
		minus.type = 'button';
		minus.className = 'hp-qty-btn hp-qty-btn--minus';
		minus.setAttribute( 'aria-label', 'Decrease quantity' );
		minus.textContent = '−'; // minus sign

		var plus = document.createElement( 'button' );
		plus.type = 'button';
		plus.className = 'hp-qty-btn hp-qty-btn--plus';
		plus.setAttribute( 'aria-label', 'Increase quantity' );
		plus.textContent = '+';

		qty.parentNode.insertBefore( wrap, qty );
		wrap.appendChild( minus );
		wrap.appendChild( qty );
		wrap.appendChild( plus );

		minus.addEventListener( 'click', function () {
			var val = parseInt( qty.value, 10 ) || 1;
			var min = parseInt( qty.getAttribute( 'min' ), 10 ) || 1;
			if ( val > min ) {
				qty.value = val - 1;
				qty.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
		} );

		plus.addEventListener( 'click', function () {
			var val = parseInt( qty.value, 10 ) || 1;
			var max = parseInt( qty.getAttribute( 'max' ), 10 ) || 9999;
			if ( val < max ) {
				qty.value = val + 1;
				qty.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
		} );
	}

	/* ====================================================================
	 * BUY NOW — add to cart via AJAX then redirect to checkout
	 * ==================================================================== */
	function initBuyNow() {
		var btn = document.querySelector( '.hp-buy-now-btn' );
		if ( ! btn ) return;

		var productId    = btn.getAttribute( 'data-product-id' );
		var productType  = btn.getAttribute( 'data-product-type' );
		var checkoutUrl  = btn.getAttribute( 'data-checkout-url' );

		btn.addEventListener( 'click', function () {
			if ( productType === 'variable' ) {
				// For variable: rely on WC's existing variation form to get the selected variation.
				var form = document.querySelector( '.variations_form' );
				if ( ! form ) { window.location.href = checkoutUrl; return; }

				var variationId = form.querySelector( '[name="variation_id"]' );
				if ( ! variationId || ! variationId.value ) {
					// No variation selected — scroll up to prompt user.
					form.scrollIntoView( { behavior: 'smooth', block: 'center' } );
					return;
				}

				var params = new URLSearchParams( new FormData( form ) );
				params.set( 'add-to-cart', variationId.value );
				params.set( 'quantity', form.querySelector( '.qty' ) ? form.querySelector( '.qty' ).value : '1' );
				doAddToCart( params.toString(), checkoutUrl );
			} else {
				// Simple product.
				var qtyEl = document.querySelector( '.hp-product-layout .qty' );
				var qty   = qtyEl ? qtyEl.value : '1';
				doAddToCart( 'add-to-cart=' + productId + '&quantity=' + qty, checkoutUrl );
			}
		} );
	}

	function doAddToCart( params, redirectUrl ) {
		var url = window.location.origin + window.location.pathname + '?' + params;

		fetch( url, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } } )
			.then( function () {
				window.location.href = redirectUrl;
			} )
			.catch( function () {
				window.location.href = redirectUrl;
			} );
	}

	/* ====================================================================
	 * PRODUCT TABS
	 * ==================================================================== */
	function initTabs() {
		var tabsEl = document.querySelector('[data-hp-tabs]');
		if (!tabsEl) return;

		var tabs   = tabsEl.querySelectorAll('.hp-tabs__tab');
		var panels = tabsEl.querySelectorAll('.hp-tabs__panel');

		function activateTab(id) {
			tabs.forEach(function (btn) {
				var active = btn.dataset.tab === id;
				btn.classList.toggle('is-active', active);
				btn.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			panels.forEach(function (panel) {
				var active = panel.id === 'hp-tab-' + id;
				panel.classList.toggle('is-active', active);
				if (active) {
					panel.removeAttribute('hidden');
				} else {
					panel.setAttribute('hidden', '');
				}
			});
			// Scroll active tab button into view on mobile.
			var activeBtn = tabsEl.querySelector('.hp-tabs__tab.is-active');
			if (activeBtn) {
				activeBtn.scrollIntoView({ block: 'nearest', inline: 'center' });
			}
		}

		tabs.forEach(function (btn) {
			btn.addEventListener('click', function () {
				var id = btn.dataset.tab;
				activateTab(id);
				history.replaceState(null, '', '#hp-tab-' + id);
			});
		});

		// Honour URL hash on load.
		var hash = window.location.hash;
		if (hash && hash.indexOf('#hp-tab-') === 0) {
			var id = hash.replace('#hp-tab-', '');
			if (tabsEl.querySelector('[data-tab="' + id + '"]')) {
				activateTab(id);
				// Scroll section into view after a short paint delay.
				setTimeout(function () {
					tabsEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}, 120);
			}
		}
	}

	/* ====================================================================
	 * RELATED PRODUCTS SLIDER
	 * ==================================================================== */
	function initRelatedSlider() {
		var section = document.querySelector('.hp-related');
		if (!section) return;

		var track = section.querySelector('[data-hp-slider="related"]');
		var prevBtn = section.querySelector('.hp-related__arrow--prev');
		var nextBtn = section.querySelector('.hp-related__arrow--next');
		if (!track || !prevBtn || !nextBtn) return;

		function getScrollAmount() {
			var card = track.querySelector('.hp-related__card');
			if (!card) return 260;
			var gap = parseFloat(getComputedStyle(track).columnGap) || 16;
			return card.offsetWidth + gap;
		}

		prevBtn.addEventListener('click', function () {
			track.scrollBy({ left: -getScrollAmount(), behavior: 'smooth' });
		});

		nextBtn.addEventListener('click', function () {
			track.scrollBy({ left: getScrollAmount(), behavior: 'smooth' });
		});

		var arrowsEl = section.querySelector('.hp-related__arrows');

		function updateArrows() {
			var hasOverflow = track.scrollWidth > track.clientWidth + 1;
			if (arrowsEl) {
				arrowsEl.style.visibility = hasOverflow ? '' : 'hidden';
			}
			var atStart = track.scrollLeft <= 4;
			var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
			prevBtn.disabled = atStart;
			nextBtn.disabled = atEnd;
			prevBtn.style.opacity = atStart ? '0.35' : '1';
			nextBtn.style.opacity = atEnd ? '0.35' : '1';
		}

		track.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);
		requestAnimationFrame(updateArrows);
	}

	/* ====================================================================
	 * STICKY OFFSET — measure fixed header and set --hp-header-h CSS var
	 * ==================================================================== */
	function initStickyTabOffset() {
		var header = document.getElementById('masthead');
		if (!header) return;

		function update() {
			var pos = window.getComputedStyle(header).position;
			var h   = (pos === 'fixed' || pos === 'sticky') ? header.offsetHeight : 0;
			document.documentElement.style.setProperty('--hp-header-h', h + 'px');
		}

		update();
		window.addEventListener('resize', update, { passive: true });
	}

	/* ====================================================================
	 * REVIEW IMAGE UPLOAD — client-side preview + enctype injection
	 * ==================================================================== */
	function initReviewImagePreview() {
		var zone     = document.getElementById('hp-review-upload-zone');
		var input    = document.getElementById('hp_review_images');
		var previews = document.getElementById('hp-review-upload-previews');
		if (!zone || !input || !previews) return;

		// WC review form uses comment_form() which has no enctype arg — add it here.
		var form = document.getElementById('commentform');
		if (form) {
			form.setAttribute('enctype', 'multipart/form-data');
		}

		var selectedFiles = [];

		input.addEventListener('change', function () {
			mergeFiles(Array.prototype.slice.call(this.files));
			// Reset native input so the same file can be re-selected after removal.
			this.value = '';
		});

		zone.addEventListener('dragover', function (e) {
			e.preventDefault();
			zone.classList.add('hp-review-upload__zone--drag');
		});

		zone.addEventListener('dragleave', function (e) {
			if (!zone.contains(e.relatedTarget)) {
				zone.classList.remove('hp-review-upload__zone--drag');
			}
		});

		zone.addEventListener('drop', function (e) {
			e.preventDefault();
			zone.classList.remove('hp-review-upload__zone--drag');
			mergeFiles(Array.prototype.slice.call(e.dataTransfer.files));
		});

		function mergeFiles(files) {
			var allowed = ['image/jpeg', 'image/png', 'image/webp'];
			var maxSize = 5 * 1024 * 1024;

			files = files.filter(function (f) {
				return allowed.indexOf(f.type) !== -1 && f.size <= maxSize;
			});

			selectedFiles = selectedFiles.concat(files).slice(0, 3);
			renderPreviews();
			syncInputFiles();
		}

		function renderPreviews() {
			previews.innerHTML = '';
			selectedFiles.forEach(function (file, i) {
				var item = document.createElement('div');
				item.className = 'hp-review-upload__preview';

				var img = document.createElement('img');
				var objectUrl = URL.createObjectURL(file);
				img.src = objectUrl;
				img.alt = file.name;
				img.addEventListener('load', function () { URL.revokeObjectURL(objectUrl); });

				var removeBtn = document.createElement('button');
				removeBtn.type = 'button';
				removeBtn.className = 'hp-review-upload__remove';
				removeBtn.setAttribute('aria-label', 'Remove image');
				removeBtn.innerHTML = '&times;';
				removeBtn.addEventListener('click', (function (idx) {
					return function () {
						selectedFiles.splice(idx, 1);
						renderPreviews();
						syncInputFiles();
					};
				})(i));

				item.appendChild(img);
				item.appendChild(removeBtn);
				previews.appendChild(item);
			});
		}

		function syncInputFiles() {
			try {
				var dt = new DataTransfer();
				selectedFiles.forEach(function (f) { dt.items.add(f); });
				input.files = dt.files;
			} catch (e) {
				// DataTransfer not supported — file list won't update in very old browsers.
			}
		}
	}

	/* ====================================================================
	 * INIT
	 * ==================================================================== */
	function init() {
		initStickyTabOffset();
		initGallery();
		initSwatches();
		initStickyBar();
		initViewers();
		initQtyButtons();
		initBuyNow();
		initTabs();
		initRelatedSlider();
		initReviewImagePreview();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
