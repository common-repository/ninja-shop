<?php
/**
 * These are hooks that add-ons should use for form actions
 *
 *
 * @package IT_Exchange
 */

/**
 * Generate a unique hash, with microtime and uniqid this should always be unique
 *
 *
 *
 * @return string the hash
 */
function it_exchange_create_unique_hash() {
	$hash = str_replace( '.', '', microtime( true ) . uniqid() ); //Remove the period from microtime, cause it's ugly

	return apply_filters( 'ninja_shop_generate_unique_hash', $hash );
}

/**
 * Pass a PHP date format string to this function to return its jQuery datepicker equivalent
 *
 *
 *
 * @param string $date_format PHP Date Format
 *
 * @return string jQuery datePicker Format
 */
function it_exchange_php_date_format_to_jquery_datepicker_format( $date_format ) {

	//http://us2.php.net/manual/en/function.date.php
	//http://api.jqueryui.com/datepicker/#utility-formatDate
	$php_format = array(
		//day
		'/d/',
		//Day of the month, 2 digits with leading zeros
		'/D/',
		//A textual representation of a day, three letters
		'/j/',
		//Day of the month without leading zeros
		'/l/',
		//A full textual representation of the day of the week
		//'/N/', //ISO-8601 numeric representation of the day of the week (added in PHP 5.1.0)
		//'/S/', //English ordinal suffix for the day of the month, 2 characters
		//'/w/', //Numeric representation of the day of the week
		'/z/',
		//The day of the year (starting from 0)

		//week
		//'/W/', //ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0)

		//month
		'/F/',
		//A full textual representation of a month, such as January or March
		'/m/',
		//Numeric representation of a month, with leading zeros
		'/M/',
		//A short textual representation of a month, three letters
		'/n/',
		//numeric month no leading zeros
		//'t/', //Number of days in the given month

		//year
		//'/L/', //Whether it's a leap year
		//'/o/', //ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead. (added in PHP 5.1.0)
		'/Y/',
		//A full numeric representation of a year, 4 digits
		'/y/',
		//A two digit representation of a year
	);

	$datepicker_format = array(
		//day
		'dd', //day of month (two digit)
		'D',  //day name short
		'd',  //day of month (no leading zero)
		'DD', //day name long
		//'',   //N - Equivalent does not exist in datePicker
		//'',   //S - Equivalent does not exist in datePicker
		//'',   //w - Equivalent does not exist in datePicker
		'z' => 'o',  //The day of the year (starting from 0)

		//week
		//'',   //W - Equivalent does not exist in datePicker

		//month
		'MM', //month name long
		'mm', //month of year (two digit)
		'M',  //month name short
		'm',  //month of year (no leading zero)
		//'',   //t - Equivalent does not exist in datePicker

		//year
		//'',   //L - Equivalent does not exist in datePicker
		//'',   //o - Equivalent does not exist in datePicker
		'yy', //year (four digit)
		'y',  //month name long
	);

	return stripslashes( preg_replace( $php_format, $datepicker_format, preg_quote( $date_format ) ) );
}

/**
 * Convert a PHP date format to one usable by Moment.js
 *
 * @link  http://stackoverflow.com/a/30192680
 *
 *
 *
 * @param string $format
 *
 * @return string
 */
function it_exchange_convert_php_to_moment( $format ) {

	$replacements = array(
		'd' => 'DD',
		'D' => 'ddd',
		'j' => 'D',
		'l' => 'dddd',
		'N' => 'E',
		'S' => 'o',
		'w' => 'e',
		'z' => 'DDD',
		'W' => 'W',
		'F' => 'MMMM',
		'm' => 'MM',
		'M' => 'MMM',
		'n' => 'M',
		't' => '', // no equivalent
		'L' => '', // no equivalent
		'o' => 'YYYY',
		'Y' => 'YYYY',
		'y' => 'YY',
		'a' => 'a',
		'A' => 'A',
		'B' => '', // no equivalent
		'g' => 'h',
		'G' => 'H',
		'h' => 'hh',
		'H' => 'HH',
		'i' => 'mm',
		's' => 'ss',
		'u' => 'SSS',
		'e' => 'zz', // deprecated since version 1.6.0 of moment.js
		'I' => '', // no equivalent
		'O' => '', // no equivalent
		'P' => '', // no equivalent
		'T' => '', // no equivalent
		'Z' => '', // no equivalent
		'c' => '', // no equivalent
		'r' => '', // no equivalent
		'U' => 'X',
	);

	$momentFormat = strtr( $format, $replacements );

	return $momentFormat;
}

