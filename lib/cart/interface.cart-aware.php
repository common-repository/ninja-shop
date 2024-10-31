<?php
/**
 * Cart Aware Interface
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Aware
 */
interface ITE_Cart_Aware {

	/**
	 * Set the cart object.
	 * 
	 *
	 * 
	 * @param \ITE_Cart $cart
	 */
	public function set_cart( ITE_Cart $cart );
}
