<?php

/**
 * Transactions class for THEME API
 *
 * 
 */
class IT_Theme_API_Transactions implements IT_Theme_API {

	/**
	 * API context
	 *
	 * @var string $_context
	 *
	 */
	private $_context = 'transactions';

	/**
	 * Maps api tags to methods
	 *
	 * @var array $_tag_map
	 *
	 */
	public $_tag_map = array(
		'found'      => 'found',
		'exist'      => 'exist',
		'pagination' => 'pagination'
	);

	/** @var int */
	public static $per_page = 25;

	/** @var int */
	private static $total;

	public static $transactions = array();

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Transaction() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an Ninja Shop theme API class
	 *
	 *
	 *
	 * @return string
	 */
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Use this function to in a while loop to determine if there are any more transactions left to loop through.
	 * If there are no more transactions found, it will return false. Otherwise, it returns 'true'.
	 *
	 *
	 * @return string
	 */
	public function found( $options = array() ) {
		return count( $this->get_transactions( true ) ) > 0;
	}

	/**
	 * This loops through the transactions GLOBAL and updates the transaction global.
	 *
	 * It return false when it reaches the last transaction
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function exist( $options = array() ) {
		// This will init/reset the transactions global and loop through them. the /api/theme/transaction.php file will handle individual transactions.
		if ( empty( $GLOBALS['it_exchange']['transactions'] ) ) {

			$transactions = $this->get_transactions();

			if ( ! $transactions ) {
				return false;
			}

			$GLOBALS['it_exchange']['transactions'] = $transactions;
			$GLOBALS['it_exchange']['transaction']  = reset( $GLOBALS['it_exchange']['transactions'] );

			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['transactions'] ) ) {
				$GLOBALS['it_exchange']['transaction'] = current( $GLOBALS['it_exchange']['transactions'] );

				return true;
			} else {
				self::$transactions                     = $GLOBALS['it_exchange']['transactions'];
				$GLOBALS['it_exchange']['transactions'] = array();
				end( $GLOBALS['it_exchange']['transactions'] );
				$GLOBALS['it_exchange']['transaction'] = false;

				return false;
			}
		}
	}

	/**
	 * Retrieve the transactions.
	 *
	 *
	 *
	 * @param bool $has
	 *
	 * @return \IT_Exchange_Transaction[]
	 */
	protected function get_transactions( $has = false ) {

		if ( it_exchange_is_page( 'purchases' ) || it_exchange_is_page( 'downloads' ) ) {

			if ( ! $customer = it_exchange_get_current_customer() ) {
				return array();
			}

			$page = get_query_var( 'page', 1 );

			if ( ! $page ) {
				$page = 1;
			}

			if ( isset( $_GET['t'] ) && current_user_can( 'read_it_transaction', $_GET['t'] ) ) {
				self::$total = 1;

				return array( it_exchange_get_transaction( $_GET['t'] ) );
			}

			if ( $has ) {
				$args = array( 'per_page' => 1 );
			} else {
				$args = array( 'per_page' => self::$per_page, 'page' => $page );
			}

			$transactions = it_exchange_get_customer_transactions( $customer->id, $args, $total );

			self::$total = $total;

			return $transactions;
		} elseif ( it_exchange_is_page( 'confirmation' ) ) {
			$confirmation_slug = it_exchange_get_page_slug( 'confirmation' );
			$transaction_hash  = get_query_var( $confirmation_slug );

			if ( ! $transaction_hash ) {
				return array();
			}

			$transaction = it_exchange_get_transaction_id_from_hash( $transaction_hash );

			if ( ! $transaction ) {
				return array();
			}

			return array( it_exchange_get_transaction( $transaction ) );
		} else {
			return it_exchange_get_transactions();
		}
	}

	/**
	 * Print pagination.
	 *
	 *
	 *
	 * @return string
	 */
	public function pagination() {
		return paginate_links( array(
			'base'      => it_exchange_get_page_url( 'purchases' ) . '%_%',
			'format'    => it_exchange_is_pages_compat_mode() ? '?page=%#%' : '%#%/',
			'total'     => ceil( self::$total / self::$per_page ),
			'current'   => get_query_var( 'page' ) ? (int) get_query_var( 'page' ) : 1,
			'type'      => 'list',
			'prev_text' => __( '&laquo; Newer' ),
			'next_text' => __( 'Older &raquo;' ),
		) );
	}
}
