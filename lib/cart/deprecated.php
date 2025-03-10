<?php
/**
 * Fire deprecated hooks an contains deprecated cart API methods.
 *
 * 
 * @license GPLv2
 */

/**
 * Save the billing address to the customer's profile when the address for the cart is updated.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function ite_save_main_billing_address_on_current_update( ITE_Cart $cart ) {

	if ( $cart->is_current() && $cart->get_customer() && is_numeric( $cart->get_customer()->ID ) ) {

		if ( $cart->get_billing_address() ) {
			$cart->get_customer()->set_billing_address( $cart->get_billing_address() );
		}
	}
}

add_action( 'ninja_shop_set_cart_billing_address', 'ite_save_main_billing_address_on_current_update' );

/**
 * Save the shipping address to the customer's profile when the address for the cart is updated.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function ite_save_main_shipping_address_on_current_update( ITE_Cart $cart ) {

	if ( $cart->is_current() && $cart->get_customer() && is_numeric( $cart->get_customer()->ID ) ) {

		if ( $cart->get_shipping_address() ) {
			$cart->get_customer()->set_shipping_address( $cart->get_shipping_address() );
		}
	}
}

add_action( 'ninja_shop_set_cart_shipping_address', 'ite_save_main_shipping_address_on_current_update' );

/**
 * Fire the deprecated quantity hook.
 *
 *
 *
 * @param \ITE_Line_Item       $item
 * @param \ITE_Line_Item|null  $old
 * @param \ITE_Cart_Repository $repo
 */
function ite_fire_deprecated_quantity_hook( ITE_Line_Item $item, ITE_Line_Item $old = null, ITE_Cart_Repository $repo ) {

	if ( ! $repo instanceof ITE_Cart_Session_Repository || $repo instanceof ITE_Cart_Cached_Session_Repository ) {
		return;
	}

	if ( ! $old ) {
		return;
	}

	if ( $item->get_quantity() != $old->get_quantity() ) {
		do_action_deprecated( 'it_exchange_cart_prouduct_count_updated', array( $item->get_id() ), '2.0.0' );
	}
}

add_action( 'ninja_shop_save_product_item', 'ite_fire_deprecated_quantity_hook', 10, 3 );

/**
 * Fire the deprecated add to cart hooks.
 *
 *
 *
 * @param \ITE_Cart_Product $item
 * @param \ITE_Cart         $cart
 */
function ite_fire_deprecated_add_cart_product_hook( ITE_Cart_Product $item, ITE_Cart $cart ) {

	if ( ! $cart->is_current() ) {
		return;
	}

	do_action_deprecated( 'it_exchange_add_cart_product', array( $item->bc() ), '2.0.0' );
	do_action_deprecated( 'it_exchange_product_added_to_cart', array( $item->get_product_id() ), '2.0.0' );
}

add_action( 'ninja_shop_add_product_to_cart', 'ite_fire_deprecated_add_cart_product_hook', 10, 2 );

/**
 * Fire the deprecated update cart product hooks.
 *
 *
 *
 * @param \ITE_Cart_Product    $item
 * @param \ITE_Line_Item       $old
 * @param \ITE_Cart_Repository $repo
 */
function ite_fire_deprecated_update_cart_product_hook( ITE_Cart_Product $item, ITE_Line_Item $old = null, ITE_Cart_Repository $repo ) {

	if ( ! $old ) {
		return;
	}

	if ( ! $repo instanceof ITE_Cart_Session_Repository ) {
		return;
	}

	do_action_deprecated( 'it_exchange_update_cart_product', array(
		$item->get_id(),
		$item->bc(),
		it_exchange_get_session()->get_session_data( 'products' )
	), '2.0.0' );
}

add_action( 'ninja_shop_save_product_item', 'ite_fire_deprecated_update_cart_product_hook', 10, 3 );

/**
 * Fire deprecated delete cart product hook.
 *
 *
 *
 * @param \ITE_Cart_Product $product
 * @param \ITE_Cart         $cart
 */
