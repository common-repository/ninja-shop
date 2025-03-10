<?php
/**
 * Adds our template directory to the list of possible sources
 *
 * Only adds it if were looking for one of our templates. No need
 * to scan our directory if we know we don't have the template being requeste
 *
 *
 *
 * @return array
*/
function it_exchange_guest_checkout_add_template_directory( $template_paths, $template_names ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
        return $template_paths;
    }

	/**
	 * Use the template_names array to target a specific template part you want to add
	 * In this example, we're adding the following template part: super-widget-registration/elements/guest.php
	 * So we're going to only add our templates directory if Exchange is looking for that part.
	*/
	$found = false;
	foreach( $template_names as $template_name ) {
		if ( false !== ( strpos( 'guest-checkout', $template_name ) ) ) {
			$found = true;
			continue;
		}
		if ( 'super-widget-registration/elements/guest.php' == $template_name ) {
			$found = true;
			continue;
		}
	}
	if ( $found )
		return $template_paths;

	/**
	 * If we are looking for the mailchimp-signup template part, go ahead and add our add_ons directory to the list
	 * No trailing slash
	*/
	$template_paths[] = dirname( __FILE__ ) . '/templates';

	return $template_paths;

}
add_filter( 'ninja_shop_possible_template_paths', 'it_exchange_guest_checkout_add_template_directory', 10, 2 );

/**
 * Returns the Guest Checkout title.
 *
 *
 *
 * @param  string $text   the text to be displayed in the heading element. pass FALSE to text to display nothing
 * @param  string $class  space separated classes you want to add to the heading element
 * @param  string $tag    the HTML tag you want to use for the heading. without brackets. default is: h3
 * @return string         html heading
*/
function it_exchange_guest_checkout_get_heading( $text=false, $class='', $tag='h3' ) {
	$in_sw = it_exchange_in_superwidget();
	$class = (bool) it_exchange_in_superwidget() ? $class . ' in-super-widget' : $class;

	if ( FALSE == $text ) {
		$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
		if ( $in_sw ) {
			$text = ! isset( $guest_checkout_settings['sw_heading_text'] ) ? __( 'Guest Checkout', 'it-l10n-ithemes-exchange' ) : $guest_checkout_settings['sw_heading_text'];
		} else {
			$text = ! isset( $guest_checkout_settings['content_heading_text'] ) ? __( 'Checkout as a guest?', 'it-l10n-ithemes-exchange' ) : $guest_checkout_settings['content_heading_text'];
		}
	} else if ( empty( $text ) ) {
		$text = '';
	}

	$classes = explode( ' ', $class );
	$class   = implode( ' ', array_filter( $classes ) );

	$heading = '<' . esc_attr( $tag ) . ' class="' . esc_attr( $class ) . '">' . $text . '</' . esc_attr( $tag ) . '>';
	$heading = apply_filters( 'ninja_shop_guest_checkout_get_heading', $heading, $class, $text, $tag );
	return $heading;
}

/**
 * Add the register and login links if settings allows them
 *
 *
 *
 * @param  array $actions
 * @return array
*/
function it_exchange_guest_checkout_modify_guest_checkout_purchase_requirement_form_actions( $actions ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
        return $actions;
    }

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $actions;

	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) )
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );

	// Remove cancel action if it is present
	$cancel = array_search( 'cancel', $actions );
	if ( false !== $cancel )
		unset( $actions[$cancel] );

	// Show Reg link if settings have enabled it.
	if ( ! empty( $guest_checkout_settings['show-registration-link'] ) )
		$actions[] = 'register';

	// Show Log in link if settings have enabled it.
	if ( ! empty( $guest_checkout_settings['show-log-in-link'] ) )
		$actions[] = 'login';

	// Add cancel back to array
	$actions[] = 'cancel';

	return $actions;
}
add_filter( 'ninja_shop_get_super_widget_guest_checkout_actions_elements', 'it_exchange_guest_checkout_modify_guest_checkout_purchase_requirement_form_actions' );

/**
 * Remove the login link from the super-widget 'Register' state if guest checkout settings have disabled it.
 *
 *
 *
 * @param  array $actions incoming actions already registered
 * @return array
*/
function it_exchange_remove_login_link_from_register_sw_state( $actions ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $actions;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $actions;

	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) )
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$index = array_search( 'cancel', $actions );
	if ( false !== $index && empty( $guest_checkout_settings['show-log-in-link'] ) )
		unset( $actions[$index] );

	return $actions;
}
add_filter( 'ninja_shop_get_super_widget_registration_fields_elements', 'it_exchange_remove_login_link_from_register_sw_state' );

