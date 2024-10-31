<?php
/**
 * Middleware Interface.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Middleware;
use iThemes\Exchange\REST\Request;

/**
 * Interface Middleware
 * @package iThemes\Exchange\REST
 */
interface Middleware {

	/**
	 * Handle a REST request.
	 *
	 *
	 *
	 * @param \iThemes\Exchange\REST\Request             $request
	 * @param \iThemes\Exchange\REST\Middleware\Delegate $next
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function handle( Request $request, Delegate $next );
}
