<?php
/**
 * Zone interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Zone
 */
interface ITE_Zone {

	/**
	 * Wildcard Match
	 *
	 * @var string
	 */
	const WILD = '*';

	/**
	 * Whether this zone contains a location.
	 *
	 *
	 *
	 * @param \ITE_Location $location
	 * @param string        $upper_bound Specify the upper bound that must match. For example, passing 'state'
	 *                                   requires the country and state to match for the method to return true.
	 *
	 * @return bool
	 */
	public function contains( ITE_Location $location, $upper_bound = '' );

	/**
	 * Mask a location based on this zone.
	 *
	 *
	 *
	 * @param \ITE_Location $location
	 *
	 * @return \ITE_Location Masked location. This should be a NEW object.
	 */
	public function mask( ITE_Location $location );

	/**
	 * Get the precision of this zone.
	 *
	 * For example 'city' if rates vary all the way down to city level.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_precision();
}
