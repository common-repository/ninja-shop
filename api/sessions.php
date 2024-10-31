<?php
/**
 * API functions pertaining to user sessions
 *
 * - IT_Exchange_Session object is stored in a global variable
 * - Sessions are only active on the frontend of the web site
 * - Ninja Shop inits the session and loads the data for you. Add-ons should not need to start the session
 *
 * 
 * @package IT_Exchange
*/

/**
 * This grabs you a copy of the IT_Exchange_Session object
 *
 *
 * @return IT_Exchange_SessionInterface|bool instance of IT_Exchange_Session
 */
function it_exchange_get_session() {
	$session = empty( $GLOBALS['it_exchange']['session'] ) ? false : $GLOBALS['it_exchange']['session'];
	return apply_filters( 'ninja_shop_get_session', $session );
}

/**
 * Returns session_data array from current session
 *
 *
 *
 * @param string|bool $key
 *
 * @return array  an array of session_data stored in $_SESSION['it_exchange']
*/
function it_exchange_get_session_data( $key=false ) {
	$session = it_exchange_get_session();
	return apply_filters( 'ninja_shop_get_session_data', maybe_unserialize( $session->get_session_data( $key ) ), $key );
}

/**
 * Adds session data to the Ninja Shop Session.
 *
 * This simply adds an item to the data array of the PHP Session.
 * Shopping cart plugins are responsible for managing the structure of the data
 * If a key is passed, it will be used as the key in the data array. Otherwise, the data array will just be
 * incremented. eg: ['data'][] = $data;
 *
 *
 * @param mixed $data data as passed by the shopping cart
 * @param mixed $key optional identifier for the data.
 *
 * @return void
*/
function it_exchange_add_session_data( $key, $data ) {
	$session = it_exchange_get_session();
	$session->add_session_data( $key, $data );
	do_action( 'ninja_shop_add_session_data', $data, $key );
}

/**
 * Updates session data by key
 *
 *
 * @param mixed $key key for the data
 * @param mixed $data updated data
 *
 * @return void
*/
function it_exchange_update_session_data( $key, $data ) {
	$session = it_exchange_get_session();
	$session->update_session_data( $key, $data );
	do_action( 'ninja_shop_update_session_data', $data, $key );
}

/**
 * Removes all data from the session key
 *
 *
 *
 * @param string|bool $key
 *
 * @return void
*/
function it_exchange_clear_session_data( $key=false ) {
	$session = it_exchange_get_session();
	$session->clear_session_data( $key );
	do_action( 'ninja_shop_clear_session_data', $key );
}

/**
 * Removes all data from the session
 *
 *
 *
 * @param bool $hard
 *
 * @return void
*/
function it_exchange_clear_session( $hard=false ) {
	$session = it_exchange_get_session();
	$session->clear_session( $hard );
	do_action( 'ninja_shop_clear_session', $hard );
}

/**
 * Returns the current session ID
 *
 *
 *
 *
 * @param bool $id_only When true, only return the ID portion of the string. By default, this returns the entire cookie value.
 *
 * @return string|false
*/
function it_exchange_get_session_id( $id_only = false ) {

	$string = empty( $_COOKIE[IT_EXCHANGE_SESSION_COOKIE] ) ? false : $_COOKIE[IT_EXCHANGE_SESSION_COOKIE];

	if ( ! $id_only || ! $string ) {
		return $string;
	}

	$parts  = explode( '||', $string );

	return empty( $parts[0] ) ? false : $parts[0];
}

/**
 * Commit session changes to storage.
 *
 *
 */
function it_exchange_commit_session() {

	$session = it_exchange_get_session();

	if ( $session instanceof ITE_Committable_Session ) {
		$session->commit();
	}
}
