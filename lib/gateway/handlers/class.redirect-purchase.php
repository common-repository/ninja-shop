<?php
/**
 * Redirect purchase request handler.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Redirect_Purchase_Request_Handler
 */
abstract class ITE_Redirect_Purchase_Request_Handler extends ITE_Purchase_Request_Handler {

	/**
	 * @inheritDoc
	 */
	public function __construct( \ITE_Gateway $gateway, \ITE_Gateway_Request_Factory $factory ) {
		parent::__construct( $gateway, $factory );

		add_action( 'init', array( $this, 'maybe_redirect' ), 20 );
	}

	/**
	 * @inheritDoc
	 */
	protected function get_form_action() {

		if ( it_exchange_is_multi_item_cart_allowed() ) {
			return it_exchange_get_page_url( 'checkout' );
		} else {
			return get_permalink( it_exchange_get_the_product_id() );
		}
	}

	/**
	 * Maybe perform a redirect to an external payment gateway.
	 *
	 *
	 * @throws \InvalidArgumentException
	 * @throws \UnexpectedValueException
	 */
	public function maybe_redirect() {

		if ( ! isset( $_REQUEST["{$this->gateway->get_slug()}_purchase"] ) ) {
			return;
		}

		if ( isset( $_REQUEST['auto_return'] ) ) {
			return;
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? $_REQUEST['_wpnonce'] : '';

		$cart = it_exchange_get_requested_cart_and_check_auth() ?: it_exchange_get_current_cart();

		$this->redirect( $this->factory->make( 'purchase', array(
			'cart'        => $cart,
			'nonce'       => $nonce,
			'redirect_to' => isset( $_REQUEST['redirect_to'] ) ? rawurldecode( $_REQUEST['redirect_to'] ) : '',
		) ) );
	}

	/**
	 * Perform the redirect to an external gateway for payment.
	 *
	 *
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 */
	protected function redirect( ITE_Gateway_Purchase_Request $request ) {
		$url = $this->get_redirect_url( $request );

		if ( ! $url ) {
			return;
		}

		wp_redirect( $url );
		die();
	}

	/**
	 * @inheritDoc
	 */
	public function get_data_for_REST( ITE_Gateway_Purchase_Request $request ) {

		$query_args = array(
			"{$this->gateway->get_slug()}_purchase" => 1,
			'_wpnonce'                              => $this->get_nonce(),
		);

		if ( ! $request->get_cart()->is_current() ) {
			$query_args['cart_id']   = $request->get_cart()->get_id();
			$query_args['cart_auth'] = $request->get_cart()->generate_auth_secret( 3600 );
		}

		if ( $request->get_redirect_to() ) {
			$query_args['redirect_to'] = rawurlencode( $request->get_redirect_to() );
		}

		$url = it_exchange_get_page_url( 'transaction' );
		$url = add_query_arg( $query_args, $url );

		$data = array_merge_recursive( parent::get_data_for_REST( $request ), array(
			'url' => $url,
		) );

		$data['method'] = 'redirect';

		return $data;
	}

	/**
	 * Get the redirect URL.
	 *
	 *
	 *
	 * @param ITE_Gateway_Purchase_Request $request
	 *
	 * @return string
	 */
	public abstract function get_redirect_url( ITE_Gateway_Purchase_Request $request );
}
