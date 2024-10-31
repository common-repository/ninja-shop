<?php
/*
 * Plugin Name: Ninja Shop
 * Version: 1.1.11
 * Text Domain: it-l10n-ithemes-exchange
 * Description: Easily sell your digital goods with Ninja Shop, simple ecommerce for WordPress
 * Plugin URI: https://getninjashop.com/
 * Author: Ninja Shop LLC
 * Author URI: https://getninjashop.com/
 *
 * Installation:
 * 1. Download and unzip the latest release zip file.
 * 2. If you use the WordPress plugin uploader to install this plugin skip to step 4.
 * 3. Upload the entire plugin directory to your `/wp-content/plugins/` directory.
 * 4. Activate the plugin through the 'Plugins' menu in WordPress Administration.
 *
 * Ninja Shop is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Ninja Shop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
*/

if ( version_compare( PHP_VERSION, '5.6', '<' ) ) {

	/**
   * Display an error notice if the PHP version is lower than 5.6.
   *
   * @return void
   */
  function ninja_shop_below_php_version_notice() {
    if ( ! current_user_can( 'activate_plugins' ) ) return;
    echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by Ninja Shop. Please contact your host and request that your version be upgraded to 5.6 or later.', 'ninja-shop' ) . '</p></div>';
  }

  add_action( 'admin_notices', 'ninja_shop_below_php_version_notice' );

	// Return early and do not load the codebase.
	return;
}

/**
 * Determines if ExchangeWP, iThemes Exchange, or related plugins are active.
 *
 *
 *
 * @return bool If any Exchange related plugins are active.
 */
function ninja_shop_is_exchange_active() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	$plugins = get_plugins();
	foreach( $plugins as $plugin_file_path => $plugin_data ){
		if( false === strpos( $plugin_file_path, 'exchange' ) ) continue;
		if( is_plugin_active( $plugin_file_path ) ) return true;
	}

	return false;
}

if( ninja_shop_is_exchange_active() ){

	/**
	 * Display an error notice if the Exchange plugin is active.
	 *
	 * @return void
	 */
	function ninja_shop_exchange_active_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) return;
		echo '<div class="error"><p>' . __( 'The Exchange plugin and its add-ons are not compatible with Ninja Shop. Please de-active Exchange to continue.', 'ninja-shop' ) . '</p></div>';
	}

	add_action( 'admin_notices', 'ninja_shop_exchange_active_notice' );

	// Return early and do not load the codebase.
	return;
}

/**
 * Sets up options to perform after activation
 *
 *
 *
 * @return void
 */
function it_exchange_activation_hook() {
	add_option( '_it-exchange-register-activation-hook', true );
}

register_activation_hook( __FILE__, 'it_exchange_activation_hook' );

// Set it off!
require_once dirname( __FILE__ ) . '/ninja-shop.php';
