jQuery( function() {
	// Switch to guest checkout view when guest checkout link is clicked
	jQuery(document).on('click', 'a.ninja-shop-login-requirement-guest-checkout', function(event) {
		event.preventDefault();
		jQuery('.ninja-shop-logged-in-purchase-requirement-link-div').removeClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('ninja-shop-hidden');
		jQuery('.ninja-shop-content-checkout-logged-in-purchase-requirement-guest-checkout-link').addClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-guest-checkout').removeClass('ninja-shop-hidden');
	});

	jQuery(document).on('click', 'a.ninja-shop-button', function(event) {
		event.preventDefault();
		console.log(this);
		if ( ! jQuery(this).hasClass( 'ninja-shop-login-requirement-guest-checkout') )
			jQuery('.checkout-purchase-requirement-guest-checkout').addClass('ninja-shop-hidden');
	});
});
