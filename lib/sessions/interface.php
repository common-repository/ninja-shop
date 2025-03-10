<?php
/**
 * Contains the session interface.
 *
 * 
 * @license GPLv2
 */

/**
 * The IT_Exchange_Session class holds cart and purchasing details
 *
 *
 */
interface IT_Exchange_SessionInterface {

	/**
	 * Returns session data
	 *
	 * All data or optionally, data for a specific key
	 *
	 *
	 *
	 * @param string|bool $key Specify the data to retrieve, if false all data will be retrieved.
	 *
	 * @return mixed. serialized string
	 */
	public function get_session_data( $key = false );

	/**
	 * Adds data to the session, associated with a specific key
	 *
	 *
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function add_session_data( $key, $data );

	/**
	 * Updates session data by key
	 *
	 *
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function update_session_data( $key, $data );

	/**
	 * Deletes session data. All or by key.
	 *
	 *
	 *
	 * @param string|bool $key Specify the key to clear, or clear all data if false.
	 *
	 * @return void
	 */
	public function clear_session_data( $key = false );

	/**
	 * Clears all session data
	 *
	 *
	 *
	 * @param bool $hard If true, old delete sessions as well.
	 */
	public function clear_session( $hard = false );
}
