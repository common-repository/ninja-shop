<?php
/**
 * This file inits our Flat Rate Shipping add-on.
 * It is only included when the add-on is enabled.
 * @package IT_Exchange
 * 
*/

/**
 * This file includes our functions / hooks for adding a settings page and saving those settings.
 * You can roll your own settings page if you want, but our API will create the little gear for you
 * on the Exchange add-ons page.
*/
include( dirname( __FILE__ ) . '/lib/settings.php' );

/**
 * This file contains the code needed to register the Simple Shipping Provider
*/
include( dirname( __FILE__ ) . '/lib/provider.php' );

/**
 * This file contains the code needed to register Exchange's Flat Rate Shipping Method
*/
include( dirname( __FILE__ ) . '/lib/methods/flat-rate-shipping.php' );

/**
 * This file contains the code needed to register Exchange's Free Shipping Method
*/
include( dirname( __FILE__ ) . '/lib/methods/free-shipping.php' );

/**
 * This file includes our template related functions / hooks for template parts
*/
include( dirname( __FILE__ ) . '/lib/template-functions.php' );
