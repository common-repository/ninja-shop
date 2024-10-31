/**
 * jQuery used by the Billing Address Purchase Requirement on the Checkout Page
 *
*/
jQuery( function() {
	itExchangeInitBillingAddressJS();
});

function itExchangeInitBillingAddressJS() {
	// Switch to edit address view when link is clicked
	jQuery(document).on('click', 'a.ninja-shop-purchase-requirement-edit-billing', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-billing-address-options').addClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-billing-address-edit').removeClass('ninja-shop-hidden');
	});

	// Switch to existing address view when clancel link is clicked
	jQuery(document).on('click', 'a.ninja-shop-billing-address-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-billing-address-options').removeClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-billing-address-edit').addClass('ninja-shop-hidden');
	});

	// Init country state sync
	var iteCountryStatesSyncOptions = {
		statesWrapper     : '.ninja-shop-state',
		stateFieldID      : '#ninja-shop-billing-address-state',
		templatePart      : 'content-checkout/elements/purchase-requirements/billing-address/elements/state',
		autoCompleteState : true
	};
	jQuery('#ninja-shop-billing-address-country').itCountryStatesSync(iteCountryStatesSyncOptions);


    // Enable Autocomplete on country and state
    jQuery('#ninja-shop-billing-address-country').selectToAutocomplete();
    jQuery('#ninja-shop-billing-address-state').selectToAutocomplete();

	jQuery(document).on( 'change', '.ninja-shop-checkout-billing-address-purchase-requirement input[type=radio][name=saved_address]', function() {
		if ( this.value == 0 ) {
			jQuery( '.ninja-shop-checkout-address-form--billing' ).show();
		} else {
			jQuery( '.ninja-shop-checkout-address-form--billing' ).hide();
		}
	} );

}

jQuery(document).on('itExchangeCheckoutReloaded', function(){
	itExchangeInitBillingAddressJS();
});
