<?php
/**
 * iThemes Exchange Easy EU Value Added Taxes Add-on
 * @package exchange-addon-easy-eu-value-added-taxes
 * 
 */

/**
 * Is the passed country valid for taxation.
 *
 *
 *
 * @param string $country
 *
 * @return bool
 */
function it_exchange_easy_eu_vat_valid_country_for_tax( $country ) {

	$member_states = it_exchange_get_data_set( 'eu-member-states' );

	return isset( $member_states[ $country ] );
}

/**
 * Get the customer's country to base VAT off of.
 *
 *
 *
 * @param \ITE_Cart $cart
 *
 * @return string
 */
function it_exchange_easy_eu_vat_get_country( ITE_Cart $cart ) {

	$address = $cart->requires_shipping() ? $cart->get_shipping_address() : $cart->get_billing_address();

	return empty( $address['country'] ) ? '' : $address['country'];
}

/**
 * Whether to show the VAT # Manager or not.
 *
 *
 *
 * @param \ITE_Cart|null $cart
 *
 * @return bool
 */
function it_exchange_easy_eu_vat_show_vat_manager( ITE_Cart $cart = null ) {

	$cart = $cart ? $cart : it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return false;
	}

	$provider = new ITE_EU_VAT_Tax_Provider();

	$address = $cart->get_shipping_address() ? $cart->get_shipping_address() : $cart->get_billing_address();

	return $provider->is_restricted_to_location()->contains( $address );
}

/**
 * Get the default tax rate.
 *
 *
 *
 * @param array $settings
 *
 * @return array
 */
function it_exchange_easy_eu_vat_get_default_tax_rate( array $settings = array() ) {

	$settings = $settings ?: it_exchange_get_option( 'addon_easy_eu_value_added_taxes' );

	$tax_rates = $settings['tax-rates'];

	foreach ( $tax_rates as $index => $rate ) {
		if ( isset( $rate['default'] ) && ( $rate['default'] === 'checked' || $rate['default'] === true ) ) {
			$rate['index'] = $index;

			return $rate;
		}
	}

	return array();
}

/**
 * Get the tax rate for a shipping method.
 *
 *
 *
 * @param string $method_slug
 * @param array  $settings
 *
 * @return array The rate configuration. 'rate' will hold the percentage rate.
 */
function it_exchange_easy_eu_vat_get_shipping_tax_rate( $method_slug, array $settings = array() ) {

	$settings = $settings ?: it_exchange_get_option( 'addon_easy_eu_value_added_taxes' );

	$shipping  = isset( $settings['shipping'] ) ? $settings['shipping'] : array();
	$tax_rates = $settings['tax-rates'];

	if ( isset( $shipping[ $method_slug ], $tax_rates[ $shipping[ $method_slug ] ] ) ) {
		$rate = $tax_rates[ $shipping[ $method_slug ] ];
		$rate['index'] = $shipping[ $method_slug ];

		return $rate;
	}

	return it_exchange_easy_eu_vat_get_default_tax_rate( $settings );
}

function it_exchange_easy_eu_value_added_taxes_get_tax_row_settings( $key, $rate = array(), $memberstate = false ) {
	if ( empty( $rate ) ) { //just set some defaults
		$rate = array( //Member State
			'label'    => '',
			'rate'     => 0,
			'shipping' => false,
			'default'  => 'unchecked',
		);
	}

	if ( ! empty( $memberstate ) ) {
		$name          = 'it-exchange-add-on-easy-eu-value-added-taxes-vat-moss-tax-rates[' . $memberstate . '][' . $key . ']';
	} else {
		$name          = 'it-exchange-add-on-easy-eu-value-added-taxes-tax-rates[' . $key . ']';
	}

	$output = '<div class="item-row block-row">'; //start block-row

	$output .= '<div class="item-column block-column block-column-1">';
	$output .= '<input type="text" name="' . $name . '[label]" value="' . $rate['label'] . '" />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-2">';
	$output .= '<input type="text" name="' . $name . '[rate]" value="' . $rate['rate'] . '" />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-default">';
	$output .= '<span class="it-exchange-easy-eu-value-added-taxes-addon-default-checkmark it-exchange-easy-eu-value-added-taxes-addon-default-checkmark-' . $rate['default'] . '"></span>';
	$output .= '<input type="hidden" class="it-exchange-easy-eu-value-added-taxes-addon-default-checkmark" name="' . $name . '[default]" value="' . $rate['default'] . '" />';
	$output .= '</div>';

	$output .= '<div class="item-column block-column block-column-delete">';
	$output .= '<a href class="it-exchange-easy-eu-value-added-taxes-addon-delete-tax-rate it-exchange-remove-item">&times;</a>';
	$output .= '</div>';

	$output .= '</div>'; //end block-row

	return $output;
}

/**
 * Get a summary of the cart taxes.
 *
 *
 * @deprecated 2.0.0
 *
 * @return array|bool
 */
function it_exchange_easy_eu_value_added_taxes_get_cart_taxes() {

	_deprecated_function( __FUNCTION__, '1.8.0' );

	return array();
}

