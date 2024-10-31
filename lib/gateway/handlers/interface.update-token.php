<?php
/**
 * Update Token interface.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Update_Payment_Token_Handler
 */
interface ITE_Update_Payment_Token_Handler extends ITE_Gateway_Request_Handler {

	/**
	 * Can this handler update a given field.
	 *
	 *
	 *
	 * @param string $field
	 *
	 * @return bool
	 */
	public function can_update_field( $field );
}
