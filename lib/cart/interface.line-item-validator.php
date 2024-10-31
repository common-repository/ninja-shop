<?php
/**
 * Line Item Validator interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Line_Item_Validator
 */
interface ITE_Line_Item_Validator {

	/**
	 * Get the name of this validator.
	 *
	 *
	 *
	 * @return string
	 */
	public static function get_name();

	/**
	 * Does this validator accept items of the given type.
	 *
	 *
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function accepts( $type );

	/**
	 * Perform validation on the cart.
	 *
	 *
	 *
	 * @param \ITE_Line_Item     $item
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool
	 */
	public function validate( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null );

	/**
	 * Coerce a cart to be valid.
	 *
	 *
	 *
	 * @param \ITE_Line_Item     $item
	 * @param \ITE_Cart          $cart
	 * @param \ITE_Cart_Feedback $feedback
	 *
	 * @return bool True if the line item was coerced, false if not.
	 * 
	 * @throws ITE_Line_Item_Coercion_Failed_Exception
	 */
	public function coerce( ITE_Line_Item $item, ITE_Cart $cart, ITE_Cart_Feedback $feedback = null );
}
