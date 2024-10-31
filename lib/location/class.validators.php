<?php
/**
 * Location Validators registry.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Location_Validators
 */
final class ITE_Location_Validators {

	/** @var ITE_Location_Validator[] */
	private static $validators = array();

	/**
	 * Add a validator.
	 *
	 *
	 *
	 * @param \ITE_Location_Validator $validator
	 */
	public static function add( ITE_Location_Validator $validator ) {
		self::$validators[ $validator->get_name() ] = $validator;
	}

	/**
	 * Remove a validator.
	 *
	 *
	 *
	 * @param string $name
	 */
	public static function remove( $name ) {
		unset( self::$validators[ $name ] );
	}

	/**
	 * Get all validators.
	 *
	 *
	 *
	 * @return \ITE_Location_Validator[]
	 */
	public static function all() {
		return array_values( self::$validators );
	}
}
