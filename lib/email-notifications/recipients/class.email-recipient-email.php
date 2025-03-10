<?php
/**
 * Contains the simple email recipient class.
 *
 * 
 * @license GPLv2
 */

/***
 * Class IT_Exchange_Email_Recipient_Email
 */
class IT_Exchange_Email_Recipient_Email implements IT_Exchange_Email_Recipient {

	/**
	 * @var string
	 */
	private $email;

	/**
	 * IT_Exchange_Email_Recipient_Email constructor.
	 *
	 * @param string $email
	 */
	public function __construct( $email ) {

		if ( empty( $email ) || ! is_string( $email ) || ! is_email( $email ) ) {
			throw new InvalidArgumentException( '$email must be a valid email string.' );
		}

		$this->email = $email;
	}

	/**
	 * Get the recipient's email address.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Get the recipient's first name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_first_name() {

		$parts = explode( '@', $this->get_email() );

		return $parts[0];
	}

	/**
	 * Get the recipient's last name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_last_name() {
		return $this->get_first_name();
	}

	/**
	 * Get the recipient's full name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_full_name() {
		return $this->get_first_name();
	}

	/**
	 * Get the recipient's username, if one exists.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_username() {
		$user = get_user_by( 'email', $this->get_email() );

		if ( $user ) {
			return $user->user_login;
		}

		return '';
	}
}
