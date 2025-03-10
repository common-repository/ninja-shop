/**
 * Any events need to be connected with jQuery(document).on([event], [selector], [function/callback];
 *
*/

jQuery.ajaxSetup({
    cache: false
});

jQuery( function( $ ) {

	var $sw = $('.ninja-shop-super-widget');

	// Register Clear Cart event
	$sw.on('click', 'a.ninja-shop-empty-cart', function(event) {
		event.preventDefault();
		// itExchangeSWOnProductPage is a JS var set in lib/super-widget/class.super-widget.php. It contains an ID or is false.
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
		itExchange.hooks.doAction( 'itExchangeSW.cartEmptied' );
	});

	// Register Remove Product from Cart event
	$sw.on('click', 'a.remove-cart-item', function(event) {
		event.preventDefault();
		var product  = jQuery(this).data('cart-product-id');
		itExchangeSWRemoveItemFromCart( product );
		itExchange.hooks.doAction( 'itExchangeSW.itemRemovedFromCart', product );
	});

	$sw.on('submit', 'form.ninja-shop-sw-update-cart-quantity', function(event) {
		event.preventDefault();
		jQuery( '.ninja-shop-super-widget input.product-cart-quantity', jQuery(this).closest('.ninja-shop-super-widget') ).each( function() {
			var product  = jQuery( this ).data('cart-product-id');
			var quantity = jQuery( this ).val();
			itExchangeSWUpdateQuantity(product, quantity);
			itExchange.hooks.doAction( 'itExchangeSW.updateQuantityClicked', product, quantity );
		});
		itExchangeGetSuperWidgetState( 'checkout' );
	});

	// Register View Checkout link
	$sw.on('click', 'a.ninja-shop-checkout-cart', function(event) {
		if ( ! jQuery(this).hasClass( 'no-sw-js' ) ) {
			event.preventDefault();
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
			itExchange.hooks.doAction( 'itExchangeSW.checkoutCartClicked', itExchangeSWOnProductPage );
		}
	});

	// Register Buy Now event
	$sw.on('submit', 'form.ninja-shop-sw-buy-now', function(event) {
		event.preventDefault();
		var quantity         = jQuery(this).children('.product-purchase-quantity').length ? jQuery(this).children('.product-purchase-quantity').val() : 1;
		var product          = jQuery(this).children('.buy-now-product-id').val();
		var additionalFields = itExchangeSWGetAdditionalFields( this, 'buyNow' );
		itExchangeSWBuyNow( product, quantity, additionalFields );
		itExchange.hooks.doAction( 'itExchangeSW.buyNowtClicked', quantity, product, additionalFields );
	});

	// Register Add to Cart event
	$sw.on('submit', 'form.ninja-shop-sw-add-to-cart', function(event) {
		event.preventDefault();
		var quantity         = jQuery(this).children('.product-purchase-quantity').length ? jQuery(this).children('.product-purchase-quantity').val() : 1;
		var product          = jQuery(this).children('.add-to-cart-product-id').attr('value');
		var additionalFields = itExchangeSWGetAdditionalFields( this, 'addToCart' );
		itExchangeSWAddToCart( product, quantity, additionalFields );
		itExchange.hooks.doAction( 'itExchangeSW.addToCartClicked', quantity, product, additionalFields );
	});

	// Register the edit shipping method event from the checkout view
	$sw.on('click', 'a.ninja-shop-sw-edit-shipping-method', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'shipping-method' );
		itExchange.hooks.doAction( 'itExchangeSW.editShippingMethodClicked' );
	});

	// Register the cancel shipping method event from the edit method view
	$sw.on('click', 'a.ninja-shop-super-widget-shipping-method-cancel-action', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'checkout' );
		itExchange.hooks.doAction( 'itExchangeSW.cancelEditShippingMethodClicked' );
	});

    // Register Change shipping method event
    $sw.on('change', '.ninja-shop-shipping-method-select', function(event) {
        var value = jQuery(this).val();
		itExchangeUpdateShippingMethod( value );
		itExchange.hooks.doAction( 'itExchangeSW.shippingMethodChanged', value );
    });

	/******************************************************
	 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
	 ******************************************************/
	// Register Apply Coupon event for Basic Coupons Add-on
	$sw.on('submit', 'form.ninja-shop-sw-update-cart-coupon', function(event) {
	//jQuery(document).on('submit', 'form.ninja-shop-sw-update-cart', function(event) {
		event.preventDefault();
		var coupon = jQuery('.apply-coupon', jQuery(this).closest('.ninja-shop-super-widget') ).val();
		itExchangeSWApplyCoupon(coupon);
	});

	// Register Remove Coupon event for Basic Coupons Add-on
	$sw.on('click', 'a.remove-coupon', function(event) {
		event.preventDefault();
		var coupon  = jQuery(this).data('coupon-code');
		itExchangeSWRemoveCoupon(coupon);
	});

	// Register Add / View Coupons Event
	$sw.on('click', 'a.sw-cart-focus-coupon', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'coupon' );
	});

	// Register Edit / View Quantity Event
	$sw.on('click', 'a.sw-cart-focus-quantity', function(event) {
		event.preventDefault();
		itExchangeSWViewCart( 'quantity' );
	});

	$sw.on('click', 'a.ninja-shop-sw-cancel-login-link', function(event) {
		event.preventDefault();
		if ( itExchangeSWMultiItemCart )
			if ( itExchangeSWOnProductPage )
				itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
			else
				itExchangeGetSuperWidgetState( 'cart' );
		else
			itExchangeSWEmptyCart( itExchangeSWOnProductPage );
	});

	// Register the Register Link event (switching to the register state from the login state)
	$sw.on('click', 'a.ninja-shop-sw-register-link', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'registration' );
		itExchange.hooks.doAction( 'itExchangeSW.registerLinkClicked' );
	});

	$sw.on('click', 'a.ninja-shop-sw-cancel-register-link', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'login' );
		itExchange.hooks.doAction( 'itExchangeSW.loginLinkClicked' );
	});

	// Register login form submit event
	$sw.on('submit', 'form.ninja-shop-sw-log-in', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		jQuery(this).find('.ninja-shop-login-button').find('input').hide().after('<div style="margin-top:15px;"><div style="float:left;">'+exchangeSWL10n.processingAction+'</div><div class="spinner"></div></div>');
		itExchangeSWLogIn(data);
		itExchange.hooks.doAction( 'itExchangeSW.loginRequested' );
	});

	// Register registration submit event
	$sw.on('submit', 'form.ninja-shop-sw-register', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		jQuery(this).children('#ninja-shop-register-customer').hide().after('<div><div style="float:left;">'+exchangeSWL10n.processingAction+'</div><div class="spinner"></div></div>');
		itExchangeSWRegister(data);
		itExchange.hooks.doAction( 'itExchangeSW.registerRequested' );
	});

	// Register the cancel event from the edit shipping address view
	$sw.on('click', 'a.ninja-shop-shipping-address-requirement-cancel', function(event) {
		event.preventDefault();
		if ( itExchangeCartShippingAddress ) {
			itExchangeGetSuperWidgetState( 'checkout' );
		} else {
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
		}
		itExchange.hooks.doAction( 'itExchangeSW.shippingAddressRequirementCancelLinkClicked' );
	});

	// Register the edit event from the edit shipping address  in the checkout view
	$sw.on('click', 'a.ninja-shop-sw-edit-shipping-address', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'shipping-address' );
		itExchange.hooks.doAction( 'itExchangeSW.editShippingAddressLinkClicked' );
	});

	// Register Shipping Address submit event
	$sw.on('submit', 'form.ninja-shop-sw-shipping-address', function(event) {
		event.preventDefault();
		var data = jQuery( ':input', this ).serializeArray();
		itExchangeSWShippingAddress(data);
		itExchange.hooks.doAction( 'itExchangeSW.editShippingAddressSubmitted' );
	});

	// Register the cancel event from the edit billing address view
	$sw.on('click', 'a.ninja-shop-billing-address-requirement-cancel', function(event) {
		event.preventDefault();
		if ( itExchangeCartBillingAddress )
			itExchangeGetSuperWidgetState( 'checkout' );
		else
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
	});

	// Register the edit event from the edit billing address  in the checkout view
	$sw.on('click', 'a.ninja-shop-sw-edit-billing-address', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'billing-address' );
	});

	// Register Billing Address submit event
	$sw.on('submit', 'form.ninja-shop-sw-billing-address', function(event) {
		event.preventDefault();
		data = jQuery( ':input', this ).serializeArray();
		itExchangeSWBillingAddress(data);
	});

	// Submit the purchase dialog
	$sw.on('submit', 'form.ninja-shop-sw-purchase-dialog', function(event) {
		event.preventDefault();

		var deferred = jQuery.Deferred(),
			method = jQuery( 'input[name="it-exchange-transaction-method"]', jQuery( this ) ).val(),
			hook = 'itExchangeSW.preSubmitPurchaseDialog_' + method;

		if ( itExchange.hooks.hasAction( hook ) ) {
			itExchange.hooks.doAction( hook, deferred );
		} else {
			deferred.resolve();
		}

		var $this = jQuery( this );
		var $submit = jQuery( ':submit', $this );
		$submit.data( 'old-value', $submit.val() );
		$submit.val( exchangeSWL10n.processingPaymentLabel).attr('disabled','disabled');

		deferred.done( function() {
			var data = jQuery( ':input', $this ).serializeArray();
			itExchangeSWSubmitPurchaseDialog( data );
		} ).fail( function() {
			$submit.val( $submit.data( 'old-value' ) ).removeAttr( 'disabled' );
		} );
	} );

	// Register the edit event from the edit customer order note in the checkout view
	$sw.on( 'click', 'a.ninja-shop-sw-edit-customer-order-note', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'customer-order-note' );
	});

	// Register the submit event from the edit customer order note view
	$sw.on( 'click', '.ninja-shop-customer-order-note-cancel', function(event) {
		event.preventDefault();
		itExchangeGetSuperWidgetState( 'checkout' );
	});

	// Register the cancel event from the edit customer order note view
	$sw.on( 'submit', '.ninja-shop-sw-customer-order-note-form', function(event) {
		event.preventDefault();
		itExchangeSWSubmitNote( jQuery( "#customer-order-note" ).val() );
	});

	$sw.on( 'change', 'input[type=radio][name=saved_address]', function() {
		if ( this.value == 0 ) {
			jQuery( '.ninja-shop-sw-address-form' ).show();
		} else {
			jQuery( '.ninja-shop-sw-address-form' ).hide();
		}
	} );

	$sw.on( 'click', '.payment-methods-wrapper form[data-type="iframe"] .ninja-shop-purchase-button', function ( e ) {
		e.preventDefault();

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		if ( !$selector.length ) {
			launchIFrame( $form );

			return;
		}

		$( '.payment-methods-wrapper form, .ninja-shop-purchase-dialog-trigger' ).not( $form ).hide();
		$this.hide();
		$selector.show();
	} );

	$sw.on( 'change', '.ninja-shop-payment-tokens-selector--list input[type="radio"]', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		if ( $this.val() !== 'new_method' ) {
			return;
		}

		launchIFrame( $form, $selector );
	} );

	$sw.on( 'click', '.ninja-shop-checkout-cancel-complete', function ( e ) {

		var $this = $( this ),
			$form = $this.closest( 'form' ),
			$selector = $( '.ninja-shop-payment-tokens-selector--list[data-method]', $form );

		e.preventDefault();

		if ( !$selector.length ) {
			return;
		}

		$( '.payment-methods-wrapper form, .ninja-shop-purchase-dialog-trigger' ).show();
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
});

