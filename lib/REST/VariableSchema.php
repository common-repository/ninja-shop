<?php
/**
 * VariableSchema interface.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface VariableSchema
 *
 * @package iThemes\Exchange\REST
 */
interface VariableSchema extends Route {

	/**
	 * Get the methods that a schema varies on.
	 *
	 * Example: 'GET', 'POST'.
	 *
	 *
	 *
	 * @return array
	 */
	public function schema_varies_on();

	/**
	 * Get the schema for a method.
	 *
	 *
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	public function get_schema_for_method( $method );
}
