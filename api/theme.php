<?php
/**
 * Loads the theme API
 *
 * Much of the inspiration for our theme API comes from Jonathan Davis from Shopp
 * https://shopplugin.net/
 *
 * The Theme API is a higher level API than the rest of the files in this directory.
 * - To use the theme API, call it_exchange( 'context', 'method', array( 'optional' => 'options' ) );
 * - Contexts are mapped to classes included in this file. ie: store, product, customer.
 * - Methods are held found within each class and contain various options for output.
 * - All theme API calls print data by default. To return the data, prefix it with 'get-'. ie: it_exchange( 'product', 'get-title' );
 */
include( $this->_plugin_path . '/api/theme/store.php' );
include( $this->_plugin_path . '/api/theme/product.php' );
include( $this->_plugin_path . '/api/theme/download.php' );
include( $this->_plugin_path . '/api/theme/cart.php' );
include( $this->_plugin_path . '/api/theme/line-item.php' );
include( $this->_plugin_path . '/api/theme/cart-item.php' );
include( $this->_plugin_path . '/api/theme/messages.php' );
include( $this->_plugin_path . '/api/theme/customer.php' );
include( $this->_plugin_path . '/api/theme/login.php' );
include( $this->_plugin_path . '/api/theme/registration.php' );
include( $this->_plugin_path . '/api/theme/coupons.php' );
include( $this->_plugin_path . '/api/theme/checkout.php' );
include( $this->_plugin_path . '/api/theme/transaction-method.php' );
include( $this->_plugin_path . '/api/theme/transactions.php' );
include( $this->_plugin_path . '/api/theme/transaction.php' );
include( $this->_plugin_path . '/api/theme/transaction-activity.php' );
include( $this->_plugin_path . '/api/theme/address.php' );
include( $this->_plugin_path . '/api/theme/billing.php' );
include( $this->_plugin_path . '/api/theme/shipping.php' );
include( $this->_plugin_path . '/api/theme/shipping-method.php' );
include( $this->_plugin_path . '/api/theme/purchase-dialog.php' );
include( $this->_plugin_path . '/api/theme/email.php' );

/**
 * Defines the main it_exchange function
 *
 * 
 *
 * @todo Provide proper documentation for the parameters and various usages
 *
 * @return void|bool|string
 *  Void on usage error, or echo of data,
 *  bool when using support and has and is,
 *  string when getting the requested data
 */