/**
 * Remove the register link from the super-widget 'Log in' state if guest checkout settings have disabled it.
 *
 *
 *
 * @param  array $actions incoming actions already registered
 * @return array
*/
function it_exchange_remove_register_link_from_login_sw_state( $actions ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $actions;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $actions;

	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) )
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$index = array_search( 'register', $actions );
	if ( false !== $index && empty( $guest_checkout_settings['show-registration-link'] ) )
		unset( $actions[$index] );

	return $actions;
}
add_filter( 'ninja_shop_get_super_widget_login_actions_elements', 'it_exchange_remove_register_link_from_login_sw_state' );

/**
 * Prints the continue link for the Checkout page purchase requirement
 *
 *
 *
 * @return string
*/
function it_exchange_guest_checkout_get_purchase_requirement_continue_action() {
	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$text = empty( $guest_checkout_settings['content_continue_button_text'] ) ? __( 'Continue as guest', 'it-l10n-ithemes-exchange' ) : $guest_checkout_settings['content_continue_button_text'];
	?>
	<input type="submit" id="ninja-shop-guest-checkout-action" name="continue" value="<?php esc_attr_e( $text ); ?>" />
	<?php
}

/**
 * Prints the link to checkout as Guest in the SW
 *
 *
 *
 * @param string $label What do you wan the link to say
 *
 * @return string
*/
function it_exchange_guest_checkout_sw_link( $label ) {
	return '<a class="ninja-shop-guest-checkout-link" href=""><input type="button" value="' . esc_attr( $label ) . '" /></a>';
}

/**
 * This prints the email field in the various template parts
 *
 *
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_email_field( $options=array() ) {
	$email = ! empty( $_POST['email'] ) && ! is_email( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
	$field = '<input type="text" name="email" class="ninja-shop-guest-checkout-email" value="' . esc_attr( $email ) . '" placeholder="' . __( 'Email address', 'it-l10n-ithemes-exchange' ) . '" />';
	return $field;
}

/**
 * This prints the continue link in the SW
 *
 *
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_sw_save_link( $options=array() ) {
	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$text = empty( $guest_checkout_settings['sw_continue_button_text'] ) ? __( 'Continue', 'it-l10n-ithemes-exchange' ) : $guest_checkout_settings['sw_continue_button_text'];
	$link = '<input type="submit" class="ninja-shop-guest-checkout-save-link" value="' . esc_attr( $text ) . '" />';
	return $link;
}

/**
 * This prints the cancel link in the SW
 *
 *
 *
 * @param array $options Options for format, output, etc
 * @return string
*/
function it_exchange_guest_checkout_get_sw_cancel_link( $options=array() ) {
	$link = '<a href="" class="it-exchange-sw-cancel-guest-checkout-link">' . __( 'Cancel', 'it-l10n-ithemes-exchange' ) . '</a>';
	return $link;
}

/**
 * Adds guest-checkout as a valid super-widget state
 *
 *
 *
 * @param array $valid_states existing valid states
 * @return array
*/
function it_exchange_guest_checkout_modify_valid_sw_states( $valid_states ) {

	if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $valid_states;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $valid_states;

	$valid_states[] = 'guest-checkout';
	return $valid_states;
}
add_filter( 'ninja_shop_super_widget_valid_states', 'it_exchange_guest_checkout_modify_valid_sw_states' );

/**
 * Overwrites the core default_form setting for the guest_checkout value on the Checkout page.
 *
 *
 *
 * @param string $template_part
 * @return string
*/
function it_exchange_guest_checkout_override_logged_in_checkout_template_part( $template_part ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $template_part;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $template_part;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$form = empty( $guest_checkout_settings['default-form'] ) ? $template_part : 'guest-checkout';

	return $form;
}
add_filter( 'ninja_shop_get_default_content_checkout_mode', 'it_exchange_guest_checkout_override_logged_in_checkout_template_part' );

/**
 * Overwrites the core default_form setting for the guest_checkout value in the SuperWidget.
 *
 *
 *
 * @param string $template_part
 * @return string
*/
function it_exchange_guest_checkout_override_logged_in_supwer_widget_template_part( $template_part ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $template_part;
	}

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );
	$form = empty( $guest_checkout_settings['default-form'] ) ? $template_part : 'guest-checkout';

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $template_part;

	return $form;
}
add_filter( 'ninja_shop_get_default_sw_checkout_mode', 'it_exchange_guest_checkout_override_logged_in_supwer_widget_template_part' );

/**
 * Add the Guest Checkin UI to the checkout page registration view
 *
 *
 *
 * @param array $elements existing elements in the content loop of the logged-in requiremnt template
 * @return array
*/
function it_exchagne_guest_checkout_add_guest_checkout_template_part_to_logged_in_purchase_requirement( $elements ) {

	if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $elements;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $elements;

	$elements[] = 'guest-checkout';
	return $elements;
}
add_filter( 'ninja_shop_get_content-checkout-logged-in-purchase-requirements-not-logged-in_content_elements', 'it_exchagne_guest_checkout_add_guest_checkout_template_part_to_logged_in_purchase_requirement' );

