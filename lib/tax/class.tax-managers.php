<?php
/**
 * Tax Managers class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Tax_Managers
 */
final class ITE_Tax_Managers {

	/** @var ITE_Tax_Manager[] */
	private static $managers = array();

	/** @var ITE_Tax_Provider[] */
	private static $providers = array();

	/**
	 * Get a tax manager for a given cart.
	 *
	 *
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return \ITE_Tax_Manager
	 */
	public static function manager( ITE_Cart $cart ) {

		if ( isset( self::$managers[ $cart->get_id() ] ) ) {
			self::$managers[ $cart->get_id() ]->set_cart( $cart );
		} else {
			self::$managers[ $cart->get_id() ] = new ITE_Tax_Manager( $cart );
			self::$managers[ $cart->get_id() ]->hooks();

			foreach ( self::$providers as $provider ) {
				self::$managers[ $cart->get_id() ]->register_provider( $provider, false );
			}

			self::$managers[ $cart->get_id() ]->sort_providers();
		}

		return self::$managers[ $cart->get_id() ];
	}

	/**
	 * Register a tax provider.
	 *
	 *
	 *
	 * @param \ITE_Tax_Provider $provider
	 */
	public static function register_provider( ITE_Tax_Provider $provider ) {
		self::$providers[] = $provider;

		foreach ( self::$managers as $manager ) {
			$manager->register_provider( $provider );
		}
	}
}
