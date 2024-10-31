<?php
/**
 * Gateway Request interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_Request
 */
interface ITE_Gateway_Request {

	/**
	 * Get the customer associated with this request.
	 *
	 *
	 *
	 * @return IT_Exchange_Customer
	 */
	public function get_customer();

	/**
	 * Get the name of this request.
	 *
	 *
	 *
	 * @return string
	 */
	public static function get_name();
}