/**
 * Loads a template part for the widget
*/
function itExchangeGetSuperWidgetState( state, product, focus ) {
	var productArg = '';
	var focusArg = '';

	// Set product if needed
	if ( product )
		productArg = '&sw-product=' + product;

	// Set focus if needed
	if ( 'coupon' == focus )
		focusArg = '&ite-sw-cart-focus=coupon';
	if ( 'quantity' == focus )
		focusArg = '&ite-sw-cart-focus=quantity';

	// Make call for new state HTML
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=get-state&state=' + state + productArg + focusArg, function(data) {
		itExchangeSWLoadState( state, data );
	});
}

/**
 * Load a State's html in the Super Widget.
 *
 *
 *
 * @param {String} state The name of the state. For example, 'checkout'.
 * @param {String} html	 The new HTML for this state.
 */
function itExchangeSWLoadState( state, html ) {
	jQuery('.ninja-shop-super-widget').filter(':visible').html(html);
	itExchangeSWState = state;

	if ( 'checkout' == state )
		itExchangeInitSWPurchaseDialogs();

	if ( 'shipping-address' == state || ( 'checkout' == state && ! itExchangeCartShippingAddress ) ) {
		var shippingSyncOptions = {
			statesWrapper: '.ninja-shop-state',
			stateFieldID:  '#ninja-shop-shipping-address-state',
			templatePart:  'super-widget-shipping-address/elements/state',
			autoCompleteState: true
		};
		itExchangeInitSWCountryStateSync('#ninja-shop-shipping-address-country', shippingSyncOptions);
	}

	if ( 'billing-address' == state || ( 'checkout' == state && ! itExchangeCartBillingAddress ) ) {
		var billingSyncOptions = {
			statesWrapper: '.ninja-shop-state',
			stateFieldID:  '#ninja-shop-billing-address-state',
			templatePart:  'super-widget-billing-address/elements/state',
			autoCompleteState: true
		};
		itExchangeInitSWCountryStateSync('#ninja-shop-billing-address-country', billingSyncOptions);
	}
	itExchange.hooks.doAction( 'itExchangeSW.stateUpdated' );
}

