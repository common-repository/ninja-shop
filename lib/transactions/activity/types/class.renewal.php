<?php
/**
 * Contains the renewal payment activity class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Txn_Renewal_Activity
 */
class IT_Exchange_Txn_Renewal_Activity extends IT_Exchange_Txn_AbstractActivity {

	/**
	 * Retrieve a renewal activity item.
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
	 * @return IT_Exchange_Txn_Renewal_Activity|null
	 */
	public static function make( $id, IT_Exchange_Txn_Activity_Actor $actor = null ) {

		$post = get_post( $id );

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		return new self( $post, $actor );
	}

	/**
	 * Get the renewal transaction.
	 *
	 *
	 *
	 * @return IT_Exchange_Transaction
	 */
	public function get_renewal_transaction() {
		return it_exchange_get_transaction( get_post_meta( $this->get_ID(), '_child_txn', true ) );
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
		return false;
	}

	/**
	 * Get this activity's actor.
	 *
	 *
	 *
	 * @return IT_Exchange_Txn_Activity_Actor
	 */
	public function get_actor() {
		$actor = parent::get_actor();

		if ( $actor === null && $txn = $this->get_renewal_transaction() ) {
			$actor = new IT_Exchange_Txn_Activity_Customer_Actor( $txn->get_customer() );
		}

		return $actor;
	}

	/**
	 * Get the activity description.
	 *
	 * This is typically 1-2 sentences.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_description() {

		if ( ! $this->get_renewal_transaction() ) {
			return __(  'Renewal payment.', 'it-l10n-ithemes-exchange' );
		}

		$link = get_edit_post_link( $this->get_renewal_transaction()->ID );
		$link = "<a href=\"$link\">" . $this->get_renewal_transaction()->get_order_number() . '</a>';

		if ( $this->has_cleared_meta() && ! $this->is_cleared() ) {
			/* translators: %1$s is transaction order number, %2$s is dollar amount. */
			$message = __( 'Pending renewal payment %1$s of %2$s.', 'it-l10n-ithemes-exchange' );
		} elseif ( $this->has_cleared_meta() && $this->is_cleared() ) {
			/* translators: %1$s is transaction order number. */
			$message = __( 'Renewal payment %1$s was cleared.', 'it-l10n-ithemes-exchange' );
		} else {
			/* translators: %1$s is transaction order number, %2$s is dollar amount. */
			$message = __( 'Renewal payment %1$s of %2$s.', 'it-l10n-ithemes-exchange' );
		}

		return sprintf( $message, $link, it_exchange_get_transaction_total( $this->get_renewal_transaction() ) );
	}

	/**
	 * Check if we have cleared meta data.
	 *
	 *
	 *
	 * @return bool
	 */
	protected function has_cleared_meta() {
		return metadata_exists( 'post', $this->get_ID(), '_child_txn_cleared' );
	}

	/**
	 * Was the renewal payment cleared when the activity item was logged.
	 *
	 *
	 *
	 * @return bool
	 */
	protected function is_cleared() {
		return (bool) get_post_meta( $this->get_ID(), '_child_txn_cleared', true );
	}

	/**
	 * Get the type of the activity.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_type() {
		return 'renewal';
	}
}
