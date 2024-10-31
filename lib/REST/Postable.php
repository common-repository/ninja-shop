<?php
/**
 * Postable Route.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Postable
 *
 * @package iThemes\Exchange\REST
 */
interface Postable extends Route {

	/**
	 * Handle a POST request.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_post( Request $request );

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
	public function user_can_post( Request $request, AuthScope $scope );
}
