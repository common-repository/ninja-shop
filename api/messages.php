<?php
/**
 * Functions for defining, initiating, and displaying Ninja Shop Errors and Notices
 *
 * Core types are 'notice' and 'error'
 *
 * 
 * @package IT_Exchange
*/

/**
 * Adds messages to Exchange session
 *
 *
 *
 * @param string $type Type of message you want displayed
 * @param string $message the message you want displayed
*/
function it_exchange_add_message( $type, $message ) {
	it_exchange_add_session_data( $type, (string) $message );
	do_action( 'ninja_shop_add_message', $type, $message );
}

/**
 * Gets messages to Exchange session
 *
 *
 *
 * @param string $type Type of message you want displayed
 * @param bool $clear Whether to clear out existing messages once returned
 *
 * @return array
*/
function it_exchange_get_messages( $type, $clear=true ) {
	$messages = it_exchange_get_session_data( $type );
	if ( $clear )
		it_exchange_clear_messages( $type );
	return apply_filters( 'ninja_shop_get_messages', $messages, $type, $clear );
}

/**
 * Checks if messages are in the to Exchange session
 *
 *
 *
 * @param string $type Type of message you want displayed
 *
 * @return bool
*/
function it_exchange_has_messages( $type ) {
	return (bool) apply_filters( 'ninja_shop_has_messages', it_exchange_get_session_data( $type, false ), $type );
}

/**
 * Checks if messages are in the to Exchange session
 *
 *
 *
 * @param string $type Type of message you want displayed
*/
function it_exchange_clear_messages( $type ) {
	it_exchange_clear_session_data( $type );
	do_action( 'ninja_shop_clear_messages', $type );
}
