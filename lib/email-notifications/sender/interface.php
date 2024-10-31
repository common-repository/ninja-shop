<?php
/**
 * Contains the 'sender' interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Email_Sender
 */
interface IT_Exchange_Email_Sender {

	/**
	 * Send the email.
	 *
	 *
	 *
	 * @param IT_Exchange_Sendable $email
	 *
	 * @return bool
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function send( IT_Exchange_Sendable $email );

	/**
	 * Bulk send emails.
	 * 
	 *
	 * 
	 * @param IT_Exchange_Sendable[] $emails
	 *
	 * @return bool
	 * @throws InvalidArgumentException If a given email does not implement IT_Exchange_Sendable
	 * @throws IT_Exchange_Email_Delivery_Exception
	 */
	public function bulk_send( array $emails );
}
