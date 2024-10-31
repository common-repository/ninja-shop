<?php

function it_exchange_basic_us_sales_taxes_settings_callback() {
	Ninja_Shop_Basic_US_Sales_Taxes_Addon_Settings::settings_callback();
}

function it_exchange_basic_us_sales_taxes_get_tax_row_settings( $row, $state = 'TN', $rate = array() ) {
	if ( empty( $rate ) ) { //just set some defaults
		$rate = array(
			'rate'     => '',
			'shipping' => false,
		);
	}

	$output = '<div class="item-row block-row">'; //start block-row

	$output .= '<div class="item-column block-column block-column-1">';
	$output .= '<select name="tax-rates[' . $row . '][state]">';

	$states = it_exchange_get_data_set( 'states', array( 'country' => 'US' ) );
	foreach ( $states as $abbr => $name ) {

		$output .= '<option value="' . $abbr . '" ' . selected( $abbr, $state, false ) . '>' . $name . '</option>';

	}

	$output .= '</select>';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-3">';
	$output .= '<input type="text" name="tax-rates[' . $row . '][rate]" value="' . $rate['rate'] . '" />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-4">';
	$shipping = empty( $rate['shipping'] ) ? false : true;
	$output .= '<input type="checkbox" name="tax-rates[' . $row . '][shipping]" ' . checked( $shipping, true, false ) . ' />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-delete">';
	$output .= '<a href class="it-exchange-basic-us-sales-taxes-addon-delete-tax-rate it-exchange-remove-item">&times;</a>';
	$output .= '</div>';

	$output .= '</div>'; //end block-row

	return $output;
}

function it_exchange_basic_us_sales_taxes_setup_session( $clear_cache = false ) {

	$cart = it_exchange_get_current_cart();

	if ( ! $cart->get_items()->count() ) {
		return false;
	}

	$provider      = new Provider();
	$tax_session   = it_exchange_get_session_data( 'addon_basic_us_sales_taxes' );
	$cart_subtotal = 0;

	foreach ( $cart->get_items()->without( 'shipping' ) as $item ) {
		if ( $item instanceof ITE_Taxable_Line_Item && ! $item->is_tax_exempt( $provider ) ) {
			$cart_subtotal += $item->get_total();
		}
	}

	$tax_session['cart_subtotal']            = $cart_subtotal;
	$tax_session['cart_subtotal_w_shipping'] = $cart_subtotal + $cart->calculate_total( 'shipping' );

	$taxes = $cart->get_items( 'tax', true )->with_only_instances_of( 'Ninja_Shop_Basic_US_Sales_Taxes_Tax_Item' );
	$data  = array();

	/** @var ITE_Canadian_Tax_Item $tax */
	foreach ( $taxes as $tax ) {

		if ( ! $tax->get_tax_rate() ) {
			continue;
		}

		$data[] = array( $tax->get_tax_rate()->to_array() ) + array( 'total' => $tax->get_total() );
	}

	$tax_session['taxes']       = $data;
	$tax_session['total_taxes'] = $taxes->total();

	it_exchange_update_session_data( 'addon_basic_us_sales_taxes', $tax_session );

	return true;
}

/**
 * Gets tax information based on products in cart
 *
 * 
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @param bool $clear_cache  Whether or not to force clear any cached tax values
 *
 * @return string The calculated tax
 */
function it_exchange_basic_us_sales_taxes_addon_get_total_taxes_for_cart( $format_price = true, $clear_cache = false ) {
	$taxes = 0;

	if ( it_exchange_basic_us_sales_taxes_setup_session() ) {
		$tax_session = it_exchange_get_session_data( 'addon_basic_us_sales_taxes' );
		if ( ! empty( $tax_session['total_taxes'] ) ) {
			$taxes = $tax_session['total_taxes'];
		}
	}

	if ( $format_price ) {
		$taxes = it_exchange_format_price( $taxes );
	}

	return $taxes;
}
