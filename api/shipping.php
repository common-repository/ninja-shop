<?php
/**
 * This file contains functions related to the shipping API
 * See also: api/shipping-features.php
 * 
 * @package IT_Exchagne
*/

/**
 * Register a shipping provider
 *
 *
 *
 * @param  string  $slug    provider slug
 * @param  array   $options options for the provider
 *
 * @return boolean
*/
function it_exchange_register_shipping_provider( $slug, $options ) {

	// Lets just make sure the slug is in the options
	$options['slug'] = $slug;

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['providers'][$slug] = $options;

	// Return the object
	return true;
}

/**
 * Returns all registered shipping providers
 *
 *
 *
 * @param  mixed $filtered a string or an array of strings to limit returned providers to specific providers
 *
 * @return array
*/
function it_exchange_get_registered_shipping_providers( $filtered=array() ) {
	$providers = empty( $GLOBALS['it_exchange']['shipping']['providers'] ) ? array() : $GLOBALS['it_exchange']['shipping']['providers'];
	if ( empty( $filtered ) )
		return $providers;

	foreach( (array) $filtered as $provider ) {
		if ( isset( $providers[$provider] ) )
			unset( $providers[$provider] );
	}
	return $providers;
}

/**
 * Returns a specific registered shipping provider object
 *
 *
 *
 * @param  string $slug the registerd slug
 *
 * @return IT_Exchange_Shipping_Provider|bool  false or object
*/
function it_exchange_get_registered_shipping_provider( $slug ) {
	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['providers'][$slug] ) )
		return false;

	// Retrieve the provider details
	$options = $GLOBALS['it_exchange']['shipping']['providers'][$slug];

	// Include the class
	include_once( dirname( dirname( __FILE__ ) ) . '/lib/shipping/class-provider.php' );

	// Init the class
	return new IT_Exchange_Shipping_Provider( $slug, $options );
}

/**
 * Is the requested shipping provider registered?
 *
 *
 *
 * @param  string  $slug the registerd slug
 *
 * @return boolean
*/
function it_exchange_is_shipping_provider_registered( $slug ) {
	return (boolean) it_exchange_get_registered_shipping_provider( $slug );
}

/**
 * Register a shipping method
 *
 *
 *
 * @param string  $slug    method slug
 * @param string  $class  class name
 *
 * @return boolean
*/
function it_exchange_register_shipping_method( $slug, $class, $args=array() ) {
	// Validate opitons
	if ( ! class_exists( $class ) ) {
		return false;
	}

	// Store the initiated class in our global
	$GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'] = $class;
	$GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'] = $args;

	return true;
}

/**
 * Returns a specific registered shipping method object
 *
 *
 *
 * @param  string $slug the registered slug
 * @param int|bool $product_id
 *
 * @return IT_Exchange_Shipping_Method|false
*/
function it_exchange_get_registered_shipping_method( $slug, $product_id=false ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return false;
	}

	// Retrieve the method class
	$class = $GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'];
	$args = $GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'];

	// Make sure we have a class index and it corresponds to a defined class
	if ( empty( $class ) || ! class_exists( $class ) ) {
		return false;
	}
		
	if ( $r = apply_filters( 'ninja_shop_get_registered_shipping_method', false, $slug, $product_id, $class, $args ) ) {
		return $r;
	}

	// Init the class
	return new $class( $product_id, $args );
}

/**
 * Get the registered shipping method class.
 * 
 *
 * 
 * @param string $slug
 *
 * @return string
 */
function it_exchange_get_registered_shipping_method_class( $slug ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return '';
	}

	return $GLOBALS['it_exchange']['shipping']['methods'][$slug]['class'];
}

/**
 * Get the registered shipping method class.
 *
 *
 *
 * @param string $slug
 *
 * @return array
 */
