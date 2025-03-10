<?php
/**
 * Product Features registry.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Product_Feature_Registry
 */
class ITE_Product_Feature_Registry {

	/** @var ITE_Product_Feature[] */
	private static $features = array();

	/**
	 * Register a product feature.
	 *
	 *
	 *
	 * @param ITE_Product_Feature $feature
	 *
	 * @return bool
	 */
	public static function register( ITE_Product_Feature $feature ) {
		if ( static::get( $feature->get_slug() ) ) {
			return false;
		} else {
			static::$features[ $feature->get_slug() ] = $feature;
			static::hooks( $feature );

			return true;
		}
	}

	/**
	 * Get a registered product feature.
	 *
	 *
	 *
	 * @param string $slug
	 *
	 * @return ITE_Product_Feature|null
	 */
	public static function get( $slug ) {
		return isset( static::$features[ $slug ] ) ? static::$features[ $slug ] : null;
	}

	/**
	 * Get all registered product features.
	 *
	 *
	 *
	 * @return ITE_Product_Feature[]
	 */
	public static function all() {
		return array_values( static::$features );
	}

	/**
	 * Get all product features that are optionally supported.
	 *
	 *
	 *
	 * @return ITE_Optionally_Supported_Product_Feature[]
	 */
	public static function optional() {
		return array_filter( static::all(), function ( ITE_Product_Feature $feature ) {
			return $feature instanceof ITE_Optionally_Supported_Product_Feature;
		} );
	}

	/**
	 * Register hooks.
	 *
	 *
	 *
	 * @param ITE_Product_Feature $feature
	 */
	protected static function hooks( ITE_Product_Feature $feature ) {

		it_exchange_register_product_feature( $feature->get_slug(), $feature->get_description() );

		add_filter( "ninja_shop_get_product_feature_{$feature->get_slug()}", function ( $_, $product_id, $options ) use ( $feature ) {
			return $feature->get( $product_id, (array) $options );
		}, 10, 3 );

		add_filter( "ninja_shop_update_product_feature_{$feature->get_slug()}", function ( $product_id, $value, $options ) use ( $feature ) {
			return $feature->set( $product_id, $value, $options );
		}, 10, 3 );

		add_filter( "ninja_shop_product_has_feature_{$feature->get_slug()}", function ( $_, $product_id, $options ) use ( $feature ) {
			return $feature->has( $product_id, (array) $options );
		}, 10, 3 );

		add_filter( "ninja_shop_product_supports_feature_{$feature->get_slug()}", function ( $_, $product_id, $options ) use ( $feature ) {
			return $feature->supports( $product_id, (array) $options );
		}, 10, 3 );

		if ( $feature->get_supported_product_types() ) {
			$types = $feature->get_supported_product_types();
		} else {
			$types = array_keys( it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) ) );
		}

		foreach ( $types as $type ) {
			it_exchange_add_feature_support_to_product_type( $feature->get_slug(), $type );
		}
	}
}
