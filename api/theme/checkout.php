<?php
/**
 * Theme API class for Checkout
 * @package IT_Exchange
 * 
*/

class IT_Theme_API_Checkout implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'checkout';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'transactionmethods' => 'transaction_methods',
		'cancel'             => 'cancel',
	);

	/**
	 * Constructor
	 *
	 *
	*/
	function __construct() {
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Checkout() {

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
	public function get_api_context() {
		return $this->_context;
	}

	/**
	 * Sets up transaction method loop
	 *
	 *
	 *
	 * @param array $options
	 * @return mixed
	*/
	public function transaction_methods( $options=array() ) {

		$methods = function() {
			$cart = it_exchange_get_requested_cart_and_check_auth() ?: it_exchange_get_current_cart();

			$methods = array_map( function ( ITE_Purchase_Request_Handler $handler ) {
				$addon = $handler->get_gateway()->get_addon();
				$addon['handler_id'] = $handler->get_id();

				return $addon;
			}, it_exchange_get_available_transaction_methods_for_cart( $cart ) );

			foreach ( it_exchange_get_enabled_addons( array( 'category' => 'transaction-methods' ) ) as $addon ) {

				if ( ! ITE_Gateways::get( $addon['slug'] ) ) {
					$methods[] = $addon;
				}
			}

			return $methods;
		};

		// Do we have any transaction methods
		if ( ! empty( $options['has'] ) ) {
			return count( $methods() ) > 0;
		}

		return it_theme_api_loop( 'transaction_method', 'transaction_methods', $methods );
	}

	/**
	 * Returns data/html for cancel action
	 *
	 *
	 *
	 * @param array $options
	 * @return mixed
	*/
	public function cancel( $options=array() ) {
		$defaults = array(
			'before' => '',
			'after'  => '',
			'format' => 'link',
			'label'  => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'class'  => '',
			'focus'  => false
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$class = empty( $options['class'] ) ? 'ninja-shop-cancel-checkout' : 'ninja-shop-cancel-checkout ' . $options['class'];

		if ( it_exchange_get_requested_cart_and_check_auth() ) {
			return '';
		}

		// Set URL
		if ( it_exchange_in_superwidget() && 2 > it_exchange_get_cart_products_count() ) {
			// Get clean url without any exchange query args
			$url = it_exchange_clean_query_args();
			$url = add_query_arg( 'ite-sw-state', 'cart', $url );
			$url = in_array( $options['focus'], array( 'coupon', 'quantity' ) ) ? add_query_arg( it_exchange_get_field_name( 'sw_cart_focus' ), $options['focus'], $url ) : $url;
		} else {
			$url = it_exchange_get_page_url ( 'cart' );
		}

		if ( 'link' == $options['format'] )
			return $options['before'] . '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $url ) . '">' . $options['label'] . '</a>' . $options['after'];

		return $url;
	}
}
