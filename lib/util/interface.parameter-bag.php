<?php
/**
 * Parameter bag interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Parameter_Bag
 */
interface ITE_Parameter_Bag {

	/**
	 * Get all parameters in this bag.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_params();

	/**
	 * Check if a parameter exists.
	 *
	 *
	 *
	 * @param string $param
	 *
	 * @return bool
	 */
	public function has_param( $param );

	/**
	 * Get a parameter's value.
	 *
	 *
	 *
	 * @param string $param
	 *
	 * @return mixed
	 *
	 * @throws OutOfBoundsException If param does not exist.
	 */
	public function get_param( $param );

	/**
	 * Set a parameter's value.
	 *
	 *
	 *
	 * @param string $param
	 * @param mixed  $value Values should be unslashed.
	 *
	 * @return bool
	 */
	public function set_param( $param, $value );

	/**
	 * Remove a parameter.
	 *
	 * Should not error if the given $param does not exist.
	 *
	 *
	 *
	 * @param string $param
	 *
	 * @return bool
	 */
	public function remove_param( $param );
}
