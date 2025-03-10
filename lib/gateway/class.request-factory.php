<?php
/**
 * Payment Gateway Request Factory.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Gateway_Request_Factory
 */
class ITE_Gateway_Request_Factory {

	/**
	 * Construct a request object.
	 *
	 *
	 *
	 * @param string $type
	 * @param array  $args
	 *
	 * @return ITE_Gateway_Request|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function make( $type, array $args = array() ) {

		$is_custom = false;

		switch ( $type ) {
			case ITE_Gateway_Purchase_Request::get_name():
				$cart  = empty( $args['cart'] ) ? it_exchange_get_current_cart() : $args['cart'];
				$nonce = empty( $args['nonce'] ) ? '' : $args['nonce'];
				$http  = empty( $args['http_request'] ) ? array() : (array) $args['http_request'];

				$request = new ITE_Gateway_Purchase_Request( $cart, $nonce, $http );

				if ( ! empty( $args['card'] ) ) {
					$card = $args['card'];

					if ( is_array( $card ) ) {
						$card = $this->build_card( $card );
					}

					if ( ! $card instanceof ITE_Gateway_Card ) {
						throw new InvalidArgumentException( 'Invalid `card` option.' );
					}

					$request->set_card( $card );
				}

				if ( ! empty( $args['token'] ) ) {
					$token = $args['token'];

					if ( is_int( $token ) ) {
						$token = ITE_Payment_Token::get( $token );
					}

					if ( ! $token instanceof ITE_Payment_Token ) {
						throw new InvalidArgumentException( 'Invalid `token` option.' );
					}

					$request->set_token( $token );
				}

				if ( ! empty( $args['one_time_token'] ) ) {
					$request->set_one_time_token( $args['one_time_token'] );
				}

				if ( ! empty( $args['tokenize'] ) ) {

					if ( ! is_object( $args['tokenize'] ) || ! $args['tokenize'] instanceof ITE_Gateway_Tokenize_Request ) {
						$tokenize = $this->make( 'tokenize', array(
							'source'   => $args['tokenize'],
							'customer' => $cart->get_customer(),
							'address'  => $cart->get_billing_address(),
						) );
					} else {
						$tokenize = $args['tokenize'];
					}

					if ( ! $tokenize instanceof ITE_Gateway_Tokenize_Request ) {
						throw new InvalidArgumentException( 'Invalid `tokenize` option.' );
					}

					$request->set_tokenize( $tokenize );
				}

				if ( ! empty( $args['child_of'] ) ) {
					$child_of = it_exchange_get_transaction( $args['child_of'] );

					if ( ! $child_of instanceof IT_Exchange_Transaction ) {
						throw new InvalidArgumentException( 'Invalid `child_of` option.' );
					}

					$request->set_child_of( $child_of );
				}

				if ( ! empty( $args['redirect_to'] ) ) {
					$request->set_redirect_to( $args['redirect_to'] );
				}

				break;
			case ITE_Webhook_Gateway_Request::get_name():
				$request = new ITE_Webhook_Gateway_Request( $args['webhook_data'], isset( $args['headers'] ) ? $args['headers'] : array() );
				break;
			case ITE_Gateway_Tokenize_Request::get_name():

				if ( empty( $args['customer'] ) ) {
					$customer = it_exchange_get_current_customer();
				} else {
					$customer = it_exchange_get_customer( $args['customer'] );
				}

				if ( empty( $args['source'] ) ) {
					$source = '';
				} elseif ( is_array( $args['source'] ) ) {
					$source = $this->build_card( $args['source'] );

					if ( ! $source ) {
						$source = $this->build_bank_account( $args['source'] );
					}
				} else {
					$source = $args['source'];
				}

				$label   = empty( $args['label'] ) ? '' : $args['label'];
				$primary = empty( $args['primary'] ) ? false : $args['primary'];

				if ( ! $customer ) {
					throw new InvalidArgumentException( 'Invalid `customer` option.' );
				}

				if ( ! is_string( $source ) && ! $source instanceof ITE_Gateway_Payment_Source && ! $source instanceof ITE_Payment_Token ) {
					throw new InvalidArgumentException( 'Invalid `source` option.' );
				}

				$request = new ITE_Gateway_Tokenize_Request( $customer, $source, $label, $primary );

				if ( isset( $args['address'] ) ) {
					$address = $args['address'];

					if ( is_numeric( $address ) ) {
						$address = ITE_Saved_Address::get( $address );
					} elseif ( is_array( $address ) ) {
						$address = new ITE_In_Memory_Address( $address );
					}

					if ( ! $address instanceof ITE_Location ) {
						throw new InvalidArgumentException( 'Invalid `address` option.' );
					}

					$request->set_address( $address );
				}

				break;
			case ITE_Gateway_Refund_Request::get_name():

				if ( empty( $args['transaction'] ) || ! $txn = it_exchange_get_transaction( $args['transaction'] ) ) {
					throw new InvalidArgumentException( 'Invalid `transaction` option.' );
				}

				if ( empty( $args['amount'] ) || $args['amount'] <= 0.00 ) {
					throw new InvalidArgumentException( 'Invalid `amount` option.' );
				}

				$reason = empty( $args['reason'] ) ? '' : $args['reason'];

				$request = new ITE_Gateway_Refund_Request( $txn, $args['amount'], $reason );

				if ( ! empty( $args['issued_by'] ) ) {
					$issued_by = $args['issued_by'];

					if ( is_numeric( $issued_by ) ) {
						$issued_by = get_user_by( 'id', $issued_by );
					}

					if ( ! $issued_by instanceof WP_User ) {
						throw new InvalidArgumentException( 'Invalid `issued_by` option.' );
					}

					$request->set_issued_by( $issued_by );
				} elseif ( is_user_logged_in() ) {
					$request->set_issued_by( wp_get_current_user() );
				}

				break;
			default:

				/**
				 * Filter the gateway request for an unknown request type.
				 *
				 *
				 *
				 * @param ITE_Gateway_Request          $request
				 * @param array                        $args
				 * @param string                       $type
				 * @param \ITE_Gateway_Request_Factory $this
				 */
				$request = apply_filters( "ninja_shop_make_{$type}_gateway_request", null, $args, $type, $this );

