/**
 * HitPrice — Bulk Specs Importer (admin)
 *
 * Adds an "Add Bulk" button to the ACF flexible content field
 * `field_hp_detail_specs` on the product edit screen. Opens a modal
 * that accepts pasted HTML (competitor spec tables), parses each
 * `.p-spec-table` block, and inserts a new `key_value_table` layout
 * per block — populating its heading and inner rows repeater
 * (label / value) via ACF's official JS API.
 *
 * No ACF core files are touched. No server roundtrip.
 */
(function ($) {
	'use strict';

	// Bail if ACF JS API or our localized config is missing.
	if (typeof acf === 'undefined' || typeof window.HP_BULK_SPECS === 'undefined') {
		return;
	}

	var CFG = window.HP_BULK_SPECS;
	var I18N = CFG.i18n || {};
	var MODAL_ID = 'hp-bulk-specs-modal';
	var $modal = null;
	var activeFlexField = null;

	/**
	 * Build the modal once and append to <body>.
	 */
	function buildModal() {
		if ($modal && $modal.length) {
			return $modal;
		}

		$modal = $(
			'<div id="' + MODAL_ID + '" class="hp-bulk-modal" hidden role="dialog" aria-modal="true" aria-labelledby="hp-bulk-modal-title">' +
			'  <div class="hp-bulk-modal__backdrop" data-action="cancel"></div>' +
			'  <div class="hp-bulk-modal__dialog" role="document">' +
			'    <button type="button" class="hp-bulk-modal__close" data-action="cancel">&times;</button>' +
			'    <h2 id="hp-bulk-modal-title" class="hp-bulk-modal__title"></h2>' +
			'    <p class="hp-bulk-modal__hint"></p>' +
			'    <textarea class="hp-bulk-modal__textarea" rows="14" spellcheck="false"></textarea>' +
			'    <div class="hp-bulk-modal__status" aria-live="polite"></div>' +
			'    <div class="hp-bulk-modal__actions">' +
			'      <button type="button" class="button button-secondary" data-action="cancel"></button>' +
			'      <button type="button" class="button button-primary" data-action="submit"></button>' +
			'    </div>' +
			'  </div>' +
			'</div>'
		);

		// Apply localized text.
		$modal.find('.hp-bulk-modal__close').attr('aria-label', I18N.closeLabel || 'Close');
		$modal.find('.hp-bulk-modal__title').text(I18N.modalTitle || '');
		$modal.find('.hp-bulk-modal__hint').text(I18N.modalHint || '');
		$modal.find('.hp-bulk-modal__textarea').attr('placeholder', I18N.placeholder || '');
		$modal.find('.button-secondary[data-action="cancel"]').text(I18N.cancel || 'Cancel');
		$modal.find('[data-action="submit"]').text(I18N.parseInsert || 'Parse & Insert');

		$('body').append($modal);

		// Wire interactions.
		$modal.on('click', '[data-action="cancel"]', closeModal);
		$modal.on('click', '[data-action="submit"]', onSubmit);

		// ESC closes modal.
		$(document).on('keydown.hpBulkSpecs', function (e) {
			if (e.key === 'Escape' && $modal && $modal.is(':visible')) {
				closeModal();
			}
		});

		return $modal;
	}

	/**
	 * Open modal and remember the active flex field instance.
	 */
	function openModal(flexField) {
		buildModal();
		activeFlexField = flexField;
		$modal.find('.hp-bulk-modal__textarea').val('');
		setStatus('', null);
		$modal.removeAttr('hidden').addClass('is-open');

		// Focus textarea after paint.
		setTimeout(function () {
			$modal.find('.hp-bulk-modal__textarea').trigger('focus');
		}, 30);
	}

	/**
	 * Close modal and clear active state.
	 */
	function closeModal(e) {
		if (e && e.preventDefault) {
			e.preventDefault();
		}
		if (!$modal) {
			return;
		}
		$modal.attr('hidden', true).removeClass('is-open');
		activeFlexField = null;
	}

	/**
	 * Update modal status line.
	 */
	function setStatus(text, type) {
		if (!$modal) return;
		var $s = $modal.find('.hp-bulk-modal__status');
		$s.removeClass('is-error is-success');
		if (type) {
			$s.addClass('is-' + type);
		}
		$s.text(text || '');
	}

	/**
	 * Parse pasted HTML into [{ heading, rows: [{label, value}] }, ...].
	 *
	 * - Loops `.p-spec-table` blocks
	 * - Heading from first <h6>/<h5>/<h4>
	 * - Walks <dl> children pairing <dt> + following <dd>
	 * - Skips empty labels, missing dd values default to empty string
	 * - Trims everything; dedupes within section by lowercased label
	 */
	function parseHtml(html) {
		var sections = [];
		if (!html || !html.trim()) {
			return sections;
		}

		var doc;
		try {
			doc = new DOMParser().parseFromString(html, 'text/html');
		} catch (err) {
			return sections;
		}
		if (!doc) {
			return sections;
		}

		var blocks = doc.querySelectorAll('.p-spec-table');
		if (!blocks.length) {
			return sections;
		}

		blocks.forEach(function (block) {
			var headingEl = block.querySelector('h6, h5, h4, h3, h2');
			var heading = headingEl ? (headingEl.textContent || '').trim() : '';

			var dl = block.querySelector('dl');
			if (!dl) {
				return;
			}

			var rows = [];
			var seen = Object.create(null);
			var currentLabel = null;
			var children = dl.children;

			for (var i = 0; i < children.length; i++) {
				var el = children[i];
				var tag = el.tagName;
				if (tag === 'DT') {
					currentLabel = (el.textContent || '').trim();
				} else if (tag === 'DD') {
					if (!currentLabel) {
						continue;
					}
					var value = (el.textContent || '').trim();
					var key = currentLabel.toLowerCase();
					if (!seen[key]) {
						rows.push({ label: currentLabel, value: value });
						seen[key] = true;
					}
					currentLabel = null;
				}
			}

			if (rows.length) {
				sections.push({ heading: heading, rows: rows });
			}
		});

		return sections;
	}

	/**
	 * Set the heading input inside a flex layout.
	 */
	function setLayoutHeading($layout, headingText) {
		var $field = $layout.find('.acf-field[data-key="' + CFG.headingFieldKey + '"]').first();
		var $input = $field.find('input[type="text"]').first();
		if ($input.length) {
			$input.val(headingText).trigger('input').trigger('change');
		}
	}

	/**
	 * Fill the inner rows repeater with spec pairs.
	 * Reuses the auto-created blank first row when present, then appends.
	 */
	function fillLayoutRows($layout, specs) {
		var $rowsFieldEl = $layout.find('.acf-field[data-key="' + CFG.rowsFieldKey + '"]').first();
		if (!$rowsFieldEl.length || !specs.length) {
			return 0;
		}

		var rowsField = null;
		try {
			rowsField = acf.getField($rowsFieldEl);
		} catch (err) {
			rowsField = null;
		}
		if (!rowsField) {
			return 0;
		}

		var added = 0;

		for (var i = 0; i < specs.length; i++) {
			var spec = specs[i];
			var $row = null;

			// Reuse the auto-created blank first row only on the first iteration.
			if (i === 0) {
				var $firstRow = $rowsFieldEl
					.find('> .acf-input .acf-row')
					.not('.acf-clone')
					.first();
				if ($firstRow.length) {
					var $existingLabel = $firstRow.find('.acf-field[data-key="' + CFG.labelFieldKey + '"] input').first();
					var $existingValue = $firstRow.find('.acf-field[data-key="' + CFG.valueFieldKey + '"] input').first();
					var hasContent = ($existingLabel.val() || '').length > 0 || ($existingValue.val() || '').length > 0;
					if (!hasContent) {
						$row = $firstRow;
					}
				}
			}

			if (!$row) {
				try {
					$row = rowsField.add();
				} catch (err) {
					continue;
				}
			}

			if (!$row || !$row.length) {
				continue;
			}

			var $labelInput = $row.find('.acf-field[data-key="' + CFG.labelFieldKey + '"] input').first();
			var $valueInput = $row.find('.acf-field[data-key="' + CFG.valueFieldKey + '"] input').first();

			if ($labelInput.length) {
				$labelInput.val(spec.label).trigger('input').trigger('change');
			}
			if ($valueInput.length) {
				$valueInput.val(spec.value).trigger('input').trigger('change');
			}
			added++;
		}

		return added;
	}

	/**
	 * Insert each parsed section as a new key_value_table layout.
	 */
	function insertSections(sections) {
		if (!activeFlexField || !sections.length) {
			return { sections: 0, rows: 0 };
		}

		var sectionCount = 0;
		var rowCount = 0;

		sections.forEach(function (section) {
			var $layout = null;
			try {
				$layout = activeFlexField.add({ layout: CFG.layoutName });
			} catch (err) {
				$layout = null;
			}
			if (!$layout || !$layout.length) {
				return;
			}

			if (section.heading) {
				setLayoutHeading($layout, section.heading);
			}

			rowCount += fillLayoutRows($layout, section.rows);
			sectionCount++;
		});

		return { sections: sectionCount, rows: rowCount };
	}

	/**
	 * Modal submit: parse → validate → insert → feedback.
	 */
	function onSubmit(e) {
		if (e && e.preventDefault) {
			e.preventDefault();
		}

		var html = $modal.find('.hp-bulk-modal__textarea').val();
		var sections = parseHtml(html);

		if (!sections.length) {
			setStatus(I18N.noSections || 'No sections found.', 'error');
			return;
		}

		var result = insertSections(sections);
		if (!result.sections) {
			setStatus(I18N.parseError || 'Parse error.', 'error');
			return;
		}

		var msg = (I18N.imported || 'Imported %1$d / %2$d')
			.replace('%1$d', result.sections)
			.replace('%2$d', result.rows);
		setStatus(msg, 'success');

		setTimeout(closeModal, 1200);
	}

	/**
	 * Inject the "Add Bulk" button next to the flex content "Add Section".
	 * Idempotent — guards against duplicate injection on re-render.
	 */
	function injectButton(field) {
		if (!field || !field.$el) {
			return;
		}
		var $field = field.$el;

		if ($field.attr('data-key') !== CFG.targetFieldKey) {
			return;
		}

		if ($field.find('.hp-bulk-add').length) {
			return;
		}

		// Primary action area for flex content "Add Section" button.
		var $actions = $field
			.find('> .acf-input > .acf-flexible-content > .acf-actions')
			.first();
		if (!$actions.length) {
			$actions = $field.find('.acf-actions').first();
		}
		if (!$actions.length) {
			return;
		}

		var $btn = $('<a href="#" class="acf-button button hp-bulk-add"></a>')
			.text(I18N.buttonLabel || 'Add Bulk');

		$btn.on('click', function (e) {
			e.preventDefault();
			openModal(field);
		});

		$actions.append($btn);
	}

	// Hook into ACF lifecycle for our specific flex field.
	acf.addAction('ready_field/key=' + CFG.targetFieldKey, injectButton);
	acf.addAction('append_field/key=' + CFG.targetFieldKey, injectButton);
})(jQuery);
