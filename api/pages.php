<?php
/**
 * This file contains API methods used with Exchange frontend pages and product pages
 * @package IT_Exchange
 * 
*/

/**
 * Returns a list of all registered IT Exchange pages (including the products slug)
 *
 *
 *
 * @param boolean $break_cache pages come from it_storage which caches options. Set this to true to not retreived cached pages
 * @param array $options
 *
 * @return array
*/
function it_exchange_get_pages( $break_cache=false, $options=array() ) {

	if ( empty( $GLOBALS['it_exchange']['registered_pages'] ) || $break_cache ) {
		// Grab registered pages
		$registered = it_exchange_get_registered_pages( $options );
		$merged     = array();

		// Grab existing DB data if its present
		if ( ! $pages = it_exchange_get_option( 'settings_pages', $break_cache ) )
			$pages = array();

		// Merge DB data with registered defaults
		foreach( $registered as $page => $default_params ) {
			$db_params = array();
			$db_params['slug'] = empty( $pages[$page . '-slug'] ) ? 0 : $pages[$page . '-slug'];
			$db_params['tip']  = empty( $pages[$page . '-tip'] )  ? 0 : $pages[$page . '-tip'];
			$db_params['name'] = empty( $pages[$page . '-name'] ) ? 0 : $pages[$page . '-name'];
			$db_params['type'] = empty( $pages[$page . '-type'] ) ? 0 : $pages[$page . '-type'];
			$db_params['wpid'] = empty( $pages[$page . '-wpid'] ) ? 0 : $pages[$page . '-wpid'];
			$merged[$page] = ITUtility::merge_defaults( $db_params, $default_params );
		}

		if ( !empty( $options ) )
			return apply_filters( 'ninja_shop_get_pages', $merged, $break_cache );
		else
			$GLOBALS['it_exchange']['registered_pages'] = $merged;
	}

	return apply_filters( 'ninja_shop_get_pages', $GLOBALS['it_exchange']['registered_pages'], $break_cache );
}

/**
 * Get name for page
 *
 *
 *
 * @param string $page page var
 * @param bool $break_cache
 *
 * @return string url
*/
function it_exchange_get_page_name( $page, $break_cache=false ) {
	$pages     = it_exchange_get_pages( $break_cache );
	$type      = it_exchange_get_page_type( $page );
	$page_name = false;

	// Return the exchagne page settings if type is exchange or if we're on the page settings tab.
	if ( 'exchange' == $type || ( is_admin() && ! empty( $_GET['page'] ) && $_GET['page'] == 'it-exchange-settings' && ! empty( $_GET['tab'] ) && 'pages' == $_GET['tab'] ) ) {
		$page_name = empty( $pages[$page]['name'] ) ? false : $pages[$page]['name'];
	} else if ( 'wordpress' == $type ) {
		$wpid = it_exchange_get_page_wpid( $page );
		$page_name = get_the_title( $wpid );
	}
	return apply_filters( 'ninja_shop_get_page_name', $page_name, $page, $break_cache );
}

/**
 * Get editable slug for page
 *
 *
 *
 * @param string $page page var
 * @param bool $break_cache
 *
 * @return string
*/
function it_exchange_get_page_slug( $page, $break_cache=false ) {
	$pages     = it_exchange_get_pages( $break_cache );
	$type      = it_exchange_get_page_type( $page );
	$page_slug = false;
	// Return the exchagne page settings if type is exchange or if we're on the page settings tab.
	if ( 'exchange' == $type || ( is_admin() && ! empty( $_GET['page'] ) && $_GET['page'] == 'it-exchange-settings' && ! empty( $_GET['tab'] ) && 'pages' == $_GET['tab'] && empty( $GLOBALS['it_exchange']['updating_nav'] ) ) ) {
		$page_slug = empty( $pages[$page]['slug'] ) ? false : $pages[$page]['slug'];
	} else if ( 'wordpress' == $type ) {
		$wpid = it_exchange_get_page_wpid( $page );
		if ( $wp_page = get_page( $wpid ) )
			$page_slug = get_page_uri( $wpid );
	}
	return apply_filters( 'ninja_shop_get_page_slug', $page_slug, $page, $break_cache );
}

/**
 * Get editable type for page
 *
 *
 *
 * @param string $page page var
 * @param bool $break_cache
 *
 * @return string
*/
function it_exchange_get_page_type( $page, $break_cache=false ) {
	$pages     = it_exchange_get_pages( $break_cache );
	$page_type = empty( $pages[$page]['type'] ) ? false : $pages[$page]['type'];
	return apply_filters( 'ninja_shop_get_page_type', $page_type, $page, $break_cache );
}

/**
 * Get editable WordPress ID (wpid) for page (only used if type is 'wordpress')
 *
 *
 *
 * @param string $page page var
 * @param bool $break_cache
 *
 * @return string
*/
function it_exchange_get_page_wpid( $page, $break_cache=false ) {
	$pages     = it_exchange_get_pages( $break_cache );
	$page_wpid = empty( $pages[$page]['wpid'] ) ? '0' : $pages[$page]['wpid'];
	return apply_filters( 'ninja_shop_get_page_wpid', $page_wpid, $page, $break_cache );
}

