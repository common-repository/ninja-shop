<?php
/**
 * Optionally Supported Feature interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Optionally_Supported_Feature
 */
interface ITE_Optionally_Supported_Feature {

	/**
	 * Slug of the feature required.
	 *
	 * For example: 'recurring-payments'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_feature_slug();

	/**
	 * Human readable describing this feature.
	 *
	 * For example: 'Recurring Payments'.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_feature_label();

	/**
	 * Get all possible details for this feature.
	 *
	 * For example: 'auto-renew', 'profile', 'signup-fee'.
	 *
	 *
	 *
	 * @return string[]
	 */
	public function get_allowed_details();
}
