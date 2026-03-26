/**
 * HitPrice AI Content Engine — Admin JS
 *
 * Handles AJAX interactions for topics, drafts, and generation.
 * Uses the localized hpAiAdmin object for AJAX URL and nonce.
 *
 * @package HitPrice_Content_Engine
 */

( function () {

	'use strict';

	if ( typeof hpAiAdmin === 'undefined' ) {
		return;
	}

	/* -----------------------------------------------
	 * Core AJAX helper
	 * ----------------------------------------------- */

	function hpAiRequest( action, data ) {

		var formData = new FormData();
		formData.append( 'action', action );
		formData.append( '_ajax_nonce', hpAiAdmin.nonce );

		if ( data && typeof data === 'object' ) {
			Object.keys( data ).forEach( function ( key ) {
				formData.append( key, data[ key ] );
			} );
		}

		return fetch( hpAiAdmin.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	window.hpAiRequest = hpAiRequest;

	/* -----------------------------------------------
	 * Utility: show result message
	 * ----------------------------------------------- */

	function showResult( el, message, type ) {

		if ( ! el ) {
			return;
		}

		el.textContent = message;
		el.className = 'hp-ai-generate-result ' + type;
		el.style.display = 'block';

		setTimeout( function () {
			el.style.display = 'none';
		}, 5000 );
	}

	/* -----------------------------------------------
	 * Topics Page: Toggle add form
	 * ----------------------------------------------- */

	var toggleBtn = document.getElementById( 'hp-ai-toggle-add-form' );
	var addForm   = document.getElementById( 'hp-ai-add-topic-form' );
	var cancelBtn = document.getElementById( 'hp-ai-cancel-topic' );

	if ( toggleBtn && addForm ) {
		toggleBtn.addEventListener( 'click', function () {
			addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
		} );
	}

	if ( cancelBtn && addForm ) {
		cancelBtn.addEventListener( 'click', function () {
			addForm.style.display = 'none';
		} );
	}

	/* -----------------------------------------------
	 * Topics Page: Add topic
	 * ----------------------------------------------- */

	var saveTopicBtn = document.getElementById( 'hp-ai-save-topic' );

	if ( saveTopicBtn ) {
		saveTopicBtn.addEventListener( 'click', function () {

			var title    = document.getElementById( 'hp-ai-topic-title' );
			var type     = document.getElementById( 'hp-ai-topic-type' );
			var keywords = document.getElementById( 'hp-ai-topic-keywords' );
			var month    = document.getElementById( 'hp-ai-topic-month' );
			var priority = document.getElementById( 'hp-ai-topic-priority' );
			var resultEl = document.getElementById( 'hp-ai-topic-result' );

			if ( ! title || ! title.value.trim() ) {
				showResult( resultEl, 'Topic title is required.', 'error' );
				return;
			}

			saveTopicBtn.classList.add( 'hp-ai-loading' );
			saveTopicBtn.textContent = 'Saving...';

			hpAiRequest( 'hp_ai_add_topic', {
				title: title.value.trim(),
				content_type: type ? type.value : 'comparison',
				keywords: keywords ? keywords.value : '',
				month_target: month ? month.value : '',
				priority: priority && priority.checked ? 1 : 0,
			} ).then( function ( res ) {

				saveTopicBtn.classList.remove( 'hp-ai-loading' );
				saveTopicBtn.textContent = 'Add Topic';

				if ( res.success ) {
					showResult( resultEl, res.data.message, 'success' );
					title.value = '';
					keywords.value = '';
					if ( priority ) {
						priority.checked = false;
					}
					// Reload to show new topic in table.
					setTimeout( function () {
						window.location.reload();
					}, 800 );
				} else {
					showResult( resultEl, res.data.message || hpAiAdmin.i18n.error, 'error' );
				}
			} ).catch( function () {
				saveTopicBtn.classList.remove( 'hp-ai-loading' );
				saveTopicBtn.textContent = 'Add Topic';
				showResult( resultEl, hpAiAdmin.i18n.error, 'error' );
			} );
		} );
	}

	/* -----------------------------------------------
	 * Topics Page: Delete topic
	 * ----------------------------------------------- */

	document.addEventListener( 'click', function ( e ) {

		var btn = e.target.closest( '.hp-ai-delete-topic' );
		if ( ! btn ) {
			return;
		}

		if ( ! confirm( hpAiAdmin.i18n.confirm_delete ) ) {
			return;
		}

		var topicId = btn.getAttribute( 'data-id' );
		var row     = document.getElementById( 'hp-ai-topic-row-' + topicId );

		btn.classList.add( 'hp-ai-loading' );

		hpAiRequest( 'hp_ai_delete_topic', { topic_id: topicId } ).then( function ( res ) {
			if ( res.success && row ) {
				row.classList.add( 'hp-ai-fade-out' );
				setTimeout( function () {
					row.remove();
				}, 300 );
			} else {
				btn.classList.remove( 'hp-ai-loading' );
				alert( res.data.message || hpAiAdmin.i18n.error );
			}
		} ).catch( function () {
			btn.classList.remove( 'hp-ai-loading' );
		} );
	} );

	/* -----------------------------------------------
	 * Topics Page: Skip topic
	 * ----------------------------------------------- */

	document.addEventListener( 'click', function ( e ) {

		var btn = e.target.closest( '.hp-ai-skip-topic' );
		if ( ! btn ) {
			return;
		}

		var topicId = btn.getAttribute( 'data-id' );

		btn.classList.add( 'hp-ai-loading' );

		hpAiRequest( 'hp_ai_update_topic_status', {
			topic_id: topicId,
			status: 'skipped',
		} ).then( function ( res ) {
			if ( res.success ) {
				window.location.reload();
			} else {
				btn.classList.remove( 'hp-ai-loading' );
				alert( res.data.message || hpAiAdmin.i18n.error );
			}
		} ).catch( function () {
			btn.classList.remove( 'hp-ai-loading' );
		} );
	} );

	/* -----------------------------------------------
	 * Dashboard: Generate
	 * ----------------------------------------------- */

	var generateBtn = document.getElementById( 'hp-ai-generate-btn' );

	if ( generateBtn ) {
		generateBtn.addEventListener( 'click', function () {

			var topicSelect = document.getElementById( 'hp-ai-gen-topic' );
			var typeSelect  = document.getElementById( 'hp-ai-gen-type' );
			var resultEl    = document.getElementById( 'hp-ai-generate-result' );

			if ( ! topicSelect || ! topicSelect.value ) {
				showResult( resultEl, 'Please select a topic.', 'error' );
				return;
			}

			generateBtn.classList.add( 'hp-ai-loading' );
			generateBtn.textContent = hpAiAdmin.i18n.generating;

			hpAiRequest( 'hp_ai_generate', {
				topic_id: topicSelect.value,
				output_type: typeSelect ? typeSelect.value : 'social',
			} ).then( function ( res ) {

				generateBtn.classList.remove( 'hp-ai-loading' );
				generateBtn.textContent = 'Generate Now';

				if ( res.success ) {
					showResult( resultEl, res.data.message, 'success' );
				} else {
					showResult( resultEl, res.data.message || hpAiAdmin.i18n.error, 'error' );
				}
			} ).catch( function () {
				generateBtn.classList.remove( 'hp-ai-loading' );
				generateBtn.textContent = 'Generate Now';
				showResult( resultEl, hpAiAdmin.i18n.error, 'error' );
			} );
		} );
	}

	/* -----------------------------------------------
	 * Drafts Page: Update draft status
	 * ----------------------------------------------- */

	document.addEventListener( 'click', function ( e ) {

		var btn = e.target.closest( '.hp-ai-draft-status-btn' );
		if ( ! btn ) {
			return;
		}

		var draftId   = btn.getAttribute( 'data-id' );
		var draftType = btn.getAttribute( 'data-type' );
		var newStatus = btn.getAttribute( 'data-status' );

		btn.classList.add( 'hp-ai-loading' );

		hpAiRequest( 'hp_ai_update_draft_status', {
			draft_id: draftId,
			draft_type: draftType,
			status: newStatus,
		} ).then( function ( res ) {
			if ( res.success ) {
				window.location.reload();
			} else {
				btn.classList.remove( 'hp-ai-loading' );
				alert( res.data.message || hpAiAdmin.i18n.error );
			}
		} ).catch( function () {
			btn.classList.remove( 'hp-ai-loading' );
		} );
	} );

	/* -----------------------------------------------
	 * Drafts Page: Delete draft
	 * ----------------------------------------------- */

	document.addEventListener( 'click', function ( e ) {

		var btn = e.target.closest( '.hp-ai-delete-draft-btn' );
		if ( ! btn ) {
			return;
		}

		if ( ! confirm( hpAiAdmin.i18n.confirm_delete ) ) {
			return;
		}

		var draftId   = btn.getAttribute( 'data-id' );
		var draftType = btn.getAttribute( 'data-type' );
		var card      = document.getElementById( 'hp-ai-draft-' + draftId );
		var row       = document.getElementById( 'hp-ai-blog-row-' + draftId );
		var target    = card || row;

		btn.classList.add( 'hp-ai-loading' );

		hpAiRequest( 'hp_ai_delete_draft', {
			draft_id: draftId,
			draft_type: draftType,
		} ).then( function ( res ) {
			if ( res.success && target ) {
				target.classList.add( 'hp-ai-fade-out' );
				setTimeout( function () {
					target.remove();
				}, 300 );
			} else {
				btn.classList.remove( 'hp-ai-loading' );
				alert( res.data.message || hpAiAdmin.i18n.error );
			}
		} ).catch( function () {
			btn.classList.remove( 'hp-ai-loading' );
		} );
	} );

	/* -----------------------------------------------
	 * Drafts Page: Copy to clipboard
	 * ----------------------------------------------- */

	document.addEventListener( 'click', function ( e ) {

		var btn = e.target.closest( '.hp-ai-copy-btn' );
		if ( ! btn ) {
			return;
		}

		var text = btn.getAttribute( 'data-copy' );
		if ( ! text ) {
			return;
		}

		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then( function () {
				btn.classList.add( 'copied' );
				var original = btn.textContent;
				btn.textContent = 'Copied!';
				setTimeout( function () {
					btn.classList.remove( 'copied' );
					btn.textContent = original;
				}, 1500 );
			} );
		}
	} );

} )();
