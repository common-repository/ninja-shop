<?php

/**
 * Shopping cart class.
 *
 * @package IT_Exchange
 */
class IT_Exchange_Shopping_Cart {

	/**
	 * Class constructor.
	 *
	 * Hooks default filters and actions for cart
	 *
	 *
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'check_requested_cart_auth' ), 0 );
		add_action( 'template_redirect', array( $this, 'handle_ninja_shop_cart_function' ) );
		add_filter( 'ninja_shop_process_transaction', array( $this, 'handle_purchase_cart_request' ), 10, 2 );
		add_action( 'template_redirect', array( $this, 'prepare_for_purchase' ) );
		add_action( 'template_redirect', array( $this, 'convert_feedback_to_notices' ) );
		add_action( 'ninja_shop_update_cart', array( $this, 'convert_feedback_to_notices' ), 100 );
		add_action( 'ninja_shop_add_transaction_success', array( $this, 'clear_cart_meta_session_on_transaction' ), 10, 2 );
		add_action( 'wp_login', array( $this, 'merge_session' ), 10, 2 );

		ITE_Session_Model::updated( array( $this, 'fire_deprecated_cache_cart' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	public function IT_Exchange_Shopping_Cart() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Check that the requested cart auth is valid.
	 *
	 * If not, redirect to the store with an error.
	 *
	 *
	 */
	public function check_requested_cart_auth() {

		try {
			it_exchange_get_requested_cart_and_check_auth();
		} catch ( UnexpectedValueException $e ) {
			it_exchange_add_message( 'error', $e->getMessage() );

			it_exchange_redirect( it_exchange_get_page_url( 'store' ), 'fail-requested-cart-auth-check' );
			die();
		}
	}

	/**
	 * Handles $_REQUESTs and submits them to the cart for processing
	 *
	 *
	 * @return void
	 */
	public function handle_ninja_shop_cart_function() {

		$this->redirect_checkout_if_empty_cart(); //if on checkout but have empty cart, redirect

		// Grab action and process it.
		if ( isset( $_REQUEST['it-exchange-action'] ) ) {
			call_user_func( array( $this, 'handle_' . esc_attr( $_REQUEST['it-exchange-action'] ) . '_request' ) );

			return;
		}

		// Possibly Handle Remove Product Request
		$remove_from_cart_var = it_exchange_get_field_name( 'remove_product_from_cart' );
		if ( ! empty( $_REQUEST[ $remove_from_cart_var ] ) ) {
			$this->handle_remove_product_from_cart_request();

			return;
		}

		// Possibly Handle Update Cart Request
		$update_cart_var = it_exchange_get_field_name( 'update_cart_action' );
		if ( ! empty( $_REQUEST[ $update_cart_var ] ) ) {
			$this->handle_update_cart_request();

			return;
		}

		// Possibly Handle Proceed to checkout
		$proceed_var = it_exchange_get_field_name( 'proceed_to_checkout' );
		if ( ! empty( $_REQUEST[ $proceed_var ] ) ) {
			$this->proceed_to_checkout();

			return;
		}

		// Possibly Handle Empty Cart request
		$empty_var = it_exchange_get_field_name( 'empty_cart' );
		if ( ! empty( $_REQUEST[ $empty_var ] ) ) {
			$this->handle_empty_shopping_cart_request();

			return;
		}

		// Possibly Handle Continue Shopping Request
		$empty_var = it_exchange_get_field_name( 'continue_shopping' );
		if ( ! empty( $_REQUEST[ $empty_var ] ) ) {
			if ( $url = it_exchange_get_page_url( 'store' ) ) {
				it_exchange_redirect( $url, 'cart-continue-shopping' );
				die();
			}

			return;
		}

		// Possibly handle update shipping address request
		if ( ! empty( $_REQUEST['ninja-shop-update-shipping-address'] ) ) {
			$this->handle_update_shipping_address_request();

			return;
		}

		// Possibly handle update billing address request
		if ( ! empty( $_REQUEST['ninja-shop-update-billing-address'] ) ) {
			$this->handle_update_billing_address_request();

			return;
		}
	}

