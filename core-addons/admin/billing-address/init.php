<?php
/**
 * This is basically a fancy setting masquerading as an addon.
 * @package IT_Exchange
 * 
*/
// No settings. This is either enabled or disabled.

/**
 * Enables multi item carts
 *
*/
add_filter( 'ninja_shop_billing_address_purchase_requirement_enabled', '__return_true' );
