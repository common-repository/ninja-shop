<?php
/**
 * Individual Log item.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Log_Item
 */
class ITE_Log_Item {

	/** @var array */
	private $properties;

	/**
	 * ITE_Log_Item constructor.
	 *
	 * @param array $properties
	 */
	public function __construct( array $properties ) { $this->properties = $properties; }

	/**
	 * Get the log level. One of the LogLevel constants.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_level() {
		return $this->properties['level'];
	}

	/**
	 * Get the log message. This should already be interpolated.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->properties['message'];
	}

	/**
	 * Get the time the log item occurred.
	 *
	 *
	 *
	 * @return \DateTime
	 */
	public function get_time() {
		return $this->properties['time'];
	}

	/**
	 * Does this log item support IP addresses.
	 *
	 *
	 *
	 * @return bool
	 */
	public function has_ip() {
		return array_key_exists( 'ip', $this->properties );
	}

	/**
	 * Get the IP address of the user.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_ip() {
		return $this->properties['ip'];
	}

	/**
	 * Does this log item support tracking the logged-in user ID.
	 *
	 *
	 *
	 * @return bool
	 */
	public function has_user() {
		return array_key_exists( 'user', $this->properties );
	}

	/**
	 * Get the logged-in user's ID.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_user_id() {
		return $this->has_user() ? $this->properties['user'] : 0;
	}

	/**
	 * Get the User object of the logged-in user. May be null if user deleted.
	 *
	 *
	 *
	 * @return \WP_User|null
	 */
	public function get_user() {
		return get_userdata( $this->get_user_id() ) ?: null;
	}

	/**
	 * Does this log item support tracking the log group.
	 *
	 *
	 *
	 * @return bool
	 */
	public function has_group() {
		return array_key_exists( 'group', $this->properties );
	}

	/**
	 * Get the group this log item is from.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_group() {
		return $this->properties['group'];
	}
}
