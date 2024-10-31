<?php
/**
 * Contains upgrade skin class.
 *
 * 
 * @license GPLv2
 */

/**
 * Interface IT_Exchange_Upgrade_SkinInterface
 *
 * Upgrade skins control how the status of an upgrade is relayed to the user.
 * This interface should be implemented for each type of user interface. For
 * example a CLI skin, a JavaScript progress bar skin, a stepped-redirect skin.
 */
interface IT_Exchange_Upgrade_SkinInterface {

	/**
	 * Output debug information.
	 *
	 * For use when in verbose mode.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function debug( $message );

	/**
	 * Notify the user of a non-critical problem.
	 *
	 *
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function warn( $message );

	/**
	 * Notify the user of a critical error.
	 *
	 *
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function error( $message );

	/**
	 * Increment the progress by a certain amount.
	 *
	 *
	 *
	 * @param int $amount
	 *
	 * @return void
	 */
	public function tick( $amount = 1 );

	/**
	 * Notify the user the upgrade has finished.
	 *
	 *
	 *
	 * @return void
	 */
	public function finish();
}
