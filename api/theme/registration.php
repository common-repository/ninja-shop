<?php
/**
 * Registration class for THEME API
 *
 * 
*/

class IT_Theme_API_Registration implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'registration';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 *
	*/
	private $_customer = '';

	/**
	 * @var array
	 */
	private $registration_session = array();

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'isenabled'        => 'is_enabled',
		'formopen'         => 'form_open',
		'firstname'        => 'firstname',
		'lastname'         => 'lastname',
		'username'         => 'username',
		'email'            => 'email',
		'password1'        => 'password1',
		'password2'        => 'password2',
		'save'             => 'save',
		'cancel'           => 'cancel',
		'formclose'        => 'form_close',
		'disabledmessage'  => 'disabled_message',
	);

	/**
	 * Constructor
	 *
	 *
	 * @todo get working for admins looking at other users profiles
	*/
	function __construct() {

		if ( it_exchange_in_superwidget() ) {

			$data = it_exchange_get_session_data( "sw-registration" );

			if ( empty( $data ) ) {
				$data = array();
			}

			$this->registration_session = $data;
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Registration() {

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
	 * Checks if registration is enabled or disabled
	 * (enabled by default unless using WordPress setting)
	 *
	 *
	 * @return string
	*/
	function is_enabled( $options=array() ) {

		$settings = it_exchange_get_option( 'settings_general' );

		if ( 'wp' === $settings['site-registration'] && !get_option('users_can_register') )
		 	return false;

		return true;

	}

	/**
	 * Outputs the profile page start of form
	 *
	 *
	 * @return string
	*/
	function form_open( $options=array() ) {
		$defaults = array(
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$default_class = it_exchange_in_superwidget() ? 'ninja-shop-sw-register' : 'ninja-shop-register';
		$class= empty( $options['class'] ) ? $default_class : $default_class . ' ' . esc_attr( $options['class'] );
		$output  = '<form class="' . $class . '" action="" method="post" >';
		$output .= wp_nonce_field( 'ninja-shop-register-customer', '_ninja_shop_register_nonce', true, true );
		return $output;
	}

	/**
	 * Outputs the login's username data
	 *
	 *
	 * @return string
	*/
	function username( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Username', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'user_login';
		$field_name = $field_id;

		if ( isset($this->registration_session[ $field_name ] ) ) {
			$value = $this->registration_session[ $field_name ];
		} else {
			$value = '';
		}

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

				break;
			case 'field-name':
				$output = $field_name;

				break;
			case 'label':
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . esc_attr( $options['label'] ) . '<span class="ninja-shop-required-star">*</span></label>';
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="' . esc_attr( $value ) .'" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's first name data
	 *
	 *
	 * @return string
	*/
	function firstname( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'First Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'first_name';
		$field_name = $field_id;

		if ( isset($this->registration_session[ $field_name ] ) ) {
			$value = $this->registration_session[ $field_name ];
		} else {
			$value = '';
		}

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;
				break;
			case 'field-name':
				$output = $field_name;
				break;
			case 'label':
				$output = esc_attr( $options['label'] );
				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . esc_attr( $value ) .'" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's last name data
	 *
	 *
	 * @return string
	*/
	function lastname( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'last_name';
		$field_name = $field_id;

		if ( isset($this->registration_session[ $field_name ] ) ) {
			$value = $this->registration_session[ $field_name ];
		} else {
			$value = '';
		}

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
				$output = '<label for="' . $field_id . '">' . $options['label'] . '</label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . esc_attr( $value ) .'" />';

		}

		return $output;
	}

	/**
	 * Outputs the customer's email data
	 *
	 *
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults      = array(
			'format'      => 'html',
			'label'  => __( 'Email', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'email';
		$field_name = $field_id;

		if ( isset($this->registration_session[ $field_name ] ) ) {
			$value = $this->registration_session[ $field_name ];
		} else {
			$value = '';
		}

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

				break;
			case 'field-name':
				$output = $field_name;

				break;
			case 'label':
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . esc_attr( $options['label'] ) . '<span class="ninja-shop-required-star">*</span></label>';
				$output .= '<input type="text" id="' . $field_id . '" name="' . $field_name . '" value="' . esc_attr( $value ) .'" />';

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
		$defaults      = array(
			'format'      => 'html',
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
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id. '">' . esc_attr( $options['label'] ) . '<span class="ninja-shop-required-star">*</span></label>';
				$output .= '<input type="password" id="' . $field_id . '" name="' . $field_name. '" value="" />';

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
		$defaults      = array(
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
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<label for="' . $field_id . '">' . $options['label'] . '<span class="ninja-shop-required-star">*</span></label>';
				$output .= '<input type="password" id="' . $field_id . '" name="' . $field_name. '" value="" />';

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
		$defaults      = array(
			'format' => 'html',
			'label'  =>  __( 'Register', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'ninja-shop-register-customer';
		$field_name = $field_id;

		switch( $options['format'] ) {

			case 'field-id':
				$output = $field_id;

				break;
			case 'field-name':
				$output = $field_name;

				break;
			case 'label':
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<input type="submit" id="' . $field_id . '" name="' . $field_name . '" value="' . esc_attr( $options['label'] ) . '" />';

		}
		return $output;
	}

	/**
	 * Outputs the registration page cancel button
	 *
	 *
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  =>  __( 'Cancel', 'it-l10n-ithemes-exchange' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'ninja-shop-cancel-register-customer';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'ninja-shop-sw-cancel-register-link' : 'ninja-shop-sw-cancel-register-link ' . $options['class'];

		switch( $options['format'] ) {

			case 'url':
				$output = it_exchange_get_page_url( 'login' );

				break;
			case 'label':
				$output = esc_attr( $options['label'] );

				break;
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( 'login' ) . '">' .esc_attr( $options['label'] ) . '</a>';

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
	 * Outputs the profile page registration disabled message
	 *
	 *
	 * @return string
	*/
	function disabled_message( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  =>  __( 'Registration Disabled', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		switch( $options['format'] ) {

			case 'label':
				$output = esc_attr( $options['label'] );
				break;

			case 'html':
			default:
				$output = '<h1>' . esc_attr( $options['label'] ) . '</h1>';

		}

		return $output;

	}
}
