<?php
/**
 * Deletable Route.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Deletable
 *
 * @package iThemes\Exchange\REST
 */
interface Deletable extends Route {

	/**
	 * Handle a DELETE request.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_delete( Request $request );

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
	public function user_can_delete( Request $request, AuthScope $scope );
}