/**
 * Setup the taxes session.
 *
 *
 *
 * @param bool $clear_cache
 *
 * @return bool
 */
function it_exchange_easy_eu_value_added_taxes_setup_session( $clear_cache = false ) {

	$cart = it_exchange_get_current_cart( false );

	if ( ! $cart ) {
		return false;
	}

	$tax_session = it_exchange_get_session_data( 'addon_easy_eu_value_added_taxes' );
	$info        = it_exchange_easy_eu_vat_get_tax_info_for_cart( $cart );

	if ( count( $info ) === 0 ) {
		return false;
	}

	$tax_session = array_merge( $tax_session, $info );

	if ( empty( $tax_session['vat_number'] ) || $tax_session['intrastate'] ) {
		//Charge Tax if no VAT Number is supplied or if the customer is purchasing from the same member state
		$tax_session['summary_only'] = false;
	} else {
		//Otherwise, don't charge tax, just summarize the VAT
		$tax_session['summary_only'] = true;
	}

	it_exchange_update_session_data( 'addon_easy_eu_value_added_taxes', $tax_session );

	return true;
}

/**
 * Get summarized info about taxes for given cart.
 *
 *
 *
 * @param \ITE_Cart $cart
 *
 * @return array
 */
function it_exchange_easy_eu_vat_get_tax_info_for_cart( ITE_Cart $cart ) {

	$info     = array();
	$settings = it_exchange_get_option( 'addon_easy_eu_value_added_taxes' );
	$address  = $cart->get_shipping_address() ? $cart->get_shipping_address() : $cart->get_billing_address();

	if ( empty( $address['country'] ) || ! it_exchange_easy_eu_vat_valid_country_for_tax( $address['country'] ) ) {
		return array();
	} else {
		$info['vat_country'] = $address['country'];
	}

	$info['vat_number']  = $cart->has_meta( 'eu-vat-number' ) ? $cart->get_meta( 'eu-vat-number' ) : '';
	$info['vat_country'] = $cart->has_meta( 'eu-vat-country' ) ? $cart->get_meta( 'eu-vat-country' ) : '';

	//Is this an intrastate transaction, used for determining VAT MOSS
	$info['intrastate'] = $settings['vat-country'] === $address['country'];

	if ( empty( $info['vat_number'] ) || $info['intrastate'] ) {
		//Charge Tax if no VAT Number is supplied or if the customer is purchasing from the same member state
		$info['summary_only'] = false;
	} else {
		//Otherwise, don't charge tax, just summarize the VAT
		$info['summary_only'] = true;
	}

	if ( ! $cart->get_items()->count() ) {
		return array();
	}

	$vat_moss_cart_subtotal = 0;

	/** @var ITE_Cart_Product $product */
	foreach ( $cart->get_items( 'product' ) as $product ) {
		if ( 'on' === $product->get_product()->get_feature( 'value-added-taxes', array( 'setting' => 'vat-moss' ) ) ) {
			$vat_moss_cart_subtotal += $product->get_total();
		}
	}

	$cart_subtotal          = it_exchange_get_cart_subtotal( false );
	$vat_moss_cart_subtotal = apply_filters( 'ninja_shop_get_cart_subtotal', $vat_moss_cart_subtotal );
	$shipping_cost          = it_exchange_get_cart_shipping_cost( false, false );
	$applied_coupons        = it_exchange_get_applied_coupons();
	$serialized_coupons     = maybe_serialize( $applied_coupons );

	$info['country']                = $address['country'];
	$info['cart_subtotal']          = $cart_subtotal;
	$info['vat_moss_cart_subtotal'] = $vat_moss_cart_subtotal;
	$info['shipping_cost']          = $shipping_cost;
	$info['applied_coupons']        = $serialized_coupons;

	$info = array_merge(
		$info,
		it_exchange_easy_eu_vat_get_tax_summary_for_taxable_items(
			$cart->get_items()->taxable()->to_array(), $address['country']
		)
	);

	return $info;
}

/**
 * Get the tax summary for taxable items.
 *
 *
 *
 * @param ITE_Taxable_Line_Item[] $items
 * @param string                  $country
 *
 * @return array
 */
