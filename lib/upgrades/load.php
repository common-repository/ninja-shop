<?php
/**
 * Load the upgrade routine library.
 *
 * 
 * @license GPLv2
 */

defined( 'ABSPATH' ) || die();

require_once dirname( __FILE__ ) . '/class.exception.php';
require_once dirname( __FILE__ ) . '/class.config.php';
require_once dirname( __FILE__ ) . '/interface.skin.php';
require_once dirname( __FILE__ ) . '/interface.upgrade.php';
require_once dirname( __FILE__ ) . '/class.upgrader.php';
require_once dirname( __FILE__ ) . '/functions.php';

// load skins and handlers
require_once dirname( __FILE__ ) . '/skins/class.multi.php';
require_once dirname( __FILE__ ) . '/skins/class.file.php';
require_once dirname( __FILE__ ) . '/skins/class.ajax.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/skins/class.cli.php';
}

require_once dirname( __FILE__ ) . '/handlers/class.ajax.php';

// load routines
// @NOTE Upgrade routines included here.
