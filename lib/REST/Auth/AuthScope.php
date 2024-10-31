<?php
/**
 * AuthScope interface.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Interface AuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
interface AuthScope {

	/**
	 * Can this auth scope perform a given function.
	 *
	 *
	 *
	 * @param string $capability
	 * @param mixed  $args,...
	 *
	 * @return bool
	 */
	public function can( $capability, $args = null );

	/**
	 * Representation of who is authed.
	 *
	 *
	 *
	 * @return string
	 */
	public function __toString();
}
