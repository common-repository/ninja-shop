<?php
/**
 * Contains the email recipient interface.
 *
 * 
 * @license GPLv2
 */

/**
 * interface IT_Exchange_Email_Recipient
 */
interface IT_Exchange_Email_Recipient {

	/**
	 * Get the recipient's email address.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_email();

	/**
	 * Get the recipient's first name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_first_name();

	/**
	 * Get the recipient's last name.
	 * 
	 *
	 * 
	 * @return string
	 */
	public function get_last_name();

	/**
	 * Get the recipient's full name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_full_name();

	/**
	 * Get the recipient's username, if one exists.
	 * 
	 *
	 * 
	 * @return string
	 */
	public function get_username();
}
