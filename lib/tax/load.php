<?php
/**
 * Load the tax module.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/abstract.provider.php';
require_once dirname( __FILE__ ) . '/class.simple-provider.php';

require_once dirname( __FILE__ ) . '/class.tax-manager.php';
require_once dirname( __FILE__ ) . '/class.tax-managers.php';

/**
 * Whenever a new cart is created, load the tax manager for it.
 *
 *
 *
 * @param \ITE_Cart $cart
 */
function it_exchange_load_tax_manager( ITE_Cart $cart ) {
	ITE_Tax_Managers::manager( $cart );
}

add_action( 'ninja_shop_construct_cart', 'it_exchange_load_tax_manager' );

/**
 * Fire the register tax providers hook.
 *
 *
 */
function it_exchange_fire_register_taxes_hook() {

	/**
	 * Fires when tax providers should be registered.
	 *
	 *
	 *
	 * @param \ITE_Tax_Managers $managers
	 */
	do_action( 'ninja_shop_register_tax_providers', new ITE_Tax_Managers() );
}

add_action( 'ninja_shop_enabled_addons_loaded', 'it_exchange_fire_register_taxes_hook' );