function it_exchange_get_registered_shipping_method_args( $slug ) {

	// Return false if we don't have one registered
	if ( empty( $GLOBALS['it_exchange']['shipping']['methods'][$slug] ) ) {
		return array();
	}

	return $GLOBALS['it_exchange']['shipping']['methods'][$slug]['args'];
}

/**
 * Returns all registered shipping methods
 *
 *
 *
 * @param  array|string $filtered a string or an array of strings to limit returned methods to specific methods
 *
 * @return array
*/
function it_exchange_get_registered_shipping_methods( $filtered=array() ) {
	$methods = empty( $GLOBALS['it_exchange']['shipping']['methods'] ) ? array() : $GLOBALS['it_exchange']['shipping']['methods'];

	if ( empty( $filtered ) )
		return $methods;

	foreach( (array) $filtered as $method ) {
		if ( isset( $methods[$method] ) )
			unset( $methods[$method] );
	}
	return $methods;
}

/**
 * Returns the value of an address field for the address form.
 *
 *
 *
 * @param string   $field       the form field we are looking for the value
 * @param int|bool $customer_id the wp ID of the customer
 *
 * @return void
*/
function it_exchange_print_shipping_address_value( $field, $customer_id=false ) {
    $customer_id = empty( $customer_id ) ? it_exchange_get_current_customer_id() : $customer_id;
    $saved_address = get_user_meta( $customer_id, 'it_exchange_shipping_address', true );
    $cart_address = it_exchange_get_cart_shipping_address();

    $value = empty( $saved_address[$field] ) ? '' : $saved_address[$field];
    $value = empty( $cart_address[$field] ) ? $value : $cart_address[$field];
    echo 'value="' . esc_attr( $value ) . '" ';
}

/**
 * Formats the Shipping Address for display
 *
 *
 *
 * @param array|bool $shipping_address
 *
 * @return string HTML
*/
function it_exchange_get_formatted_shipping_address( $shipping_address=false ) {

	$shipping = empty( $shipping_address ) ? it_exchange_get_cart_shipping_address() : $shipping_address;

	if ( empty( $shipping['address1'] ) ) {
		$shipping = array();
	}

	$formatted = it_exchange_format_address( $shipping );

	return apply_filters( 'ninja_shop_get_formatted_shipping_address', $formatted );
}

/**
 * Grabs all the shipping methods available to the passed product
 *
 * 1) Grab all shipping methods
 * 2) Check to see if they're enabled
 * 3) Return an arry of ones that are enabled.
 *
 *
 *
 * @param  IT_Exchange_Product $product an IT_Exchange_Product object
 * @param \ITE_Cart            $cart
 *
 * @return IT_Exchange_Shipping_Method[]
*/
function it_exchange_get_available_shipping_methods_for_product( $product, ITE_Cart $cart = null ) {

	$cart              = $cart ?: it_exchange_get_current_cart( false );
	$providers         = it_exchange_get_registered_shipping_providers();
	$provider_methods  = array();
	$available_methods = array();

	// Grab all registerd shipping methods for all providers
	foreach( (array) $providers as $provider ) {
		$provider         = it_exchange_get_registered_shipping_provider( $provider['slug'] );
		$provider_methods = array_merge( $provider_methods, $provider->shipping_methods );
	}

	// Loop through provider methods and only use the ones that are available for this product
	$provider_methods = apply_filters( 'ninja_shop_get_available_shipping_methods_for_product_provider_methods', $provider_methods, $product, $cart );

	foreach( (array) $provider_methods as $slug ) {
		$method = it_exchange_get_registered_shipping_method( $slug, $product->ID );

		if ( ! $method ) {
			continue;
		}

		$available = apply_filters( 'ninja_shop_get_registered_shipping_method_available', $method->available, $slug, $method, $product );

		if ( ! $available ) {
			continue;
		}

		$available_methods[ $slug ] = $method;
	}

	return apply_filters( 'ninja_shop_get_available_shipping_methods_for_product', $available_methods, $product, $cart );
}