function ite_fire_deprecated_delete_cart_product_hook( ITE_Cart_Product $product, ITE_Cart $cart ) {

	if ( ! $cart->is_current() ) {
		return;
	}

	do_action_deprecated( 'it_exchange_delete_cart_product', array(
		$product->get_id(),
		it_exchange_get_session_data( 'products' )
	), '2.0.0' );
}

add_action( 'ninja_shop_remove_product_from_cart', 'ite_fire_deprecated_delete_cart_product_hook', 10, 2 );

/**
 * Fire deprecated empty cart hook.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function ite_fire_deprecated_empty_cart_hook( ITE_Cart $cart ) {

	if ( $cart->is_current() ) {
		do_action_deprecated( 'it_exchange_before_empty_shopping_cart', array( it_exchange_get_session_data() ), '2.0.0' );
	}
}

add_action( 'ninja_shop_empty_cart', 'ite_fire_deprecated_empty_cart_hook' );

/**
 * Fire deprecated emptied cart hook.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function ite_fire_deprecated_emptied_cart_hook( ITE_Cart $cart ) {

	if ( $cart->is_current() ) {
		do_action_deprecated( 'it_exchange_empty_shopping_cart', array(), '2.0.0' );
	}
}

add_action( 'ninja_shop_emptied_cart', 'ite_fire_deprecated_emptied_cart_hook' );

/**
 * Get cached cart.
 *
 *
 *
 * @param mixed  $value
 * @param int    $customer_id
 * @param string $meta_key
 * @param bool   $single
 *
 * @return array|bool
 */
function ite_get_deprecated_cart_cache( $value, $customer_id, $meta_key, $single ) {

	if ( $meta_key !== '_it_exchange_cached_cart' ) {
		return $value;
	}

	return it_exchange_get_cached_customer_cart( $customer_id );
}

add_filter( 'get_user_metadata', 'ite_get_deprecated_cart_cache', 10, 4 );

/**
 * Handle deprecating the active user carts metadata.
 *
 *
 *
 * @param mixed  $value
 * @param int    $user_id
 * @param string $meta_key
 * @param bool   $single
 *
 * @return mixed
 */
function it_exchange_handle_deprecated_active_carts_meta( $value, $user_id, $meta_key, $single ) {

	if ( $meta_key !== '_it_exchange_active_user_carts' ) {
		return $value;
	}

	return it_exchange_get_active_carts_for_customer( true, $user_id );
}

add_filter( 'get_user_metadata', 'it_exchange_handle_deprecated_active_carts_meta', 10, 4 );

/* === Deprecate API Functions === */

/**
 * Returns an array of all products in the cart
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param  array $options
 *
 * @return array
 */
function it_exchange_get_cart_products( $options = array() ) {
	if ( empty( $options['use_cached_customer_cart'] ) ) {
		$products = it_exchange_get_session_data( 'products' );
	} else {
		$cart     = it_exchange_get_cached_customer_cart( $options['use_cached_customer_cart'] );
		$products = empty( $cart['products'] ) ? array() : $cart['products'];
	}

	$products = ( empty( $products ) || ! is_array( $products ) ) ? array() : $products;

	return array_filter( $products );
}

/**
 * Inserts product into the cart session
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data
 *
 * @return void
 */
function it_exchange_add_cart_product( $cart_product_id, $product ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( $cart_product_id && $product ) {

		if ( empty( $product['product_id'] ) ) {
			return;
		}

		$item = new ITE_Cart_Product( $cart_product_id, new ITE_Array_Parameter_Bag(
			array_merge( array(
				'count'           => 1,
				'product_name'    => get_the_title( $product['product_id'] ),
				'itemized_data'   => array(),
				'additional_data' => array(),
				'product_cart_id' => $cart_product_id,
				'itemized_hash'   => '',
			), $product )
		), new ITE_Array_Parameter_Bag() );

		it_exchange_get_current_cart()->add_item( $item );
	}

	do_action_deprecated( 'it_exchange_add_cart_product', array( $product ), '2.0.0' );
}