/**
 * Get permalink for ghost page
 *
 *
 *
 * @param string $page page setting
 * @param bool $clear_settings_cache
 *
 * @return string url
*/
function it_exchange_get_page_url( $page, $clear_settings_cache=false ) {
    $pages    = it_exchange_get_pages( $clear_settings_cache );
	$type     = it_exchange_get_page_type( $page );
	$page_url = false;

	// Give addons ability to skip this logic
	$filtered_url = apply_filters( 'ninja_shop_get_page_url', false, $page, $clear_settings_cache );
	if ( $filtered_url )
		return $filtered_url;

	// If page is disabled, attempt to redirect to store. If store is disabled, redirect to site home.
	if ( $type == 'disabled' && $page != 'store' ) {
		$page = 'store';
		$type = it_exchange_get_page_type( 'store' );
	} else if ( 'disabled' == $type && 'store' == $page ) {
		return get_home_url();
	}

	// Return the exchange page settings if type is exchange or if we're on the page settings tab.
	if ( 'exchange' == $type || ( is_admin() && ! empty( $_GET['page'] ) && $_GET['page'] == 'it-exchange-settings' && ! empty( $_GET['tab'] ) && 'pages' == $_GET['tab'] && empty( $GLOBALS['it_exchange']['updating_nav'] ) ) ) {
		if ( empty( $pages[$page]['url'] ) || ! is_callable( $pages[$page]['url'] ) )
			return false;

		if ( ! $page_url = call_user_func( $pages[$page]['url'], $page ) )
			return false;
	} else if ( 'wordpress' == $type ) {
		if ( $wpid = it_exchange_get_page_wpid( $page ) )
			return get_permalink( $wpid );
	}

    return $page_url;
}

/**
 * Is the page using a ghost page?
 *
 *
 *
 * @param string $page page setting
 * @param bool $break_cache
 *
 * @return boolean
*/
function it_exchange_is_page_ghost_page( $page, $break_cache=false ) {
	$pages    = it_exchange_get_pages( $break_cache );
	$is_ghost = ( 'exchange' == it_exchange_get_page_type( $page, $break_cache ) );
	return apply_filters( 'ninja_shop_is_page_ghost_page', $is_ghost, $page, $break_cache );
}

/**
 * Tests to see if current page is a specific exchange page or if its an exchange page at all.
 *
 * Pass in a page as a string to test for a specific string. Leaving it blank will return the current exchange page or false
 *
 *
 *
 * @param string|bool $page optional. the exchange page were checking for
 *
 * @return string|boolean Returns name of current page if $page is false. Otherwise bool whether current page is passed.
*/
function it_exchange_is_page( $page=false ) {
	global $wpdb;

	// If no page was passed, return the name of the exchange page or false
	if ( empty( $page ) ) {
		$is_exchange_page = get_query_var( 'it_exchange_view' );
		$is_exchange_page = apply_filters( 'ninja_shop_is_this_an_exchange_page', $is_exchange_page );
		return $is_exchange_page;
	}

	// Give addons ability to skip this logic
	$filtered_is = apply_filters( 'ninja_shop_is_page', null, $page );
	if ( ! is_null( $filtered_is ) ) {
		return $filtered_is;
	}

	// Page Data
	$type = it_exchange_get_page_type( $page );
	$slug = it_exchange_get_page_slug( $page );

	// If type is disabled, return false
	if ( 'disabled' == $type ) {
		return false;
	}

	// If type is wordpress, pass it on to the wordpress function
	if ( 'wordpress' == $type ) {
		$wpid = it_exchange_get_page_wpid( $page );
		return is_page( $wpid );
	}

	if ( ! empty( $_GET['it-exchange-sw-ajax'] ) && ! empty( $_GET['sw-product'] ) && ( empty( $page ) || $page == 'product' ) ) {
		// Are we doing AJAX, if so, grab product ID from it.
		return (boolean) it_exchange_get_product( $_GET['sw-product'] );
	} else if ( ! $query_var = get_query_var( $slug ) ) {
		// If we made it here, page is exchange type. Get query var
		return false;
	}

	// When asking if this is the account page, we need to remember that the account query_var is always set when viewing other
	// account based pages. if another exchange pages's query_var is also set, we will return false for account.
	if ( 'account' == $page && get_query_var( 'account' ) ) {

		// Grab all exchange pages
		$account_based_pages = it_exchange_get_pages();

		// Loop through exchange pages
		foreach ( $account_based_pages as $account_based_page => $values ) {
			// Get page slug
			$account_based_slug = it_exchange_get_page_slug( $account_based_page );
			// If this page slug is set and it isn't 'account', return false for acocunt
			if ( get_query_var( $account_based_slug ) && $account_based_slug != 'account' ) {
				return false;
			}
		}
		return true;
	}

	// Return true if set and not product
	if ( ! empty( $query_var ) && 'product' != $page  ) {
		return true;
	}

	// Try to get the post from the slug
	$name = get_query_var( 'name' );
	$sql = $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "it_exchange_prod" AND post_status = "publish" AND post_name = "%s"', $name );
	if ( $id = $wpdb->get_var( $sql ) ) {
		return true;
	}

	return false;
}