	/**
	 * Listens for $_REQUESTs to buy a product now
	 *
	 *
	 * @return void
	 */
	public function handle_buy_now_request() {
		$buy_now_var        = it_exchange_get_field_name( 'buy_now' );
		$product_id         = empty( $_REQUEST[ $buy_now_var ] ) ? 0 : absint( $_REQUEST[ $buy_now_var ] );
		$product            = it_exchange_get_product( $product_id );
		$quantity_var       = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[ $quantity_var ] ) ? 1 : absint( $_REQUEST[ $quantity_var ] );
		$cart               = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product ) {
			$error = 'bad-product';
		}

		// Verify nonce
		$nonce_var = apply_filters( 'ninja_shop_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[ $nonce_var ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_var ], 'ninja-shop-purchase-product-' . $product_id ) ) {
			$error = 'product-not-added-to-cart';
		}

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'checkout' : 'login';
			// Get current URL without exchange query args
			$url = it_exchange_clean_query_args();
			if ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'checkout' ) ) {
				$url = it_exchange_get_page_url( 'checkout' );
				it_exchange_redirect( $url, 'buy-now-success-no-sw' );
				die();
			} else {
				$url = add_query_arg( 'ite-sw-state', $sw_state, $url );
				it_exchange_redirect( esc_url_raw( $url ), 'buy-now-success-in-sw' );
				die();
			}
		}

		$error = empty( $error ) ? 'product-not-added-to-cart' : $error;
		it_exchange_add_message( 'error', __( 'Product not added to cart', 'it-l10n-ithemes-exchange' ) );
		it_exchange_redirect( esc_url_raw( $cart ), 'buy-now-failed' );
		die();
	}

	/**
	 * Listens for $_REQUESTs to add a product to the cart and processes
	 *
	 *
	 * @return void
	 */
	public function handle_add_product_to_cart_request() {

		$add_to_cart_var    = it_exchange_get_field_name( 'add_product_to_cart' );
		$product_id         = empty( $_REQUEST[ $add_to_cart_var ] ) ? 0 : absint( $_REQUEST[ $add_to_cart_var ] );
		$product            = it_exchange_get_product( $product_id );
		$quantity_var       = it_exchange_get_field_name( 'product_purchase_quantity' );
		$requested_quantity = empty( $_REQUEST[ $quantity_var ] ) ? 1 : absint( $_REQUEST[ $quantity_var ] );
		$cart               = it_exchange_get_page_url( 'cart' );

		// Vefify legit product
		if ( ! $product ) {
			$error = 'bad-product';
		}

		// Verify nonce
		$nonce_var = apply_filters( 'ninja_shop_purchase_product_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[ $nonce_var ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_var ], 'it-exchange-purchase-product-' . $product_id ) ) {
			$error = 'product-not-added-to-cart';
		}

		// Add product
		if ( empty( $error ) && it_exchange_add_product_to_shopping_cart( $product_id, $requested_quantity ) ) {
			$sw_state = is_user_logged_in() ? 'cart' : 'login';
			// Get current URL without exchange query args
			$url = it_exchange_clean_query_args();
			it_exchange_add_message( 'notice', __( 'Product added to cart', 'it-l10n-ithemes-exchange' ) );
			if ( it_exchange_is_multi_item_cart_allowed() && it_exchange_get_page_url( 'cart' ) ) {
				$url = it_exchange_get_page_url( 'cart' );
				it_exchange_redirect( $url, 'add-to-cart-success-no-sw' );
				die();
			} else {
				$url = add_query_arg( 'ite-sw-state', $sw_state, $url );
				it_exchange_redirect( esc_url_raw( $url ), 'add-to-cart-success-in-sw' );
				die();
			}
		}

		$error_var = it_exchange_get_field_name( 'error_message' );
		$error     = empty( $error ) ? 'product-not-added-to-cart' : $error;
		$url       = add_query_arg( array( $error_var => $error ), $cart );
		it_exchange_redirect( esc_url_raw( $url ), 'add-to-cart-failed' );
		die();
	}

	/**
	 * Empty the Ninja Shop shopping cart
	 *
	 *
	 * @return void
	 */
	public function handle_empty_shopping_cart_request() {
		// Verify nonce
		$nonce_var   = apply_filters( 'ninja_shop_cart_action_nonce_var', '_wpnonce' );
		$error_var   = it_exchange_get_field_name( 'error_message' );
		$message_var = it_exchange_get_field_name( 'alert_message' );
		$session_id  = it_exchange_get_session_id();

		if ( it_exchange_is_multi_item_cart_allowed() ) {
			$cart = it_exchange_get_page_url( 'cart' );
		} else {
			$cart = it_exchange_clean_query_args();
		}

		if ( empty( $_REQUEST[ $nonce_var ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_var ], 'it-exchange-cart-action-' . $session_id ) ) {
			$url = add_query_arg( array( $error_var => 'cart-not-emptied' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );

			$redirect_options = array( 'query_arg' => array( $error_var => 'cart-not-emptied' ) );
			it_exchange_redirect( $url, 'cart-empty-failed', $redirect_options );
			die();
		}

		// Empty the cart
		it_exchange_empty_shopping_cart();

		$url = remove_query_arg( $error_var, $cart );
		$url = add_query_arg( array( $message_var => 'cart-emptied' ), $url );
		$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );

		$redirect_options = array( 'query_arg' => array( $message_var => 'cart-emptied' ) );
		it_exchange_redirect( esc_url_raw( $url ), 'cart-empty-success', $redirect_options );
		die();
	}

	/**
	 * Removes a single product from the shopping cart
	 *
	 * This listens for REQUESTS to remove a product from the cart, verifies the request, and passes it along to the
	 * correct public function
	 *
	 *
	 * @return void
	 */
	public function handle_remove_product_from_cart_request() {
		$var             = it_exchange_get_field_name( 'remove_product_from_cart' );
		$car_product_ids = empty( $_REQUEST[ $var ] ) ? array() : sanitize_textarea_field( $_REQUEST[ $var ] );
		$session_id      = it_exchange_get_session_id();

		// Base URL
		if ( it_exchange_is_multi_item_cart_allowed() ) {
			$cart_url = it_exchange_get_page_url( 'cart' );
		} else {
			$cart_url = it_exchange_clean_query_args();
		}

		// Verify nonce
		$nonce_var = apply_filters( 'ninja_shop_remove_product_from_cart_nonce_var', '_wpnonce' );
		if ( empty( $_REQUEST[ $nonce_var ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_var ], 'it-exchange-cart-action-' . $session_id ) ) {
			$var = it_exchange_get_field_name( 'error_message' );
			$url = add_query_arg( array( $var => 'product-not-removed' ), $cart_url );

			$redirect_options = array( 'query_arg' => array( $var => 'product-not-removed' ) );
			it_exchange_redirect( esc_url_raw( $url ), 'cart-remove-product-failed', $redirect_options );
			die();
		}

		foreach ( (array) $car_product_ids as $car_product_id ) {
			it_exchange_get_current_cart()->remove_item( 'product', $car_product_id );
		}

		$var = it_exchange_get_field_name( 'alert_message' );
		$url = add_query_arg( array( $var => 'product-removed' ), $cart_url );

		$redirect_options = array( 'query_arg' => array( $var => 'product-removed' ) );
		it_exchange_redirect( esc_url_raw( $url ), 'cart-remove-product-success', $redirect_options );
		die();
	}

	/**
	 * Listens for the REQUEST to update the shopping cart, verifies it, and calls the correct public function
	 *
	 *
	 * @return void
	 */
	public function handle_update_cart_request( $redirect = true ) {
		$session_id = it_exchange_get_session_id();
		// Verify nonce
		$nonce_var = apply_filters( 'ninja_shop_cart_action_nonce_var', '_wpnonce' );
		if ( it_exchange_is_multi_item_cart_allowed() ) {
			$cart = it_exchange_get_page_url( 'cart' );
		} else {
			$cart = it_exchange_clean_query_args( array( it_exchange_get_field_name( 'sw_cart_focus' ) ) );
			if ( it_exchange_in_superwidget() ) {
				$cart = add_query_arg( 'ite-sw-state', 'cart', $cart );
			}
		}
		if ( empty( $_REQUEST[ $nonce_var ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_var ], 'it-exchange-cart-action-' . $session_id ) ) {
			$var = it_exchange_get_field_name( 'error_message' );

			$url = add_query_arg( array( $var => 'cart-not-updated' ), $cart );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );

			$redirect_options = array( 'query_arg' => array( $var => 'cart-not-updated' ) );
			it_exchange_redirect( esc_url( $url ), 'cart-update-failed', $redirect_options );
			die();
		}

		// Are we updating any quantities
		$var_name = it_exchange_get_field_name( 'product_purchase_quantity' );
		if ( ! empty( $_REQUEST[ $var_name ] ) ) {
			foreach ( (array) $_REQUEST[ $var_name ] as $cart_product_id => $quantity ) {
				it_exchange_update_cart_product_quantity( $cart_product_id, $quantity, false );
			}
		}

		$current_cart = it_exchange_get_current_cart();

		$var_name = it_exchange_get_field_name( 'line_item_quantity' );
		if ( ! empty( $_REQUEST[ $var_name ] ) ) {
			foreach ( (array) $_REQUEST[ $var_name ] as $id_type => $quantity ) {
				list( $id, $type ) = explode( ':', $id_type );
				$item = $current_cart->get_item( $type, $id );

				if ( $item && $item instanceof ITE_Quantity_Modifiable_Item && $item->is_quantity_modifiable() ) {
					$max = $item->get_max_quantity_available();

					if ( $max !== '' && (int) $quantity <= $max ) {
						$item->set_quantity( (int) $quantity );
						$current_cart->save_item( $item );
					}
				}
			}
		}

		do_action( 'ninja_shop_update_cart' );

		$message_var = it_exchange_get_field_name( 'alert_message' );
		if ( ! empty ( $message_var ) && $redirect ) {
			$url = remove_query_arg( $message_var, $cart );
			$url = add_query_arg( array( $message_var => 'cart-updated' ), $url );
			$url = remove_query_arg( it_exchange_get_field_name( 'empty_cart' ), $url );

			$redirect_options = array( 'query_arg' => array( $message_var => 'cart-updated' ) );
			it_exchange_redirect( esc_url( $url ), 'cart-update-success', $redirect_options );
			die();
		}
	}

	/**
	 * Handles updating a Shipping address
	 *
	 *
	 *
	 * @return bool
	 */
	public function handle_update_shipping_address_request() {

		// Validate nonce
		if ( empty( $_REQUEST['ninja-shop-update-shipping-address'] ) || ! wp_verify_nonce( $_REQUEST['ninja-shop-update-shipping-address'], 'ninja-shop-update-checkout-shipping-address-' . it_exchange_get_session_id() ) ) {
			it_exchange_add_message( 'error', __( 'Error adding Shipping Address. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$GLOBALS['it_exchange']['shipping-address-error'] = true;

			return false;
		}

		if ( ! empty( $_REQUEST['saved_address'] ) && $location = ITE_Saved_Address::get( $_REQUEST['saved_address'] ) ) {
			return $this->update_shipping( $location );
		}

		// Validate required fields
		$required_fields = apply_filters( 'ninja_shop_required_shipping_address_fields', array(
			'first-name',
			'last-name',
			'address1',
			'state',
			'country',
			'zip'
		) );

		$states = it_exchange_get_data_set( 'states', array( 'country' => $_REQUEST['it-exchange-shipping-address-country'] ) );
		if ( empty( $states ) && $key = array_search( 'state', $required_fields ) ) {
			unset( $required_fields[ $key ] );
		}

		foreach ( $required_fields as $field ) {
			if ( empty( $_REQUEST[ 'it-exchange-shipping-address-' . $field ] ) ) {
				it_exchange_add_message( 'error', __( 'Please fill out all required fields', 'it-l10n-ithemes-exchange' ) );
				$GLOBALS['it_exchange']['shipping-address-error'] = true;

				return false;
			}
		}

		/** @todo This is hardcoded for now. will be more flexible at some point * */
		$shipping = array();
		$fields   = apply_filters( 'ninja_shop_shipping_address_fields', array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone',
		) );
		foreach ( $fields as $field ) {
			$shipping[ $field ] = empty( $_REQUEST[ 'it-exchange-shipping-address-' . $field ] ) ? '' : sanitize_text_field( $_REQUEST[ 'it-exchange-shipping-address-' . $field ] );
		}

		$location = new ITE_In_Memory_Address( $shipping );

		return $this->update_shipping( $location );
	}

	/**
	 * Update the shipping address.
	 *
	 *
	 *
	 * @param ITE_Location $location
	 *
	 * @return bool
	 */
	protected function update_shipping( ITE_Location $location ) {

		if ( it_exchange_get_current_cart()->set_shipping_address( $location ) ) {
			it_exchange_add_message( 'notice', __( 'Shipping Address Saved', 'it-l10n-ithemes-exchange' ) );

			return true;
		}

		return false;
	}

	/**
	 * Handles updating a billing address
	 *
	 *
	 *
	 * @return bool
	 */
	public function handle_update_billing_address_request() {

		$action = 'ninja-shop-update-checkout-billing-address-' . it_exchange_get_session_id();

		// Validate nonce
		if ( empty( $_REQUEST['ninja-shop-update-billing-address'] ) || ! wp_verify_nonce( $_REQUEST['ninja-shop-update-billing-address'], $action ) ) {
			it_exchange_add_message( 'error', __( 'Error adding Billing Address. Please try again.', 'it-l10n-ithemes-exchange' ) );
			$GLOBALS['it_exchange']['billing-address-error'] = true;

			return false;
		}

		if ( ! empty( $_REQUEST['saved_address'] ) && $location = ITE_Saved_Address::get( $_REQUEST['saved_address'] ) ) {
			return $this->update_billing( $location );
		}

		// Validate required fields
		$required_fields = apply_filters( 'ninja_shop_required_billing_address_fields', array(
			'first-name',
			'last-name',
			'address1',
			'city',
			'state',
			'country',
			'zip'
		) );

		$states = it_exchange_get_data_set( 'states', array( 'country' => $_REQUEST['ninja-shop-billing-address-country'] ) );
		if ( empty( $states ) && $key = array_search( 'state', $required_fields ) ) {
			unset( $required_fields[ $key ] );
		}

		foreach ( $required_fields as $field ) {
			if ( empty( $_REQUEST[ 'ninja-shop-billing-address-' . $field ] ) ) {
				it_exchange_add_message( 'error', __( 'Please fill out all required fields', 'it-l10n-ithemes-exchange' ) );
				$GLOBALS['it_exchange']['billing-address-error'] = true;

				return false;
			}
		}

		/**
		 * @todo This is hardcoded for now. will be more flexible at some point
		 * If you're having trouble getting your custom field to save, make sure that your form field's name
		 * matches what we're looking for in the REQUEST below. eg: adding 'custom-form' to the $fields var
		 * via this next filter means that your form field name has to be: 'ninja-shop-billing-address-custom-form'
		 */
		$billing = array();
		$fields  = apply_filters( 'ninja_shop_billing_address_fields', array(
			'first-name',
			'last-name',
			'company-name',
			'address1',
			'address2',
			'city',
			'state',
			'zip',
			'country',
			'email',
			'phone',
		) );
		foreach ( $fields as $field ) {
			$billing[ $field ] = empty( $_REQUEST[ 'ninja-shop-billing-address-' . $field ] ) ? '' : sanitize_text_field( $_REQUEST[ 'ninja-shop-billing-address-' . $field ] );
		}

		$location = new ITE_In_Memory_Address( $billing );

		return $this->update_billing( $location );
	}

	/**
	 * Update the billing address.
	 *
	 *
	 *
	 * @param ITE_Location $location
	 *
	 * @return bool
	 */
	protected function update_billing( ITE_Location $location ) {

		if ( it_exchange_get_current_cart()->set_billing_address( $location ) ) {
			it_exchange_add_message( 'notice', __( 'Billing Address Saved', 'it-l10n-ithemes-exchange' ) );

			// Update Shipping if checked
			if ( ! empty( $_REQUEST['ninja-shop-ship-to-billing'] ) && '1' == $_REQUEST['ninja-shop-ship-to-billing'] ) {
				it_exchange_get_current_cart()->set_shipping_address( $location );
			}

			return true;
		}

		return false;
	}

	/**
	 * Advances the user to the checkout screen after updating the cart
	 *
	 *
	 * @return void
	 */
	public function proceed_to_checkout() {

		// Update cart info before redirecting.
		$this->handle_update_cart_request( false );

		// Redirect to Checkout
		if ( $checkout = it_exchange_get_page_url( 'checkout' ) ) {
			it_exchange_redirect( $checkout, 'cart-proceed-to-checkout' );
			die();
		}
	}

	/**
	 * Process checkout
	 *
	 * Formats data and hands it off to the appropriate tranaction method
	 *
	 *
	 *
	 *
	 * @param bool           $status
	 * @param \ITE_Cart|null $cart
	 *
	 * @return boolean
	 */
	public function handle_purchase_cart_request( $status, $cart = null ) {

		if ( $status ) {
			return $status;
		}

		$cart = $cart ?: it_exchange_get_current_cart();

		if ( ( $feedback = $cart->get_requirements_for_purchase() ) && $feedback->has_feedback() ) {
			$feedback->to_messages();

			it_exchange_log( 'Cart #{cart_id} purchase rejected due to pending requirements.', ITE_Log_Levels::DEBUG, array(
				'cart_id' => $cart->get_id(),
				'_group'  => 'cart',
			) );

			return false;
		}

		// Verify transaction method exists
		$method_var = it_exchange_get_field_name( 'transaction_method' );
		$method     = empty( $_REQUEST[ $method_var ] ) ? false : sanitize_text_field( $_REQUEST[ $method_var ] );

		$enabled_addons = it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) );
		$handler        = it_exchange_get_purchase_handler_by_id( $method );

		if ( ! $method || ( empty( $enabled_addons[ $method ] ) && ! $handler ) ) {
			do_action( 'ninja_shop_error_bad_transaction_method_at_purchase', $method, $cart );
			it_exchange_add_message( 'error', $this->get_cart_message( 'bad-transaction-method' ) );

			return false;
		}

		it_exchange_log(
			'Processing purchase request for cart #{cart_id} containing {description} totalling {total} via {method}.',
			ITE_Log_Levels::DEBUG,
			array(
				'cart_id'     => $cart->get_id(),
				'description' => $cart,
				'total'       => $cart->get_total(),
				'method'      => $method
			)
		);

		$transaction_object = it_exchange_generate_transaction_object( $cart );

		if ( ! $transaction_object ) {
			$transaction_id = apply_filters( 'handle_purchase_cart_request_already_processed_for_' . $method, false );

			if ( $transaction_id ) {
				it_exchange_clear_messages( 'error' );

				return $transaction_id;
			}

			return false;
		}

		$transaction_object = apply_filters( 'ninja_shop_transaction_object', $transaction_object, $method, $cart );

		try {
			// Do the transaction
			$transaction_id = it_exchange_do_transaction( $method, $transaction_object, $cart );

			if ( $transaction_id ) {
				$cart->empty_cart();
			} else {
				$cart->get_feedback()->to_messages();
			}

			return $transaction_id;
		} catch ( IT_Exchange_Locking_Exception $e ) {
			sleep( 2 );

			$transaction = it_exchange_get_transaction_by_cart_id( $cart->get_id() );

			if ( $transaction ) {
				$cart->empty_cart();

				it_exchange_log( 'Captured transaction for cart #{cart_id} after waiting due to locking exception.', ITE_Log_Levels::DEBUG, array(
					'cart_id' => $cart->get_id(),
					'_group'  => 'cart',
				) );

				return $transaction->ID;
			}

			// this would happen in the following flow
			// IPN -> Auto Return ( wait 2 seconds ) -> IPN fails

			$message = __( 'A possible error occurred during your purchase.', 'it-l10n-ithemes-exchange' );
			$message .= ' ' . __( 'If you do not receive an email receipt shortly, please contact the site owner', 'it-l10n-ithemes-exchange' );

			it_exchange_add_message( 'error', $message );

			it_exchange_log( 'Failed to purchase cart #{cart_id} via {method} after waiting due to locking exception.', array(
				'cart_id' => $cart->get_id(),
				'method'  => $method,
				'_group'  => 'cart',
			) );
		}

		return false;
	}

	/**
	 * Prepare the cart for purchase on the checkout page.
	 *
	 *
	 */
	public function prepare_for_purchase() {

		if ( it_exchange_is_page( 'checkout' ) ) {
			it_exchange_get_current_cart()->prepare_for_purchase();
		}
	}

	/**
	 * Convert feedback to notices.
	 *
	 *
	 */
	public function convert_feedback_to_notices() {

		$cart = it_exchange_get_current_cart( false );

		if ( ! $cart ) {
			return;
		}

		$cart->get_feedback()->to_messages();
	}

	/**
	 * Clear the cart meta session data when a transaction is completed.
	 *
	 *
	 *
	 * @param int            $transaction_id
	 * @param \ITE_Cart|null $cart
	 */
	public function clear_cart_meta_session_on_transaction( $transaction_id, ITE_Cart $cart = null ) {
		if ( $cart && $cart->is_current() ) {
			it_exchange_clear_session_data( 'cart_meta' );
		}
	}

	/**
	 * Redirect from checkout to cart if there are no items in the cart
	 *
	 *
	 * @return void
	 */
	public function redirect_checkout_if_empty_cart() {

		$checkout = it_exchange_get_page_url( 'checkout' );

		if ( empty( $checkout ) || ! it_exchange_is_page( 'checkout' ) ) {
			return;
		}

		$cart = it_exchange_get_requested_cart_and_check_auth() ?: it_exchange_get_current_cart();

		if ( ! $cart->get_items()->count() ) {
			it_exchange_redirect( it_exchange_get_page_url( 'cart' ), 'checkout-empty-send-to-cart' );
			die();
		}
	}

	/**
	 * Gets message for given key
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_cart_message( $key ) {
		$message = $this->default_cart_messages();

		return ( ! empty( $message[ $key ] ) ) ? $message[ $key ] : __( 'Unknown error. Please try again.', 'it-l10n-ithemes-exchange' );;
	}

	/**
	 * Sets up default messages
	 *
	 *
	 * @return array
	 */
	public function default_cart_messages() {
		$messages['bad-transaction-method'] = __( 'Please select a payment method', 'it-l10n-ithemes-exchange' );
		$messages['failed-transaction']     = __( 'There was an error processing your transaction. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['product-not-removed']    = __( 'Product not removed from cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-not-emptied']       = __( 'There was an error emptying your cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-not-updated']       = __( 'There was an error updating your cart. Please try again.', 'it-l10n-ithemes-exchange' );
		$messages['cart-updated']           = __( 'Cart Updated.', 'it-l10n-ithemes-exchange' );
		$messages['cart-emptied']           = __( 'Cart Emptied', 'it-l10n-ithemes-exchange' );
		$messages['product-removed']        = __( 'Product removed from cart.', 'it-l10n-ithemes-exchange' );
		$messages['product-added-to-cart']  = __( 'Product added to cart', 'it-l10n-ithemes-exchange' );

		return apply_filters( 'ninja_shop_default_cart_messages', $messages );
	}

	/**
	 * Merge sessions on user login.
	 *
	 *
	 *
	 * @param string  $user_login
	 * @param WP_User $user
	 */
	public function merge_session( $user_login, $user ) {

		$cart = it_exchange_get_current_cart( false );

		if ( $cart ) {
			it_exchange_merge_cached_customer_cart_into_current_session( $user_login, $user );
		} else {
			$customer = it_exchange_get_customer( $user );

			if ( ! $customer ) {
				return;
			}

			try {
				$repository = ITE_Cart_Cached_Session_Repository::from_customer( $customer );
				IT_Exchange_DB_Sessions::get_instance()->transfer_session( $repository->get_model(), true );
			}
			catch ( InvalidArgumentException $e ) {

			}
		}
	}

	/**
	 * Fire the deprecated cache customer cart hooks.
	 *
	 *
	 *
	 * @param \IronBound\WPEvents\GenericEvent $event
	 */
	public function fire_deprecated_cache_cart( \IronBound\WPEvents\GenericEvent $event ) {

		$session = $event->get_subject();

		if ( ! $session instanceof ITE_Session_Model ) {
			return;
		}

		$current_cart_id = it_exchange_get_cart_id();

		if ( $current_cart_id !== $session->cart_id ) {
			return;
		}

		it_exchange_cache_customer_cart();
	}

	/**
	 * Makes calls to sync carts when customer modifies cart data
	 *
	 *
	 *
	 * @deprecated 2.0.0
	 *
	 * @return void
	 */
	public function sync_customer_active_carts() {
		_deprecated_function( __FUNCTION__, '2.0.0' );
	}
}

if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	$GLOBALS['IT_Exchange_Shopping_Cart'] = new IT_Exchange_Shopping_Cart();
}