/**
 * Returns an integer value of the price passed
 *
 *
 *
 * @param string|int|float price to convert to database integer
 *
 * @return string|int converted price
 */
function it_exchange_convert_to_database_number( $price ) {
	$settings = it_exchange_get_option( 'settings_general' );
	$sep      = $settings['currency-decimals-separator'];

	$price = html_entity_decode( trim( $price ), ENT_COMPAT, 'UTF-8' );
	$price = preg_replace( '/[^0-9\\' . $sep . ']*/', '', $price );

	// If the price has a decimal seperator in it...
	if ( strstr( $price, $sep ) ) {
		if ( '.' !== $sep ) {
			$price = str_replace( $sep, '.', $price );
		}
		$price = number_format( (float) $price, 2 ); //make sure we have 2 decimal places!
		$price = preg_replace( '/[^0-9]*/', '', $price );

	// If there is not a decimal seperator...
	} else {
		// In PHP 7, subjecting empty strings to numerical operators emits an E_WARNING, so they'll skip this next assignment
		if ( $price != '' ) {
			// We want to multiply by 100 for future decimal operations
			$price = preg_replace( '/[^0-9]*/', '', $price ) * 100;
		}
	}
	return $price;
}

/**
 * Returns a float value of the price passed from database
 *
 *
 *
 *
 * @param string|int price from database integer
 *
 * @return float converted price
 */
function it_exchange_convert_from_database_number( $price ) {
	return number_format( (float) $price / 100, 2, '.', '' );
}

/**
 * Returns a field name used in links and forms
 *
 *
 *
 * @param string $var var being requested
 *
 * @return string var used in links / forms for different actions
 */
function it_exchange_get_field_name( $var ) {
	$field_names = it_exchange_get_field_names();
	$field_name  = empty( $field_names[ $var ] ) ? false : $field_names[ $var ];

	return apply_filters( 'ninja_shop_get_field_name', $field_name, $var );
}

/**
 * Returns an array of all field names registered with Ninja Shop
 *
 *
 *
 * @return array
 */
function it_exchange_get_field_names() {
	// required field names
	$required = array(
		'add_product_to_cart'      => 'it-exchange-add-product-to-cart',
		'buy_now'                  => 'it-exchange-buy-now',
		'remove_product_from_cart' => 'it-exchange-remove-product-from-cart',
		'line_item_quantity'       => 'it-exchange-update-line-item-quantity',
		'update_cart_action'       => 'it-exchange-update-cart-request',
		'empty_cart'               => 'it-exchange-empty-cart',
		'continue_shopping'        => 'it-exchange-continue-shopping',
		'proceed_to_checkout'      => 'it-exchange-proceed-to-checkout',
		'view_cart'                => 'it-exchange-view-cart',
		'purchase_cart'            => 'it-exchange-purchase-cart',
		'alert_message'            => 'it-exchange-messages',
		'error_message'            => 'it-exchange-errors',
		'transaction_id'           => 'it-exchange-transaction-id',
		'transaction_method'       => 'it-exchange-transaction-method',
		'sw_cart_focus'            => 'ite-sw-cart-focus',
		'sw_ajax_call'             => 'it-exchange-sw-ajax',
		'sw_ajax_action'           => 'sw-action',
		'sw_ajax_product'          => 'sw-product',
		'sw_ajax_quantity'         => 'sw-quantity',
	);

	//We don't want users to modify the core vars, but we should let them add new ones.
	return apply_filters( 'ninja_shop_get_field_names', array_merge( $required, apply_filters( 'ninja_shop_default_field_names', array() ) ) );
}

/**
 * Grabs the current URL, removes all registerd exchange query_args from it
 *
 * Exempts args in first parameter
 * Cleans additional args in second parameter
 *
 *
 *
 * @param array $exempt     optional array of query args not to clean
 * @param array $additional optional array of params to clean even if not found in register params
 *
 * @return string
 */
