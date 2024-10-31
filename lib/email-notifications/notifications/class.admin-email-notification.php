<?php
/**
 * Contains the admin notification email class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Admin_Email_Notification
 */
class IT_Exchange_Admin_Email_Notification extends IT_Exchange_Email_Notification {

	/**
	 * @var array
	 */
	private $emails = array();

	/**
	 * Setup this object's properties.
	 *
	 *
	 *
	 * @param array $data
	 */
	protected function setup_properties( $data ) {
		parent::setup_properties( $data );

		if ( isset( $data['emails'] ) && is_array( $data['emails'] ) ) {
			$this->set_emails( $data['emails'] );
		} else {

			$general = it_exchange_get_option( 'settings_general' );
			$email   = ! empty( $general['company-email'] ) ? $general['company-email'] : get_option( 'admin_email' );

			$this->add_email( $email );
		}
	}

	/**
	 * Get the notification type.
	 *
	 *
	 *
	 * @param bool $label
	 *
	 * @return string
	 */
	public function get_type( $label = false ) {
		return $label ? __( 'Admin', 'it-l10n-ithemes-exchange' ) : 'admin';
	}

	/**
	 * Get the emails to notify.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_emails() {
		return $this->emails;
	}

	/**
	 * Set the emails to notify.
	 *
	 *
	 *
	 * @param array $emails
	 *
	 * @return self
	 */
	public function set_emails( array $emails = array() ) {
		$this->emails = array_filter( array_map( 'trim', $emails ) );

		return $this;
	}

	/**
	 * Add an email to notify.
	 *
	 *
	 *
	 * @param string $email
	 *
	 * @return self
	 */
	public function add_email( $email ) {
		if ( ! in_array( $email, $this->get_emails() ) ) {
			$this->emails[] = $email;
		}

		return $this;
	}

	/**
	 * Remove an email to notify.
	 *
	 *
	 *
	 * @param string $email
	 *
	 * @return self
	 */
	public function remove_email( $email ) {

		$i = array_search( $email, $this->get_emails() );

		if ( $i !== false ) {
			unset( $this->emails[ $i ] );
		}

		return $this;
	}

	/**
	 * Get the data to save.
	 *
	 *
	 *
	 * @return array
	 */
	protected function get_data_to_save() {
		$data = parent::get_data_to_save();

		$data['emails'] = $this->get_emails();

		return $data;
	}
}
