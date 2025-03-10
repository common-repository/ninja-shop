<?php
/**
 * For Purgeable Loggers.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Purgeable_Logger
 */
interface ITE_Purgeable_Logger extends \Psr\Log\LoggerInterface {

	/**
	 * Delete all logs generated by this logger.
	 *
	 *
	 *
	 * @return bool
	 */
	public function purge();
}