/**
 * Updates product into the cart session
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param string $cart_product_id
 * @param array  $product Cart product data. This must be the entire new data, not a partial diff.
 *
 * @return void
 */
function it_exchange_update_cart_product( $cart_product_id, $product ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	if ( empty( $cart_product_id ) || empty( $product ) ) {
		return;
	}

	/** @var ITE_Cart_Product $cart_product */
	$cart_product = it_exchange_get_current_cart()->get_item( 'product', $cart_product_id );

	if ( ! $cart_product ) {
		it_exchange_add_cart_product( $cart_product_id, $product );

		do_action_deprecated(
			'it_exchange_update_cart_product',
			array( $cart_product_id, $product, it_exchange_get_session_data( 'products' ) ),
			'2.0.0'
		);

		return;
	}

	if ( $cart_product->get_quantity() != $product['count'] ) {
		$cart_product->set_quantity( $product['count'] );
		it_exchange_get_current_cart()->save_item( $cart_product );
	}

	$bc = $cart_product->bc();
	sort( $bc );
	sort( $product );

	if ( $bc !== $product ) {

		$products = it_exchange_get_session_data( 'products' );

		foreach ( $product as $key => $value ) {
			$products[ $cart_product_id ][ $key ] = $value;
		}

		it_exchange_update_session_data( 'products', $products );

		do_action_deprecated(
			'it_exchange_update_cart_product',
			array( $cart_product_id, $product, $products ),
			'2.0.0'
		);
	}
}

/**
 * Deletes product from the cart session
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param string $cart_product_id
 *
 * @return void
 */
function it_exchange_delete_cart_product( $cart_product_id ) {

	_deprecated_function( __FUNCTION__, '2.0.0', 'ITE_Cart::remove_item()' );

	$cart = it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return;
	}

	$cart->remove_item( 'product', $cart_product_id );
}

/**
 * Returns a specific product from the cart.
 *
 * The returned data is not an Ninja Shop Product object. It is a cart-product
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param mixed  $id id for the cart's product data
 * @param  array $options
 *
 * @return array|false
 */
function it_exchange_get_cart_product( $id, $options = array() ) {
	if ( ! $products = it_exchange_get_cart_products( $options ) ) {
		return false;
	}

	if ( empty( $products[ $id ] ) ) {
		return false;
	}

	return apply_filters_deprecated( 'it_exchange_get_cart_product', array(
		$products[ $id ],
		$id,
		$options
	), '2.0.0' );
}

/**
 * Add a session ID to the list of active customer cart sessions
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param int|bool $customer_id Pass false to retrieve the current customer's ID.
 *
 * @return void|false
 */
function it_exchange_add_current_session_to_customer_active_carts( $customer_id = false ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );

	return false;
}

/**
 * Remove session from a customer's active carts
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @return void
 */
function it_exchange_remove_current_session_from_customer_active_carts() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Syncs the current cart with all other active carts
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @return void
 */
function it_exchange_sync_current_cart_with_all_active_customer_carts() {
	_deprecated_function( __FUNCTION__, '2.0.0' );
}

/**
 * Caches the user's cart in user meta if they are logged in
 *
 *
 *
 * @deprecated 2.0.0
 *
 * @param int|bool $customer_id
 *
 * @return void
 */
function it_exchange_cache_customer_cart( $customer_id = false ) {

	$customer = $customer_id ? it_exchange_get_customer( $customer_id ) : it_exchange_get_current_customer();

	if ( ! $customer ) {
		return;
	}

	$cart_id = it_exchange_get_cart_id();

	if ( ! $cart_id ) {
		return;
	}

	$session = ITE_Session_Model::from_cart_id( $cart_id );

	if ( ! $session ) {
		return;
	}

	$cart_data = $session->get_decoded_data();

	do_action( 'ninja_shop_cache_customer_cart', $customer, $cart_data );
}