/**
 * Add link back to Guest Checkout from Registration and Login forms on Checkout Page
 *
 *
 *
 * @param array $links incoming links
 * @return array
*/
function it_exchange_add_guest_checkout_links_to_logged_in_purchase_requirement_on_checkout_page( $links ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $links;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $links;

	$links[] = 'guest-checkout';
	return $links;
}
add_filter( 'ninja_shop_get_content-checkout-logged-in-purchase-requirements-not-logged-in_links_elements', 'it_exchange_add_guest_checkout_links_to_logged_in_purchase_requirement_on_checkout_page' );

/**
 * Removes the Login and the and Registration link from the Checkout page template parts when turned off in guest-checkout settings
 *
 *
 *
 * @param array $links incoming links
 * @return array
*/
function it_exchange_guest_checout_maybe_remove_reg_and_login_links_from_checkout_page( $links ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $links;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $links;

	$general_settings = it_exchange_get_option( 'settings_general' );
	if ( 'wp' == $general_settings['site-registration'] && ! get_option( 'users_can_register' ) )
		return $actions;

	$guest_checkout_settings = it_exchange_get_option( 'addon-guest-checkout' );

	// Remove Reg link if settings have disabled it.
	if ( empty( $guest_checkout_settings['show-registration-link'] ) && in_array( 'register', $links ) ) {
		$index = array_search( 'register', $links );
		unset( $links[$index] );
	}

	// Remove Log in link if settings have disabled it.
	if ( empty( $guest_checkout_settings['show-log-in-link'] ) && in_array( 'login', $links ) ) {
		$index = array_search( 'login', $links );
		unset( $links[$index] );
	}

	return $links;
}
add_filter( 'ninja_shop_get_content-checkout-logged-in-purchase-requirements-not-logged-in_links_elements', 'it_exchange_guest_checout_maybe_remove_reg_and_login_links_from_checkout_page' );

/**
 * Add Guest Checkout links to the SuperWidget Login / Registration Forms
 *
 *
 *
 * @param array $links incoming template parts from WP filter
 * @return array
*/
function it_exchange_add_guest_checkout_link_to_sw_registration_state( $links ) {

    if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $links;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $links;

	// Place it before cancel if cancel is found. Otherwise, place it at the end.
	if ( ! ( $index = array_search( 'cancel', $links ) ) )
		$index = count( $links );
	array_splice( $links, $index, 0, 'guest-checkout-link' );

	return $links;
}
add_filter( 'ninja_shop_get_super_widget_registration_actions_elements', 'it_exchange_add_guest_checkout_link_to_sw_registration_state' );

/**
 * Add Guest Checkout links to the SuperWidget Log in form
 *
 *
 *
 * @param array $links incoming template parts from WP filter
 * @return array
*/
function it_exchange_add_guest_checkout_link_to_sw_login_state( $links ) {

	if ( ! it_exchange_can_cart_be_purchased_by_guest() ) {
		return $links;
	}

	// Don't offer guest checkout if we're doing membership
	if ( function_exists( 'it_exchange_membership_cart_contains_membership_product' ) && it_exchange_membership_cart_contains_membership_product() )
		return $links;

	if ( ! ( $index = array_search( 'recover', $links ) ) )
		$index = count( $links );
	array_splice( $links, $index, 0, 'guest-checkout-link' );

	return $links;
}
add_filter( 'ninja_shop_get_super_widget_login_actions_elements', 'it_exchange_add_guest_checkout_link_to_sw_login_state' );

/**
 * Removes the User Menu links from the confirmation page if doing guest checkout
 *
 *
 *
 * @param  array $loops   the array of loops to include in the header
 * @return array modified loops missing the menu
*/
function it_exchange_remove_customer_menu_when_doing_guest_checkout( $loops ) {

	if ( ! isset( $GLOBALS['it_exchange']['transaction'] ) ) {
		$page_slug      = it_exchange_get_page_slug( 'confirmation', true );

		if ( $transaction_hash = get_query_var( $page_slug ) ) {
			$transaction_id = it_exchange_get_transaction_id_from_hash( $transaction_hash );
			$transaction    = it_exchange_get_transaction( $transaction_id );
		} else {
			return $loops;
		}
	} else {
		$transaction = $GLOBALS['it_exchange']['transaction'];
	}

    if ( ! $transaction instanceof IT_Exchange_Transaction ) {
	    return $loops;
    }

    if ( ! $transaction->is_guest_purchase() ) {
    	return $loops;
    }

	if ( false !== ( $index = array_search( 'menu', $loops ) ) ) {
		unset( $loops[ $index ] );
	}

	return $loops;
}
add_filter( 'ninja_shop_get_content_confirmation_header_loops', 'it_exchange_remove_customer_menu_when_doing_guest_checkout' );
