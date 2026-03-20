/**
 * Single Product Page — Variation Swatches, Accordion Toggle, Sticky Bar
 *
 * @package HitPrice
 */
(function () {
	'use strict';

	/* ---- Variation Swatch Overlay ---- */
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

				// Detect color-like values.
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
					select.dispatchEvent(new Event('change', { bubbles: true }));
					updateActiveSwatches(container, option.value);
				});

				container.appendChild(swatch);
			});

			// Insert swatches before the select cell.
			var selectCell = row.querySelector('td.value');
			if (selectCell) {
				selectCell.insertBefore(container, selectCell.firstChild);
			}
		});

		// Mark form so CSS can hide native selects.
		form.classList.add('hp-has-swatches');

		// Listen for external resets.
		form.addEventListener('reset_data', function () {
			form.querySelectorAll('.hp-swatch.is-active').forEach(function (s) {
				s.classList.remove('is-active');
			});
		});
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

	function isColorName(text) {
		return text.toLowerCase() in COLOR_MAP;
	}

	function getColorHex(text) {
		return COLOR_MAP[text.toLowerCase()] || '#ccc';
	}

	/* ---- Sticky Bar ---- */
	function initStickyBar() {
		var bar = document.getElementById('hp-sticky-bar');
		if (!bar) return;

		var summary = document.querySelector('.summary.entry-summary');
		if (!summary) return;

		var priceEl = document.getElementById('hp-sticky-bar-price');
		var shown = false;

		function checkScroll() {
			var rect = summary.getBoundingClientRect();
			var shouldShow = rect.bottom < 0;

			if (shouldShow !== shown) {
				shown = shouldShow;
				bar.classList.toggle('hp-sticky-bar--hidden', !shown);
				bar.setAttribute('aria-hidden', String(!shown));
			}
		}

		window.addEventListener('scroll', checkScroll, { passive: true });
		checkScroll();

		// Listen for variation changes.
		var form = document.querySelector('.variations_form');
		if (form && priceEl) {
			form.addEventListener('found_variation', function (e) {
				var variation = e.detail || (jQuery && jQuery(form).data('lastVariation'));
				if (!variation && typeof e.originalEvent !== 'undefined') {
					return;
				}
				// jQuery event — use jQuery handler.
			});

			if (typeof jQuery !== 'undefined') {
				jQuery(form).on('found_variation', function (event, variation) {
					if (variation && variation.price_html) {
						priceEl.innerHTML = variation.price_html;
					}
					if (variation && !variation.is_in_stock) {
						var cta = bar.querySelector('.hp-sticky-bar__cta');
						if (cta) {
							cta.style.display = 'none';
						}
						var oos = bar.querySelector('.hp-sticky-bar__out-of-stock');
						if (!oos) {
							oos = document.createElement('span');
							oos.className = 'hp-sticky-bar__out-of-stock';
							oos.textContent = 'Out of stock';
							bar.querySelector('.hp-sticky-bar__actions').appendChild(oos);
						}
						oos.style.display = '';
					} else {
						var cta = bar.querySelector('.hp-sticky-bar__cta');
						if (cta) cta.style.display = '';
						var oos = bar.querySelector('.hp-sticky-bar__out-of-stock');
						if (oos) oos.style.display = 'none';
					}
				});

				jQuery(form).on('reset_data', function () {
					var product = form.closest('.product');
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
		}
	}

	/* ---- Init ---- */
	function init() {
		initSwatches();
		initStickyBar();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