				if ( $request && ( ! $request instanceof ITE_Gateway_Request || $request->get_name() !== $type ) ) {
					throw new UnexpectedValueException( "Unable to construct {$type} request." );
				}

				if ( ! $request ) {
					return null;
				}

				$is_custom = true;

				break;
		}

		if ( ! $is_custom ) {
			/**
			 * Filter the created gateway request.
			 *
			 *
			 *
			 * @param ITE_Gateway_Request          $request
			 * @param array                        $args
			 * @param string                       $type
			 * @param \ITE_Gateway_Request_Factory $this
			 */
			$filtered = apply_filters( "ninja_shop_make_{$type}_gateway_request", $request, $args, $type, $this );

			if ( $filtered instanceof $request ) {
				$request = $filtered;
			}
		}

		/**
		 * Filter the created gateway request.
		 *
		 *
		 *
		 * @param \ITE_Gateway_Request         $request
		 * @param array                        $args
		 * @param string                       $type
		 * @param \ITE_Gateway_Request_Factory $this
		 */
		$filtered = apply_filters( 'ninja_shop_make_gateway_request', $request, $args, $type, $this );

		if ( $filtered instanceof $request ) {
			$request = $filtered;
		}

		return $request;
	}

	/**
	 * Build a card from an array.
	 *
	 *
	 *
	 * @param array $card
	 *
	 * @return \ITE_Gateway_Card|null
	 */
	public function build_card( array $card ) {
		if ( isset( $card['number'], $card['year'], $card['month'], $card['cvc'] ) ) {
			$name = empty( $card['name'] ) ? '' : $card['name'];

			return new ITE_Gateway_Card( $card['number'], $card['year'], $card['month'], $card['cvc'], $name );
		}

		return null;
	}

	/**
	 * Build a bank account from an array.
	 *
	 *
	 *
	 * @param array $account
	 *
	 * @return \ITE_Gateway_Bank_Account|null
	 *
	 * @throws \InvalidArgumentException
	 */
	public function build_bank_account( array $account ) {

		if ( isset( $account['name'], $account['number'], $account['type'] ) ) {
			$routing = empty( $account['routing'] ) ? '' : $account['routing'];

			return new ITE_Gateway_Bank_Account( $account['name'], $account['type'], $account['number'], $routing );
		}

		return null;
	}
}