/**
 * Get additional fields.
 *
 *
 *
 * @param form
 * @param ns
 *
 * @returns {*}
 */
function itExchangeSWGetAdditionalFields( form, ns ) {

	var inputs = jQuery( ':input', form ).serializeArray();

	return itExchange.hooks.applyFilters( 'SWAdditionalFields.' + ns, inputs );
}

/**
 * Build a query string for additional fields.
 *
 *
 *
 * @param {Array} fields
 * @param {Array} [additionalBlacklist] Optionally, specify additional fields to blacklist.
 *
 * @returns {String}
 */
function itExchangeSWFormatAdditionalFields( fields, additionalBlacklist ) {

	var blacklist = ( additionalBlacklist || [] ) + ['it-exchange-action', '_wpnonce', '_wp_http_referer'];
	var qs = '';

	for ( var i =0; i < fields.length; i++ ) {
		var field = fields[i];

		if ( typeof field.name != 'undefined' && typeof field.value != 'undefined' && blacklist.indexOf(field.name) === -1 ) {
			qs += '&' + field.name + '=' + field.value;
		}
	}

	return qs;
}

/**
 * Makes an ajax request to buy a product and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWBuyNow( product, quantity, additionalFields ) {
	var additionalFieldsString = itExchangeSWFormatAdditionalFields( additionalFields, ['it-exchange-buy-now' ] );
	var url = itExchangeSWAjaxURL+'&sw-action=buy-now&get-state=checkout&sw-product=' + product + '&sw-quantity=' + quantity + additionalFieldsString;

	jQuery.get( url, function(data) {
		itExchangeSWLoadState( 'checkout', data );

		itExchange.hooks.doAction( 'itExchangeSW.BuyNow' );
	});
}

/**
 * Makes an ajax request to add a product to the cart and cycle through to the checkout
 * We force users to be logged-in before seeing the cart. This is also checked on the AJAX script to prevent URL hacking via direct access.
*/
function itExchangeSWAddToCart( product, quantity, additionalFields ) {

	var additionalFieldsString = itExchangeSWFormatAdditionalFields( additionalFields, ['it-exchange-add-product-to-cart' ] );
	var state = itExchangeSWMultiItemCart ? 'cart' : 'checkout';
	var url  = itExchangeSWAjaxURL+'&sw-action=add-to-cart&sw-product=' + product + '&sw-quantity=' + quantity + '&get-state=' + state + additionalFieldsString;

	jQuery.get( url, function(data) {
		itExchangeSWLoadState( state, data );
		itExchange.hooks.doAction( 'itExchangeSW.addToCart' );
	});
}