function it_exchange_clean_query_args( $exempt = array(), $additional = array() ) {
	// Get registered
	$registered = array_values( (array) it_exchange_get_field_names() );
	$registered = array_merge( $registered, (array) array_values( $additional ) );

	// Additional args
	$registered[] = '_wpnonce';
	$registered[] = apply_filters( 'ninja_shop_purchase_product_nonce_var', '_wpnonce' );
	$registered[] = apply_filters( 'ninja_shop_cart_action_nonce_var', '_wpnonce' );
	$registered[] = apply_filters( 'ninja_shop_remove_product_from_cart_nonce_var', '_wpnonce' );
	$registered[] = apply_filters( 'ninja_shop_checkout_action_nonce_var', '_wpnonce' );
	$registered[] = 'it-exchange-basic-coupons-remove-coupon-cart';

	$registered = array_unique( $registered );

	$url = false;
	foreach ( $registered as $key => $param ) {
		if ( ! in_array( $param, $exempt ) ) {
			$url = remove_query_arg( $param, $url );
		}
	}

	if ( ! empty( $url ) ) {
		$url = esc_url( $url );
	}

	return apply_filters( 'ninja_shop_clean_query_args', $url );
}

/**
 * Replace Log in text with Log out text in nav menus
 *
 *
 *
 * @param array  $items page setting
 * @param string $menu
 * @param array  $args
 *
 * @return string url
 */
function it_exchange_wp_get_nav_menu_items_filter( $items, $menu, $args ) {
	if ( is_user_logged_in() && 'disabled' != it_exchange_get_page_type( 'logout' ) && ! is_admin() ) {
		foreach ( $items as $item ) {

			if ( $item->type === 'it-exchange-ghost-page' && $item->object === 'login' ) {

				$item->url   = it_exchange_get_page_url( 'logout' );
				$item->title = it_exchange_get_page_name( 'logout' );

				continue;
			}

			//We really just want to compare the URL PATH, so grab that and compare later
			if ( '' == get_option( 'permalink_structure' ) ) {
				// No permalinks
				$item_url_path   = parse_url( $item->url, PHP_URL_QUERY );
				$login_url_path  = parse_url( it_exchange_get_page_url( 'login' ), PHP_URL_QUERY );
				$logout_url_path = parse_url( it_exchange_get_page_url( 'logout' ), PHP_URL_QUERY );
			} else {
				// Permalinks
				$item_url_path   = parse_url( $item->url, PHP_URL_PATH );
				$login_url_path  = parse_url( it_exchange_get_page_url( 'login' ), PHP_URL_PATH );
				$logout_url_path = parse_url( it_exchange_get_page_url( 'logout' ), PHP_URL_PATH );
			}

			if ( $item_url_path == $login_url_path || $item_url_path == $logout_url_path ) {
				$item->url   = it_exchange_get_page_url( 'logout' );
				$item->title = it_exchange_get_page_name( 'logout' );
			}
		}
	}

	return apply_filters( 'ninja_shop_wp_get_nav_menu_items_filter', $items, $menu, $args );

}

add_filter( 'wp_get_nav_menu_items', 'it_exchange_wp_get_nav_menu_items_filter', 10, 3 );

/**
 * Returns the currency symbol based on the currency key
 *
 *
 *
 * @param string $country_code country code for the currency
 *
 * @return string
 */
function it_exchange_get_currency_symbol( $country_code ) {
	$currencies = it_exchange_get_data_set( 'currencies' );;
	$symbol = empty( $currencies[ $country_code ] ) ? '$' : $currencies[ $country_code ];
	$symbol = ( is_array( $symbol ) && ! empty( $symbol['symbol'] ) ) ? $symbol['symbol'] : '$';

	return apply_filters( 'ninja_shop_get_currency_symbol', $symbol );
}

/**
 * Sets the value of a GLOBALS
 *
 *
 *
 * @param string $key   in the GLOBALS array
 * @param mixed  $value in the GLOBALS array
 *
 * @return void
 */
function it_exchange_set_global( $key, $value ) {
	$GLOBALS['it_exchange'][ $key ] = $value;
}

/**
 * Returns the value of a GLOBALS
 *
 *
 *
 * @param string $key in the GLOBALS array
 *
 * @return mixed value from the GLOBALS
 */
function it_exchange_get_global( $key ) {
	return isset( $GLOBALS['it_exchange'][ $key ] ) ? $GLOBALS['it_exchange'][ $key ] : null;
}

/**
 * Returns boolean if passed paramater is the current checkout mode
 *
 *
 *
 * @param  string $mode    the checkout mode we're testing
 * @param string  $context 'content' or 'sw'
 *
 * @return boolean
 */
