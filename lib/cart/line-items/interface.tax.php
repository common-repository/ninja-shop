<?php
/**
 * Tax Line Item interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Tax_Line_Item
 */
interface ITE_Tax_Line_Item extends ITE_Aggregatable_Line_Item {
	
	/**
	 * Get the tax rate as a percentage.
	 *
	 * Ex: 8.75
	 *
	 *
	 *
	 * @return float
	 */
	public function get_rate();

	/**
	 * Determine whether this tax applies to a given line item.
	 *
	 *
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 *
	 * @return bool
	 */
	public function applies_to( ITE_Taxable_Line_Item $item );

	/**
	 * Clone this tax item to be applied to a given taxable item.
	 *
	 *
	 *
	 * @param \ITE_Taxable_Line_Item $item
	 *
	 * @return self A new instance of this class.
	 */
	public function create_scoped_for_taxable( ITE_Taxable_Line_Item $item );

	/**
	 * Get the tax provider.
	 *
	 *
	 *
	 * @return ITE_Tax_Provider
	 */
	public function get_provider();
}
