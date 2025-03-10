<?php
/**
 * Contains the sendable interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email
 */
interface IT_Exchange_Sendable extends Serializable {

	/**
	 * Get the subject line.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_subject();

	/**
	 * Get the body.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_body();

	/**
	 * Get the email template.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template();

	/**
	 * Get the recipient for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient
	 */
	public function get_recipient();

	/**
	 * Get the CCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_ccs();

	/**
	 * Get the BCCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_bccs();

	/**
	 * Get the context for this email.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_context();
}
