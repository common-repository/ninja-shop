<?php
/**
 * Purchase Request class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Purchase_Request
 */
class ITE_Gateway_Purchase_Request implements ITE_Gateway_Request {

	/** @var ITE_Cart */
	protected $cart;

	/** @var array */
	protected $http_request;

	/** @var string */
	protected $nonce;

	/** @var ITE_Gateway_Card|null */
	protected $card;

	/** @var ITE_Payment_Token|null */
	protected $token;

	/** @var string */
	protected $one_time_token;

	/** @var ITE_Gateway_Tokenize_Request */
	protected $tokenize;

	/** @var string */
	protected $redirect_to;

	/** @var IT_Exchange_Transaction|null */
	protected $child_of;

	/**
	 * ITE_Gateway_Purchase_Request constructor.
	 *
	 * @param \ITE_Cart $cart
	 * @param string    $nonce
	 * @param array     $http_request
	 */
	public function __construct( \ITE_Cart $cart, $nonce, array $http_request = array() ) {
		$this->cart         = $cart;
		$this->http_request = $http_request;
		$this->nonce        = $nonce;
	}

	/**
	 * Get the cart being purchased.
	 *
	 *
	 *
	 * @return \ITE_Cart
	 */
	public function get_cart() {
		return $this->cart;
	}

	/**
	 * Get the HTTP request.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_http_request() {
		return $this->http_request;
	}

	/**
	 * Get the nonce.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_nonce() {
		return $this->nonce;
	}

	/**
	 * Get the card being used for the purchase.
	 *
	 *
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	public function get_card() {
		return $this->card;
	}

	/**
	 * Set the card to be used for the purchase.
	 *
	 *
	 *
	 * @param \ITE_Gateway_Card $card
	 */
	public function set_card( ITE_Gateway_Card $card ) {
		$this->card = $card;
	}

	/**
	 * Get the payment token.
	 *
	 *
	 *
	 * @return \ITE_Payment_Token|null
	 */
	public function get_token() {
		return $this->token;
	}

	/**
	 * Set the payment token.
	 *
	 *
	 *
	 * @param \ITE_Payment_Token|null $token
	 *
	 * @throws \InvalidArgumentException
	 */
	public function set_token( ITE_Payment_Token $token ) {

		if ( ! $token->customer || $token->customer->ID !== $this->get_customer()->ID ) {
			throw new InvalidArgumentException( 'Invalid token for customer.' );
		}

		$this->token = $token;
	}

	/**
	 * Get the one time use token.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_one_time_token() {
		return $this->one_time_token;
	}

	/**
	 * Set the one time token.
	 *
	 *
	 *
	 * @param string $one_time_token
	 */
	public function set_one_time_token( $one_time_token ) {
		$this->one_time_token = $one_time_token;
	}

	/**
	 * Get the possible tokenize request.
	 *
	 * This might be used in scenarios like Guest Checkout.
	 *
	 *
	 *
	 * @return \ITE_Gateway_Tokenize_Request|null
	 */
	public function get_tokenize() {
		return $this->tokenize;
	}

	/**
	 * Set the tokenize request.
	 *
	 *
	 *
	 * @param \ITE_Gateway_Tokenize_Request $tokenize
	 */
	public function set_tokenize( ITE_Gateway_Tokenize_Request $tokenize ) {
		$this->tokenize = $tokenize;
	}

	/**
	 * Get the destination the customer should be redirected to after purchase.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_redirect_to() {
		return $this->redirect_to;
	}

	/**
	 * Set the destination the customer should be redirected to after purchase.
	 *
	 * This defaults to the confirmation page.
	 *
	 *
	 *
	 * @param string $redirect_to
	 */
	public function set_redirect_to( $redirect_to ) {
		$this->redirect_to = $redirect_to;
	}

	/**
	 * @inheritDoc
	 */
	public function get_child_of() {
		return $this->child_of;
	}

	/**
	 * @inheritDoc
	 */
	public function set_child_of( IT_Exchange_Transaction $transaction ) {
		$this->child_of = $transaction;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() {
		return $this->cart->get_customer();
	}

	/**
	 * @inheritDoc
	 */
	public static function get_name() {
		return 'purchase';
	}
}