/**
 * Registers a page with IT Exchange.
 *
 * Registering a page with Exchange does the following:
 *  - Creates a Ghost page for your page
 *  - Allows WP Admin to rename the slug and the display name of the page
 *  - Allows WP Admin to turn off its associated Ghost page and replace it with a WP page / shortcode
 *  - Allows it to be added to our nav list in Appearance -> menus
 *  - Allows template files to be added to themefolder/exchange/page-slug.php
 *  - Allows for content-page-slug.php template parts to be added to the list of possible template_names
 *  - Allows 3rd party add-ons to tell Exchange where to find the template parts
 *
 * Options:
 *  - slug          required. eg: store
 *  - name          required. eg: __( 'Store', 'it-l10n-ithemes-exchange' )
 *  - rewrite-rules required. an array. 1st element is priority within all exchange page rewrites. 2nd element is callback that will provide the rewrite array.
 *  - url           required. callback that will provide the url for the page. Make sure to check for permalinks
 *  - settings-name optional. The title given to the setting on Settings -> Pages
 *  - type          optional. the default value of the select box.
 *  - menu          optional. include this in the Exchange menu options under Appearances -> Menus?
 *  - optional      optional. Is the page requried? If not optional, Disable is removed from dropdown for type on Settings page
 *
 * Rewrites and URL options:
 *  - For working examples see it_exchange_register_core_pages() in ithemes-exchange/lib/functions/function.php
 *
 *
 *
 * @param string $page unique name it-exchange uses to refer to this page
 * @param array  $options page options
 *
 * @return boolean
*/
function it_exchange_register_page( $page, $options ) {
	$pages = empty( $GLOBALS['it_exchange']['registered_pages'] ) ? array() : (array) $GLOBALS['it_exchange']['registered_pages'];

	// Page needs to be sanatized with underscores
	$page = str_replace( '-', '_', sanitize_title( $page ) );

	// Validate we have the data we need
	if ( empty( $options['slug'] ) || empty( $options['name'] ) || empty( $options['url'] ) )
		return false;

	// Defaults
	$defaults = array(
		'settings-name' => ucwords( $options['name'] ),
		'type'          => 'exchange',
		'tip'			=> empty( $options['tip'] ) ? '' : $options['tip'],
		'wpid'          => 0,
		'menu'          => true,
		'optional'      => true,
	);

	// Merge with defaults
	$options = ITUtility::merge_defaults( $options, $defaults );

	$pages[sanitize_title( $page )] = $options;
	$GLOBALS['it_exchange']['registered_pages'] = $pages;
	do_action( 'ninja_shop_register_page', $page, $options );
	return true;
}

/**
 * Returns a list of registerd pages
 *
 * This returns pages that are registered, with their defaults.
 * It DOES NOT RETURN THE ADMIN'S SETTINGS for those pages
 * For the admin's settings, use it_exchange_get_pages()
 *
 *
 *
 * @param array $options
 *
 * @return array
*/
function it_exchange_get_registered_pages( $options=array() ) {
	$pages = empty( $GLOBALS['it_exchange']['registered_pages'] ) ? array() : (array) $GLOBALS['it_exchange']['registered_pages'];

	if ( ! empty( $options['type'] ) ) {
		foreach( $pages as $page => $page_options ) {
			if ( $options['type'] != it_exchange_get_page_type( $page ) )
				unset( $pages[$page] );
		}
	}

	return $pages;
}

/**
 * Retrieve the account based pages.
 *
 *
 *
 * @return array
 */
function it_exchange_get_account_based_pages() {

	$account_based_pages = array( 'account', 'profile', 'downloads', 'purchases' );

	/**
	 * Filter the account based pages.
	 *
	 *
	 *
	 * @param array $account_based_pages
	 */
	return apply_filters( 'ninja_shop_account_based_pages', $account_based_pages );
}

/**
 * Returns an array of WP page IDs to page names
 *
 *
 *
 * @param array $options
 *
 * @return array
*/
function it_exchange_get_wp_pages( $options=array() ) {
	$defaults = array(
		'post_type'      => 'page',
		'posts_per_page' => -1,
		'order'          => 'ASC',
		'orderby'        => 'title',
	);
	$options = ITUtility::merge_defaults( $options, $defaults );

	$pages = get_posts( $options );

	$returnval = array();

	foreach( $pages as $page ) {
		$returnval[$page->ID] = get_the_title( $page->ID );
	}
	return apply_filters( 'ninja_shop_get_wp_pages', $returnval, $options );
}

/**
 * Is pages in compatibility mode.
 *
 *
 *
 * @param bool $break_cache
 *
 * @return bool
 */
function it_exchange_is_pages_compat_mode( $break_cache = false ) {

	$settings = it_exchange_get_option( 'settings_pages', $break_cache );

	return ! empty( $settings['compat-mode'] );
}
