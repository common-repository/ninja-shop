<?php
/**
 * Payment Source.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface ITE_Gateway_Payment_Source
 */
interface ITE_Gateway_Payment_Source {

	/**
	 * Get a label for this payment source.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_label();

	/**
	 * Get a unique-ish identifier for this payment source.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_identifier();
}