/**
 * Makes an ajax request that changes the selected shipping method and then refresh the shipping-method state
*/
function itExchangeUpdateShippingMethod( value ) {
	var url = itExchangeSWAjaxURL+'&sw-action=update-shipping-method&sw-shipping-method=' + value;

	jQuery.get( url, function(response) {
		if ( response )
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		else
			itExchangeGetSuperWidgetState( 'shipping-method' );

		itExchange.hooks.doAction( 'itExchangeSW.UpdateShippingMethod' );
	});
}

/******************************************************
 * COUPON CALLS WILL NEED TO MOVE TO COUPON ADDON CODE *
 ******************************************************/
/**
 * Makes AJAX request to Apply a coupon to the cart
*/
function itExchangeSWApplyCoupon(coupon) {

	var url = itExchangeSWAjaxURL + '&sw-action=apply-coupon&get-state=checkout&sw-coupon-type=cart&sw-coupon-code=' + coupon;

	jQuery.get(url , function(data) {
		if ( 'levelup' == data ) {
			jQuery('.ninja-shop-super-widget').filter(':visible').html(
				'<div class="nes-super-widget"><iframe width="420" height="315" src="//www.youtube.com/embed/4gWQn0Qo1Bo?autoplay=1&start=17" frameborder="0" allowfullscreen></iframe></div>'
			);
			setTimeout( function() { itExchangeGetSuperWidgetState( 'checkout' ) }, 25000 );
		} else {
			itExchangeSWLoadState( 'checkout', data );
		}

		itExchange.hooks.doAction( 'itExchangeSW.applyCoupon', coupon );
	});
}
/**
 * Remove a coupon from the cart
*/
function itExchangeSWRemoveCoupon(coupon) {
	var url = itExchangeSWAjaxURL+'&sw-action=remove-coupon&get-state=checkout&sw-coupon-type=cart&sw-coupon-code=' + coupon;

	jQuery.get( url, function(data) {
		itExchangeSWLoadState( 'checkout', data );

		itExchange.hooks.doAction( 'itExchangeSW.removeCoupon', coupon );
	});
}


