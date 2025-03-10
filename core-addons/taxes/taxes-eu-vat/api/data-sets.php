<?php
/**
 * Add the EU Member States data set to the iThemes Exchange
 * Core Data sets library
 *
 * @package exchange-addon-easy-eu-value-added-taxes
 * 
*/

function it_exchange_easy_eu_value_added_taxes_addon_get_data_set_properties_eu_member_states( $data_sets ) {
	$data_sets = array(
		'file'     => dirname( __FILE__ ) . '/data-sets/member-states.php',
		'function' => 'it_exchange_easy_eu_value_added_taxes_addon_get_eu_member_states',
	);
	return $data_sets;
}
add_filter( 'ninja_shop_get_data_set_properties_eu-member-states', 'it_exchange_easy_eu_value_added_taxes_addon_get_data_set_properties_eu_member_states' );