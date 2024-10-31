(function ( $ ) {

	var orderNotesForm = $( ".ninja-shop-customer-order-notes-form" );
	var orderNotesSummary = $( ".ninja-shop-customer-order-notes-summary" );

	$( ".ninja-shop-edit-customer-order-notes" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.addClass("ninja-shop-hidden");
		orderNotesForm.removeClass("ninja-shop-hidden");
	} );

	$( ".ninja-shop-customer-order-note-cancel" ).click( function ( e ) {
		e.preventDefault();

		orderNotesSummary.removeClass("ninja-shop-hidden");
		orderNotesForm.addClass("ninja-shop-hidden");
	} );

	$( document ).on( 'click', '.ninja-shop-checkout-transaction-methods form[data-type="iframe"] .ninja-shop-purchase-button', function ( e ) {
		e.preventDefault();

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		if ( !$selector.length ) {
			launchIFrame( $form );

			return;
		}

		$( '.ninja-shop-checkout-transaction-methods form, .ninja-shop-purchase-dialog-trigger' ).not( $form ).hide();
		$this.hide();
		$selector.show();
	} );

	$( document ).on( 'change', '.ninja-shop-payment-tokens-selector--list input[type="radio"]', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		if ( $this.val() !== 'new_method' ) {
			return;
		}

		launchIFrame( $form, $selector );
	} );

	$( document ).on( 'click', '.ninja-shop-checkout-cancel-complete', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		e.preventDefault();

		if ( !$selector.length ) {
			return;
		}

		$( '.ninja-shop-checkout-transaction-methods form, .ninja-shop-purchase-dialog-trigger' ).show();
		$( '.ninja-shop-purchase-button', $form ).show();
		$selector.hide();
	} );

	/**
	 * Launch the payment iFrame.
	 *
	 * @param {*} $form
	 * @param {*} [$selector]
	 */
	function launchIFrame( $form, $selector ) {

		var gateway = $form.data( 'gateway' );
		var deferred = $.Deferred();
		itExchange.hooks.doAction( 'iFramePurchaseBegin.' + gateway, deferred );

		deferred.done( function ( data ) {

			if ( data.cancelled ) {
				if ( $selector ) {
					$( 'input[type="radio"]:first', $selector ).prop( 'checked', true );
				}

				return;
			} else if ( data.tokenize ) {
				$form.append( $( '<input type="hidden" name="to_tokenize">' ).val( data.tokenize ) );
			} else if ( data.one_time_token ) {
				$form.append( $( '<input type="hidden" name="one_time_token">' ).val( data.one_time_token ) );
			}

			$form.submit();
		} );
		deferred.fail( function ( message ) {
			alert( message );
		} );
	}

})( jQuery );