function it_exchange_is_checkout_mode( $mode, $context = 'content' ) {
	return apply_filters( 'ninja_shop_is_' . $context . '_' . $mode . '_checkout_mode', false );
}

/**
 * Formats the Billing Address for display
 *
 *
 *
 * @param array|bool $billing_address
 *
 * @return string HTML
 */
function it_exchange_get_formatted_billing_address( $billing_address = false ) {

	$billing = empty( $billing_address ) ? it_exchange_get_cart_billing_address() : $billing_address;

	$formatted = it_exchange_format_address( $billing );

	return apply_filters( 'ninja_shop_get_formatted_billing_address', $formatted );
}

/**
 * Format any address.
 *
 *
 *
 * @param array $address Raw parts of the address.
 * @param array $args    {
 *                       Additional args used to specify how an address is formatted.
 *
 * @type string $open    -block  String rendered before the contents of the address are outputted. HTML is allowed.
 * @type string $close   -block String rendered after the contents of the address are outputted. HTML is allowed.
 * @type string $open    -line   String used to open up a new line. Defaults to an empty string. HTML is allowed.
 * @type string $close   -line  String used to close a line. Defaults to an empty string. HTML is allowed.
 * }
 *
 * @param array $format  . Optionally, override the format.
 *
 * @return string
 */
function it_exchange_format_address( $address, $args = array(), $format = null ) {

	$default_format = array(
		'{first-name} {last-name}',
		'{company-name}',
		'{address1}',
		'{address2}',
		'{city} {state} {zip}',
		'{country}'
	);

	$format = $format === null ? $default_format : (array) $format;

	/**
	 * Filter the format used to format an address.
	 *
	 *
	 *
	 * @param array $format  Format for address, see default_format for example.
	 * @param array $address Raw address to be formatted.
	 * @param array $args    Additional args used to format the address.
	 */
	$format = apply_filters( 'ninja_shop_format_address_format', $format, $address, $args );

	$defaults = array(
		'open-block'  => '',
		'close-block' => '',
		'open-line'   => '',
		'close-line'  => '<br>'
	);
	$args     = ITUtility::merge_defaults( $args, $defaults );

	/**
	 * Filter the formatting args.
	 *
	 *
	 *
	 * @param array $args    Args used for controlling address format.
	 * @param array $address Raw address to be formatted.
	 * @param array $format  Format used.
	 */
	$args = apply_filters( 'ninja_shop_format_address_args', $args, $address, $format );

	$address_parts = array();

	foreach ( $address as $part_name => $part_val ) {
		$address_parts[ $part_name ] = '{' . $part_name . '}';
	}

	$replaced = array();

	foreach ( $format as $line ) {

		$replaced_line = $line;

		foreach ( $address_parts as $part_name => $replace_tag ) {
			$value         = isset( $address[ $part_name ] ) ? esc_html( $address[ $part_name ] ) : '';
			$replaced_line = str_replace( $replace_tag, $value, $replaced_line );
		}

		$replaced[] = trim( $replaced_line );
	}

	// get rid of any remaining, un-replaced tags
	$replaced = preg_replace( '/{.*?}/', '', $replaced );

	$open  = $args['open-line'];
	$close = $args['close-line'];

	$output = $args['open-block'];

	foreach ( $replaced as $replaced_line ) {
		if ( trim( $replaced_line ) !== '' ) {
			$output .= $open . $replaced_line . $close;
		}
	}

	$output .= $args['close-block'];

	/**
	 * Filter the final output of the formatted address.
	 *
	 *
	 *
	 * @param string $output  Final formatted address.
	 * @param array  $address Raw address before formatting.
	 * @param array  $args    Additional args used to format the address.
	 * @param array  $format  Format used to format this address.
	 */
	$output = apply_filters( 'ninja_shop_format_address_output', $output, $address, $args, $format );

	return $output;
}

/**
 * Inits the IT_Exchange_Admin_Settings_Form class
 *
 *
 *
 * @param array $options options for the class constructor
 *
 * @return void
 */
function it_exchange_print_admin_settings_form( $options ) {
	if ( ! is_admin() ) {
		return;
	}

	if ( $settings_form = new IT_Exchange_Admin_Settings_Form( $options ) ) {
		$settings_form->print_form();
	}
}

/**
 * Wrapper for wp_redirect to allow standardized filtering
 *
 *
 *
 * @param  string $url     the URL to redirect the user to
 * @param  string $context the context of the redirect to help with filtering
 * @param  array  $options additional options available for filters
 * @param  int    $status  HTTP code passed to wp_redirect's 2nd param
 *
 * @return void
 */
