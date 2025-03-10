<?php
/**
 * Enqueues Guest Checkout SW JS
 *
 * 
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_sw_js() {
	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/super-widget.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-sw', $file, array( 'it-exchange-super-widget' ), false, true );
}
add_action( 'ninja_shop_enqueue_super_widget_scripts', 'it_exchange_guest_checkout_enqueue_sw_js' );

/**
 * Enqueues the checkout page scripts
 *
 *
 *
 * @return void
*/
function it_exchange_guest_checkout_enqueue_checkout_scripts() {
	if ( ! it_exchange_is_page( 'checkout' ) )
		return;

	$file = ITUtility::get_url_from_file( dirname( __FILE__ ) . '/assets/js/checkout.js' );
	wp_enqueue_script( 'it-exchange-guest-checkout-checkout-page', $file, array( 'jquery' ), false, true );
}
add_action( 'wp_enqueue_scripts', 'it_exchange_guest_checkout_enqueue_checkout_scripts' );

/**
 * Init Guest Checkout Registration/Login via email
 *
 *
 *
 * @return void
*/
function it_exchange_guest_checkout_init_login() {
	if ( empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		return;
	}

	$customer_email = sanitize_email( $_POST['email'] );

	it_exchange_init_guest_checkout_session( $customer_email );
}
add_action( 'template_redirect', 'it_exchange_guest_checkout_init_login' );

/**
 * Continues the guest checkout session or ends it based on timeout
 *
 *
 *
 * @return void
*/
function it_exchange_handle_guest_checkout_session() {

	// Abandon if also initing. We have another function hooked to template_redirect for that.
	if ( ! empty( $_POST['it-exchange-init-guest-checkout'] ) )
		return;

	$guest_session = it_exchange_get_cart_data( 'guest-checkout' );
	$guest_session = empty( $guest_session ) ? false : reset( $guest_session );

	// IF we don't have a guest session, return
	if ( ! $guest_session ) {
		return;
	}

	it_exchange_guest_checkout_bump_session();
}
add_action( 'template_redirect', 'it_exchange_handle_guest_checkout_session', 9 );
add_action( 'ninja_shop_super_widget_ajax_top', 'it_exchange_handle_guest_checkout_session', 9 );

/**
 * Save the billing address to the guest checkout session for BC.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function it_exchange_guest_checkout_deprecated_billing_address_shipping( ITE_Cart $cart ) {

	if ( $cart->is_current() && it_exchange_doing_guest_checkout() ) {
		it_exchange_update_cart_data( 'guest-billing-address', $cart->get_billing_address()->to_array() );
	}
}

add_action( 'ninja_shop_set_cart_billing_address', 'ite_save_main_billing_address_on_current_update' );

/**
 * Save the shipping address to the guest checkout session for BC.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function it_exchange_guest_checkout_deprecated_shipping_address_shipping( ITE_Cart $cart ) {

	if ( $cart->is_current() && it_exchange_doing_guest_checkout() ) {
		it_exchange_update_cart_data( 'guest-shipping-address', $cart->get_shipping_address()->to_array() );
	}
}

add_action( 'ninja_shop_set_cart_shipping_address', 'ite_save_main_shipping_address_on_current_update' );

/**
 * Returns the customer id for a guest transaction
 *
 *
 *
 * @param string $id          the id passed through from the WP filter
 * @param mixed  $transaction the id or the object
 *
 * @return int|string
*/
function it_exchange_get_guest_checkout_transaction_id( $id, $transaction ) {
	$transaction = it_exchange_get_transaction( $transaction );

	if ( ! $transaction->is_guest_purchase() ) {
		return $id;
	}

	return $transaction->customer_email;
}
add_filter( 'ninja_shop_get_transaction_customer_id', 'it_exchange_get_guest_checkout_transaction_id', 10, 2 );

