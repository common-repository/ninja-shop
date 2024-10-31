/**
 * This gets loaded on the checkout page.
 */
// Bind to page load
jQuery( document ).ready( 'itExchangeInitPurchaseDialogs' );

// Bind our init to the custom jQuery trigger that fires when the Checkout page is reloaded.
jQuery( document ).on( 'itExchangeCheckoutReloaded', itExchangeInitPurchaseDialogs );

// Function to init
function itExchangeInitPurchaseDialogs() {
	// Hide all dialogs
	jQuery( '.ninja-shop-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	jQuery( '.ninja-shop-purchase-dialog-trigger' ).on( 'click', function ( event ) {
		event.preventDefault();
		var addon_slug = jQuery( this ).data( 'addon-slug' );
		jQuery( '.ninja-shop-purchase-dialog-trigger' ).hide();
		jQuery( 'form', '.ninja-shop-checkout-transaction-methods' ).hide();
		jQuery( 'form', '.ninja-shop-purchase-dialog-' + addon_slug ).show();
		jQuery( '.ninja-shop-purchase-dialog-' + addon_slug ).show();
	} );

	// Open any dialog that has errors, hide the rest of the buttons
	jQuery( '.ninja-shop-purchase-dialog-trigger' ).filter( '.has-errors' ).trigger( 'click' );

	// Cancel
	jQuery( '.ninja-shop-purchase-dialog-cancel' ).on( 'click', function ( event ) {
		event.preventDefault();
		jQuery( '.ninja-shop-purchase-dialog' ).hide();
		jQuery( '.ninja-shop-purchase-dialog-trigger' ).show();
		jQuery( 'form', '.ninja-shop-checkout-transaction-methods' ).show();
	} );

	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-month"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-year"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-code"]' ).payment( 'formatCardCVC' );

	var ccNumbers = jQuery( 'input[name="it-exchange-purchase-dialog-cc-number"]' );
	ccNumbers.payment( 'formatCardNumber' );

	ccNumbers.each( function () {

		var $this = jQuery( this );

		$this.it_exchange_detect_credit_card_type( {
			'element': '#' + $this.attr( 'id' )
		} );
	} );

	jQuery( document ).on( 'click', '.ninja-shop-payment-tokens-selector--list input', function () {
		var $this = jQuery( this );
		var $visualCC = jQuery( '.ninja-shop-visual-cc-wrap', $this.closest( '.ninja-shop-purchase-dialog' ) );

		if ( $this.is( ':checked' ) && $this.val() === 'new_method' ) {
			$visualCC.show();
		} else {
			$visualCC.hide();
		}
	} );
}

// Finally, since its printed half way through - call it as well.
itExchangeInitPurchaseDialogs();
