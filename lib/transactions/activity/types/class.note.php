<?php
/**
 * Contains activity note class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Note_Activity
 */
class IT_Exchange_Txn_Note_Activity extends IT_Exchange_Txn_AbstractActivity {

	/**
	 * Retrieve a note activity item.
	 *
	 * This is used by the activity factory, and should not be called directly.
	 *
	 *
	 *
	 * @internal
	 *
	 * @param int                            $id
	 * @param IT_Exchange_Txn_Activity_Actor $actor
	 *
	 * @return IT_Exchange_Txn_Note_Activity|null
	 */
	public static function make( $id, IT_Exchange_Txn_Activity_Actor $actor = null ) {

		$post = get_post( $id );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		return new self( $post, $actor );
	}

	/**
	 * Get the type of the activity.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_type() {
		return 'note';
	}

	/**
	 * Is this activity public.
	 *
	 * The customer is notified for public activities.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_public() {

		if ( $this->has_actor() && $this->get_actor() instanceof IT_Exchange_Txn_Activity_Customer_Actor ) {
			return true;
		}

		return parent::is_public();
	}
}
