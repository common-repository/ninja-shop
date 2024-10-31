<?php
/**
 * Requires optionally supported features interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Requires_Optionally_Supported_Features
 */
interface ITE_Requires_Optionally_Supported_Features {

	/**
	 * Retrieve all optionally supported features that are required by this object.
	 *
	 *
	 *
	 * @return ITE_Optionally_Supported_Feature_Requirement[]
	 */
	public function optional_features_required();
}
