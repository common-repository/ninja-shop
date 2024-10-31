<?php
/**
 * Contains the middleware interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Middleware
 */
interface IT_Exchange_Email_Middleware {

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 *
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable );
}
