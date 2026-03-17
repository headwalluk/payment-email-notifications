/**
 * Payment Email Notifications - Admin scripts.
 *
 * @since 0.1.0
 */

/* global jQuery, penAdmin */

( function ( $ ) {
	'use strict';

	// Test email button handler.
	$( document ).on( 'click', '.pen-test-email', function () {
		var $button      = $( this );
		var definitionId = $button.data( 'definition-id' );
		var recipient    = window.prompt( penAdmin.promptMessage );

		if ( ! recipient ) {
			return;
		}

		$button.prop( 'disabled', true ).text( penAdmin.sendingText );

		$.post( penAdmin.ajaxUrl, {
			action:        penAdmin.testAction,
			nonce:         penAdmin.testNonce,
			definition_id: definitionId,
			recipient:     recipient
		} ).done( function ( response ) {
			if ( response.success ) {
				window.alert( response.data.message );
			} else {
				window.alert( response.data.message );
			}
		} ).fail( function () {
			window.alert( 'Request failed.' );
		} ).always( function () {
			$button.prop( 'disabled', false ).text( penAdmin.testText );
		} );
	} );

	// Token pill click-to-copy handler.
	$( document ).on( 'click', '.pen-token-pill', function () {
		var $pill = $( this );
		var token = $pill.data( 'token' );

		navigator.clipboard.writeText( token ).then( function () {
			// Remove any existing tooltip on this pill.
			$pill.find( '.pen-copied-tooltip' ).remove();

			var $tooltip = $( '<span class="pen-copied-tooltip">Copied!</span>' );
			$pill.append( $tooltip );

			setTimeout( function () {
				$tooltip.remove();
			}, 1500 );
		} );
	} );

	// Recipient type toggle for custom email field.
	$( document ).on( 'change', '#pen_recipient', function () {
		var $custom = $( '#pen_recipient_custom' );

		if ( 'custom' === $( this ).val() ) {
			$custom.show().trigger( 'focus' );
		} else {
			$custom.hide();
		}
	} );
} )( jQuery );
