<?php
/**
 * Login class for THEME API
 *
 * 
*/

class IT_Theme_API_Login implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'login';

	/**
	 * Current customer being viewed
	 * @var string $_customer
	 *
	*/
	private $_customer = '';

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'formopen'    => 'form_open',
		'username'    => 'username',
		'password'    => 'password',
		'rememberme'  => 'remember_me',
		'loginbutton' => 'login_button',
		'recover'     => 'recover',
		'register'    => 'register',
		'cancel'      => 'cancel',
		'formclose'   => 'form_close',
	);

	/**
	 * Constructor
	 *
	 *
	 * @todo get working for admins looking at other users profiles
	*/
	function __construct() {
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Login() {

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
	 * Outputs the login page start of form
	 *
	 * - Use the redirect option to set where you want the user to be redirected to after logging in.
	 * - Use the class option to add a custom class to the form.
	 *
	 *
	 *
	 * @param array $options array of options
	 * @return string
	*/
	function form_open( $options=array() ) {
		$defaults = array(
			'redirect' => it_exchange_in_superwidget() ? it_exchange_clean_query_args() : it_exchange_get_page_url( 'account' ),
			'class'    => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$current_page = it_exchange_is_page();
		$current_page = empty( $current_page ) ? 'login' : $current_page;
		$options['redirect'] = apply_filters( 'ninja_shop_redirect_for-login-success-from-' . $current_page, $options['redirect'], array( 'context' => 'login-success-from-' . $current_page ), 302 );

		// Grab redirect var from session
		$login_redirect = it_exchange_get_session_data( 'login_redirect' );
		if ( ! empty( $login_redirect ) ) {
			$options['redirect'] = reset( $login_redirect );
			it_exchange_clear_session_data( 'login_redirect' );
		}

		if ( it_exchange_in_superwidget() ) {
			$class = empty( $options['class'] ) ? 'ninja-shop-sw-log-in' : 'ninja-shop-sw-log-in ' . esc_attr( $options['class'] );
		} else {
			$class = empty( $options['class'] ) ? 'ninja-shop-log-in' : 'ninja-shop-log-in ' . esc_attr( $options['class'] );
		}

		$action = site_url( 'wp-login.php', 'login_post' );

		if ( apply_filters( 'ninja_shop_login_page_use_compatibility_mode', false ) ) {
			$action = add_query_arg( 'it_exchange_login', 1, it_exchange_get_page_url( 'login' ) );
		}

		$action = add_query_arg( 'redirect_to', urlencode( $options['redirect'] ), $action );

		// WP-Engine only plays with the 'login_post' schema, so we cannot use wp_login_url()
		// At least until we can modify the schema with that function
		return '<form id="loginform" class="' . $class . '" action="' . esc_url( $action ) . '" method="post">';
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
		$field_name = 'log';

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
				$output .= '<input type="text" id="' . $field_id. '" name="' . $field_name. '" value="" />';

		}

		return $output;
	}

	/**
	 * Outputs the login's password input data
	 *
	 *
	 * @return string
	*/
	function password( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Password', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'user_pass';
		$field_name = 'pwd';

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
	 * Outputs the login's remember me input data
	 *
	 *
	 * @return string
	*/
	function remember_me( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Remember Me', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'rememberme';
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
				$output = '<input type="checkbox" id="' . $field_id. '" name="' . $field_name. '" value="forever" />';
				$output .= '<label for="' . $field_id. '">' . $options['label'] . '</label>';

		}

		return $output;
	}

	/**
	 * Outputs the login page login button
	 *
	 *
	 * @return string
	*/
	function login_button( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Log In', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'wp-submit';
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
	 * Outputs the login page login button
	 *
	 *
	 * @return string
	*/
	function recover( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Lost your password?', 'it-l10n-ithemes-exchange' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'wp-submit';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'ninja-shop-sw-lost-pass-link' : 'ninja-shop-sw-lost-pass-link ' . $options['class'];

		switch( $options['format'] ) {

			case 'text':
				$output = $options['label_recover'];
				break;
			case 'url':
				$output = esc_attr( wp_lostpassword_url() );
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . esc_attr( wp_lostpassword_url() ) . '">' . esc_attr( $options['label'] ) . '</a>';

		}

		return $output;
	}

	/**
	 * Outputs the registration link by default.
	 *
	 * Can also output the registration URL
	 *
	 *
	 * @return string
	*/
	function register( $options=array() ) {
		$defaults = array(
			'format' => 'html',
			'label'  => __( 'Register', 'it-l10n-ithemes-exchange' ),
			'class'  => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$field_id = 'wp-submit';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'ninja-shop-sw-register-link' : 'ninja-shop-sw-register-link ' . $options['class'];

		switch( $options['format'] ) {

			case 'url':
				$output = it_exchange_get_page_url( 'registration' );
				break;
			case 'label':
				$output = esc_attr( $options['label'] );
				break;
			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( 'registration' ) . '">' . esc_attr( $options['label'] ) . '</a>';

		}

		return $output;
	}

	/**
	 * Outputs the login page cancel button
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

		$field_id = 'ninja-shop-cancel-login-customer';
		$field_name = $field_id;
		$class = empty( $options['class'] ) ? 'ninja-shop-sw-cancel-login-link' : 'ninja-shop-sw-cancel-login-link ' . $options['class'];

		if ( it_exchange_is_multi_item_cart_allowed() )
			$page = 'cart';
		else
			$page = 'product';

		switch( $options['format'] ) {

			case 'url':
				$output = it_exchange_get_page_url( $page );

			case 'label':
				$output = esc_attr( $options['label'] );

			case 'html':
			default:
				$output = '<a class="' . esc_attr( $class ) . '" href="' . it_exchange_get_page_url( $page ) . '">' .esc_attr( $options['label'] ) . '</a>';

		}
		return $output;
	}

	/**
	 * Outputs the closing form tag for the login form.
	 *
	 *
	 * @return string
	*/
	function form_close( $options=array() ) {
		return '</form>';
	}
}