function it_exchange() {

	// Set array keys for possible params
	$params = array( 'one', 'two', 'three' );

	// Grab number of params passed in
	$num_args = func_num_args();

	// Set initial values
	$object  = false;
	$context = false;
	$tag     = false;
	$method  = false;
	$options = array(
		'echo'   => true,
		'return' => false,
	);
	$get     = false;

	// Die if we don't have any args
	if ( $num_args < 1 ) {
		it_exchange_add_message( 'error', sprintf( __( 'Coding Error: Incorrect number of args passed to %s', 'it-l10n-ithemes-exchange' ), 'it_exchange()' ) );
		return false;
	}

	$passed_params = func_get_args();
	$params = array_combine( array_slice( $params, 0, $num_args ), $passed_params );

	// Parse Params
	if ( is_object( $params['one'] ) ) {
		// When first param is an API object
		$object = $params['one'];
		$context = ! empty( $object->api ) ? $object->api : strtolower( get_class( $object ) );
		$tag = strtolower( $params['two'] );
		// Parse options
		if ( $num_args > 2 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['three'] );
		}
	} else if ( false !== strpos( $params['one'], '.' ) ) {
		// When first param is the object.method string format
		list( $context, $tag ) = explode( '.', strtolower( $params['one'] ) );
		// Parse options if present
		if ( $num_args > 1 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['two'] );
		}
	} else if ( '' == $context . $tag ) {
		// When context is first param and method is second param
		list( $context, $tag ) = array_map( 'strtolower', array( $params['one'], $params['two'] ) );
		// Parse options
		if ( $num_args > 2 ) {
			// This is cool. It allows options to be passed as an array or in URL param format
			$options = it_exchange_parse_options( $params['three'] );
		}
	}

	// Strip hypens from method name
	$tag = str_replace ( '-', '', $tag );

	// Strip get prefix from requested method and set flags
	if ( 'get' == substr( $tag, 0, 3 ) ) {
		$tag = substr( $tag, 3 );
		$get = true;
	}

	// Strip has prefix from request method and set flags
	if ( 'has' == substr( $tag, 0, 3 ) ) {
		$tag = substr( $tag, 3 );
		$options['has'] = true;
	} else {
		$options['has'] = false;
	}

	// Strip has prefix from request method and set flags
	if ( 'supports' == substr( $tag, 0, 8 ) ) {
		$tag = substr( $tag, 8 );
		$options['supports'] = true;
	} else {
		$options['supports'] = false;
	}

	// Set object
	if ( ! is_object( $object ) ) {

		// Set the class name based on params
		$class_name = 'IT_Theme_API_' . str_replace( ' ', '_', ucwords( str_replace( '-', ' ', strtolower( $context ) ) ) );
		if ( 'IT_Theme_API_Cart-item' == $class_name )
			$class_name = 'IT_Theme_API_Cart_Item';
		if ( 'IT_Theme_API_Transaction-method' == $class_name )
			$class_name = 'IT_Theme_API_Transaction_Method';
		if ( 'IT_Theme_API_Purchase-dialog' == $class_name )
			$class_name = 'IT_Theme_API_Purchase_Dialog';
		if ( 'IT_Theme_API_Shipping-method' == $class_name )
			$class_name = 'IT_Theme_API_Shipping_Method';

		// Does the class exist and return an Ninja Shop theme API context?
		if ( ! is_callable( array( $class_name, 'get_api_context' ) ) ) {
			it_exchange_add_message( 'error', sprintf( __( 'Coding Error: <em>%s</em> is not a valid Ninja Shop theme API context', 'it-l10n-ithemes-exchange' ), $context ) );
			return false;
		}

		// Set the object
		$object = new $class_name();
	} else {
		$class_name = get_class( $object );
	}

	// Is the requested tag mapped to a method
	if ( empty( $object->_tag_map[$tag] ) ) {
		if ( ! $tag_callback = apply_filters( 'ninja_shop_theme_api_get_extended_tag_functions', false, $class_name, $tag ) ) {
			it_exchange_add_message( 'error', sprintf( __( 'Coding Error: <em>%s</em> in not a mapped method inside the <em>%s</em> Ninja Shop theme API class', 'it-l10n-ithemes-exchange' ), $tag, $class_name ) );
			return false;
		}

	} else {
		$method       = $object->_tag_map[$tag];
		$tag_callback = array( $object, strtolower( $method ) );
	}

	// Does the method called exist on this class?
	if ( empty( $tag_callback )  || ! is_callable( $tag_callback ) ) {
		it_exchange_add_message( 'error', sprintf( __( 'Coding Error: <em>%s</em> in not a callable method inside the <em>%s</em> Ninja Shop theme API class', 'it-l10n-ithemes-exchange' ), $method, $class_name ) );
		return false;
	}

	// Get the results from the class method
	$result = call_user_func( $tag_callback, $options );

	// Filters
	$result = apply_filters( 'ninja_shop_theme_api', $result, strtolower( $context ), strtolower( $method ), $options );
	$result = apply_filters( 'ninja_shop_theme_api_' . strtolower( $context ), $result, strtolower( $method ), $options );
	$result = apply_filters( 'ninja_shop_theme_api_' . strtolower( $context ) . '_' . strtolower( $method ), $result, $options );

	// Force boolean result
	if ( isset( $options['is'] ) ) {
		if ( it_exchange_str_true( $options['is'] ) ) {
			if ( $result )
				return true;
		} else {
			if ($result == false)
				return true;
		}
		return false;
	}

	// Always return a boolean if the result is boolean
	if ( is_bool( $result ) )
		return $result;

	if ( isset( $options['format'] ) && in_array( $options['format'], array( 'value', 'var_value', 'raw' ), true ) && empty( $options['no-esc'] ) ) {
		$result = esc_html( $result );
	}

	// Return result without printing if requested
	if ( $get
			|| ( isset( $options['return'] ) && it_exchange_str_true( $options['return'] ) )
			|| ( isset( $options['echo'] ) && ! it_exchange_str_true( $options['echo'] ) )
		)
		return $result;

	// Output the result
	if ( is_scalar( $result ) )
		echo $result;
	else
		return $result;

	return true;
}

/**
 * Setup a loop for use in the Theme API.
 *
 *
 *
 * @param string   $single Singular key form. Example 'product'.
 * @param string   $plural Plural key form. Example 'products'.
 * @param callable $items  Callback that returns the items to loop over.
 *
 * @return bool
 */
function it_theme_api_loop( $single, $plural, $items ) {
	
	if ( empty( $GLOBALS['it_exchange'][ $plural ] ) ) {
		$GLOBALS['it_exchange'][ $plural ] = call_user_func( $items );
		$GLOBALS['it_exchange'][ $single ] = reset( $GLOBALS['it_exchange'][ $plural ]  );
		return true;
	} elseif ( next( $GLOBALS['it_exchange'][ $plural ] ) ) {
		$GLOBALS['it_exchange'][ $single ] = current( $GLOBALS['it_exchange'][ $plural ]  );

		return true;
	} else {
		$GLOBALS['it_exchange'][ $plural ] = array();
		end( $GLOBALS['it_exchange'][ $plural ] );
		$GLOBALS['it_exchange'][ $single ] = null;

		return false;
	}
}

/**
 * Enforces minimal class structure
 *
 *
*/
interface IT_Theme_API {
	function get_api_context();
}
