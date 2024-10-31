<?php

class IT_Theme_API_US_Taxes implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 * 
	*/
	private $_context = 'us-taxes';

	/**
	 * Current customer Address
	 * @var string $_address
	 *
	*/
	private $_address = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'taxes'             => 'taxes',
		'confirmationtaxes' => 'confirmation_taxes',
	);

	/**
	 * Constructor
	 *
	 *
	 *
	 * @return void
	*/
	function __construct() {
		$this->_address = it_exchange_get_cart_shipping_address();
		//We only care about the province!
		if ( empty( $this->_address['state'] ) )
			$this->_address = it_exchange_get_cart_billing_address();
	}

	/**
	 * Returns the context. Also helps to confirm we are an iThemes Exchange theme API class
	 *
	 *
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 *
	 * @return string
	*/
	function taxes( $options=array() ) {

		$settings  = it_exchange_get_option( 'addon_basic_us_sales_taxes' );
		$result = '';
		$taxes = 0;

		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'format_price' => true,
		);
		$options      = ITUtility::merge_defaults( $options, $defaults );


		$result .= $options['before'];
		if ( it_exchange_basic_us_sales_taxes_setup_session() ) {
			$tax_session = it_exchange_get_session_data( 'addon_basic_us_sales_taxes' );
			$result .= '<ul class="us-sales-taxes">';
			$total_tax = 0;
			foreach ( $tax_session['taxes'] as $tax ) {
				if ( $tax['shipping'] ) {
					$taxes = $tax_session['cart_subtotal_w_shipping'] * ( $tax['rate'] / 100 );
				} else {
					$taxes = $tax_session['cart_subtotal'] * ( $tax['rate'] / 100 );
				}
				$total_tax += $taxes;
				if ( !empty( $taxes ) ) {
					if ( $options['format_price'] )
						$taxes = it_exchange_format_price( $taxes );
					$result .= '<li>' . $taxes . ' (' . $tax['type'] . ')</li>';
				}
			}

			if ( empty( $total_tax ) ) {
				if ( $options['format_price'] )
					$total_tax = it_exchange_format_price( $total_tax );
				$result .= '<li>' . $total_tax . '</li>';
			}
			$result .= '</ul>';
		} else {
			if ( $options['format_price'] )
				$taxes = it_exchange_format_price( $taxes );
			$result .= $taxes;
		}
		$result .= $options['after'];

		return $result;

	}

	function confirmation_taxes( $options=array() ) {
		$result = '';

		$defaults      = array(
			'before'       => '',
			'after'        => '',
			'format_price' => true,
		);
		$options      = ITUtility::merge_defaults( $options, $defaults );

	    if ( !empty( $GLOBALS['it_exchange']['transaction'] ) ) {
	        $transaction = $GLOBALS['it_exchange']['transaction'];
	        $tax_items = get_post_meta( $transaction->ID, '_it_exchange_basic_us_sales_taxes', true );
	    }

		if ( !empty( $tax_items ) ) {
			$result .= $options['before'];
			$result .= '<ul class="us-sales-taxes">';
			foreach ( $tax_items as $tax ) {
				if ( !empty( $tax['total'] ) ) {
					if ( $options['format_price'] )
						$tax['total'] = it_exchange_format_price( $tax['total'] );
					$result .= '<li>' . $tax['total'] . ' (' . $tax['type'] . ')</li>';
				}
			}
			$result .= '</ul>';
			$result .= $options['after'];
		}

		return $result;
	}
}
