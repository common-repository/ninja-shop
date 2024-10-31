<?php
/**
 * Load the personal data module.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop;

// load interfaces.
require_once dirname( __FILE__ ) . '/interface.eraser.php';
require_once dirname( __FILE__ ) . '/interface.exporter.php';

// load policy classes.
require_once dirname( __FILE__ ) . '/class.policy.php';

// load address classes.
require_once dirname( __FILE__ ) . '/address/class.eraser.php';

// load transaction classes.
require_once dirname( __FILE__ ) . '/transaction/class.eraser.php';
require_once dirname( __FILE__ ) . '/transaction/class.exporter.php';

// load hooks, actions, and filters.
require_once dirname( __FILE__ ) . '/hooks.php';
