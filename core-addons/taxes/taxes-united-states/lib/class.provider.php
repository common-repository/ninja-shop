<?php

class Ninja_Shop_Basic_US_Sales_Taxes_Provider extends \ITE_Tax_Provider {

	/**
	 * @inheritDoc
	 */
	public function is_product_tax_exempt( IT_Exchange_Product $product ) {
		return false;
	}

	/**
	 * Get all of the tax rates for a given state.
	 *
	 * 
	 *
	 * @param string $state
	 *
	 * @return Tax_Rate[]
	 */
	public function get_rates_for_state( $state ) {

		$settings = it_exchange_get_option( 'addon_basic_us_sales_taxes', true, false );

		if ( empty( $settings['tax-rates'] ) ) {
			$settings = it_exchange_get_option( 'addon_basic_us_sales_taxes', true );
		}

		if ( ! isset( $settings['tax-rates'][ $state ] ) ) {
			return array();
		}

		$rates = array();

		foreach ( $settings['tax-rates'][ $state ] as $i => $data ) {
			$rates[] = new Ninja_Shop_Basic_US_Sales_Taxes_Tax_Rate( $state, $i, $data );
		}

		return $rates;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_class() {
		return 'Tax_Item';
	}

	/**
	 * @inheritDoc
	 */
	public function add_taxes_to( ITE_Taxable_Line_Item $item, ITE_Cart $cart ) {

		$address = $cart->get_shipping_address() ? $cart->get_shipping_address() : $cart->get_billing_address();
		$rates   = $this->get_rates_for_state( $address['state'] );

		$item->remove_all_taxes(); // @NOTE Prevents stacked tax rates on address change.

		foreach ( $rates as $rate ) {
			$item->add_tax( Ninja_Shop_Basic_US_Sales_Taxes_Tax_Item::create( $rate, $item ) );
			$cart->save_item( $item );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function is_restricted_to_location() {
		return new ITE_Simple_Zone( array(
			'country' => 'US',
			'state'   => array_keys( it_exchange_get_data_set( 'states', array( 'country' => 'US' ) ) )
		) );
	}
}