/**
 * Get all of the enabled shipping methods for this product.
 *
 * A product can have certain shipping methods disabled, even though the product might otherwise be eligible for them.
 *
 *
 *
 * @param IT_Exchange_Product $product
 * @param string              $return  Return value for shipping methods. Either 'slug' or 'object'.
 * @param ITE_Cart            $cart
 *
 * @return IT_Exchange_Shipping_Method[]|string[]|false
 */
function it_exchange_get_enabled_shipping_methods_for_product( $product, $return = 'object', ITE_Cart $cart = null ) {

	// Are we viewing a new product?
	$screen         = is_admin() ? get_current_screen() : false;
	$is_new_product = is_admin() && ! empty( $screen->action ) && 'add' === $screen->action;

	// Return false if shipping is turned off for this product
	if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) && ! $is_new_product )
		return false;

	$enabled_methods                    = array();
	$product_overriding_default_methods = it_exchange_get_shipping_feature_for_product( 'core-available-shipping-methods', $product->ID );

	foreach( (array) it_exchange_get_available_shipping_methods_for_product( $product, $cart ) as $slug => $available_method ) {
		// If we made it here, the method is available. Check to see if it has been turned off for this specific product
		if ( false !== $product_overriding_default_methods ) {
			if ( ! empty( $product_overriding_default_methods->$slug ) )
				$enabled_methods[$slug] = ( 'slug' === $return ) ? $slug : $available_method;
		} else {
			$enabled_methods[$slug] = ( 'slug' === $return ) ? $slug : $available_method;
		}
	}
	return $enabled_methods;
}

/**
 * Returns the selected shipping method saved in the cart Session
 *
 *
 *
 * @return string|false method slug
*/
function it_exchange_get_cart_shipping_method() {

	$cart = it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return false;
	}

	$method = $cart->get_shipping_method();

	if ( ! $method ) {
		return false;
	}

	return $method->slug;
}

/**
 * This returns available shipping methods for the cart
 *
 * By default, it only returns the highest common denominator for all products.
 * ie: If product one supports methods A and B but product two only supports method A,
 *     this function will only return method A.
 * Toggling the first paramater to false will return a composite of all available methods across products
 *
 *
 *
 * @param boolean   $only_methods_available_to_all defaults to true.
 * @param \ITE_Cart $cart
 *
 * @return IT_Exchange_Shipping_Method[]
*/
function it_exchange_get_available_shipping_methods_for_cart( $only_methods_available_to_all = true, ITE_Cart $cart = null ) {

	// I need this as a global for some hooks later with Table Rate Shipping (and possibly other future add-ons
	$GLOBALS['it_exchange']['shipping']['only_return_methods_available_to_all_cart_products'] = $only_methods_available_to_all;

	$cart      = $cart ?: it_exchange_get_current_cart();
	$methods   = array();
	$product_i = 0;

	/** @var ITE_Cart_Product $cart_product */
	foreach ( $cart->get_items( 'product' ) as $cart_product ) {

		if ( ! $product = $cart_product->get_product() ) {
			continue;
		}

		if ( ! $product->has_feature( 'shipping' ) ) {
			continue;
		}

		// Bump product incrementer
		$product_i++;
		$product_methods = array();

		// Loop through shipping methods available for this product
		foreach( (array) it_exchange_get_enabled_shipping_methods_for_product( $product, 'object', $cart ) as $method ) {

			// Skip if method is false
			if ( empty( $method->slug ) ) {
				continue;
			}

			// If this is the first product, put all available methods in methods array
			if ( ! empty( $method->slug ) && 1 === $product_i ) {
				$methods[$method->slug] = $method;
			}

			// If we're returning all methods, even when they aren't available to other products, tack them onto the array
			if ( ! $only_methods_available_to_all ) {
				$methods[ $method->slug ] = $method;
			}

			// Keep track of all this products methods
			$product_methods[] = $method->slug;
		}

		// Remove any methods previously added that aren't supported by this product
		if ( $only_methods_available_to_all ) {
			foreach( $methods as $slug => $object ) {
				if ( ! in_array( $slug, $product_methods ) ) {
					unset( $methods[ $slug ] );
				}
			}
		}
	}

	$r = apply_filters( 'ninja_shop_get_available_shipping_methods_for_cart', $methods, $cart );

	$GLOBALS['it_exchange']['shipping']['only_return_methods_available_to_all_cart_products'] = true;

	return $r;
}

