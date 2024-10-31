<?php

if ( ! class_exists( 'ITE_Tax_Provider' ) ) {
	return;
}

/**
 * New API functions.
 */
include( 'api/load.php' );

include( 'lib/class.admin.php' );
(new Ninja_Shop_Basic_US_Sales_Taxes_Admin())->setup();

/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to
 * save / retreive options. Add-ons are not required to do this.
 */
include( 'lib/class.addon-settings.php' );

/**
 * The following file contains utility functions specific to our customer pricing add-on
 * If you're building your own addon, it's likely that you will
 * need to do similar things.
 */
include( 'lib/addon-functions.php' );

/**
 * We decided to place all AJAX hooked functions into this file, just for ease of use
 */
include( 'lib/addon-ajax-hooks.php' );

/**
 * Exchange Add-ons require several hooks in order to work properly.
 * We've placed them all in one file to help add-on devs identify them more easily
 */
include( 'lib/required-hooks.php' );

require_once dirname( __FILE__ ) . '/lib/class.provider.php';
require_once dirname( __FILE__ ) . '/lib/class.line-item.php';
require_once dirname( __FILE__ ) . '/lib/class.rate.php';
