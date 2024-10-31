<?php
/**
 * Queryable Logger.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Queryable_Logger
 */
interface ITE_Queryable_Logger extends \Psr\Log\LoggerInterface {

	/**
	 * Query the log source for given logs.
	 *
	 * See ::get_supported_filters() for available where expressions.
	 *
	 * MUST support pagination and ordering by 'time' and 'severity'
	 *
	 *
	 *
	 * @param \Doctrine\Common\Collections\Criteria $criteria
	 * @param bool                                  $has_more Set to whether there are more records available.
	 *
	 * @return ITE_Log_Item[]
	 */
	public function query( \Doctrine\Common\Collections\Criteria $criteria, &$has_more );

	/**
	 * Get the filters that are supported by this log source.
	 *
	 * Should return an array of data => criterion names.
	 *
	 * Example:
	 *  'group' => 'lgroup'
	 *
	 * Globally, the following filters can be supported. 'group', 'user', 'ip', 'level', 'message'. Where 'message'
	 * is a %LIKE% comparison and 'ip' is a LIKE% comparison.
	 *
	 *
	 *
	 * @return string[]
	 */
	public function get_supported_filters();
}
