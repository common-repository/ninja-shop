<?php
/**
 * Ajax upgrade skin.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Upgrade_Skin_Ajax
 */
class IT_Exchange_Upgrade_Skin_Ajax implements IT_Exchange_Upgrade_SkinInterface {

	const DEBUG = 'debug';
	const WARNING = 'warning';
	const ERROR = 'error';

	/**
	 * @var array
	 */
	private $messages = array();

	/**
	 * @var int
	 */
	private $total_upgraded = 0;

	/**
	 * Output debug information.
	 *
	 * For use when in verbose mode.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function debug( $message ) {
		$this->messages[] = array(
			'message' => $message,
			'type'    => self::DEBUG
		);
	}

	/**
	 * Notify the user of a non-critical problem.
	 *
	 *
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function warn( $message ) {
		$this->messages[] = array(
			'message' => $message,
			'type'    => self::WARNING
		);
	}

	/**
	 * Notify the user of a critical error.
	 *
	 *
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function error( $message ) {
		$this->messages[] = array(
			'message' => $message,
			'type'    => self::ERROR
		);
	}

	/**
	 * Increment the progress by a certain amount.
	 *
	 *
	 *
	 * @param int $amount
	 *
	 * @return void
	 */
	public function tick( $amount = 1 ) {
		$this->total_upgraded += $amount;
	}

	/**
	 * Notify the user the upgrade has finished.
	 *
	 *
	 *
	 * @return void
	 */
	public function finish() {
		// no-op. this is handled in JS
	}

	/**
	 * Get data for ajax handler.
	 *
	 *
	 *
	 * @return array
	 */
	public function out() {
		return array(
			'feedback'      => $this->messages,
			'itemsUpgraded' => $this->total_upgraded
		);
	}
}
