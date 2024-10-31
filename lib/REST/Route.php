<?php
/**
 * REST Route Interface.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;

/**
 * Interface Route
 * @package iThemes\Exchange\REST
 */
interface Route {

	/**
	 * Get the route major version number.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_version();

	/**
	 * Get the route path.
	 *
	 * No starting slash. Include trailing slash. This should not exclude the version number, or any parent paths.
	 *
	 * For example:
	 *
	 * transactions/(?P<id>[\d+])/
	 *
	 *
	 *
	 * @return string
	 */
	public function get_path();

	/**
	 * Get route args.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_query_args();

	/**
	 * Get the route schema.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_schema();

	/**
	 * Whether this has a parent route.
	 *
	 *
	 *
	 * @return bool
	 */
	public function has_parent();

	/**
	 * Get the parent route.
	 *
	 *
	 *
	 * @return Route
	 *
	 * @throws \UnexpectedValueException If no parent route exists.
	 */
	public function get_parent();
}
