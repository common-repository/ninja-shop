<?php
/**
 * Customer class for THEME API
 *
 * 
*/

class IT_Theme_API_Customer implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'customer';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 *
	*/
	protected $_customer = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'formopen'        => 'form_open',
		'username'        => 'username',
		'avatar'          => 'avatar',
		'nickname'        => 'nickname',
		'firstname'       => 'first_name',
		'lastname'        => 'last_name',
		'displayname'     => 'display_name',
		'email'           => 'email',
		'website'         => 'website',
		'password1'       => 'password1',
		'password2'       => 'password2',
		'save'            => 'save',
		'formclose'       => 'form_close',
		'menu'            => 'menu',
		'welcome'         => 'welcome',
		'sitename'        => 'sitename',
		'accountlink'     => 'account_link',
		'thankyoumessage' => 'thank_you_message',
		'addresses'       => 'addresses',
	);

	/**
	 * Constructor
	 *
	 *
	 * @todo get working for admins looking at other users profiles
	*/
	function __construct() {
		if ( is_user_logged_in() )
			$this->_customer = it_exchange_get_current_customer();
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Customer() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an Ninja Shop theme API class
	 *
	 *
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Outputs the profile page start of form
	 *
	 *
	 * @return string
	*/
	function form_open( $options=array() ) {
		$output = '<form action="" method="post" >';
		$output .= '<input type="hidden" name="user_id" value="' . $this->_customer->data->ID . '" >';
		$output .= wp_nonce_field( 'ninja-shop-update-profile-' . $this->_customer->data->ID, '_profile_nonce', true, false );
		return $output;
	}

	/**
	 * Outputs the customer's username data
	 *
	 *
	 * @return string
	*/
	function username( $options=array() ) {
		$defaults = array(
			'format' => 'html',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_value = $this->_customer->data->user_login;
		$label = '<label>' . $field_value . '</label>';

		switch( $options['format'] ) {

			case 'label':
				$output = $label;
				break;
			case 'field-value' :
				$output = $field_value;
				break;
			case 'html':
			default:
				$output = $label;

		}

		return $output;
	}

	/**
	 * Outputs the customer's avatar data
	 *
	 *
	 * @return string
	*/
	function avatar( $options=array() ) {
		$defaults = array(
			'size' => 128,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		return get_avatar( $this->_customer->data->ID, apply_filters( 'ninja_shop_avatar_size', (int) $options['size'] ), apply_filters( 'ninja_shop_default_avatar', 'blank' ) );
	}

	/**
	 * Outputs the customer's nickname data
	 *
	 *
	 * @return string
	*/
	function nickname( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Nickname', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'nickname';
		$field_name = $field_id;
		$field_value = $this->_customer->wp_user->nickname;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}


	/**
	 * Outputs the customer's first name data
	 *
	 *
	 * @return string
	*/
	function first_name( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'First Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'first_name';
		$field_name = $field_id;
		$field_value = $this->_customer->data->first_name;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's last name data
	 *
	 *
	 * @return string
	*/
	function last_name( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'last_name';
		$field_name = $field_id;
		$field_value = $this->_customer->data->last_name;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's display name data
	 *
	 *
	 * @return string
	*/
	function display_name( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Display Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'display_name';
		$field_name = $field_id;
		$field_value = $this->_customer->data->display_name;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}

	/**

	/**
	 * Outputs the customer's email data
	 *
	 *
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Email', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'email';
		$field_name = $field_id;
		$field_value = $this->_customer->data->user_email;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's website data
	 *
	 *
	 * @return string
	*/
	function website( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Website', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'url';
		$field_name = $field_id;
		$field_value = $this->_customer->data->user_url;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'field-value':
				$output = $field_value;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $field_value ) . '" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's password(1) input data
	 *
	 *
	 * @return string
	*/
	function password1( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Password', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'pass1';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="password" id="' . $field_id. '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's password(2) input data
	 *
	 *
	 * @return string
	*/
	function password2( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Confirm Password', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'pass2';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . $options['label'] . '</label>';
				$output .= '<input type="password" id="' . $field_id. '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the profile page save button
	 *
	 *
	 * @return string
	*/
	function save( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  =>  __( 'Save Profile', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'ninja-shop-save-profile';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<input type="submit" id="' . $field_id. '" name="' . $field_name. '" value="' . $options['label'] . '" />';

		}

		return $output;
	}

	/**
	 * Outputs the profile page end of form
	 *
	 *
	 * @return string
	*/
	function form_close( $options=array() ) {
		return '</form>';
	}

	/**
	 * Outputs the customer menu
	 * Default: profile / purchases / downloads
	 *
	 *
	 * @return string
	*/
	function menu( $options=array() ) {

		if ( it_exchange_get_current_customer() instanceof IT_Exchange_Guest_Customer ) {
			return '';
		}

		if ( isset( $_REQUEST['confirmation_auth'] ) && ! is_user_logged_in() ) {
			return '';
		}

		$defaults = array(
			'format' => 'html',
			'pages'  => 'account,profile,purchases,downloads',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$nav  = '<ul class="ninja-shop-customer-menu">';

		$pages = explode( ',', $options['pages']  );
		$pages = apply_filters( 'ninja_shop_customer_menu_pages', $pages );

		$nav .= apply_filters( 'ninja_shop_before_customer_menu_loop', '', $this->_customer );

		foreach( $pages as $page_slug ) {

			// Skip menu item if disabled
			if ( 'disabled' == it_exchange_get_page_type( $page_slug ) ) {
				continue;
			}

			$page_slug = trim( $page_slug );
			$class = it_exchange_is_page( $page_slug ) ? ' class="current"' : '';

			$nav .= '<li' . $class . '><a href="' . it_exchange_get_page_url( $page_slug ) . '">' . it_exchange_get_page_name( $page_slug ) . '</a></li>';

		}

		$nav .= apply_filters( 'ninja_shop_after_customer_menu_loop', '', $this->_customer );

		$nav .= '</ul>';

		return $nav;

	}

	/**
	 * Prints the welcome message for the customer. Used on the account page template by core
	 *
	 *
	 *
	 * @return string
	*/
	function welcome( $options=array() ) {
		$options = it_exchange_get_option( 'settings_general' );
		$message = wpautop( $options['customer-account-page'] );
		$message = do_shortcode( $message );
		return $message;
	}

	/**
	 * Returns the site name
	 *
	 *
	 *
	 * @param array $options
	 * @return string
	*/
	function sitename( $options=array() ) {
		return get_option( 'blogname' );
	}

	/**
	 * Outputs the customer account link
	 *
	 *
	 * @return string
	*/
	function account_link( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'before' => '',
			'after'  => '',
			'label' => __( 'View your Account', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$url = it_exchange_get_page_url( 'account' );

		switch( $options['format'] ) {

			case 'url':
				$output = $url;
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<a href="' . $url . '">' . $options['label'] . '</a>';
				break;

		}

		return $output;
	}

	/**
	 * Outputs the customer thank you message
	 *
	 *
	 * @return string
	*/
	function thank_you_message( $options=array() ) {

		if ( ! $this->_customer ) {
			return false;
		}

		$defaults = array(
			'format' => 'html',
			'before' => '',
			'after'  => '',
			'label' => __( 'Thank you for your order. An email confirmation has been sent to %s.', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		switch( $options['format'] ) {

			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$user_info = it_exchange_get_customer( $this->_customer->id );
				$output = sprintf( $options['label'], $user_info->data->user_email );
				break;

		}

		return $output;
	}

	/**
	 * Loop over a customer's addresses.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return bool
	 */
	public function addresses( $options = array() ) {

		if ( ! $this->_customer ) {
			return false;
		}

		$defaults = array(
			'include_primary' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( $options['has'] ) {
			return (bool) $this->_customer->get_addresses( $options['include_primary'] );
		}

		$customer = $this->_customer;

		return it_theme_api_loop( 'address', 'addresses', function() use ( $customer, $options ) {
			return $customer->get_addresses( $options['include_primary'] );
		} );
	}
}
