<?php
/**
 * RESTful object type.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_RESTful_Object_Type
 */
interface ITE_RESTful_Object_Type extends ITE_Object_Type {

	/**
	 * Get the collection route.
	 *
	 *
	 *
	 * @return \iThemes\Exchange\REST\Getable
	 */
	public function get_collection_route();

	/**
	 * Get the route for a single object.
	 *
	 *
	 *
	 * @param string $object_id
	 *
	 * @return \iThemes\Exchange\REST\Getable
	 */
	public function get_object_route( $object_id );
}
