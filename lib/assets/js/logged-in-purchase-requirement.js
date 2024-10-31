jQuery( function() {
	// Switch to login view when login link is clicked
	jQuery(document).on('click', 'a.ninja-shop-login-requirement-login', function(event) {
		event.preventDefault();
		jQuery('.ninja-shop-logged-in-purchase-requirement-link-div').removeClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login-options').addClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('ninja-shop-hidden');
		jQuery('.ninja-shop-content-checkout-logged-in-purchase-requirement-login-link').addClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login').removeClass('ninja-shop-hidden');

		// Toggle login/register notices.
		jQuery('.ninja-shop-login-requirement-login-wrapper').addClass( 'ninja-shop-hidden');
		jQuery('.ninja-shop-login-requirement-registration-wrapper').removeClass( 'ninja-shop-hidden');
	});

	// Switch to registration view when register link is clicked
	jQuery(document).on('click', 'a.ninja-shop-login-requirement-registration', function(event) {
		event.preventDefault();
		jQuery('.ninja-shop-logged-in-purchase-requirement-link-div').removeClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login-options').addClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass('ninja-shop-hidden');
		jQuery('.ninja-shop-content-checkout-logged-in-purchase-requirement-register-link').addClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-registration').removeClass('ninja-shop-hidden');

		// Toggle login/register notices.
		jQuery('.ninja-shop-login-requirement-registration-wrapper').addClass( 'ninja-shop-hidden');
		jQuery('.ninja-shop-login-requirement-login-wrapper').removeClass( 'ninja-shop-hidden');
	});

	// Switch to login options view when clancel link is clicked
	jQuery(document).on('click', 'a.ninja-shop-login-requirement-cancel', function(event) {
		event.preventDefault();
		jQuery('.checkout-purchase-requirement-login-options').removeClass( 'ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-login').addClass('ninja-shop-hidden');
		jQuery('.checkout-purchase-requirement-registration').addClass('ninja-shop-hidden');
	});
});
