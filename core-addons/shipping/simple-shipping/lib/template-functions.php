<?php
/**
 * Adds shipping-address as a valid super-widget state
 *
 * 
 *
 * @param array $valid_states existing valid states
 * @return array
*/
function it_exchange_simple_shipping_modify_valid_sw_states( $valid_states ) {
	$valid_states[] = 'shipping-address';
	return $valid_states;
}
add_filter( 'ninja_shop_super_widget_valid_states', 'it_exchange_simple_shipping_modify_valid_sw_states' );
