<?php
/**
 * This file contains methods for accessing and updating Shipping features
 * 
 * @package IT_Exchange
*/

/**
 * Keeps track of all available shipping features
 *
 *
 * @param string $slug the identifying slug for the shipping feature
 * @param string $class the shipping feature class
 *
 * @return bool|void
*/
function it_exchange_register_shipping_feature( $slug, $class ) {
	// Don't attempt to register if class doesn't exist
	if ( ! class_exists( $class ) )
		return false;

	// Add it to the global
	$GLOBALS['it_exchange']['shipping']['shipping_features'][$slug] = $class;

	// Provide an action for 3rd parties
	do_action( 'ninja_shop_register_shipping_feature', $slug, $class);
}

/**
 * Grabs all shipping registered features from GLOBALS and inits objects and returns
 *
 *
 *
 * @return array
*/
function it_exchange_get_registered_shipping_features() {
	$features = empty( $GLOBALS['it_exchange']['shipping']['shipping_features'] ) ? array() : $GLOBALS['it_exchange']['shipping']['shipping_features'];
	return $features;
}

/**
 * Prints the shipping feature boxes on the add/edit product page
 *
 * This will loop through all registered shipping features for the current product
 * and print thier feature boxes inside the shipping settings metabox.
 * If it determines that a shipping registered shipping feature is not available because
 * the shipping methods its assoicated with are not enabled, it will hide the box.
 *
 *
 *
 * @param  object $product an IT_Exchange_Product object
 *
 * @return void
*/
function it_exchange_do_shipping_feature_boxes( $product ) {
	// Grab all shipping features needed for the passed product
	$shipping_features = it_exchange_get_shipping_features_for_product( $product );

	// Loop through returned shipping features and call the method to print the UI
	foreach( (array) $shipping_features as $feature ) {
		$feature->print_add_edit_feature_box();
	}
}

/**
 * Get a registered shipping feature object
 *
 *
 *
 * @param string                  $slug feature slug
 * @param int|IT_Exchange_Product $product
 * @param array $args Option arguments to pass to features class
 *
 * @return IT_Exchange_Shipping_Feature|false
*/
function it_exchange_get_registered_shipping_feature( $slug, $product = 0, $args=array() ) {

	if ( ! $features = it_exchange_get_registered_shipping_features() ) {
		return false;
	}

	if ( empty( $features[ $slug ] ) ) {
		return false;
	}

	$class = $features[ $slug ];

	if ( ! class_exists( $class ) ) {
		return false;
	}

	$product = $product instanceof IT_Exchange_Product ? $product->ID : $product;

	if ( $args ) {
		return new $class( $product, $args );
	}

	return new $class( $product );
}

/**
 * Grab any features needed by all possible shipping methods applicable to this product
 *
 * - Shipping features are tied to Shipping Methods
 * - Shipping methods are associated with Shipping Providers
 * - Shipping methods are available to a product if the Provider is available to the product type
 *
 *
 *
 * @param IT_Exchange_Product $product an IT_Exchange_Product object
 *
 * @return array of shipping feature objects
*/
function it_exchange_get_shipping_features_for_product( $product ) {
	// Grab all available methods for this product
	$methods  = it_exchange_get_available_shipping_methods_for_product( $product );
	
	// Init features array
	/** @todo move this filter to lib/shipping/shipping-features/init.php. create a functiont o get core shipping features **/
	$features = apply_filters( 'ninja_shop_core_shipping_features', array( 'core-available-shipping-methods' ) );
	
	// Loop through methods and add all required features to the array
	foreach( $methods as $method ) {
		if ( ! empty( $method->shipping_features ) && is_array( $method->shipping_features ) ) {
			$features = array_merge( $features, $method->shipping_features );
		}
	}
	
	// Clean the array
	$features = array_values( array_unique( $features ) );
	
	// Grab registered feature details
	$registered_features = it_exchange_get_registered_shipping_features();
	
	// Init return array
	$shipping_features = array();

	// Loop through array and init objects
	foreach( $registered_features as $slug => $class ) {
		if ( in_array( $slug, $features ) && $feature = it_exchange_get_registered_shipping_feature( $slug, $product ) ) {
			$shipping_features[$slug] = $feature;
		}
	}

	return apply_filters( 'ninja_shop_get_shipping_features_for_product', $shipping_features, $product );
}

/**
 * Gets the values of a shipping feature for a specific post
 *
 *
 *
 * @param string                  $feature The registered feature slug
 * @param int|IT_Exchange_Product $product The product model or product ID.
 * @param array $args                      Optional arguments to pass to the shipping feature
 *
 * @return mixed|false
*/
function it_exchange_get_shipping_feature_for_product( $feature, $product, $args=array() ) {

	if ( ! $product = it_exchange_get_product( $product ) ) {
		return false;
	}
		
	$features = it_exchange_get_registered_shipping_features();

	if ( empty( $features[ $feature ] ) ) {
		return false;
	}

	$shipping_feature = it_exchange_get_registered_shipping_feature( $feature, $product, $args );

	if ( ! $shipping_feature ) {
		return false;
	}

	if ( empty( $shipping_feature->enabled ) || empty( $shipping_feature->values ) ) {
		return false;
	}

	return $shipping_feature->values;
}
