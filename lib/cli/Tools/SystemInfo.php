<?php
/**
 * System Info Command.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\CLI\Tools;

/**
 * Class SystemInfo
 *
 * @package iThemes\Exchange\CLI\Tools
 */
class SystemInfo extends \WP_CLI_Command {

	/**
	 * Output System Info.
	 *
	 *
	 *
	 * @param array $args
	 * @param array $assoc_args
	 */
	public function __invoke( $args, $assoc_args ) {

		$info = it_exchange_get_system_info();

		foreach ( $info as $heading => $fields ) {
			\WP_CLI::line( '## ' . $heading . ' ##' );
			\WP_CLI::line();

			foreach ( $fields as $label => $value ) {
				\WP_CLI::line( "$label: $value" );
			}

			\WP_CLI::line();
		}
	}
}
