<?php
/**
 * Load the WP CLI commands.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\CLI;

use iThemes\Exchange\CLI\Tools\Upgrades;

if ( ! version_compare( PHP_VERSION, '5.4.0', '>=' ) ) {
	return;
}

add_action( 'ninja_shop_enabled_addons_loaded', function () {
	\WP_CLI::add_command( 'it-exchange info', 'iThemes\Exchange\CLI\Info' );
	\WP_CLI::add_command( 'it-exchange tools system-info', 'iThemes\Exchange\CLI\Tools\SystemInfo' );
	\WP_CLI::add_command( 'it-exchange tools upgrades', new Upgrades( it_exchange_make_upgrader() ) );
} );