/**
 * Do not print link to customer details on payment transactions admin page
 *
 *
 *
 * @param boolean $display_link yes or no
 * @param WP_Post $wp_post      the wp post_type for the transaction
 *
 * @return boolean
*/
function it_exchange_hide_admin_customer_details_link_on_transaction_details_page( $display_link, $wp_post ) {

	if ( ! $transaction = it_exchange_get_transaction( $wp_post->ID ) ) {
		return $display_link;
	}

	if ( ! $transaction->is_guest_purchase() ) {
		return $display_link;
	}

	return false;
}
add_filter( 'ninja_shop_transaction_detail_has_customer_profile', 'it_exchange_hide_admin_customer_details_link_on_transaction_details_page', 10, 2 );

/**
 * Flag transaction object as guest checkout
 *
 *
 *
 * @param object        $transaction_object The transaction object right before being added to database
 * @param ITE_Cart|null $cart
 *
 * @return object
*/
function it_exchange_flag_transaction_as_guest_checkout( $transaction_object, ITE_Cart $cart = null ) {

	if ( ! $cart || ! $cart->is_guest() ) {
		return $transaction_object;
	}

	$transaction_object->is_guest_checkout = true;

	return $transaction_object;
}
add_filter( 'ninja_shop_generate_transaction_object', 'it_exchange_flag_transaction_as_guest_checkout', 10, 2 );

/**
 * Adds post meta to flag as guest checkout after its inserted into the DB
 *
 * So that we can filter it out of queries
 *
 *
 *
 * @param int           $transaction_id
 * @param ITE_Cart|null $cart
 *
 * @return void
*/
function it_exchange_flag_transaction_post_as_guest_checkout( $transaction_id, ITE_Cart $cart = null ) {
	$transaction = it_exchange_get_transaction( $transaction_id );

	if ( $cart && $cart->is_guest() ) {
		update_post_meta( $transaction_id, '_it-exchange-is-guest-checkout', true );

		if ( $cart && $cart->is_current() ) {
			it_exchange_set_guest_email_cookie( $transaction->get_customer_email() );
		}
	}
}
add_action( 'ninja_shop_add_transaction_success', 'it_exchange_flag_transaction_post_as_guest_checkout', 0, 2 );

/**
 * Modifies the Customer data when dealing with a guest checkout
 *
 * This modifies the feedback on the Checkout Page in the Logged-In purchse requirement
 *
 *
 *
 * @param IT_Exchange_Customer $customer the customer object
 *
 * @return IT_Exchange_Customer
*/
function it_exchange_guest_checkout_modify_customer( $customer ) {

	if ( ! it_exchange_doing_guest_checkout() ) {
		return $customer;
	}

	if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ( is_admin() && defined( 'DOING_AJAX' ) && ! DOING_AJAX ) ) {
		return $customer;
	}

	$email = it_exchange_get_cart_data( 'guest-checkout-user' );
	$email = is_array( $email ) ? reset( $email ) : $email;

	if ( ! $email ) {
		return $customer;
	}

	$customer = it_exchange_guest_checkout_generate_guest_user_object( $email, true );

	return $customer;
}

add_filter( 'ninja_shop_get_current_customer', 'it_exchange_guest_checkout_modify_customer' );

/**
 * This modifies the loginout link generated by WP when we're doing Guest Checkout
 *
 *
 *
 * @param string $url      the html for the loginout link
 * @param string $redirect the URL we're redirecting to after logged out.
 * @return string
*/
function it_exchange_guest_checkout_modify_loginout_link( $url, $redirect ) {

	if ( ! it_exchange_doing_guest_checkout() )
		return $url;

	$url = add_query_arg( array( 'it-exchange-guest-logout' => 1 ), esc_url( $redirect ) );

	return $url;
}
add_filter( 'logout_url', 'it_exchange_guest_checkout_modify_loginout_link', 10, 2 );

