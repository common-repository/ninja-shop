<?php
/**
 * This file contains the class in charge of rewrites and template fetching
 *
 * 
 * @package IT_Exchange
*/

/**
 * Pages Class. Registers rewrite rules and associated logic
 *
 *
*/
class IT_Exchange_Pages {

	/**
	 * @var $_account the WP username for the current user
	 *
	*/
	public $_account = false;

	/**
	 * @var string $_current_view the current Exchange frontend view
	 *
	*/
	public $_current_view = false;

	/**
	 * @var boolean $_pretty_permalinks are pretty permalinks set in WP Settings?
	 *
	*/
	public $_pretty_permalinks = false;

	/** @var bool */
	public $_in_sidebar = false;

	/** @var bool */
	public $request_email_for_confirmation = false;

	/**
	 * Constructor
	 *
	 *
	*/
	function __construct() {
		add_action( 'init', array( $this, 'set_slugs_and_names' ) );
		add_action( 'init', array( $this, 'set_pretty_permalinks_boolean' ) );
		add_filter( 'rewrite_rules_array', array( $this, 'register_rewrite_rules' ) );

		if ( is_admin() ) {
			add_action( 'save_post', array( $this, 'flush_rewrites_when_wp_confirmation_page_is_updated' ) );
		} else {
			add_action( 'template_redirect', array( $this, 'set_environment' ), 1 );
			add_action( 'template_redirect', array( $this, 'set_account' ), 2 );
			add_action( 'template_redirect', array( $this, 'registration_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'login_out_page_redirect' ), 9 );
			add_action( 'template_redirect', array( $this, 'protect_pages' ), 11 );
			add_action( 'template_redirect', array( $this, 'prevent_empty_checkouts' ), 11 );
			add_action( 'template_redirect', array( $this, 'process_transaction' ), 12 );
			add_action( 'template_redirect', array( $this, 'set_wp_query_vars' ) );

			add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
			add_filter( 'template_include', array( $this, 'fetch_template' ) );
			add_filter( 'template_include', array( $this, 'load_casper' ), 11 );

			add_filter( 'ninja_shop_get_page_url', array( $this, 'transaction_compat_mode' ), 10, 2 );
			add_filter( 'ninja_shop_get_transaction_confirmation_url', array( $this, 'confirmation_compat_mode' ), 10, 2 );

			add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ), 10, 2 );

			add_action( 'dynamic_sidebar_before', array( $this, 'mark_in_sidebar' ) );
			add_action( 'dynamic_sidebar_after', array( $this, 'mark_out_of_sidebar' ) );
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Pages() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Loads the slug properties from settings
	 *
	 *
	 *
	 * @return void
	*/
	function set_slugs_and_names() {
		// registered pages
		$registered_pages = it_exchange_get_pages( false );
		foreach( (array) $registered_pages as $page => $data ) {
			$slug = '_' . $page . '_slug';
			$name = '_' . $page . '_name';
			$this->$slug = it_exchange_get_page_slug( $page );
			$this->$name = it_exchange_get_page_name( $page );
		}
	}

	/**
	 * Sets the pretty permalinks boolean
	 *
	 *
	 *
	 * @return void
	*/
	function set_pretty_permalinks_boolean() {
		$permalinks = get_option( 'permalink_structure' );
		$this->_pretty_permalinks = ! empty( $permalinks );
	}

	/**
	 * Sets the environment based properties
	 *
	 *
	 *
	 * @return void
	*/
	function set_environment() {
		$pages      = it_exchange_get_pages( false );
		foreach( (array) $pages as $page => $data ) {
			if ( 'product' == $page || 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			$property = '_is_' . $page;
			$this->$property = it_exchange_is_page( $page );
		}

		$post_type = get_query_var( 'post_type' );
		if ( (boolean) get_query_var( $this->_product_slug )
			|| ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) )
			$this->_is_product = true;
		else
			$this->_is_product = false;

		// Set current view property
		krsort( $pages );
		foreach( $pages as $page => $data ) {
			if ( 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			$property = '_is_' . $page;
			if ( $this->$property ) {
				$this->_current_view = $page;
				break;
			}
		}

		// Add hook for things that need to be done when on an exchange page
		if ( $this->_current_view )
			do_action( 'ninja_shop_template_redirect', $this->_current_view );
	}

	/**
	 * Sets the account property based on current query_var or current user
	 *
	 *
	 *
	 * @return void
	*/
	function set_account() {
		// Return if not viewing an account based page: account, profile, downloads, purchases, login
		$account_based_pages = it_exchange_get_account_based_pages();
		if ( ! in_array( $this->_current_view, $account_based_pages ) )
			return;

		$account = get_query_var( $this->_account_slug );

		if ( empty( $account ) || 1 == $account ) {

			$customer_id = get_current_user_id();

		} else if ( $account == (int) $account ) {

			$customer_id = $account;

		} else {

			if ( $customer = get_user_by( 'login', $account ) ) {
				$customer_id = $customer->ID;
			} else {
				$customer_id = false;
			}

		}

		$this->_account = $customer_id;
		set_query_var( 'account', $customer_id );

	}

	/**
	 * Adds some custom query vars to WP_Query
	 *
	 *
	 *
	 * @return void
	*/
	function set_wp_query_vars() {
		set_query_var( 'it_exchange_view', $this->_current_view );
	}

	/**
	 * Redirects users away from login page if they're already logged in
	 * or Redirects to /store/ if they log out.
	 *
	 *
	 *
	 * @return void
	*/
	function login_out_page_redirect() {
		if ( is_user_logged_in() && 'login' == $this->_current_view ) {
			$url = it_exchange_get_page_url( 'account' );
			it_exchange_redirect( $url, 'login-to-account-when-user-logged-in' );
			die();
		} else if ( is_user_logged_in() && 'logout' == $this->_current_view ) {
			/**
			 * This redirect will not use it_exchange_redirect
			 * If you want to filter the location off site, you will have to use the allowed_redirect_hosts filter also:
			 * http://codex.wordpress.org/Function_Reference/wp_logout_url#Logout_and_Redirect_to_Another_Site
			*/
			$default = 'disabled' == it_exchange_get_page_type( 'login' ) ? '' : it_exchange_get_page_url( 'login', false, true );
			$url = apply_filters( 'ninja_shop_redirect_on_logout', $default );
			$url = str_replace( '&amp;', '&', wp_logout_url( $url ) );
			wp_redirect( $url ); // See above block comment
			die();
		} else if ( ! is_user_logged_in() && 'logout' == $this->_current_view ) {
			$url = it_exchange_get_page_url( 'login' );
			it_exchange_redirect( $url, 'logout-to-login-when-user-logged-out' );
			die();
		}
	}

	/**
	 * Redirects users away from registration page if they're already logged in
	 * except for Administrators, because they might want to see the registration page.
	 *
	 *
	 *
	 * @return void
	*/
	function registration_redirect() {
		if ( is_user_logged_in() && 'registration' == $this->_current_view
			&& ! current_user_can( 'administrator' ) ) {
			$url = it_exchange_get_page_url( 'account' );
			it_exchange_redirect( $url, 'registration-to-account-when-logged-in' );
			die();
		}
	}

	/**
	 * Redirects users away from pages they don't have permission to view
	 *
	 *
	 *
	 * @return void
	*/
	function protect_pages() {

		// Don't give access to single product views if product is disabled
		if ( 'product' === $this->_current_view ) {
			$enabled_product_types = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
			$product_type = it_exchange_get_product_type();
			if ( ! in_array( $product_type, array_keys( $enabled_product_types ) ) ) {
				$url = it_exchange_get_page_url( 'store' );
				it_exchange_redirect( $url, 'disabled-product-to-store' );
				die();
			}
		}

		// If user is an admin, abandon this. They can see it all
		if ( current_user_can( 'administrator' ) ) {
			return;
		}

		if ( $this->_current_view === 'confirmation' ) {
			$this->protect_confirmation();

			return;
		}

		// Set pages that we want to protect in one way or another
		$pages_to_protect = array(
			'account', 'profile', 'downloads', 'purchases',
		);
		$pages_to_protect = apply_filters( 'ninja_shop_pages_to_protect', $pages_to_protect );

		// Abandon if not a proteced page
		if ( ! in_array( $this->_current_view, $pages_to_protect ) ) {
			return;
		}

		// If user isn't logged in, redirect
		if ( ! is_user_logged_in() ) {
			// Redirect to the page we were trying to access after login/registration if this isn't a login/registration page
			if ( $this->_current_view != 'login' && $this->_current_view != 'registration' ) {
				it_exchange_add_session_data( 'login_redirect', it_exchange_get_page_url( $this->_current_view ) );
			}

			// If looking for registration page or purchases page, send to login, else send to register
			$redirect = ( in_array( $this->_current_view, array( 'account', 'profile', 'downloads', 'purchases' ) ) ) ? it_exchange_get_page_url( 'login' ) : it_exchange_get_page_url( 'registration' );
			$redirect = apply_filters( 'ninja_shop_pages_to_protect_redirect_if_not_logged_in', $redirect, $this->_current_view );

			$redirect_options = array( 'current-page' => $this->_current_view );
			it_exchange_redirect( $redirect, 'protected-pages-to-registration-when-not-logged-in', $redirect_options );
			die();
		} elseif ( 'checkout' === $this->_current_view ) {
			return; //We just want to make sure users are logged in to see the checkout page
		}

		// Get current user
		$user_id = get_current_user_id();

		// If trying to view account and not an admin, and not the owner, redirect
		if ( in_array( $this->_current_view, $pages_to_protect )
				&& $this->_account != $user_id && ! current_user_can( 'administrator' ) ) {
			$redirect = apply_filters( 'ninja_shop_pages_to_protect_redirect_if_non_admin_requests_account' , it_exchange_get_page_url( 'store' ) );

			it_exchange_redirect( $redirect, 'no-permission-account-to-store' );
			die();
		}

		do_action( 'ninja_shop_protect_pages' );
	}

	/**
	 * Protect the confirmation page.
	 *
	 *
	 */
	protected function protect_confirmation() {

		if ( isset( $_POST['it-exchange-guest-email'] ) ) {
			$guest_email = trim( sanitize_email( $_POST['it-exchange-guest-email'] ) );
		} elseif ( isset( $_COOKIE['it-exchange-guest-email'] ) ) {
			$guest_email = $_COOKIE['it-exchange-guest-email'];
		} else {
			$guest_email = '';
		}

		if ( $guest_email && is_email( $guest_email ) ) {
			@setcookie( 'it-exchange-guest-email', $guest_email, time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, '', true );
		}

		$customer       = it_exchange_get_current_customer();
		$transaction    = null;
		$transaction_id = false;
		$page_slug      = it_exchange_get_page_slug( 'confirmation', true );

		if ( $transaction_hash = get_query_var( $page_slug ) ) {
			$transaction_id = it_exchange_get_transaction_id_from_hash( $transaction_hash );
			$transaction    = it_exchange_get_transaction( $transaction_id );

			if ( $transaction->is_guest_purchase() && $guest_email ) {
				$customer = it_exchange_get_customer( $guest_email );
			}
		}

		if ( isset( $_REQUEST['confirmation_auth'] ) ) {
			if ( it_exchange_verify_transaction_confirmation_auth( $transaction, $_REQUEST['confirmation_auth'] ) ) {
				return;
			}
		}

		$has = it_exchange_customer_has_transaction( $transaction_id, $customer );

		if ( ! $has && $transaction && $transaction->is_guest_purchase() ) {
			$this->request_email_for_confirmation = true;

			return;
		}

		if ( ! $has ) {
			$redirect = it_exchange_get_page_url( 'purchases' );
			$redirect = apply_filters( 'ninja_shop_pages_to_protect_redirect_if_non_admin_requests_confirmation', $redirect );

			$redirect_options = array(
				'transaction_id' => $transaction_id,
				'user_id'        => $customer ? $customer->ID : false
			);
			it_exchange_redirect( $redirect, 'incorrect-confirmation-to-purchases', $redirect_options );
			die();
		}
	}

	/**
	 * Redirect away from checkout if cart is empty
	 *
	 *
	 *
	 * @return void
	*/
	function prevent_empty_checkouts() {
		if ( 'checkout' != $this->_current_view )
			return;

		$cart = it_exchange_get_requested_cart_and_check_auth() ?: it_exchange_get_current_cart();

		if ( ! $cart->get_items()->count() ) {
			it_exchange_redirect( it_exchange_get_page_url( 'cart' ), 'checkout-empty-send-to-cart' );
			die();
		}
	}

	/**
	 * Redirects users to confirmation page if the transaction was successful
	 * or to the checkout page if there was a failure.
	 *
	 *
	 *
	 * @return void
	*/
	public function process_transaction() {

		if ( $this->_current_view !== 'transaction' ) {
			return;
		}

		try {
			$cart = it_exchange_get_requested_cart_and_check_auth();
		} catch ( UnexpectedValueException $e ) {
			it_exchange_add_message( 'error', $e->getMessage() );

			return;
		}

		if ( $cart || is_user_logged_in() ) {

			$cart = $cart ?: it_exchange_get_current_cart();

			$transaction_id = apply_filters( 'ninja_shop_process_transaction', false, $cart );

			// If we made a transaction
			if ( $transaction_id ) {

				if ( $cart->is_current() ) {
					// Clear the cart
					it_exchange_empty_shopping_cart();
				}

				if ( isset( $_REQUEST['redirect_to'] ) ) {
					wp_safe_redirect( add_query_arg( 'transaction_id', $transaction_id, rawurldecode( $_REQUEST['redirect_to'] ) ) );
					die();
				}

				// Grab the transaction confirmation URL. fall back to store if confirmation url fails
				$confirmation_url = it_exchange_get_transaction_confirmation_url( $transaction_id, $cart && ! is_user_logged_in() );

				if ( empty( $confirmation_url ) ) {
					$confirmation_url = it_exchange_get_page_url( 'store' );
				}

				// Redirect
				wp_redirect( $confirmation_url ); // no filter or it_exchange_redirect on this one
				die();
			}
		}

		// the transaction failed, or no products in the cart

		if ( it_exchange_is_multi_item_cart_allowed() ) {
			wp_redirect( it_exchange_get_page_url( 'checkout' ) ); // no filter or it_exchange_redirect on this one
		} else {

			foreach ( it_exchange_get_current_cart()->get_items( 'product' ) as $product ) {
				wp_redirect( get_permalink( $product->get_product()->ID ) );
				die();
			}

			wp_redirect( it_exchange_get_page_url( 'store' ) ); // no filter or it_exchange_redirect
		}

		die();
	}

	/**
	 * Determines which template file should be used for the current frontend view.
	 *
	 * If this is an Exchange view, look for the appropriate Exchange template in the users current theme.
	 * If an Exchange template is found in the theme, use the theme's page template and swap out our the_content for our template_parts
	 *
	 *
	 *
	 * @param the default template as determined by WordPress
	 * @return string a template file
	*/
	function fetch_template( $existing ) {

		// Return existing if this isn't an Exchange frontend view
		if ( ! $this->_current_view || 'exchange' != it_exchange_get_page_type( $this->_current_view ) )
			return $existing;

		// Set pages that we want to protect in one way or another
		$profile_pages = array(
			'account', 'profile', 'downloads', 'purchases',
		);
		$profile_pages = apply_filters( 'ninja_shop_profile_pages', $profile_pages );

		if ( in_array( $this->_current_view, $profile_pages ) ) {
			if ( ! $this->_account )
				return get_404_template();
		}

		$template = it_exchange_locate_template( $this->_current_view . '.php' );

		if ( $this->_current_view === 'confirmation' && $this->request_email_for_confirmation ) {
			$template = 'content-confirmation-email-form.php';
		}

		/**
		 * 1) If we found an Ninja Shop Page Template in the theme's /exchange/ folder, return it.
		 * 2) If the found Ninja Shop Theme Template has been filtered, return the filtered one instead and add the callback the_content filter
		 * -- In the event of option 2, this is working much like the 'product' == $this_current_view clase below would act with page.php
		*/
		if ( $template ) {
			remove_filter( 'the_content', 'wpautop' );
			$filtered_template = apply_filters( 'ninja_shop_fetch_template_override_located_template', $template, $this );
			if ( $filtered_template != $template && 'product' == $this->_current_view ) {
				add_filter( 'the_content', array( $this, 'fallback_filter_for_page_template' ) );
				$template = $filtered_template;
			}

			return $template;
		}

		// If no Ninja Shop template was found by it_exchange_location_template and we've viewing a product
		// then were'e going to need to set a filter
		if ( 'product' == $this->_current_view )
			add_filter( 'the_content', array( $this, 'fallback_filter_for_page_template' ) );

		// If no Ninja Shop Template was found, use the theme's page template
		if ( $template = get_page_template() ) {
			$template = apply_filters( 'ninja_shop_fetch_template_override_default_page_template', $template, $this );
			remove_filter( 'the_content', 'wpautop' );
			return $template;
		}

		// If nothing was found here, the theme has issues. Just return whatever template WP was going to use
		return $existing;
	}

	/**
	 * This loads our ghost post data and vars into the wp_query global when needed
	 *
	 *
	 *
	 * @param string $template We are hooking into a filter for an action. Always return value unchanged
	 * @return string
	*/
	function load_casper( $template ) {
		if ( $this->_current_view ) {
			if ( 'product' != $this->_current_view && 'exchange' == it_exchange_get_page_type( $this->_current_view ) ) {
				require( dirname( __FILE__ ) . '/class.casper.php' );
				new IT_Exchange_Casper( $this->_current_view );
			}
		}
		return $template;
	}

	/**
	 * This substitutes the themes content for our content-[$this->_current_view] template part.
	 *
	 * This only gets fired off if we couldn't find an exchange specific template file for the current view.
	 * If that happens, we use the theme's page.php template and filter the_content with our template part for that view.
	 *
	 *
	 *
	 * @param string $content exising default content
	 *
	 * @return string Content generated from template part
	*/
	public function fallback_filter_for_page_template( $content ) {
		$global_post = empty( $GLOBALS['post']->ID ) ? 0 : $GLOBALS['post']->ID;

		if ( ! it_exchange_get_product( $global_post ) ) {
			return $content;
		}

		if ( $this->_in_sidebar ) {
			return $content;
		}
		
		if ( ! is_main_query() && ! is_singular( array( 'it_exchange_prod' ) ) ) {
			return $content;
		}

		ob_start();
		add_filter( 'the_content', 'wpautop' );
		it_exchange_get_template_part( 'content', $this->_current_view );
		remove_filter( 'the_content', 'wpautop' );
		return ob_get_clean();
	}

	/**
	 * Registers our custom query vars with WordPress
	 *
	 *
	 *
	 * @param array $existing existing query vars
	 * @return array modified query vars
	*/
	function register_query_vars( $existing ) {
		$pages = it_exchange_get_pages( false );
		$vars  = array();

		foreach( $pages as $page => $data ) {
			if ( 'product' == $page || 'disabled' == it_exchange_get_page_type( $page ) )
				continue;
			if ( $var = it_exchange_get_page_slug( $page ) ) {

				// Exception for confirmation page set as wordpress page type
				if ( 'confirmation' == $page && 'wordpress' == it_exchange_get_page_type( 'confirmation', true ) ) {
					$wpid = it_exchange_get_page_wpid( 'confirmation' );
					if ( $wp_page = get_page( $wpid ) ) {
						$vars[] = get_page_uri( $wpid );
					} else {
						$vars[] = $var;
					}
				} else {
					$vars[] = $var;
				}
			}
		}
		$new_vars = array_merge( $vars, $existing );
		return $new_vars;
	}

	/**
	 * Registers our custom rewrite rules based on slug settings
	 *
	 * Loop through all the pages, grabbing their rewrite rules, grouped by order
	 * Then add to existing array of rewrites
	 *
	 *
	 *
	 * @param array $exisiting existing rewrite rules
	 * @return array modified rewrite rules
	*/
	function register_rewrite_rules( $existing ) {
		$this->set_slugs_and_names();

		// We only want pages that are exchange types for rewrites
		$pages = it_exchange_get_pages( true, array( 'type' => 'exchange' ) );
		$prioritized_rewrites = array();

		// Loop through and group rewrite callbacks by priority
		foreach( $pages as $page => $data ) {
			// Grab priority of rewrites and store in prioritized_rewrites array
			if ( ! empty( $data['rewrite-rules'] ) && is_array( $data['rewrite-rules'] ) ) {
				$priority = absint( $data['rewrite-rules'][0] );
				// Make sure priority key already exists
				if ( ! isset( $prioritized_rewrites[$priority] ) || ! is_array( $prioritized_rewrites[$priority] ) ) {
					$prioritized_rewrites[$priority] = array();
				}
				// Add rules for page to prioritized array
				if ( ! empty( $data['rewrite-rules'][1] ) && is_callable( $data['rewrite-rules'][1] ) ) {
					$rules = call_user_func( $data['rewrite-rules'][1], $page );
					if ( ! empty( $rules ) && is_array( $rules ) )
						$prioritized_rewrites[$priority][] = $rules;
				}
			}
		}

		// Reverse sort prioritized by keys
		krsort( $prioritized_rewrites );

		// Loop through priority array and apply rules
		foreach( $prioritized_rewrites as $priority => $rewrites ) {
			foreach( $rewrites as $rewrite ) {
				$existing = array_merge( $rewrite, $existing );
			}
		}

		// This is an exception for the confirmation page.
		if ( 'wordpress' == it_exchange_get_page_type( 'confirmation', true ) ) {
			$wpid = it_exchange_get_page_wpid( 'confirmation' );
	        if ( $wp_page = get_page( $wpid ) ) {
	            $page_slug = get_page_uri( $wpid );
			} else {
	        	$page_slug = 'confirmation';
			}

			$rewrite = array( $page_slug . '/([^/]+)/?$' => 'index.php?pagename=' . $page_slug . '&' . $page_slug . '=$matches[1]' );
			$existing = array_merge( $rewrite, $existing );
		}
		do_action( 'ninja_shop_rewrite_rules_registered' );

		return $existing;
	}

	/**
	 * Flush rewrite rules if confirmation page is set to WP type and updated
	 *
	 * This is needed in the event that the post_parent is updated
	 *
	 *
	 *
	 * @param int $post_id the wp post id
	 * @return void
	*/
	function flush_rewrites_when_wp_confirmation_page_is_updated( $post_id ) {
		// Abort if we aren't saving a page
		if ( 'page' != get_post_type( $post_id ) )
			return;

		// Only proceed if the Exchange confirmation page is a WordPress type
		if ( 'wordpress' == it_exchange_get_page_type( 'confirmation', true ) ) {
			// Flag a rewrite flush if the current page being saved is the confirmation page
			$wpid = it_exchange_get_page_wpid( 'confirmation' );
			if ( $wpid == $post_id )
				add_option('_it-exchange-flush-rewrites', true );
		}
	}

	/**
	 * Mark when we are currently rendering a sidebar.
	 *
	 *
	 */
	public function mark_in_sidebar() {
		$this->_in_sidebar = true;
	}

	/**
	 * Mark when we are out of a sidebar.
	 *
	 *
	 */
	public function mark_out_of_sidebar() {
		$this->_in_sidebar = false;
	}

	/**
	 * Override the transaction URL when in compat-mode.
	 *
	 *
	 *
	 * @param string $url
	 * @param string $page
	 *
	 * @return string
	 */
	public function transaction_compat_mode( $url, $page ) {
		
		if ( ! it_exchange_is_pages_compat_mode() ) {
			return $url;
		}

		if ( $page === 'transaction' ) {
			$url = add_query_arg( 'transaction', 1, trailingslashit( site_url() ) );
		}

		return $url;
	}

	/**
	 * Override the confirmation URL to not use pretty permalinks when we are in compat-mode.
	 *
	 *
	 *
	 * @param string $url
	 * @param int    $transaction_id
	 *
	 * @return bool|string
	 */
	public function confirmation_compat_mode( $url, $transaction_id ) {
		
		if ( ! it_exchange_is_pages_compat_mode() ) {
			return $url;
		}

		// If we can't grab the hash, return false
		if ( ! $transaction_hash = it_exchange_get_transaction_hash( $transaction_id ) ) {

			return false;
		}

		// Get base page URL

		$confirmation_url = it_exchange_get_page_url( 'confirmation' );
		$slug             = it_exchange_get_page_slug( 'confirmation' );

		$confirmation_url = remove_query_arg( $slug, $confirmation_url );
		$confirmation_url = add_query_arg( $slug, $transaction_hash, $confirmation_url );

		return $confirmation_url;
	}

	public function redirect_canonical( $redirect, $original ) {

		if ( ! it_exchange_is_page( 'purchases' ) ) {
			return $redirect;
		}

		if ( ! it_exchange_is_pages_compat_mode() ) {
			return $redirect;
		}

		if ( ! get_query_var( 'page' ) ) {
			return $redirect;
		}

		if ( strpos( $redirect, '/page/') === false ) {
			return $redirect;
		}

		return false;
	}
}
global $IT_Exchange_Pages; // We need it inside casper
$IT_Exchange_Pages = new IT_Exchange_Pages();
