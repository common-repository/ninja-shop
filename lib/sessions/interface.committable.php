<?php
/**
 * Committable session.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Committable_Session
 */
interface ITE_Committable_Session extends IT_Exchange_SessionInterface {

	/**
	 * Commit session changes to storage.
	 *
	 *
	 *
	 * @return void
	 */
	public function commit();
}
