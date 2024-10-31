<?php
/**
 * Settings Controller class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Settings_Controller
 */
class ITE_Settings_Controller {

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array|null
	 */
	private $settings = null;

	/**
	 * ITE_Settings_Controller constructor.
	 *
	 * @param string $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * Lazy-load settings.
	 *
	 *
	 *
	 * @return array
	 */
	protected function lazy_load_settings() {

		if ( ! $this->settings ) {
			$this->settings = it_exchange_get_option( $this->name );
		}

		return $this->settings;
	}

	/**
	 * Get a setting.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws OutOfBoundsException If invalid $key requested.
	 */
	public function get( $key ) {

		$settings = $this->lazy_load_settings();

		if ( array_key_exists( $key, $settings ) ) {
			return $settings[ $key ];
		}

		/**
		 * TODO: Investigate why this was originally setup to throw an error.
		 * The `return null` below was added because this error was being thrown by third-party add-ons.
		 * Not sure what the purpose of throwing an error just because a setting hasn't been added yet is.
		 */
		return null;

		throw new OutOfBoundsException( "Key '$key' does not exist.'" );
	}

	/**
	 * Check if a setting exists.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return array_key_exists( $key, $this->lazy_load_settings() );
	}

	/**
	 * Get all settings values.
	 *
	 *
	 *
	 * @return array
	 */
	public function all() {
		return $this->lazy_load_settings();
	}

	/**
	 * Alter a setting value.
	 *
	 *
	 *
	 * @param string $key
	 * @param mixed  $val
	 * @param bool   $save
	 *
	 * @return bool
	 */
	public function set( $key, $val, $save = true ) {

		$settings         = $this->lazy_load_settings();
		$settings[ $key ] = $val;
		$this->settings   = $settings;

		if ( $save ) {
			return it_exchange_save_option( $this->name, $settings, true );
		} else {
			return true;
		}
	}

	/**
	 * Save settings.
	 *
	 *
	 *
	 * @return bool
	 */
	public function save() {

		if ( ! $this->settings ) {
			return false;
		}

		return it_exchange_save_option( $this->name, $this->settings, true );
	}
}
