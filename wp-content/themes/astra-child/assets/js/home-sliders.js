/**
 * Hit Price homepage sliders.
 *
 * Hero slider (one slide per view, autoplay, pause on hover/focus)
 * and product sliders (multi-item snap, arrows + dots).
 * Vanilla JS, no deps.
 */

(function () {
	'use strict';

	var SELECTOR = '[data-hp-slider]';
	var HERO_AUTOPLAY_DELAY = 6000;

	/**
	 * Step = distance between two adjacent slide starts.
	 * Uses offsetLeft so padding on the scroll container does not skew it.
	 */
	function getStep(slider) {
		var track = slider.querySelector('.hp-slider__track');
		if (!track) return 0;

		var firstSlide = track.querySelector('.hp-slider__slide');
		if (!firstSlide) return 0;

		var secondSlide = firstSlide.nextElementSibling;
		if (secondSlide) {
			return secondSlide.offsetLeft - firstSlide.offsetLeft;
		}

		return firstSlide.offsetWidth;
	}

	/**
	 * Page count.
	 * Hero: one dot per slide.
	 * Products: pages of visible scroll area.
	 */
	function getPageCount(slider) {
		var track = slider.querySelector('.hp-slider__track');
		if (!track) return 0;

		var type = slider.getAttribute('data-hp-slider');

		if (type === 'hero') {
			return track.children.length;
		}

		var viewport = slider.querySelector('.hp-slider__viewport');
		if (!viewport) return 1;

		var step = getStep(slider);
		if (step <= 0) return 1;

		var totalSlides = track.children.length;
		var slidesPerPage = Math.max(1, Math.round(viewport.clientWidth / step));

		return Math.max(1, Math.ceil(totalSlides / slidesPerPage));
	}

	/**
	 * Current active page index.
	 */
	function getActivePage(slider) {
		var viewport = slider.querySelector('.hp-slider__viewport');
		var step = getStep(slider);

		if (!viewport || step <= 0) return 0;

		var type = slider.getAttribute('data-hp-slider');

		if (type === 'hero') {
			return Math.round(viewport.scrollLeft / step);
		}

		var slidesPerPage = Math.max(1, Math.round(viewport.clientWidth / step));
		var pageStep = step * slidesPerPage;

		return Math.round(viewport.scrollLeft / pageStep);
	}

	/**
	 * Build product-slider dots (hero dots render in PHP).
	 */
	function buildProductDots(slider) {
		var dotsContainer = slider.querySelector('[data-hp-slider-dots]');
		if (!dotsContainer) return;

		if (slider.getAttribute('data-hp-slider') !== 'products') return;

		var pages = getPageCount(slider);
		var current = getActivePage(slider);

		dotsContainer.innerHTML = '';

		if (pages <= 1) {
			dotsContainer.style.display = 'none';
			return;
		}

		dotsContainer.style.display = '';

		for (var i = 0; i < pages; i++) {
			var dot = document.createElement('button');
			dot.type = 'button';
			dot.className = 'hp-slider__dot' + (i === current ? ' is-active' : '');
			dot.setAttribute('role', 'tab');
			dot.setAttribute('data-hp-slider-dot', String(i));
			dot.setAttribute('aria-label', 'Go to page ' + (i + 1));
			dot.setAttribute('aria-selected', i === current ? 'true' : 'false');
			dotsContainer.appendChild(dot);
		}
	}

	/**
	 * Update arrow disabled + dot active state.
	 * Page-index based — does not rely on scrollWidth vs clientWidth comparison
	 * (which can be wrong at init when images are still loading).
	 */
	function updateState(slider) {
		var prev = slider.querySelector('[data-hp-slider-prev]');
		var next = slider.querySelector('[data-hp-slider-next]');
		var dots = slider.querySelectorAll('[data-hp-slider-dot]');

		var current = getActivePage(slider);
		var total = getPageCount(slider);

		if (prev) prev.disabled = current <= 0;
		if (next) next.disabled = current >= total - 1;

		for (var i = 0; i < dots.length; i++) {
			var active = i === current;
			dots[i].classList.toggle('is-active', active);
			dots[i].setAttribute('aria-selected', active ? 'true' : 'false');
		}
	}

	/**
	 * Scroll to a specific page index.
	 */
	function scrollToPage(slider, pageIndex) {
		var viewport = slider.querySelector('.hp-slider__viewport');
		if (!viewport) return;

		var step = getStep(slider);
		if (step <= 0) return;

		var type = slider.getAttribute('data-hp-slider');
		var pageStep = step;

		if (type !== 'hero') {
			var slidesPerPage = Math.max(1, Math.round(viewport.clientWidth / step));
			pageStep = step * slidesPerPage;
		}

		var total = getPageCount(slider);
		var clamped = Math.max(0, Math.min(pageIndex, total - 1));

		viewport.scrollTo({ left: clamped * pageStep, behavior: 'smooth' });
	}

	/**
	 * Step by ±1 page.
	 */
	function moveBy(slider, direction) {
		var current = getActivePage(slider);
		scrollToPage(slider, current + direction);
	}

	/**
	 * Advance hero to the next slide, wrap around.
	 */
	function advanceHero(slider) {
		var current = getActivePage(slider);
		var total = getPageCount(slider);
		var nextIndex = (current + 1) % total;

		scrollToPage(slider, nextIndex);
	}

	/**
	 * Debounce helper.
	 */
	function debounce(fn, delay) {
		var timer;
		return function () {
			var ctx = this;
			var args = arguments;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(ctx, args);
			}, delay);
		};
	}

	/**
	 * Initialize a slider.
	 */
	function initSlider(slider) {
		if (slider.classList.contains('is-single')) return;
		if (slider.__hpInit) return;
		slider.__hpInit = true;

		var viewport = slider.querySelector('.hp-slider__viewport');
		var prev = slider.querySelector('[data-hp-slider-prev]');
		var next = slider.querySelector('[data-hp-slider-next]');
		var dotsContainer = slider.querySelector('[data-hp-slider-dots]');
		var type = slider.getAttribute('data-hp-slider');

		buildProductDots(slider);
		updateState(slider);

		if (prev) {
			prev.addEventListener('click', function () {
				moveBy(slider, -1);
			});
		}
		if (next) {
			next.addEventListener('click', function () {
				moveBy(slider, 1);
			});
		}

		if (dotsContainer) {
			dotsContainer.addEventListener('click', function (event) {
				var target = event.target.closest('[data-hp-slider-dot]');
				if (!target) return;
				var index = parseInt(target.getAttribute('data-hp-slider-dot'), 10);
				if (!isNaN(index)) {
					scrollToPage(slider, index);
				}
			});
		}

		if (viewport) {
			var onScroll = debounce(function () {
				updateState(slider);
			}, 60);
			viewport.addEventListener('scroll', onScroll, { passive: true });

			viewport.tabIndex = viewport.tabIndex >= 0 ? viewport.tabIndex : 0;
			viewport.addEventListener('keydown', function (event) {
				if (event.key === 'ArrowRight') {
					event.preventDefault();
					moveBy(slider, 1);
				} else if (event.key === 'ArrowLeft') {
					event.preventDefault();
					moveBy(slider, -1);
				}
			});
		}

		// Re-sync state once images have loaded (layout may shift).
		var images = slider.querySelectorAll('img');
		var loaded = 0;
		for (var i = 0; i < images.length; i++) {
			if (images[i].complete) {
				loaded++;
			} else {
				images[i].addEventListener('load', function () {
					updateState(slider);
					buildProductDots(slider);
				}, { once: true });
			}
		}
		if (loaded === images.length) {
			updateState(slider);
		}

		// Hero: autoplay with pause on hover / focus-within / tab hidden.
		if (type === 'hero') {
			var timer = null;
			var paused = false;

			function start() {
				if (paused) return;
				stop();
				timer = setInterval(function () {
					if (document.hidden) return;
					advanceHero(slider);
				}, HERO_AUTOPLAY_DELAY);
			}
			function stop() {
				if (timer) {
					clearInterval(timer);
					timer = null;
				}
			}

			slider.addEventListener('mouseenter', function () { paused = true; stop(); });
			slider.addEventListener('mouseleave', function () { paused = false; start(); });
			slider.addEventListener('focusin', function () { paused = true; stop(); });
			slider.addEventListener('focusout', function () { paused = false; start(); });

			document.addEventListener('visibilitychange', function () {
				if (document.hidden) {
					stop();
				} else if (!paused) {
					start();
				}
			});

			start();
		}
	}

	var onResize = debounce(function () {
		var sliders = document.querySelectorAll(SELECTOR);
		for (var i = 0; i < sliders.length; i++) {
			if (sliders[i].classList.contains('is-single')) continue;
			buildProductDots(sliders[i]);
			updateState(sliders[i]);
		}
	}, 150);

	function init() {
		var sliders = document.querySelectorAll(SELECTOR);
		for (var i = 0; i < sliders.length; i++) {
			initSlider(sliders[i]);
		}
		window.addEventListener('resize', onResize, { passive: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
