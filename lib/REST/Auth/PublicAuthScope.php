<?php
/**
 * Public Auth Scope.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Auth;

/**
 * Class PublicAuthScope
 *
 * @package iThemes\Exchange\REST\Auth
 */
class PublicAuthScope implements AuthScope {

	/**
	 * @inheritDoc
	 */
	public function can( $capability, $args = null ) {

		if ( $capability === 'read' || $capability === 'exists' ) {
			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return __( 'Anonymous', 'it-l10n-ithemes-exchange' );
	}
}