/**
 * Returns all available shipping methods for all cart products
 *
 *
 *
 * @param \ITE_Cart $cart
 *
 * @return array an array of shipping methods
 */
function it_exchange_get_available_shipping_methods_for_cart_products( ITE_Cart $cart = null ) {

	$cart    = $cart ?: it_exchange_get_current_cart();
	$methods = it_exchange_get_available_shipping_methods_for_cart( false, $cart );

	return apply_filters( 'ninja_shop_get_available_shipping_methods_for_cart_products', $methods, $cart );
}

/**
 * Determine if a cart requires shipping.
 *
 *
 *
 * @param \ITE_Cart $cart
 *
 * @return bool
 */
function it_exchange_cart_requires_shipping( ITE_Cart $cart = null ) {
	$cart = it_exchange_get_current_cart( false ) ?: $cart;

	return $cart && $cart->requires_shipping();
}

/**
 * Returns the cost of shipping for the cart based on selected shipping method(s)
 *
 * If called without the method param, it uses the selected cart method. Use with a param to get estimates for an
 * unselected method
 *
 *
 *
 * @param string|bool $shipping_method optional method.
 * @param bool        $format_price
 * @param \ITE_Cart   $cart
 *
 * @return mixed
 */
function it_exchange_get_cart_shipping_cost( $shipping_method = false, $format_price = true, ITE_Cart $cart = null ) {

	$cart  = $cart ?: it_exchange_get_current_cart();
	$items = $cart->get_items();

	if ( $items->count() === 0 ) {
		return false;
	}

	$cart_cost = 0.00;

	if ( $shipping_method = trim( $shipping_method ) ) {
		$additional_cost = array();
		foreach ( $cart->get_items( 'product' ) as $product ) {
			if ( $product->get_product()->has_feature( 'shipping' ) ) {
				$cart_cost += it_exchange_get_shipping_method_cost_for_cart_item(
					$shipping_method, $product->bc(), false, $cart
				);

				if ( $method = it_exchange_get_registered_shipping_method( $shipping_method ) ) {
					if ( ! isset( $additional_cost[$shipping_method] ) ) {
						$additional_cost[$shipping_method] = 0;
					} else {
						$additional_cost[$shipping_method]++;
					}
				}
			}
		}

		foreach ( $additional_cost as $method => $times ) {
			while ( $times > 0 ) { // intentionally > 0 not >= 0 so that only one additional cost remains.
				$times--;
				$cart_cost-= it_exchange_get_registered_shipping_method( $method )->get_additional_cost_for_cart( $cart );
			}
		}
	} else {

		$shipping_method = it_exchange_get_cart_shipping_method();

		$cart_cost = $cart->get_items( 'shipping', true )
           ->filter( function ( ITE_Shipping_Line_Item $shipping ) use ( $shipping_method ) {

               if ( $shipping_method === 'multiple-methods' ) {

                   if ( ! $shipping->get_aggregate() ) {
                       return true;
                   }

	               $shipping_method = it_exchange_get_multiple_shipping_method_for_cart_product(
                       $shipping->get_aggregate()->get_id()
                   );
               }

               return $shipping->get_method()->slug === $shipping_method;
           } )->total();
	}

	$cart_cost = $format_price ? it_exchange_format_price( $cart_cost ) : $cart_cost;
	
	return apply_filters( 'ninja_shop_get_cart_shipping_cost',
		$cart_cost, $shipping_method, it_exchange_get_session_data( 'products' ), $format_price );
}