/**
 * Logs out a guest checkout session
 *
 *
 *
 * @return void
*/
function it_exchange_logout_guest_checkout_session() {
	if ( ( it_exchange_is_page( 'logout' ) && it_exchange_doing_guest_checkout() ) || ! empty( $_REQUEST['it-exchange-guest-logout'] ) ) {
		it_exchange_kill_guest_checkout_session();
		wp_redirect( esc_url_raw( remove_query_arg( 'it-exchange-guest-logout' ) ) );
	}
}
add_action( 'template_redirect', 'it_exchange_logout_guest_checkout_session', 1 );

/**
 * Logout Guest user after hitting confirmation page.
 *
 *
 *
 * @return void
*/
function it_exchange_logout_guest_checkout_session_on_confirmation_page() {
	if ( it_exchange_is_page( 'confirmation' ) && it_exchange_doing_guest_checkout() ) {
		it_exchange_kill_guest_checkout_session();
	}
}
add_action( 'wp_footer', 'it_exchange_logout_guest_checkout_session_on_confirmation_page' );

/**
 * Allow downloads to be served regardless of the requirement to be logged in if user checkout out as a guest
 *
 *
 *
 * @param boolean  $setting the default setting
 * @param array    $hash_data the download has data
 *
 * @return boolean
*/
function it_exchange_allow_file_downloads_for_guest_checkout( $setting, $hash_data ) {
	if ( ! $transaction = it_exchange_get_transaction( $hash_data['transaction_id'] ) )
		return $setting;

	return $transaction->is_guest_purchase() ? false : $setting;
}
add_filter( 'ninja_shop_require_user_login_for_download', 'it_exchange_allow_file_downloads_for_guest_checkout', 10, 2 );

/**
 * Clear guest session when an authentication attemp happens.
 *
 *
 *
 * @param  mixed $incoming Whatever is coming from WP hook API. We don't use it.
 *
 * @return WP_User|null
*/
function it_exchange_end_guest_checkout_on_login_attempt( $incoming ) {
	if ( it_exchange_doing_guest_checkout() ) {
		it_exchange_kill_guest_checkout_session();
	}

	return $incoming;
}
add_filter( 'authenticate', 'it_exchange_end_guest_checkout_on_login_attempt' );

/**
 * Proccesses Guest login via superwidget
 *
 *
 *
*/
function it_exchange_guest_checkout_process_ajax_login() {

	if ( empty( $_REQUEST['sw-action'] ) || 'guest-checkout' !== $_REQUEST['sw-action'] || empty( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	// Vaidate email address
	if ( ! is_email( $_POST['email'] ) ) {
		it_exchange_add_message( 'error', __( 'Please use a properly formatted email address.', 'it-l10n-ithemes-exchange' ) );
		die('0');
	}

	$customer_email = sanitize_email( $_POST['email'] );

	it_exchange_init_guest_checkout_session( $customer_email );
	die('1');
}
add_action( 'ninja_shop_processing_super_widget_ajax_guest-checkout', 'it_exchange_guest_checkout_process_ajax_login' );

/**
 * Remove the download page link in the email if this was a guest checkout transaction
 *
 *
 *
 * @param  boolean  $boolean incoming from WP Filter
 * @param  int      $id      the transaction ID
 * @return boolean
*/
function it_exchange_guest_checkout_maybe_remove_download_page_link_from_email( $boolean, $id ) {

	if ( ! $transaction = it_exchange_get_transaction( $id ) ) {
		return $boolean;
	}

	if ( ! $transaction->is_guest_purchase() ) {
		return $boolean;
	}

	$settings = it_exchange_get_option( 'addon_digital_downloads', true );

	if ( empty( $settings['require-user-login'] ) ) {
		return $boolean;
	}

	return false;
}
add_filter( 'ninja_shop_print_downlods_page_link_in_email', 'it_exchange_guest_checkout_maybe_remove_download_page_link_from_email', 10, 2 );
