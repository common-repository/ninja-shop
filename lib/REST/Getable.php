<?php
/**
 * Getable Route.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Getable
 *
 * @package iThemes\Exchange\REST
 */
interface Getable extends Route {

	/**
	 * Handle a GET request.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_get( Request $request );

	/**
	 * Whether the user has permission to access this route.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 * @param AuthScope                      $scope
	 *
	 * @return bool
	 */
	public function user_can_get( Request $request, AuthScope $scope );
}
