<?php
/**
 * Optional Product Feature.
 *
 * 
 * @license GPlv2
 */

/**
 * Interface ITE_Optionally_Supported_Product_Feature
 */
interface ITE_Optionally_Supported_Product_Feature extends ITE_Optionally_Supported_Feature {

	/**
	 * Get details for a product.
	 *
	 *
	 *
	 * @param IT_Exchange_Product $product
	 *
	 * @return array
	 */
	public function get_details_for_product( IT_Exchange_Product $product );
}
