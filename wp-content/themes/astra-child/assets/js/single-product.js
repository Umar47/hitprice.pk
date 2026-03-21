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

		var rows = form.querySelectorAll('.variations tr');
		if (!rows.length) return;

		rows.forEach(function (row) {
			var select = row.querySelector('select');
			if (!select) return;

			var options = select.querySelectorAll('option');
			if (options.length <= 1) return;

			var container = document.createElement('div');
			container.className = 'hp-swatches';

			options.forEach(function (option) {
				if (!option.value) return;

				var swatch = document.createElement('button');
				swatch.type = 'button';
				swatch.className = 'hp-swatch';
				swatch.setAttribute('data-value', option.value);

				var text = option.textContent.trim();
				if (isColorName(text)) {
					swatch.classList.add('hp-swatch--color');
					swatch.style.backgroundColor = getColorHex(text);
					swatch.setAttribute('aria-label', text);
				} else {
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

	var COLOR_MAP = {
		'black': '#000', 'white': '#fff', 'red': '#d32f2f', 'blue': '#1976d2',
		'green': '#388e3c', 'yellow': '#fbc02d', 'pink': '#e91e63', 'purple': '#7b1fa2',
		'orange': '#f57c00', 'gray': '#757575', 'grey': '#757575', 'gold': '#c8a415',
		'silver': '#c0c0c0', 'navy': '#0d3b66', 'teal': '#00897b', 'brown': '#795548',
		'midnight': '#191970', 'natural titanium': '#8c8479', 'desert titanium': '#c4a882',
		'white titanium': '#e8e6e1', 'black titanium': '#3c3c3c'
	};

	function isColorName(text) { return text.toLowerCase() in COLOR_MAP; }
	function getColorHex(text) { return COLOR_MAP[text.toLowerCase()] || '#ccc'; }


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
	 * INIT
	 * ==================================================================== */
	function init() {
		initGallery();
		initSwatches();
		initStickyBar();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
