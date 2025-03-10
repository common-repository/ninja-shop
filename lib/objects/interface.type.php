<?php
/**
 * Object Type interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Object_Type
 */
interface ITE_Object_Type {

	/**
	 * Get the slug of this object type.
	 *
	 * MUST be globally unique. Ex: 'transaction' or 'customer'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the label for this object type.
	 *
	 * Ex: Transaction or Customer.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Create an object from a set of attributes.
	 *
	 *
	 *
	 * @param array $attributes
	 *
	 * @return ITE_Object
	 *
	 * @throws BadMethodCallException If this method is not supported by the object type.
	 */
	public function create_object( array $attributes );

	/**
	 * Retrieve an object by its ID.
	 *
	 *
	 *
	 * @param string $id
	 *
	 * @return ITE_Object|null Object or null if not found.
	 */
	public function get_object_by_id( $id );

	/**
	 * Retrieve objects matching the given criteria.
	 *
	 *
	 *
	 * @param array|\Doctrine\Common\Collections\Criteria $criteria
	 *
	 * @return ITE_Object[]
	 *
	 * @throws BadMethodCallException If this method is not supported by the object type.
	 */
	public function get_objects( \Doctrine\Common\Collections\Criteria $criteria = null );

	/**
	 * Delete an object by its ID.
	 *
	 *
	 *
	 * @param string $id
	 *
	 * @return bool True if deleted or already deleted, false if not able to delete.
	 *
	 * @throws BadMethodCallException If this method is not supported by the object type.
	 */
	public function delete_object_by_id( $id );

	/**
	 * Does this object type support meta.
	 *
	 *
	 *
	 * @return bool
	 */
	public function supports_meta();

	/**
	 * Is this object type RESTful.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_restful();
}
