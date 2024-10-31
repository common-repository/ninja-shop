/**
 * jQuery used by the Shipping Address Purchase Requirement on the Checkout Page
 *
 */
jQuery(document).ready( function () {
	itExchangeInitShippingJSForCheckout();
	itExchangeInitShippingJSForCheckoutMethodHandler();
} );

function itExchangeInitShippingJSForCheckout() {
	// Switch to edit address view when link is clicked
	jQuery( document ).on( 'click', 'a.ninja-shop-purchase-requirement-edit-shipping', function ( event ) {
		event.preventDefault();
		jQuery( '.checkout-purchase-requirement-shipping-address-options' ).addClass( 'ninja-shop-hidden' );
		jQuery( '.checkout-purchase-requirement-shipping-address-edit' ).removeClass( 'ninja-shop-hidden' );
	} );

	// Switch to existing address view when clancel link is clicked
	jQuery( document ).on( 'click', 'a.ninja-shop-shipping-address-requirement-cancel', function ( event ) {
		event.preventDefault();
		jQuery( '.checkout-purchase-requirement-shipping-address-options' ).removeClass( 'ninja-shop-hidden' );
		jQuery( '.checkout-purchase-requirement-shipping-address-edit' ).addClass( 'ninja-shop-hidden' );
	} );

	// Init country state sync
	var iteCountryStatesSyncOptions = {
		statesWrapper    : '.ninja-shop-state',
		stateFieldID     : '#ninja-shop-shipping-address-state',
		templatePart     : 'content-checkout/elements/purchase-requirements/shipping-address/elements/state',
		autoCompleteState: true
	};
	jQuery( '#ninja-shop-shipping-address-country' ).itCountryStatesSync( iteCountryStatesSyncOptions );

	// Enable Autocomplete on country and state
	jQuery( '#ninja-shop-shipping-address-country' ).selectToAutocomplete();
	jQuery( '#ninja-shop-shipping-address-state' ).selectToAutocomplete();

	jQuery(document).on( 'change', '.ninja-shop-checkout-shipping-address-purchase-requirement input[type=radio][name=saved_address]', function() {
		if ( this.value == 0 ) {
			jQuery( '.ninja-shop-checkout-address-form--shipping' ).show();
		} else {
			jQuery( '.ninja-shop-checkout-address-form--shipping' ).hide();
		}
	} );
}

function itExchangeInitShippingJSForCheckoutMethodHandler() {

	// Save value and reload checkout page when shipping method is changed
	jQuery( '#ninja-shop-cart .ninja-shop-shipping-method-select' ).unbind();
	jQuery( document ).on( 'change', '#ninja-shop-cart .ninja-shop-shipping-method-select', function ( event ) {
		event.preventDefault();

		var value = jQuery( this ).val();
		jQuery.post( ITExchangeCheckoutRefreshAjaxURL, { 'shipping-method': value }, function ( response ) {
			if ( response ) {
				jQuery( '#ninja-shop-cart' ).replaceWith( response );
				jQuery.event.trigger( {
					type: "itExchangeCheckoutReloaded"
				} );
			}
		} );
	} );

	// Save value and reload checkout page when multiple methods shipping method is changed for a product
	jQuery( '.ninja-shop-multiple-shipping-methods-select' ).unbind();
	jQuery( document ).on( 'change', '.ninja-shop-multiple-shipping-methods-select', function ( event ) {
		event.preventDefault();

		var value = jQuery( this ).val();
		var cartProductID = jQuery( this ).data( 'ninja-shop-product-cart-id' );

		jQuery.post( ITExchangeCheckoutRefreshAjaxURL, {
			'cart-product-id': cartProductID,
			'shipping-method': value
		}, function ( response ) {
			if ( response ) {
				jQuery( '#ninja-shop-cart' ).replaceWith( response );
				jQuery.event.trigger( {
					type: "itExchangeCheckoutReloaded"
				} );
			}
		} );
	} );
}

jQuery( document ).on( 'itExchangeCheckoutReloaded', function () {
	itExchangeInitShippingJSForCheckout();
} );
