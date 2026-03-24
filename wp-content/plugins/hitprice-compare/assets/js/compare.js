/**
 * HitPrice Compare — localStorage management, floating bar, button state.
 *
 * @package HitPriceCompare
 */
(function () {
	'use strict';

	var STORAGE_KEY = 'hpc_compare_ids';
	var MAX = (window.hpcConfig && hpcConfig.maxItems) || 4;
	var COMPARE_URL = (window.hpcConfig && hpcConfig.compareUrl) || '/compare/';
	var i18n = (window.hpcConfig && hpcConfig.i18n) || {};

	/* ==== localStorage helpers ==== */

	function getIds() {
		try {
			var raw = localStorage.getItem(STORAGE_KEY);
			if (!raw) return [];
			var ids = JSON.parse(raw);
			if (!Array.isArray(ids)) return [];
			return ids.map(Number).filter(function (n) { return n > 0; });
		} catch (e) {
			return [];
		}
	}

	function saveIds(ids) {
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(ids.slice(0, MAX)));
		} catch (e) {
			// Storage full or unavailable — silent fail.
		}
	}

	function addId(id) {
		var ids = getIds();
		id = Number(id);
		if (ids.indexOf(id) !== -1) return false; // Duplicate.
		if (ids.length >= MAX) return false; // Full.
		ids.push(id);
		saveIds(ids);
		return true;
	}

	function removeId(id) {
		id = Number(id);
		var ids = getIds().filter(function (n) { return n !== id; });
		saveIds(ids);
	}

	function clearAll() {
		saveIds([]);
	}

	function hasId(id) {
		return getIds().indexOf(Number(id)) !== -1;
	}

	/* ==== Sync all compare controls (buttons + checkboxes) ==== */

	function syncAllButtons() {
		var ids = getIds();

		// Sync standalone compare buttons.
		var buttons = document.querySelectorAll('.hpc-compare-btn');
		buttons.forEach(function (btn) {
			var pid = Number(btn.getAttribute('data-product-id'));
			var isAdded = ids.indexOf(pid) !== -1;
			var textEl = btn.querySelector('.hpc-compare-btn__text');

			btn.classList.toggle('is-added', isAdded);

			if (textEl) {
				if (isAdded) {
					textEl.textContent = i18n.added || 'Added';
				} else if (ids.length >= MAX) {
					textEl.textContent = i18n.full || 'Max 4 items';
				} else {
					textEl.textContent = i18n.compare || 'Compare';
				}
			}
		});

		// Sync product card checkboxes.
		var checks = document.querySelectorAll('.hpc-compare-check');
		checks.forEach(function (cb) {
			var pid = Number(cb.getAttribute('data-product-id'));
			var isAdded = ids.indexOf(pid) !== -1;
			var label = cb.closest('.hitprice-product-card__compare');

			cb.checked = isAdded;
			if (label) {
				label.classList.toggle('is-checked', isAdded);
			}
		});
	}

	/* ==== Floating bar ==== */

	function syncBar() {
		var bar = document.getElementById('hpc-bar');
		if (!bar) return;

		var ids = getIds();
		var count = ids.length;
		var countEl = document.getElementById('hpc-bar-count');
		var ctaEl = document.getElementById('hpc-bar-cta');

		if (count === 0) {
			bar.classList.add('hpc-bar--hidden');
			bar.setAttribute('aria-hidden', 'true');
			return;
		}

		bar.classList.remove('hpc-bar--hidden');
		bar.setAttribute('aria-hidden', 'false');

		if (countEl) {
			var label = count === 1 ? (i18n.item || 'item selected') : (i18n.items || 'items selected');
			countEl.textContent = count + ' ' + label;
		}

		if (ctaEl) {
			var url = COMPARE_URL;
			var separator = url.indexOf('?') !== -1 ? '&' : '?';
			ctaEl.href = url + separator + 'ids=' + ids.join(',');
		}
	}

	/* ==== Event delegation ==== */

	function handleButtonClick(e) {
		var btn = e.target.closest('.hpc-compare-btn');
		if (!btn) return;

		var pid = Number(btn.getAttribute('data-product-id'));
		if (!pid) return;

		e.preventDefault();

		if (hasId(pid)) {
			removeId(pid);
		} else {
			if (!addId(pid)) return; // Full — do nothing.
		}

		syncAllButtons();
		syncBar();
	}

	function handleRemoveClick(e) {
		var btn = e.target.closest('.hpc-compare-card__remove');
		if (!btn) return;

		var pid = Number(btn.getAttribute('data-product-id'));
		if (!pid) return;

		e.preventDefault();
		removeId(pid);

		// Redirect to updated compare URL.
		var ids = getIds();
		if (ids.length < 2) {
			window.location.href = COMPARE_URL;
		} else {
			var url = COMPARE_URL;
			var separator = url.indexOf('?') !== -1 ? '&' : '?';
			window.location.href = url + separator + 'ids=' + ids.join(',');
		}
	}

	function handleClearClick(e) {
		var btn = e.target.closest('#hpc-bar-clear');
		if (!btn) return;

		e.preventDefault();
		clearAll();
		syncAllButtons();
		syncBar();
	}

	function handleCheckboxChange(e) {
		var cb = e.target.closest('.hpc-compare-check');
		if (!cb) return;

		var pid = Number(cb.getAttribute('data-product-id'));
		if (!pid) return;

		if (cb.checked) {
			if (!addId(pid)) {
				// Full — revert checkbox.
				cb.checked = false;
				return;
			}
		} else {
			removeId(pid);
		}

		syncAllButtons();
		syncBar();
	}

	/* ==== Init ==== */

	function init() {
		document.addEventListener('click', handleButtonClick);
		document.addEventListener('click', handleRemoveClick);
		document.addEventListener('click', handleClearClick);
		document.addEventListener('change', handleCheckboxChange);

		syncAllButtons();
		syncBar();

		// Sync across tabs.
		window.addEventListener('storage', function (e) {
			if (e.key === STORAGE_KEY) {
				syncAllButtons();
				syncBar();
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
