<?php
/**
 * Load the roles and capabilities module.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.roles.php';
require_once dirname( __FILE__ ) . '/class.capabilities.php';

new IT_Exchange_Roles( new IT_Exchange_Capabilities() );
