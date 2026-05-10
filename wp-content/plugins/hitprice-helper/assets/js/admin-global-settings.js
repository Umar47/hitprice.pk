/* HitPrice Global Settings — gallery icons + price icons repeater + WP media upload */
( function ( $ ) {
	'use strict';

	var MAX_ROWS        = 6;
	var MAX_GALLERY_BOT = 4;
	var MAX_WHY_BUY     = 5;

	function getRowIndex() {
		return $( '#hp-icon-repeater .hp-icon-row' ).length;
	}

	function reindexRows() {
		$( '#hp-icon-repeater .hp-icon-row' ).each( function ( i ) {
			$( this ).find( '[name]' ).each( function () {
				var name = $( this ).attr( 'name' );
				$( this ).attr( 'name', name.replace( /hp_price_icons\[\d+\]/, 'hp_price_icons[' + i + ']' ) );
			} );
		} );
	}

	function openMediaFrame( $row ) {
		var frame = wp.media( {
			title:    'Select Icon',
			button:   { text: 'Use this icon' },
			multiple: false,
			library:  { type: [ 'image', 'image/svg+xml' ] },
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var thumbUrl   = attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;

			$row.find( '.hp-icon-image-id' ).val( attachment.id );
			$row.find( '.hp-icon-image-url' ).val( attachment.url );
			$row.find( '.hp-icon-row__preview' ).html( '<img src="' + thumbUrl + '" alt="" class="hp-icon-thumb">' );
			$row.find( '.hp-icon-remove-img-btn' ).removeClass( 'hidden' );
		} );

		frame.open();
	}

	function openGalleryTopFrame( $field ) {
		var frame = wp.media( {
			title:    'Select Badge Icon',
			button:   { text: 'Use this icon' },
			multiple: false,
			library:  { type: [ 'image', 'image/svg+xml' ] },
		} );
		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var thumbUrl   = attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;
			$field.find( '.hp-gallery-top-image-id' ).val( attachment.id );
			$field.find( '.hp-gallery-top-image-url' ).val( attachment.url );
			$field.find( '.hp-gallery-top-preview' ).html( '<img src="' + thumbUrl + '" alt="" class="hp-icon-thumb">' );
			$field.find( '.hp-gallery-top-remove-btn' ).removeClass( 'hidden' );
		} );
		frame.open();
	}

	function openGalleryBotFrame( $row ) {
		var frame = wp.media( {
			title:    'Select Icon',
			button:   { text: 'Use this icon' },
			multiple: false,
			library:  { type: [ 'image', 'image/svg+xml' ] },
		} );
		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var thumbUrl   = attachment.sizes && attachment.sizes.thumbnail
				? attachment.sizes.thumbnail.url
				: attachment.url;
			$row.find( '.hp-gbot-image-id' ).val( attachment.id );
			$row.find( '.hp-gbot-image-url' ).val( attachment.url );
			$row.find( '.hp-icon-row__preview' ).html( '<img src="' + thumbUrl + '" alt="" class="hp-icon-thumb">' );
			$row.find( '.hp-gbot-remove-img-btn' ).removeClass( 'hidden' );
		} );
		frame.open();
	}

	function reindexGalleryBot() {
		$( '#hp-gallery-bottom-repeater .hp-gallery-bottom-row' ).each( function ( i ) {
			$( this ).find( '[name]' ).each( function () {
				var name = $( this ).attr( 'name' );
				$( this ).attr( 'name', name.replace( /hp_gallery_bottom_icons\[\d+\]/, 'hp_gallery_bottom_icons[' + i + ']' ) );
			} );
		} );
	}

	$( function () {
		// ── Gallery top badge fields ─────────────────────────────────────────
		$( document ).on( 'click', '.hp-gallery-top-upload-btn', function () {
			openGalleryTopFrame( $( this ).closest( '.hp-gallery-top-field' ) );
		} );
		$( document ).on( 'click', '.hp-gallery-top-remove-btn', function () {
			var $field = $( this ).closest( '.hp-gallery-top-field' );
			$field.find( '.hp-gallery-top-image-id' ).val( '0' );
			$field.find( '.hp-gallery-top-image-url' ).val( '' );
			$field.find( '.hp-gallery-top-preview' ).html( '<span class="hp-icon-empty">No icon</span>' );
			$( this ).addClass( 'hidden' );
		} );

		// ── Gallery bottom repeater ──────────────────────────────────────────
		var $gbotRepeater = $( '#hp-gallery-bottom-repeater' );
		var $gbotAddBtn   = $( '#hp-gbot-add-row' );

		$gbotRepeater.on( 'click', '.hp-gbot-upload-btn', function () {
			openGalleryBotFrame( $( this ).closest( '.hp-gallery-bottom-row' ) );
		} );
		$gbotRepeater.on( 'click', '.hp-gbot-remove-img-btn', function () {
			var $row = $( this ).closest( '.hp-gallery-bottom-row' );
			$row.find( '.hp-gbot-image-id' ).val( '0' );
			$row.find( '.hp-gbot-image-url' ).val( '' );
			$row.find( '.hp-icon-row__preview' ).html( '<span class="hp-icon-empty">No icon</span>' );
			$( this ).addClass( 'hidden' );
		} );
		$gbotRepeater.on( 'click', '.hp-gbot-delete-row', function () {
			$( this ).closest( '.hp-gallery-bottom-row' ).remove();
			reindexGalleryBot();
			toggleGbotBtn();
		} );
		$gbotAddBtn.on( 'click', function () {
			if ( $( '#hp-gallery-bottom-repeater .hp-gallery-bottom-row' ).length >= MAX_GALLERY_BOT ) {
				return;
			}
			var i   = $( '#hp-gallery-bottom-repeater .hp-gallery-bottom-row' ).length;
			var $row = $( '<div class="hp-icon-row hp-gallery-bottom-row">' +
				'<div class="hp-icon-row__preview"><span class="hp-icon-empty">No icon</span></div>' +
				'<input type="hidden" name="hp_gallery_bottom_icons[' + i + '][image_id]"  value="0" class="hp-gbot-image-id">' +
				'<input type="hidden" name="hp_gallery_bottom_icons[' + i + '][image_url]" value=""  class="hp-gbot-image-url">' +
				'<div class="hp-icon-row__fields">' +
					'<button type="button" class="button hp-gbot-upload-btn">Upload Icon</button>' +
					'<button type="button" class="button button-link hidden hp-gbot-remove-img-btn">Remove Icon</button>' +
					'<label>Title<input type="text" name="hp_gallery_bottom_icons[' + i + '][title]"    value="" class="widefat" placeholder="e.g. 100% Genuine"></label>' +
					'<label>Subtitle<input type="text" name="hp_gallery_bottom_icons[' + i + '][subtitle]" value="" class="widefat" placeholder="e.g. Verified product"></label>' +
				'</div>' +
				'<button type="button" class="button button-link-delete hp-gbot-delete-row">Remove Row</button>' +
			'</div>' );
			$gbotRepeater.append( $row );
			toggleGbotBtn();
		} );
		function toggleGbotBtn() {
			var count = $( '#hp-gallery-bottom-repeater .hp-gallery-bottom-row' ).length;
			$gbotAddBtn.prop( 'disabled', count >= MAX_GALLERY_BOT );
		}
		toggleGbotBtn();

		// ── Why Buy repeater ─────────────────────────────────────────────────
		var $wbRepeater = $( '#hp-why-buy-repeater' );
		var $wbAddBtn   = $( '#hp-wb-add-row' );

		$wbRepeater.on( 'click', '.hp-wb-upload-btn', function () {
			var $row = $( this ).closest( '.hp-why-buy-row' );
			var frame = wp.media( { title: 'Select Icon', button: { text: 'Use this icon' }, multiple: false, library: { type: [ 'image', 'image/svg+xml' ] } } );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				var thumb = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
				$row.find( '.hp-wb-image-id' ).val( att.id );
				$row.find( '.hp-wb-image-url' ).val( att.url );
				$row.find( '.hp-icon-row__preview' ).html( '<img src="' + thumb + '" alt="" class="hp-icon-thumb">' );
				$row.find( '.hp-wb-remove-img-btn' ).removeClass( 'hidden' );
			} );
			frame.open();
		} );
		$wbRepeater.on( 'click', '.hp-wb-remove-img-btn', function () {
			var $row = $( this ).closest( '.hp-why-buy-row' );
			$row.find( '.hp-wb-image-id' ).val( '0' );
			$row.find( '.hp-wb-image-url' ).val( '' );
			$row.find( '.hp-icon-row__preview' ).html( '<span class="hp-icon-empty">No icon</span>' );
			$( this ).addClass( 'hidden' );
		} );
		$wbRepeater.on( 'click', '.hp-wb-delete-row', function () {
			$( this ).closest( '.hp-why-buy-row' ).remove();
			reindexWb();
			toggleWbBtn();
		} );
		$wbAddBtn.on( 'click', function () {
			if ( $( '#hp-why-buy-repeater .hp-why-buy-row' ).length >= MAX_WHY_BUY ) { return; }
			var i = $( '#hp-why-buy-repeater .hp-why-buy-row' ).length;
			var $row = $( '<div class="hp-icon-row hp-why-buy-row">' +
				'<div class="hp-icon-row__preview"><span class="hp-icon-empty">No icon</span></div>' +
				'<input type="hidden" name="hp_why_buy_items[' + i + '][image_id]"  value="0" class="hp-wb-image-id">' +
				'<input type="hidden" name="hp_why_buy_items[' + i + '][image_url]" value=""  class="hp-wb-image-url">' +
				'<div class="hp-icon-row__fields">' +
					'<button type="button" class="button hp-wb-upload-btn">Upload Icon</button>' +
					'<button type="button" class="button button-link hidden hp-wb-remove-img-btn">Remove Icon</button>' +
					'<label>Title<input type="text" name="hp_why_buy_items[' + i + '][title]"       value="" class="widefat" placeholder="e.g. PTA Approved"></label>' +
					'<label>Description<input type="text" name="hp_why_buy_items[' + i + '][description]" value="" class="widefat" placeholder="e.g. All our phones are officially PTA approved."></label>' +
				'</div>' +
				'<button type="button" class="button button-link-delete hp-wb-delete-row">Remove Row</button>' +
			'</div>' );
			$wbRepeater.append( $row );
			toggleWbBtn();
		} );
		function reindexWb() {
			$( '#hp-why-buy-repeater .hp-why-buy-row' ).each( function ( i ) {
				$( this ).find( '[name]' ).each( function () {
					$( this ).attr( 'name', $( this ).attr( 'name' ).replace( /hp_why_buy_items\[\d+\]/, 'hp_why_buy_items[' + i + ']' ) );
				} );
			} );
		}
		function toggleWbBtn() {
			$wbAddBtn.prop( 'disabled', $( '#hp-why-buy-repeater .hp-why-buy-row' ).length >= MAX_WHY_BUY );
		}
		toggleWbBtn();

		// ── Price icons repeater ─────────────────────────────────────────────
		var $repeater = $( '#hp-icon-repeater' );
		var $addBtn   = $( '#hp-icon-add-row' );

		// Upload icon per row.
		$repeater.on( 'click', '.hp-icon-upload-btn', function () {
			openMediaFrame( $( this ).closest( '.hp-icon-row' ) );
		} );

		// Remove icon image.
		$repeater.on( 'click', '.hp-icon-remove-img-btn', function () {
			var $row = $( this ).closest( '.hp-icon-row' );
			$row.find( '.hp-icon-image-id' ).val( '0' );
			$row.find( '.hp-icon-image-url' ).val( '' );
			$row.find( '.hp-icon-row__preview' ).html( '<span class="hp-icon-empty">No icon</span>' );
			$( this ).addClass( 'hidden' );
		} );

		// Delete row.
		$repeater.on( 'click', '.hp-icon-delete-row', function () {
			$( this ).closest( '.hp-icon-row' ).remove();
			reindexRows();
			toggleAddBtn();
		} );

		// Add row.
		$addBtn.on( 'click', function () {
			if ( $( '#hp-icon-repeater .hp-icon-row' ).length >= MAX_ROWS ) {
				return;
			}
			var i = getRowIndex();
			var $row = $( '<div class="hp-icon-row">' +
				'<div class="hp-icon-row__preview"><span class="hp-icon-empty">No icon</span></div>' +
				'<input type="hidden" name="hp_price_icons[' + i + '][image_id]"  value="0" class="hp-icon-image-id">' +
				'<input type="hidden" name="hp_price_icons[' + i + '][image_url]" value=""  class="hp-icon-image-url">' +
				'<div class="hp-icon-row__fields">' +
					'<button type="button" class="button hp-icon-upload-btn">Upload Icon</button>' +
					'<button type="button" class="button button-link hidden hp-icon-remove-img-btn">Remove Icon</button>' +
					'<label>Title<input type="text" name="hp_price_icons[' + i + '][title]"    value="" class="widefat" placeholder="e.g. PTA Approved"></label>' +
					'<label>Subtitle<input type="text" name="hp_price_icons[' + i + '][subtitle]" value="" class="widefat" placeholder="e.g. All devices are PTA approved"></label>' +
				'</div>' +
				'<button type="button" class="button button-link-delete hp-icon-delete-row">Remove Row</button>' +
			'</div>' );
			$repeater.append( $row );
			toggleAddBtn();
		} );

		function toggleAddBtn() {
			var count = $( '#hp-icon-repeater .hp-icon-row' ).length;
			$addBtn.prop( 'disabled', count >= MAX_ROWS );
		}

		toggleAddBtn();
	} );
} )( jQuery );
