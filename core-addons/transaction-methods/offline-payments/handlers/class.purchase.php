<?php
/**
 * Offline Payments Purchase Request Handler.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Offline_Payments_Purchase_Request_Handler
 */
class ITE_Offline_Payments_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 */
	public function handle( $request ) {

		if ( ! static::can_handle( $request::get_name() ) ) {
			throw new InvalidArgumentException();
		}

		if ( ! wp_verify_nonce( $request->get_nonce(), $this->get_nonce_action() ) ) {
			$request->get_cart()->get_feedback()->add_error(
				__( 'Purchase failed. Unable to verify security token.', 'it-l10n-ithemes-exchange' )
			);

			return null;
		}

		$status    = $this->gateway->settings()->get( 'offline-payments-default-status' );
		$method_id = it_exchange_get_offline_transaction_uniqid();

		$txn_id = it_exchange_add_transaction(
			'offline-payments',
			$method_id,
			$status,
			$request->get_cart()
		);

		if ( ! $txn_id ) {
			return null;
		}

		return it_exchange_get_transaction( $txn_id );
	}

	/**
	 * @inheritDoc
	 */
	public function get_payment_button_label() {

		if ( $this->get_gateway()->settings()->has( 'offline-payments-title' ) ) {
			return $this->get_gateway()->settings()->get( 'offline-payments-title' );
		}

		return parent::get_payment_button_label();
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature( ITE_Optionally_Supported_Feature $feature ) {

		switch ( $feature->get_feature_slug() ) {
			case 'recurring-payments':
			case 'one-time-fee':
				return true;
		}

		return parent::supports_feature( $feature );
	}

	/**
	 * @inheritDoc
	 */
	public function supports_feature_and_detail( ITE_Optionally_Supported_Feature $feature, $slug, $detail ) {

		switch ( $feature->get_feature_slug() ) {
			case 'one-time-fee':
				switch ( $slug ) {
					case 'discount':
						return true;
					default:
						return false;
				}
			case 'recurring-payments':
				switch ( $slug ) {
					case 'auto-renew':
					case 'profile':
					case 'trial':
					case 'trial-profile':
					case 'max-occurrences':
						return true;
					default:
						return false;
				}
		}

		return parent::supports_feature( $feature );
	}
}
