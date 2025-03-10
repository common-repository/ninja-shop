<?php
/**
 * For a logger that can retrieve its log items.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Retrievable_Logger
 */
interface ITE_Retrievable_Logger extends \Psr\Log\LoggerInterface {

	/**
	 * Get log items from this logger.
	 *
	 *
	 *
	 * @param int  $page
	 * @param int  $per_page
	 * @param bool $has_more Set to whether there are more logs available.
	 *
	 * @return ITE_Log_Item[]
	 */
	public function get_log_items( $page = 1, $per_page = 100, &$has_more );
}
