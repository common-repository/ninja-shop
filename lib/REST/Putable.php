<?php
/**
 * Putable Route.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST;
use iThemes\Exchange\REST\Auth\AuthScope;

/**
 * Interface Putable
 *
 * @package iThemes\Exchange\REST
 */
interface Putable extends Route {

	/**
	 * Handle a PUT request.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handle_put( Request $request );

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
	public function user_can_put( Request $request, AuthScope $scope );
}
