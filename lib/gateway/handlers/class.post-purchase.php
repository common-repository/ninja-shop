<?php
/**
 * POST Purchase Request.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_POST_Redirect_Purchase_Request_Handler
 */
abstract class ITE_POST_Redirect_Purchase_Request_Handler extends ITE_Redirect_Purchase_Request_Handler {

	/**
	 * Get a list of vars to POST to the gateway.
	 *
	 *
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return array
	 */
	protected abstract function get_vars_to_post( ITE_Gateway_Purchase_Request $request );

	/**
	 * @inheritDoc
	 */
	protected function redirect( ITE_Gateway_Purchase_Request $request ) {
		it_exchange_set_global( 'purchase_interstitial', array(
			'gateway' => $this->get_gateway()->get_slug(),
			'url'     => $this->get_redirect_url( $request ),
			'vars'    => $this->get_vars_to_post( $request ),
		) );
		it_exchange_get_template_part( 'purchase-interstitial' );
		die();
	}
}