/**
 * Changes view back to cart with an optional focus on the coupons or the quantity
*/
function itExchangeSWViewCart(focus) {
	itExchangeGetSuperWidgetState( 'cart', false, focus );
}

/**
 * Makes an ajax request to empty the cart
*/
function itExchangeSWEmptyCart( product ) {

	var state = itExchangeSWOnProductPage ? 'product' : 'cart';
	var url = itExchangeSWAjaxURL+'&sw-action=empty-cart&get-state=' + state;

	if ( state === 'product' ) {
		url += '&sw-product=' + itExchangeSWOnProductPage;
	}

	jQuery.get( url, function(data) {
		itExchangeSWLoadState( state, data );

		itExchange.hooks.doAction( 'itExchangeSWEmptyCart' );
	});
}

/**
 * Makes an ajax request to remove an item from the cart
*/
function itExchangeSWRemoveItemFromCart( product ) {

	var state;

	if ( itExchangeSWMultiItemCart ) {
		if ( itExchangeSWOnProductPage ) {
			state = 'product';
		} else {
			state = 'cart';
		}
	} else {
		state = 'checkout';
	}

	var url = itExchangeSWAjaxURL+'&sw-action=remove-from-cart&sw-cart-product=' + product + '&get-state=' + state;

	if ( state === 'product' ) {
		url += '&sw-product=' + itExchangeSWOnProductPage;
	}

	jQuery.get( url, function( data ) {
		itExchangeSWLoadState( state, data );

		itExchange.hooks.doAction( 'itExchangeSW.removeItemFromCart', product );
	});
}

/**
 * Update Quantity
*/
function itExchangeSWUpdateQuantity(product, quantity) {
	jQuery.get( itExchangeSWAjaxURL+'&sw-action=update-quantity&sw-cart-product=' + product + '&sw-quantity=' + quantity );
}

/**
 * Log the user in
*/
function itExchangeSWLogIn(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=login', data, function(data) {
		if ( '0' === data ) {
			itExchangeIsUserLoggedIn = '';
			itExchangeGetSuperWidgetState( 'login' );
		} else {
			itExchangeIsUserLoggedIn = '1';
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );

			itExchange.hooks.doAction( 'itExchangeSW.userLoggedIn' );
		}
	});
}

/**
 * Register a new users
*/
function itExchangeSWRegister(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=register', data, function(data) {
		if ( '0' === data ) {
			itExchangeGetSuperWidgetState('registration');
		} else {
			itExchangeGetSuperWidgetState('checkout', itExchangeSWOnProductPage);

			itExchange.hooks.doAction( 'itExchangeSW.userRegistered' );
		}
	});
}

