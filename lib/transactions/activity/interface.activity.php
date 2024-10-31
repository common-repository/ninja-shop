<?php
/**
 * Contains the activity item interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Txn_Activity
 */
interface IT_Exchange_Txn_Activity {

	/**
	 * Get the ID for this item.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_ID();

	/**
	 * Get the activity description.
	 *
	 * This is typically 1-2 sentences.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_description();

	/**
	 * Get the type of the activity.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Get the time this activity occurred.
	 *
	 *
	 *
	 * @return DateTime
	 */
	public function get_time();

	/**
	 * Is this activity public.
	 *
	 * The customer is notified for public activities.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_public();

	/**
	 * Get the transaction this activity belongs to.
	 *
	 *
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_transaction();

	/**
	 * Does this activity item have an actor.
	 *
	 *
	 *
	 * @return bool
	 */
	public function has_actor();

	/**
	 * Get this activity's actor.
	 *
	 *
	 *
	 * @return IT_Exchange_Txn_Activity_Actor
	 */
	public function get_actor();

	/**
	 * Delete an activity item.
	 *
	 *
	 *
	 * @return bool
	 */
	public function delete();

	/**
	 * Convert the activity to an array of data.
	 *
	 * Substitute for jsonSerialize because 5.2 ;(
	 *
	 *
	 *
	 * @return array
	 */
	public function to_array();
}
