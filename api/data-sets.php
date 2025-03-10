<?php
/**
 * This file includes several or accesses several data-sets
 * for use in core and add-on development
 *
 * @package IT_Exchange
 * 
*/

/**
 * Returns a list of data-sets along with their meta data
 *
 * Meta data includes:
 * - file location
 * - function name
 *
 *
 *
 * @param string|bool $data_key
 *
 * @return array
*/
function it_exchange_get_data_set_properties( $data_key=false ) {

	$core_data_sets = array(
		'countries' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/countries.php',
			'function' => 'it_exchange_get_countries',
		),
		'country-codes' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/countries.php',
			'function' => 'it_exchange_get_iso3_country_codes',
		),
		'provinces' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/states.php',
			'function' => 'it_exchange_get_country_states',
		),
		'states' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/states.php',
			'function' => 'it_exchange_get_country_states',
		),
		'currencies' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/currencies.php',
			'function' => 'it_exchange_get_currencies',
		),
		'address-formats' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/data-formats.php',
			'function' => 'it_exchange_get_address_formats',
		),
		'measurement-formats' => array(
			'file'     => dirname( __FILE__ ) . '/data-sets/data-formats.php',
			'function' => 'it_exchange_get_measurement_formats',
		),
	);

	// If a key was passed, just return that info.
	if ( ! empty( $data_key ) )
		$data_set = empty( $core_data_sets[$data_key] ) ? array() : $core_data_sets[$data_key];
	else
		$data_set = $core_data_sets;

	// Modify the key for the filter
	$data_key = empty( $data_key ) ? '' : '_' . $data_key;

	// Apply filter and return info.
	return apply_filters( 'ninja_shop_get_data_set_properties' . $data_key, $data_set );
}

/**
 * Returns data from one of our data sets
 *
 * Add-ons can add data sets to this API with the it_exchange_get_data_set_properties filter
 *
 *
 *
 * @param string $key     the data set you are looking for
 * @param array  $options any options you want passed through to the function that retuns the data set
 *
 * @return mixed
*/
function it_exchange_get_data_set( $key, $options=array() ) {

	static $cache = array();

	if ( isset( $cache[ $key . serialize( $options ) ] ) && empty( $options['break_cache'] ) ) {
		return $cache[ $key . serialize( $options ) ];
	}

	$data_set_props = it_exchange_get_data_set_properties( $key );

	// Return false if we don't have a file or function
	if ( empty( $data_set_props['file'] ) || empty( $data_set_props['function'] ) )
		return false;

	// If the file is located, include it.
	if ( is_file( $data_set_props['file'] ) )
		include_once( $data_set_props['file'] );
	else
		return false;

	// Call the function if its callable. Pass the options to it as well
	if ( is_callable( $data_set_props['function'] ) )
		$data_set = call_user_func( $data_set_props['function'], $options );
	else
		return false;

	$cache[ $key . serialize( $options ) ] = $data_set;

	// Return the data. It should be filtered by the function. Not here.
	return $data_set;
}
