<?php
/**
 * This file contains the session class
 *
 * ############### WP Session Manager ##########
 * ## This is a wrapper to our lightly modified version of WP Session Manager by Eric Mann
 * ## Author: http://twitter.com/ericmann
 * ## Donate link: http://jumping-duck.com/wordpress/plugins
 * ## Github link: https://github.com/ericmann/wp-session-manager
 * ## Requires at least: WordPress 3.4.2
 * ## License: GPLv2 or later License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * #############################################
 *
 * 
 * @package IT_Exchange
 */

/**
 * The IT_Exchange_Session class holds cart and purchasing details
 *
 *
 */
class IT_Exchange_Session implements IT_Exchange_SessionInterface, ITE_Expiring_Session, ITE_Committable_Session {

	/**
	 * @var IT_Exchange_DB_Sessions
	 *
	 */
	private $_session;

	/**
	 * Keep track of whether the session committed changes by itself or the user did manually.
	 *
	 * Used for issuing doing it wrong notices.
	 *
	 * @var bool
	 */
	private $user_committed_changes = null;

	/** @var array */
	private $keys_changed = array();

	/**
	 * Constructor.
	 *
	 *
	 */
	public function __construct() {

		if ( ! defined( 'IT_EXCHANGE_SESSION_COOKIE' ) ) {
			define( 'IT_EXCHANGE_SESSION_COOKIE', 'it_exchange_session_' . COOKIEHASH );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			require_once( dirname( __FILE__ ) . '/db_session_manager/class-recursive-arrayaccess.php' );
		}

		// Only include the functionality if it's not pre-defined.
		if ( ! class_exists( 'IT_Exchange_DB_Sessions' ) ) {
			require_once( dirname( __FILE__ ) . '/db_session_manager/class-db-session.php' );
			require_once( dirname( __FILE__ ) . '/db_session_manager/db-session.php' );
		}

		add_action( 'init', array( $this, 'init' ), - 1 );

		// Reset the session when the user loggs out
		add_action( 'wp_logout', array( $this, 'reset_session_and_cache_cart_on_logout' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Session() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Initialize the DB Sessions and returns the object
	 *
	 *
	 *
	 * @return IT_Exchange_DB_Sessions
	 */
	public function init() {
		$this->_session = IT_Exchange_DB_Sessions::get_instance();

		return $this->_session;
	}

	/**
	 * Returns session data
	 *
	 * All data or optionally, data for a specific key
	 *
	 *
	 *
	 * @param string|bool $key Specify the data to retrieve, if false all data will be retrieved.
	 *
	 * @return mixed. serialized string
	 */
	public function get_session_data( $key = false ) {
		if ( $key ) {
			$key = sanitize_key( $key );

			if ( $key && ! empty( $this->_session[ $key ] ) ) {
				$data = $this->_session[ $key ];
				if ( is_array( $data ) ) {
					$data = array_map( 'maybe_unserialize', $data );
				} else {
					$data = maybe_unserialize( $data );
				}

				return $data;
			}
		} else {
			if ( $json = it_exchange_db_session_encode() ) {
				$session_data = json_decode( $json, true );
				if ( ! empty( $session_data ) ) {
					$session_data = array_map( 'maybe_unserialize', $session_data );

					return $session_data;
				}
			}
		}

		return array();
	}

	/**
	 * Adds data to the session, associated with a specific key
	 *
	 *
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function add_session_data( $key, $data ) {
		$key = sanitize_key( $key );

		if ( ! empty( $this->_session[ $key ] ) ) {
			$current_data           = maybe_unserialize( $this->_session[ $key ] );
			$this->_session[ $key ] = maybe_serialize( array_merge( $current_data, (array) $data ) );
		} else if ( ! empty( $data ) ) {
			$this->_session[ $key ] = maybe_serialize( (array) $data );
		}
		it_exchange_db_session_commit();

		$this->user_committed_changes = false;
		isset( $this->keys_changed[ $key ] ) ? $this->keys_changed[ $key ] ++ : $this->keys_changed[ $key ] = 1;
	}

	/**
	 * Updates session data by key
	 *
	 *
	 *
	 * @param string $key  key for the data
	 * @param mixed  $data data to be stored. will be serialized if not already
	 *
	 * @return void
	 */
	public function update_session_data( $key, $data ) {
		$key                    = sanitize_key( $key );
		$this->_session[ $key ] = maybe_serialize( (array) $data );
		it_exchange_db_session_commit();

		$this->user_committed_changes = false;
		isset( $this->keys_changed[ $key ] ) ? $this->keys_changed[ $key ] ++ : $this->keys_changed[ $key ] = 1;
	}

	/**
	 * Deletes session data. All or by key.
	 *
	 *
	 *
	 * @param string|bool $key Specify the key to clear, or clear all data if false.
	 *
	 * @return void
	 */
	public function clear_session_data( $key = false ) {
		if ( ! empty( $key ) ) {
			$key = sanitize_key( $key );

			if ( isset( $this->_session[ $key ] ) ) {
				unset( $this->_session[ $key ] );
				it_exchange_db_session_commit();
			}
		} else {
			foreach ( $this->_session as $key => $value ) {
				unset( $this->_session[ $key ] );
			}
			it_exchange_db_session_commit();
		}

		$this->user_committed_changes = false;
		isset( $this->keys_changed[ $key ] ) ? $this->keys_changed[ $key ] ++ : $this->keys_changed[ $key ] = 1;
	}

	/**
	 * Clears all session data
	 *
	 *
	 *
	 * @param bool $hard If true, old delete sessions as well.
	 */
	public function clear_session( $hard = false ) {
		it_exchange_db_session_regenerate_id( $hard );
		it_exchange_db_session_commit();

		if ( $c = it_exchange_get_current_cart( false ) ) {
			$c->destroy();
		}
	}

	/**
	 * Kills the DB Session and starts over
	 *
	 * This should only be hooked to logout. Don't fire if not logging out.
	 *
	 *
	 *
	 * @return void
	 */
	public function reset_session_and_cache_cart_on_logout() {

		if ( 'wp_logout' === current_filter() ) {

			// Flag this as a logout action
			$GLOBALS['it_exchange']['logging_out_user'] = true;

			$this->_session->remove_cookie();

			do_action( 'ninja_shop_db_session_reset_on_logout' );
			it_exchange_cache_customer_cart();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function commit() {
		it_exchange_db_session_commit();
		$this->user_committed_changes = true;
	}

	/**
	 * @inheritDoc
	 */
	public function __destruct() {

		if ( $this->user_committed_changes === false ) {

			$keys = '';
			foreach ( $this->keys_changed as $key => $times ) {
				$keys .= "{$key}:{$times}, ";
			}

			_doing_it_wrong(
				'it_exchange_(add|update|clear)_session_data',
				sprintf(
					__( '%s not called before page end for keys: %s', 'it-l10n-ithemes-exchange' ),
					'it_exchange_commit_session()', $keys
				),
				'2.0.0'
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function expires_at() { return $this->_session->expires_at(); }
}
