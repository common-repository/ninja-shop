<?php
/**
 * Contains the activity actor interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Txn_Activity_Actor
 */
interface IT_Exchange_Txn_Activity_Actor {

	/**
	 * Get the actor's name.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_name();

	/**
	 * Get the URL to the icon representing this actor.
	 *
	 *
	 *
	 * @param int $size Suggested size. Do not rely on this value.
	 *
	 * @return string
	 */
	public function get_icon_url( $size );

	/**
	 * Get the URL to view details about this actor.
	 *
	 * This could be a user's profile, for example.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_detail_url();

	/**
	 * Get the type of this actor.
	 *
	 * Ex: 'user', 'customer'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Attach this actor to an activity item.
	 *
	 *
	 *
	 * @param IT_Exchange_Txn_Activity $activity
	 *
	 * @return self
	 */
	public function attach( IT_Exchange_Txn_Activity $activity );

	/**
	 * Convert the actor to an array of data.
	 *
	 * Substitute for jsonSerialize because 5.2 ;(
	 *
	 *
	 *
	 * @return array
	 */
	public function to_array();
}
