<?php
/**
 * Scopable Line Item interface
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Scopable_Line_Item
 */
interface ITE_Scopable_Line_Item {

	/**
	 * Is this line item a scoped line item.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_scoped();

	/**
	 * Get the line item this item is scoped from.
	 *
	 *
	 *
	 * @return static
	 *
	 * @throws UnexpectedValueException If ::is_scoped() returns false.
	 */
	public function scoped_from();

	/**
	 * Set the line item this item is scoped from.
	 *
	 *
	 *
	 * @param ITE_Scopable_Line_Item $scoped_from
	 */
	public function set_scoped_from( ITE_Scopable_Line_Item $scoped_from );

	/**
	 * Get a list of all the parameter keys shared in this scope.
	 *
	 * This is used to optimize storage by not duplicating parameters across scoped line items.
	 *
	 *
	 *
	 * @return string[]
	 */
	public function shared_params_in_scope();
}
