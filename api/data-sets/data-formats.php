<?php
/**
 * Contains functions for the formats data set
 * 
 * @package IT_Exchange
*/

/**
 * Returns a list of possible address formats
 *
 *
 *
 * @param array $options
 * @return array
*/
function it_exchange_get_address_formats( $options=array() ) {
	$formats = array(
		'city-comma-province-postalcode'  => 'City, Province Postcode',
		'city-province-postalcode'        => 'City Province Postalcode',
		'postalcode-city-hyphen-province' => 'Postalcode City-Province',
		'postalcode-city-comma-province'  => 'Postalcode City, Province',
		'postalcode-city'                 => 'Postalcode City',
		'city-postalcode'                 => 'City Postalcode',
	);

	$formats = apply_filters( 'ninja_shop_get_address_formats', $formats );
	return $formats;
}

/**
 * Returns a list of possible measurement formats
 *
 *
 *
 * @param array $options
 * @return array
*/
function it_exchange_get_measurement_formats( $options=array() ) {
	$formats = array(
		'standard' => __( 'Standard', 'it-l10n-ithemes-exchange' ),
		'metric'   => __( 'Metric', 'it-l10n-ithemes-exchange' ),
	);

	$formats = apply_filters( 'ninja_shop_get_measurement_formats', $formats );
	return $formats;
}