function it_exchange_easy_eu_vat_get_tax_summary_for_taxable_items( array $items, $country ) {

	$product_taxes = array();
	$taxes         = array();
	$moss_taxes    = array();
	$total_taxes   = 0;

	foreach ( $items as $item ) {

		/** @var ITE_EU_VAT_Line_Item $tax_item */
		$tax_item = $item->get_taxes()->with_only_instances_of( 'ITE_EU_VAT_Line_Item' )->first();

		if ( ! $tax_item ) {
			continue;
		}

		$vat_rate = $tax_item->get_vat_rate();
		$key      = $vat_rate->get_index();

		if ( $item instanceof ITE_Cart_Product ) {
			$product_taxes[ $item->get_product_id() ] = $vat_rate->get_index();
		}

		if ( $vat_rate->get_type() === ITE_EU_VAT_Rate::VAT ) {
			$taxes[ $key ]['tax-rate']       = $vat_rate->to_array();
			$taxes[ $key ]['total']          = $tax_item->get_total();
			$taxes[ $key ]['taxable_amount'] = $tax_item->get_aggregate()->get_taxable_amount() * $tax_item->get_aggregate()->get_quantity();
			$taxes[ $key ]['country']        = $country;
			$total_taxes += $tax_item->get_total();
		} elseif ( $vat_rate->get_type() === ITE_EU_VAT_Rate::MOSS ) {
			$moss_taxes[ $key ]['tax-rate']       = $vat_rate->to_array();
			$moss_taxes[ $key ]['total']          = $tax_item->get_total();
			$moss_taxes[ $key ]['taxable_amount'] = $tax_item->get_aggregate()->get_taxable_amount() * $tax_item->get_aggregate()->get_quantity();
			$moss_taxes[ $key ]['country']        = $country;
			$total_taxes += $tax_item->get_total();
		}
	}

	$info = array();

	$info['taxes']          = $taxes;
	$info['total_taxes']    = $total_taxes;
	$info['product_taxes']  = $product_taxes;
	$info['vat_moss_taxes'] = $moss_taxes;

	return $info;
}

/**
 * Generate taxes for a cart that is summary only.
 *
 * Summary only carts just have the VAT info summarized, but the totals aren't charged to the customer,
 * so we don't save the items to the cart with the taxes provided.
 *
 *
 *
 * @param \ITE_Cart $cart
 *
 * @return \ITE_Line_Item_Collection
 */
function it_exchange_easy_eu_vat_do_summary_only_taxes( ITE_Cart $cart ) {

	$provider = new ITE_EU_VAT_Tax_Provider();

	$taxable = $cart->get_items()->taxable();

	foreach ( $taxable as $item ) {
		$tax = $provider->make_tax_for( $item, $cart );

		if ( $tax ) {
			$item->add_tax( $tax );
		}
	}

	return $taxable;
}

/**
 * Gets tax taxes based on products in cart
 *
 *
 *
 * @param bool $format_price Whether or not to format the price or leave as a float
 * @param bool $clear_cache  Whether or not to force clear any cached tax values
 *
 * @return string|float
 */
function it_exchange_easy_eu_value_added_taxes_addon_get_total_taxes_for_cart( $format_price = true, $clear_cache = false ) {
	$taxes = 0;

	if ( it_exchange_easy_eu_value_added_taxes_setup_session() ) {
		$tax_session = it_exchange_get_session_data( 'addon_easy_eu_value_added_taxes' );
		if ( ! empty( $tax_session['total_taxes'] ) ) {
			$taxes = $tax_session['total_taxes'];
		}
	}

	if ( $format_price ) {
		$taxes = it_exchange_format_price( $taxes );
	}

	return $taxes;
}

/**
 * Verify a VAT number.
 *
 * This performs an external API request.
 *
 *
 *
 * @param string $country_code
 * @param string $vat_number
 *
 * @return true|string True if valid VAT Number. String error message otherwise.
 */
function it_exchange_easy_eu_value_added_taxes_addon_verify_vat( $country_code, $vat_number ) {
	$soap_url    = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';
	$soap_client = new SOAPClient( $soap_url );
	$query       = array(
		'countryCode' => strtoupper( trim( $country_code ) ),
		'vatNumber'   => strtoupper( trim( $vat_number ) ),
	);

	if ( 'UK' === $query['countryCode'] ) {
		$query['countryCode'] = 'GB'; //VIES treats UK like Great Britain...
	}

	try {
		$result = $soap_client->checkVat( $query );
		if ( is_soap_fault( $result ) ) {
			throw new Exception( $result->faultstring() );
		} else if ( ! empty( $result->valid ) && $result->valid ) {
			return true;
		} else {
			throw new Exception( sprintf( __( 'Error trying to validate VAT number: %s-%s.', 'LION' ), $country_code, $vat_number ) );
		}
	}
	catch ( Exception $e ) {
		return $e->getMessage();
	}
}

/**
 * Get Customer EU VAT Details
 *
 * Among other things this function is used as a callback for the customer EU VAT
 * purchase requriement.
 *
 *
 * @deprecated 2.0.0
 *
 * @param int $customer_id the customer id. leave blank to use the current customer.
 *
 * @return array|int
 */
function it_exchange_easy_eu_value_added_taxes_get_customer_vat_details( $customer_id = 0 ) {

	_deprecated_function( __FUNCTION__, '1.8.0' );

	if ( it_exchange_easy_eu_value_added_taxes_setup_session() ) {
		$tax_session = it_exchange_get_session_data( 'addon_easy_eu_value_added_taxes' );
		//We only want to require the VAT details if the user is in an EU Memberstate
		if ( ! empty( $tax_session['vat_country'] ) ) {
			$vat_details = it_exchange_get_customer_data( 'eu_vat_details', $customer_id );

			return apply_filters( 'ninja_shop_easy_eu_value_added_taxes_get_customer_vat_details', $vat_details, $customer_id );
		}
	}

	return - 1;
}
