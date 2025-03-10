<?php
/**
 * iThemes Exchange Easy Canadian Sales Taxes Add-on
 * @package exchange-addon-easy-canadian-sales-taxes
 * 
 */

if ( ! class_exists( 'ITE_Tax_Provider' ) ) {
	return;
}

/**
 * New API functions.
 */
include( 'api/load.php' );

/**
 * Exchange will build your add-on's settings page for you and link to it from our add-on
 * screen. You are free to link from it elsewhere as well if you'd like... or to not use our API
 * at all. This file has all the functions related to registering the page, printing the form, and saving
 * the options. This includes the wizard settings. Additionally, we use the Exchange storage API to
 * save / retreive options. Add-ons are not required to do this.
 */
include( 'lib/addon-settings.php' );

/**
 * We decided to place all AJAX hooked functions into this file, just for ease of use
 */
include( 'lib/addon-ajax-hooks.php' );
/**
 * The following file contains utility functions specific to our customer pricing add-on
 * If you're building your own addon, it's likely that you will
 * need to do similar things.
 */
include( 'lib/addon-functions.php' );

/**
 * Exchange Add-ons require several hooks in order to work properly.
 * We've placed them all in one file to help add-on devs identify them more easily
 */
include( 'lib/required-hooks.php' );

/**
 * New Product Features added by the Exchange Membership Add-on.
 */
require( 'lib/product-features/load.php' );

require_once dirname( __FILE__ ) . '/lib/class.provider.php';
require_once dirname( __FILE__ ) . '/lib/class.line-item.php';
require_once dirname( __FILE__ ) . '/lib/class.rate.php';

/**
 * Register and enqueue admin-specific stylesheets.
 *
 *
 *
 * @return null Return early if not on our addon page in the admin.
 */
function ninja_shop_easy_canadian_sales_taxes_enqueue_admin_styles() {

	if ( ! isset( $_GET['add-on-settings'] ) || 'easy-canadian-sales-taxes' !== $_GET['add-on-settings'] ) return;

	$plugin_slug = 'ninja-shop-easy-canadian-sales-taxes';

	wp_enqueue_style( $plugin_slug . '-admin-styles', plugins_url( 'lib/styles/admin.css', __FILE__ ), array() );

}
add_action( 'admin_enqueue_scripts', 'ninja_shop_easy_canadian_sales_taxes_enqueue_admin_styles' );

/**
 * Register and enqueue admin-specific JS.
 *
 *
 *
 * @return null Return early if not on our addon page in the admin.
 */
function ninja_shop_easy_canadian_sales_taxes_enqueue_admin_scripts() {

	if ( ! isset( $_GET['add-on-settings'] ) || 'easy-canadian-sales-taxes' !== $_GET['add-on-settings'] ) return;

	$plugin_slug = 'ninja-shop-easy-canadian-sales-taxes';

	wp_enqueue_script( $plugin_slug . '-admin-script', plugins_url( 'lib/js/admin.js', __FILE__ ), array( 'jquery' ) );

}
add_action( 'admin_enqueue_scripts', 'ninja_shop_easy_canadian_sales_taxes_enqueue_admin_scripts' );
