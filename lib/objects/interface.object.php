<?php
/**
 * Object Interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Object
 */
interface ITE_Object {

	/**
	 * Get the object ID.
	 *
	 *
	 *
	 * @return int|string
	 */
	public function get_ID();

	/**
	 * Get a string representation of this object.
	 *
	 * Should be a short string.
	 *
	 *
	 *
	 * @return string
	 */
	public function __toString();

	/**
	 * Get the object type.
	 *
	 *
	 *
	 * @return ITE_Object_Type
	 */
	public static function get_object_type();
}