/**
 * This will return the shipping cost for a specific method/product combination in the cart.
 *
 *
 *
 * @param string  $method_slug  the shipping method slug
 * @param array   $cart_product the cart product array
 * @param boolean $format_price format the price for a display
 * @param \ITE_Cart $cart
 *
 * @return float|string
*/
function it_exchange_get_shipping_method_cost_for_cart_item( $method_slug, $cart_product, $format_price = false, ITE_Cart $cart = null ) {
	$method = it_exchange_get_registered_shipping_method( $method_slug, $cart_product['product_id'] );
	
	if ( ! $method || ! $method->slug ) {
		return 0;
	}
	
	$cart = $cart ?: it_exchange_get_current_cart();

	$shipping = $cart->get_item( 'product', $cart_product['product_cart_id'] )
		->get_line_items()->with_only( 'shipping' )->filter( function ( ITE_Shipping_Line_Item $item ) use ( $method_slug ) {
			return $item->get_method()->slug === $method_slug && $item->get_aggregate();
		} );

	if ( $shipping->count() === 0 ) {
		$cost = $method->get_shipping_cost_for_product( $cart_product );
	} else {
		$cost = $shipping->total();
	}

	$cost += $method->get_additional_cost_for_cart( $cart );

	$cost = is_numeric( $cost ) ? $cost : 0;

	$cost = $format_price ? it_exchange_format_price( $cost ) : $cost;
	
	return apply_filters( 'ninja_shop_get_shipping_method_cost_for_cart_item', $cost, $method_slug, $cart_product, $format_price, $cart );
}

/**
 * Returns the shipping method slug used by a specific cart product
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 *
 *
 * @param string          $product The product_cart_id in the cart session. NOT the database ID of the product.
 * @param \ITE_Cart|null  $cart
 *
 * @return string
*/
function it_exchange_get_multiple_shipping_method_for_cart_product( $product, ITE_Cart $cart = null ) {

	if ( ! $cart ) {
		$cart = it_exchange_get_current_cart();
	}

	if ( is_string( $product ) ) {
		$product = $cart->get_item( 'product', $product );
	} elseif ( is_array( $product ) && isset( $product['product_cart_id'] ) ) {
		$product = $cart->get_item( 'product', $product['product_cart_id'] );
	}

	if ( ! $product instanceof ITE_Cart_Product ) {
		return false;
	}

	$method   = it_exchange_get_shipping_method_for_item( $product );
	$selected = array();

	foreach ( $cart->get_items( 'product' ) as $other_product ) {
		$product_method = it_exchange_get_shipping_method_for_item( $other_product );

		if ( $product_method ) {
			$selected[ $other_product->get_id() ] = $product_method->slug;
		}
	}

	$slug = $method ? $method->slug : false;

	return apply_filters( 'ninja_shop_get_multiple_shipping_method_for_cart_product', $slug, $selected, $product->get_id(), $cart );
}

/**
 * This function updates the shipping method being used for a specific product in the cart
 *
 * Only applicable when the cart is using multiple shipping methods for multiple products
 *
 *
 *
 * @internal ITE_Cart::set_shipping_method( 'method', $cart_product_item ) should be used instead.
 *
 * @param string $product_cart_id the product_cart_id in the cart session. NOT the database ID of the product
 * @param string $method_slug     the slug of the method this cart product will use
 *
 * @return void
*/
function it_exchange_update_multiple_shipping_method_for_cart_product( $product_cart_id, $method_slug ) {
	$selected_multiple_methods = it_exchange_get_cart_data( 'multiple-shipping-methods' );
	$selected_multiple_methods = empty( $selected_multiple_methods ) ? array() : $selected_multiple_methods;

	$selected_multiple_methods[$product_cart_id] = $method_slug;

	it_exchange_update_cart_data( 'multiple-shipping-methods', $selected_multiple_methods );
}
