<?php
/**
 * Product Feature interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Product_Feature
 */
interface ITE_Product_Feature {

	/**
	 * Get the product feature slug.
	 *
	 * Ex: 'inventory'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the name of the product feature.
	 *
	 * Ex: 'Inventory'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the description of this product feature.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the product types supported by this feature.
	 *
	 *
	 *
	 * @return string[] If empty, it is assumed all product types are supported.
	 */
	public function get_supported_product_types();

	/**
	 * Get the feature for this product.
	 *
	 *
	 *
	 * @param int   $product_id
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function get( $product_id, array $options = array() );

	/**
	 * Set this feature's value for the given product.
	 *
	 *
	 *
	 * @param int   $product_id
	 * @param mixed $value
	 * @param array $options
	 *
	 * @return bool
	 */
	public function set( $product_id, $value, array $options = array() );

	/**
	 * Delete this feature from the given product.
	 *
	 *
	 *
	 * @param int   $product_id
	 * @param array $options
	 *
	 * @return bool
	 */
	public function delete( $product_id, array $options = array() );

	/**
	 * Does the given product have this feature.
	 *
	 *
	 *
	 * @param int   $product_id
	 * @param array $options
	 *
	 * @return bool
	 */
	public function has( $product_id, array $options = array() );

	/**
	 * Does the given product support this feature.
	 *
	 *
	 *
	 * @param int   $product_id
	 * @param array $options
	 *
	 * @return bool
	 */
	public function supports( $product_id, array $options = array() );

}