/**
 * Update the shipping address
*/
function itExchangeSWShippingAddress(data) {
	var url = itExchangeSWAjaxURL+'&sw-action=update-shipping&get-state=checkout&sw-product' + itExchangeSWOnProductPage;
	jQuery.post( url, data, function(data) {
		itExchangeSWLoadState( 'checkout', data );
	});
}

/**
 * Update the billing address
*/
function itExchangeSWBillingAddress(data) {
	var url = itExchangeSWAjaxURL+'&sw-action=update-billing&get-state=checkout&sw-product' + itExchangeSWOnProductPage;
	jQuery.post( url, data, function(data) {
		itExchangeSWLoadState( 'checkout', data );
	});
}

/**
 * Submit the purchase dialog
*/
function itExchangeSWSubmitPurchaseDialog(data) {
	jQuery.post( itExchangeSWAjaxURL+'&sw-action=submit-purchase-dialog', data, function(response) {
		if ( response ) {
			window.location = response;
		} else {
			itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
			itExchangeInitSWPurchaseDialogs();
		}
	}).fail( function() {
		itExchangeGetSuperWidgetState( 'checkout', itExchangeSWOnProductPage );
		itExchangeInitSWPurchaseDialogs();
	});
}

/**
 * Submit the order note from the customer.
 *
 *
 *
 * @param note
 */
function itExchangeSWSubmitNote( note ) {

	var url = itExchangeSWAjaxURL + '&sw-action=customer-order-note&get-state=checkout&sw-product=' + itExchangeSWOnProductPage;

	jQuery.post( url, {note: note}, function( response ) {
		itExchangeSWLoadState( 'checkout', response );
	});
}

/**
 * Init purchase dialog JS
*/
function itExchangeInitSWPurchaseDialogs() {

	// Hide all dialogs
	jQuery('.ninja-shop-purchase-dialog' ).hide();

	// Open dialogs when triggers are clicked
	jQuery( '.ninja-shop-purchase-dialog-trigger' ).on( 'click', function(event) {
		event.preventDefault();
		var addon_slug = jQuery(this).data('addon-slug');
		jQuery('.ninja-shop-purchase-dialog-trigger', jQuery(this).closest('.ninja-shop-super-widget')).hide();
		jQuery('form', jQuery(this).closest('.payment-methods-wrapper')).not('.ninja-shop-purchase-dialog-' + addon_slug).hide();
		jQuery('.ninja-shop-purchase-dialog-' + addon_slug, jQuery(this).closest('.payment-methods-wrapper') ).show();
	});

	// Open any dialog that has errors, hide the rest of the buttons
	jQuery('.ninja-shop-purchase-dialog-trigger').filter('.has-errors').trigger('click');

	// Cancel
	jQuery( '.ninja-shop-purchase-dialog-cancel' ).on( 'click', function(event) {
		event.preventDefault();
		jQuery('.ninja-shop-purchase-dialog', jQuery(this).closest('.ninja-shop-super-widget') ).hide();
		jQuery('.ninja-shop-purchase-dialog-trigger', jQuery(this).closest('.ninja-shop-super-widget')).show();
		jQuery('form', '.payment-methods-wrapper', jQuery(this).closest('.ninja-shop-super-widget')).show();
	});

	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-month"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-expiration-year"]' ).payment( 'restrictNumeric' );
	jQuery( 'input[name="it-exchange-purchase-dialog-cc-code"]' ).payment( 'formatCardCVC' );

	var ccNumbers = jQuery( 'input[name="it-exchange-purchase-dialog-cc-number"]' );
	ccNumbers.payment( 'formatCardNumber' );

	ccNumbers.each(function() {

		var $this = jQuery( this );

		$this.it_exchange_detect_credit_card_type({
			'element' : '#' + $this.attr('id')
		});
	});
}

/**
 * Inits the sync plugin for country/state fields for billing address
*/
function itExchangeInitSWCountryStateSync(countryElement, options) {
	jQuery(countryElement, jQuery('.ninja-shop-super-widget').filter(':visible')).itCountryStatesSync(options).selectToAutocomplete();
	jQuery(options.stateFieldID, jQuery('.ninja-shop-super-widget').filter(':visible')).selectToAutocomplete();
}
