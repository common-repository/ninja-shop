<?php
/**
 * JS Tokenize Request interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_JS_Tokenize_Handler
 */
interface ITE_Gateway_JS_Tokenize_Handler {

	/**
	 * Get JavaScript to tokenize a payment source without touching the Exchange server.
	 *
	 * This will be revealed in the ITExchangeAPI global.
	 *
	 * Example:
	 *
	 * function( type, source ) {
	 *      return Stripe.card.createToken( source );
	 * }
	 *
	 * The function should return a jQuery Promise that is resolved with the tokenized input.
	 *
	 * Type is one of 'card' or 'bank'.
	 * Source is an object.
	 *
	 * 'card':
	 *      - number
	 *      - year
	 *      - month
	 *      - cvc
	 *
	 * 'bank':
	 *      - name
	 *      - number
	 *      - type
	 *      - routing
	 *
	 * Each source type can also optionally accept:
	 *
	 *      - name
	 *      - address1
	 *      - address2
	 *      - city
	 *      - state
	 *      - zip
	 *      - country
	 *
	 *
	 *
	 * @return string
	 */
	public function get_tokenize_js_function();

	/**
	 * Is the JS tokenizer configured properly.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_js_tokenizer_configured();
}
