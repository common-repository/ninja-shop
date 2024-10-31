<?php
/**
 * Quantity Modifiable Interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Quantity_Modifiable_Item
 */
interface ITE_Quantity_Modifiable_Item {

	/**
	 * Set the item's new quantity.
	 *
	 *
	 *
	 * @param int $quantity
	 */
	public function set_quantity( $quantity );

	/**
	 * Is the item's quantity modifiable.
	 *
	 * An item type can generally have its quantity modified,
	 * but a particular instance of it could not be.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_quantity_modifiable();

	/**
	 * Get the maximum purchase quantity available.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_max_quantity_available();
}
