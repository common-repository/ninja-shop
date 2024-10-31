<?php
/**
 * Cart Repository aware interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Repository_Aware
 */
interface ITE_Cart_Repository_Aware {

	/**
	 * Set the cart repository on this object.
	 * 
	 *
	 * 
	 * @param \ITE_Cart_Repository $repository
	 */
	public function set_cart_repository( ITE_Cart_Repository $repository );
}
