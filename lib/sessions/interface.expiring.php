<?php
/**
 * Expiring Session Interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Expiring_Session
 */
interface ITE_Expiring_Session extends IT_Exchange_SessionInterface  {

	/**
	 * Get the time this session expires.
	 *
	 *
	 *
	 * @return \DateTime|null
	 */
	public function expires_at();
}