function it_exchange_redirect( $url, $context = '', $options = array(), $status = 302 ) {
	$options['context'] = $context;
	$url                = apply_filters( 'ninja_shop_redirect_for-' . $context, $url, $options, $status );
	wp_redirect( $url, $status );
}

/**
 * Get's current IP address for a customer
 *
 * Copied from Better WP Security (HT: Chris Wiegman)
 *
 *
 *
 * @return string Current IP address for customer
 */
function it_exchange_get_ip() {
	//Just get the headers if we can or else use the SERVER global
	if ( function_exists( 'apache_request_headers' ) ) {

		$headers = apache_request_headers();

	} else {

		$headers = $_SERVER;

	}

	//Get the forwarded IP if it exists
	if ( array_key_exists( 'X-Forwarded-For', $headers ) &&
	     (
		     filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ||
		     filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) )
	) {

		$the_ip = $headers['X-Forwarded-For'];

	} elseif (
		array_key_exists( 'HTTP_X_FORWARDED_FOR', $headers ) &&
		(
			filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ||
			filter_var( $headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 )
		)
	) {

		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];

	} else {

		$the_ip = $_SERVER['REMOTE_ADDR'];

	}

	return esc_sql( $the_ip );
}

/**
 * Convert a country code to its opposite format.
 *
 *
 *
 * @param string $code Either a 2-digit or 3-digit code.
 *
 * @return string|false False if no transposition is found.
 */
function it_exchange_convert_country_code( $code ) {

	$codes = it_exchange_get_data_set( 'country-codes' );

	if ( strlen( $code ) === 3 ) {
		$codes = array_flip( $codes );
	}

	if ( isset( $codes[ $code ] ) ) {
		return $codes[ $code ];
	}

	return false;
}

/**
 * Send an email.
 *
 *
 *
 * @param IT_Exchange_Sendable|IT_Exchange_Sendable[]|string                  $email_or_message
 * @param string                                                              $subject
 * @param string|IT_Exchange_Email_Recipient|IT_Exchange_Customer|WP_User|int $recipient
 * @param int|IT_Exchange_Transaction                                         $transaction
 * @param array                                                               $context
 *
 * @return bool
 * @throws InvalidArgumentException
 */
function it_exchange_send_email( $email_or_message, $subject = '', $recipient = '', $transaction = 0, $context = array() ) {

	if ( is_array( $email_or_message ) ) {
		return it_exchange_email_notifications()->get_sender()->bulk_send( $email_or_message );
	}

	if ( ! $email_or_message instanceof IT_Exchange_Sendable ) {
		if ( ! is_string( $email_or_message ) ) {
			throw new InvalidArgumentException( '$email_or_message must be a string if not instanceof IT_Exchange_Sendable' );
		}

		if ( empty( $subject ) || ! is_string( $subject ) ) {
			throw new InvalidArgumentException( '$subject must be a non-empty string.' );
		}

		$transaction = it_exchange_get_transaction( $transaction );

		if ( ! $recipient instanceof IT_Exchange_Email_Recipient ) {

			if ( is_numeric( $recipient ) ) {
				$customer = it_exchange_get_customer( $recipient );
			} elseif ( $recipient instanceof WP_User ) {
				$customer = it_exchange_get_customer( $recipient->ID );
			} elseif ( $recipient instanceof IT_Exchange_Customer ) {
				$customer = $recipient;
			}

			if ( ! empty( $customer ) ) {
				$recipient = new IT_Exchange_Email_Recipient_Customer( $customer );
			} elseif ( is_string( $recipient ) && is_email( $recipient ) ) {
				$recipient = new IT_Exchange_Email_Recipient_Email( $recipient );
			}

			if ( empty( $recipient ) && $transaction instanceof IT_Exchange_Transaction ) {
				$recipient = new IT_Exchange_Email_Recipient_Transaction( $transaction );
			}
		}

		if ( ! $recipient instanceof IT_Exchange_Email_Recipient ) {
			throw new InvalidArgumentException( 'Unable to create an email recipient from given data.' );
		}

		if ( $transaction instanceof IT_Exchange_Transaction && empty( $context['transaction'] ) ) {
			$context['transaction'] = $transaction;
		}

		if ( ! empty( $customer ) && empty( $context['customer'] ) ) {
			$context['customer'] = $customer;
		}

		$email_or_message = new IT_Exchange_Simple_Email( $subject, $email_or_message, $recipient, $context );
	}

	it_exchange_log( 'Sending email {subject} to {to}.', ITE_Log_Levels::INFO, array(
		'subject' => $email_or_message->get_subject(),
		'to'      => $email_or_message->get_recipient() ? $email_or_message->get_recipient()->get_email() : 'unknown',
		'_group'  => 'email',
	) );

	try {
		return it_exchange_email_notifications()->get_sender()->send( $email_or_message );
	} catch ( IT_Exchange_Email_Delivery_Exception $e ) {
		it_exchange_log( 'Failed to send email {subject} to {to}: {exception}', array(
			'subject'   => $email_or_message->get_subject(),
			'to'      => $email_or_message->get_recipient() ? $email_or_message->get_recipient()->get_email() : 'unknown',
			'exception' => $e,
			'_group'    => 'email',
		) );

		return false;
	}
}

