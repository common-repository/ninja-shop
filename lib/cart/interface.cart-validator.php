<?php
/**
 * Cart Validator interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Cart_Validator
 */
interface ITE_Cart_Validator {

	/**
	 * Get the name of this validator.
	 *
	 *
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Perform validation on the cart.
	 *
	 *
	 *
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool
	 */
	public function validate( ITE_Cart $cart, ITE_Cart_Feedback $feedback = null );

	/**
	 * Coerce a cart to be valid.
	 *
	 *
	 *
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Line_Item     $new_item The most recently added item.
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool True if coercion took place, false if not.
	 *
	 * @throws ITE_Cart_Coercion_Failed_Exception
	 */
	public function coerce( ITE_Cart $cart, \ITE_Line_Item $new_item = null, ITE_Cart_Feedback $feedback = null );
}
