<?php
/**
 * This file contains methods for accessing and updating product features
 *
 * 
 * @package IT_Exchange
*/

/**
 * Check if a given product supports a specific feature
 *
 *
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 *
 * @return boolean
*/
function it_exchange_product_supports_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'ninja_shop_product_supports_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Check if a given product has a specific feature
 *
 *
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 *
 * @return boolean
*/
function it_exchange_product_has_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'ninja_shop_product_has_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Update the given product's feature value
 *
 *
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param mixed $feature_value the value for the feature
 * @param array $options the options for the feature
 *
 * @return boolean
*/
function it_exchange_update_product_feature( $product_id, $feature_key, $feature_value, $options=array() ) {
	do_action( 'ninja_shop_update_product_feature_' . $feature_key, $product_id, $feature_value, $options );
}

/**
 * Get the value for a feature of a specific product
 *
 *
 * @param integer $product_id the WordPress post ID for the product
 * @param string $feature_key the slug for the feature
 * @param array $options
 *
 * @return mixed the value of the feature
*/
function it_exchange_get_product_feature( $product_id, $feature_key, $options=array() ) {
	return apply_filters( 'ninja_shop_get_product_feature_' . $feature_key, false, $product_id, $options );
}

/**
 * Adds support for a specific product-feature to a specific product-type
 *
 *
 * @param string $feature_key the slug for the feature
 * @param string $product_type the product-type slug
 *
 * @return void
*/
function it_exchange_add_feature_support_to_product_type( $feature_key, $product_type ) {
	$_feature_key = str_replace( 'temp_disabled_', '', $feature_key );
	if ( ! isset( $GLOBALS['it_exchange']['product_features'][$_feature_key] ) )
		return;
	$GLOBALS['it_exchange']['product_features'][$feature_key]['product_types'][$product_type] = true;
}

/**
 * Removes support for a product-feature from a specific product-type
 *
 *
 * @param string $feature_key the slug for the feature
 * @param string $product_type the product-type slug
 *
 * @return void
*/
function it_exchange_remove_feature_support_for_product_type( $feature_key, $product_type ) {
	if ( isset( $GLOBALS['it_exchange']['product_features'][$feature_key]['product_types'][$product_type] ) )
		$GLOBALS['it_exchange']['product_features'][$feature_key]['product_types'][$product_type] = false;
	do_action( 'ninja_shop_remove_feature_support_for_product_type', $feature_key, $product_type );
}

/**
 * Check if a given product-type supports a specific product feature
 *
 *
 *
 * @param string $product_type the product-type slug
 * @param string $feature_key the slug for the feature
 *
 * @return boolean
*/
function it_exchange_product_type_supports_feature( $product_type, $feature_key ) {
	$product_features = it_exchange_get_registered_product_features();

	if ( empty( $product_features[$feature_key] ) )
		return false;

	$product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );

	if ( empty( $product_types[$product_type] ) ) {
		return false;
	}

	if ( ! empty( $product_features[$feature_key]['product_types'][$product_type] ) ) {
		return true;
	}

	if ( isset( $product_types[$product_type]['options']['supports'][$feature_key] ) ) {
		return $product_types[$product_type]['options']['supports'][$feature_key];
	}

	return false;
}

/**
 * Keeps track of all available product features
 *
 *
 * @param string $slug
 * @param string $description
 * @param array $default_product_types
 *
 * @return void
*/
function it_exchange_register_product_feature( $slug, $description='', $default_product_types=array() ) {
	$GLOBALS['it_exchange']['product_features'][$slug]['slug']        = $slug;
	$GLOBALS['it_exchange']['product_features'][$slug]['description'] = $description;
	do_action( 'ninja_shop_register_product_feature', $slug, $description, $default_product_types );
}

/**
 * Returns all registered product_features
 *
 *
 *
 * @return array
*/
function it_exchange_get_registered_product_features() {
	$product_features = isset( $GLOBALS['it_exchange']['product_features'] ) ? (array) $GLOBALS['it_exchange']['product_features'] : array();
	return apply_filters( 'ninja_shop_get_registered_product_features', $product_features );
}
