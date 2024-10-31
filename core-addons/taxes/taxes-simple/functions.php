<?php
/**
 * This file contains functions for interacting with the addon
 * 
 * @package IT_Exchange
*/

/**
 * Get taxes for cart
 *
 *
 *
*/
function it_exchange_addon_get_simple_taxes_for_cart( $format_price=true ) {

	$cart       = it_exchange_get_current_cart();
	$cart_taxes = $cart->get_items( 'tax', true )->with_only_instances_of( 'ITE_Simple_Tax_Line_Item' )->total();

	$taxes = apply_filters( 'ninja_shop_addon_get_simple_taxes_for_cart', $cart_taxes );

	if ( $format_price ) {
		$taxes = it_exchange_format_price( $taxes );
	}

	return $taxes;
}

/**
 * Get taxes for transaction
 *
 *
 *
*/
function it_exchange_addon_get_simple_taxes_for_transaction( $transaction=false, $format_price=true ) {
    $taxes = 0;
    if ( !empty( $transaction ) ) {
	    $transaction = it_exchange_get_transaction( $transaction );
        if ( !empty( $transaction->cart_details->taxes_raw ) ) {
        	$taxes = $transaction->cart_details->taxes_raw;
        } else {
	        $taxes = 0;
        }
    } else if ( !empty( $GLOBALS['it_exchange']['transaction'] ) ) {
        $transaction = $GLOBALS['it_exchange']['transaction'];
        if ( !empty( $transaction->cart_details->taxes_raw ) ) {
        	$taxes = $transaction->cart_details->taxes_raw;
        } else {
	        $taxes = 0;
        }
    }
    if ( $format_price )
        $taxes = it_exchange_format_price( $taxes );
    return $taxes;  
}

/**
 * Get labels from settings
 *
 *
 *
 * @param string $label which label do you want to return? tax or taxes
 * @return string
*/
function it_exchange_add_simple_taxes_get_label( $label ) {
	$settings = it_exchange_get_option( 'addon_taxes_simple' );
	if ( 'tax' == $label )
		$label = 'tax-label-singular';
	if ( 'taxes' == $label )
		$label = 'tax-label-plural';

	return empty( $settings[$label] ) ? '' : esc_attr( $settings[$label] );
}