/**
 * Check if a given upgrade has been completed.
 *
 * Note, this function will internally cache the result. If you want an uncached value, check with
 * the upgrader object directly.
 *
 *
 *
 * @param string $upgrade
 *
 * @return bool
 */
function it_exchange_is_upgrade_complete( $upgrade ) {

	static $upgrades = array();

	if ( ! isset( $upgrades[ $upgrade ] ) ) {

		$upgrader        = it_exchange_make_upgrader();
		$upgrade_handler = $upgrader->get_upgrade( $upgrade );

		if ( ! $upgrade_handler ) {
			$upgrades[ $upgrade ] = false;
		} else {
			$upgrades[ $upgrade ] = $upgrader->is_upgrade_completed( $upgrade_handler );
		}
	}

	return $upgrades[ $upgrade ];
}

/**
 * Get the log file for an upgrade.
 *
 *
 *
 * @param string $upgrade
 *
 * @return string
 */
function it_exchange_get_upgrade_log_file( $upgrade ) {

	if ( ! it_exchange_is_upgrade_complete( $upgrade ) ) {
		return '';
	}

	it_classes_load( 'it-file-utility.php' );

	$files = ITFileUtility::locate_file( "ninja-shop-upgrade/{$upgrade}*" );

	if ( empty( $files ) || is_wp_error( $files ) ) {
		return '';
	}

	return add_query_arg(
		'it-exchange-serve-upgrade-log',
		$upgrade,
		admin_url( 'admin.php?page=it-exchange-tools' )
	);
}

/**
 * Proportionally distribute a cost amongst multiple items.
 *
 *
 *
 * @param int|float                $cost  Cost to distribute. Can be negative.
 * @param float[]|array[]|object[] $items Array of current item totals.
 * @param string                   $field Instead of being an array of totals, $items is an array of arrays or objects
 *                                        where the $field is the field containing the total.
 *
 * @return array An array of items with their new totals. Keys will be maintained.
 */
function it_exchange_proportionally_distribute_cost( $cost, array $items, $field = '' ) {

	if ( empty( $items ) || empty( $cost ) ) {
		return $items;
	}

	if ( count( $items ) === 1 ) {
		reset( $items );
		$k = key( $items );

		if ( $field && is_array( $items[ $k ] ) ) {
			$items[ $k ][ $field ] += $cost;
		} elseif ( $field && is_object( $items[ $k ] ) ) {
			$items[ $k ]->$field += $cost;
		} else {
			$items[ $k ] += $cost;
		}

		return $items;
	}

	$sum      = array_sum( $items );
	$interval = $cost / $sum;

	foreach ( $items as $k => $v ) {
		if ( $field && is_array( $v ) ) {
			$items[ $k ][ $field ] += $v[ $field ] * $interval;
		} elseif ( $field && is_object( $v ) ) {
			$items[ $k ]->$field += $v->$field * $interval;
		} else {
			$items[ $k ] += $v * $interval;
		}
	}

	return $items;
}

/**
 * Get the default currency for the store.
 *
 *
 *
 * @return string
 */
function it_exchange_get_default_currency() {

	$settings = it_exchange_get_option( 'settings_general' );

	return $settings['default-currency'];
}

/**
 * Check if a function is disabled.
 *
 *
 *
 * @param string $function
 *
 * @return bool
 */
function it_exchange_function_is_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled, true );
}
