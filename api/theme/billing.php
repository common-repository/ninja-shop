<?php
/**
 * Billing class for THEME API
 *
 *
*/

class IT_Theme_API_Billing implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'billing';

	/**
	 * Current customer Billing Address
	 * @var string $_billing_address
	 *
	*/
	private $_billing_address = '';

	/**
	 * @var array
	 */
	private $session = array();

	/**
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	public $_tag_map = array(
		'firstname'     => 'first_name',
		'lastname'      => 'last_name',
		'companyname'   => 'company_name',
		'address1'      => 'address1',
		'address2'      => 'address2',
		'city'          => 'city',
		'state'         => 'state',
		'zip'           => 'zip',
		'shipping'      => 'shipping',
		'country'       => 'country',
		'email'         => 'email',
		'phone'         => 'phone',
		'submit'        => 'submit',
		'cancel'        => 'cancel',
		'radioexisting' => 'radio_existing',
		'radionew'      => 'radio_new',
		'saved'         => 'saved_address',
	);

	/**
	 * Constructor
	 *
	 *
	*/
	function __construct() {
		$this->_billing_address = it_exchange_get_cart_billing_address();

		if ( it_exchange_in_superwidget() ) {

			$data = it_exchange_get_session_data( "sw-billing" );

			if ( empty( $data ) ) {
				$data = array();
			}

			$this->session = $data;
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Billing() {

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
	 * Outputs the billing address first name data
	 *
	 *
	 * @return string
	*/
	function first_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'First Name', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-first-name';
		$options['field_name'] = 'ninja-shop-billing-address-first-name';
		$options['value']      = empty( $this->_billing_address['first-name'] ) ? '' : $this->_billing_address['first-name'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address last name data
	 *
	 *
	 * @return string
	*/
	function last_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Last Name', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-last-name';
		$options['field_name'] = 'ninja-shop-billing-address-last-name';
		$options['value']      = empty( $this->_billing_address['last-name'] ) ? '' : $this->_billing_address['last-name'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address compnay name data
	 *
	 *
	 * @return string
	*/
	function company_name( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Company Name', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-company-name';
		$options['field_name'] = 'ninja-shop-billing-address-company-name';
		$options['value']      = empty( $this->_billing_address['company-name'] ) ? '' : $this->_billing_address['company-name'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address address 1 data
	 *
	 *
	 * @return string
	*/
	function address1( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Address', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-address1';
		$options['field_name'] = 'ninja-shop-billing-address-address1';
		$options['value']      = empty( $this->_billing_address['address1'] ) ? '' : $this->_billing_address['address1'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address address 2data
	 *
	 *
	 * @return string
	*/
	function address2( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-address2';
		$options['field_name'] = 'ninja-shop-billing-address-address2';
		$options['value']      = empty( $this->_billing_address['address2'] ) ? '' : $this->_billing_address['address2'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address city data
	 *
	 *
	 * @return string
	*/
	function city( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'City', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-city';
		$options['field_name'] = 'ninja-shop-billing-address-city';
		$options['value']      = empty( $this->_billing_address['city'] ) ? '' : $this->_billing_address['city'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address zip data
	 *
	 *
	 * @return string
	*/
	function zip( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Zip Code', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-zip';
		$options['field_name'] = 'ninja-shop-billing-address-zip';
		$options['value']      = empty( $this->_billing_address['zip'] ) ? '' : $this->_billing_address['zip'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the Ship to Billing address when shipping is needed
	 *
	 *
	 *
	 * @param array $options options
	 * @return string
	*/
	function shipping( $options=array() ) {

		// Abort if shipping is not enabled
		if ( ! it_exchange_get_available_shipping_methods_for_cart_products() && ! apply_filters( 'ninja_shop_shipping_address_purchase_requirement_enabled', false ) )
			return;

		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Ship to billing address?', 'it-l10n-ithemes-exchange' ),
			'required' => false,
			'value'    => '1',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-ship-to-billing';
		$options['field_name'] = 'ninja-shop-ship-to-billing';

		// Grab saved setting
		$options['value']      = empty( $this->_billing_address['ship-to-billing'] ) ? $options['value'] : $this->_billing_address['ship-to-billing'];

		$field = '<input type="checkbox" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" ' . checked( $options['value'], '1', false ) . ' value="1" />';

		switch( $options['format'] ) {
			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = $field;
				break;
			case 'value':
				$output = $current_value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">';
				$output .= $field . $options['label'];
				$output .= '</label>';
		}
		return $output;
	}

	/**
	 * Outputs the billing address country data
	 *
	 *
	 * @return string
	*/
	function country( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Country', 'it-l10n-ithemes-exchange' ),
			'required' => true,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-country';
		$options['field_name'] = 'ninja-shop-billing-address-country';
		$options['value']      = empty( $this->_billing_address['country'] ) ? '' : $this->_billing_address['country'];

		// Update value if doing ajax
		$options['value'] = empty( $_POST['ite_base_country_ajax'] ) ? $options['value'] : sanitize_text_field( $_POST['ite_base_country_ajax'] );

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		$countries = it_exchange_get_data_set( 'countries' );

		$current_value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );

		$field  = '<select id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '">';
		$field .= '<option value=""></option>';
		foreach( $countries as $key => $value ) {
			$alternatives = esc_attr( $key );
			if ( 'US' == $key ) {
				$alternatives .= ' US us usa USA u';
			}
			$alternatives = apply_filters( 'ninja_shop_country_select_alternatives_' . strtolower( $key ), $alternatives );
			$field .= '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $current_value, false ) . ' data-alternative-spellings="' . $alternatives . '">' . esc_html( $value ) . '</option>';
		}
		$field .= '</select>';

		switch( $options['format'] ) {
			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = $field;
				break;
			case 'value':
				$output = $current_value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '&nbsp;<span class="ninja-shop-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= $field;
		}
		return $output;
	}

	/**
	 * Outputs the billing address state data
	 *
	 *
	 * @return string
	*/
	function state( $options=array() ) {

		// Default state value for normal page load
		$billing_value = empty( $this->_billing_address['state'] ) ? '' : $this->_billing_address['state'];
		$default_value = empty( $_POST['ninja-shop-billing-address-state'] ) ? $billing_value : sanitize_text_field( $_POST['ninja-shop-billing-address-state'] );

		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'State', 'it-l10n-ithemes-exchange' ),
			'required' => false,
			'value'    => $default_value,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		// Update value if doing ajax
		$options['value'] = empty( $_POST['ite_base_state_ajax'] ) ? $options['value'] : sanitize_text_field( $_POST['ite_base_state_ajax'] );

		$options['field_id']   = 'ninja-shop-billing-address-state';
		$options['field_name'] = 'ninja-shop-billing-address-state';
		$options['value']      = empty( $this->_billing_address['state'] ) ? '' : $this->_billing_address['state'];

		if ( empty( $options['value'] ) && ! empty( $this->session[ $options['field_name'] ] ) ) {
			$options['value'] = $this->session[ $options['field_name'] ];
		}

		$states = it_exchange_get_data_set( 'states', array( 'country' => it_exchange( 'billing', 'get-country', array( 'format' => 'value' ) ) ) );

		$current_value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );

		$field = '';
		if ( ! empty( $states ) && is_array( $states ) ) {
			$options['required'] = true;
			$field .= '<select id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '">';
			$field .= '<option value=""></option>';
			foreach( (array) $states as $key => $value ) {
				$alternatives = esc_attr( $key );
				$alternatives = apply_filters( 'ninja_shop_state_select_alternatives_' . strtolower( $key ), $alternatives );
				$field .= '<option value="' . esc_attr( $key ) . '" ' . selected( $key, $current_value, false ) . ' data-alternative-spellings="' . $alternatives . '">' . esc_html( $value ) . '</option>';
			}
			$field .= '</select>';
		} else {
			$text_options = $options;
			$text_options['format']    = 'field';
			$field .= $this->get_fields( $text_options );
		}

		switch( $options['format'] ) {
			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				break;
			case 'field':
				$output = $field;
				break;
			case 'value':
				$output = $current_value;
				break;
			case 'html':
			default:
				$output  = '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '&nbsp;<span class="ninja-shop-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= $field;
		}
		return $output;
	}

	/**

	/**
	 * Outputs the billing address email data
	 *
	 *
	 * @return string
	*/
	function email( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Email', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-email';
		$options['field_name'] = 'ninja-shop-billing-address-email';
		$options['value']      = empty( $this->_billing_address['email'] ) ? '' : $this->_billing_address['email'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address phone data
	 *
	 *
	 * @return string
	*/
	function phone( $options=array() ) {
		$defaults      = array(
			'format'   => 'html',
			'label'    => __( 'Phone', 'it-l10n-ithemes-exchange' ),
			'required' => false,
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-phone';
		$options['field_name'] = 'ninja-shop-billing-address-phone';
		$options['value']      = empty( $this->_billing_address['phone'] ) ? '' : $this->_billing_address['phone'];

		return $this->get_fields( $options );
	}

	/**
	 * Outputs the billing address submit button
	 *
	 *
	 * @return string
	*/
	function submit( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Submit', 'it-l10n-ithemes-exchange' ),
			'name'   => '',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		$options['field_id']   = 'ninja-shop-billing-address-submit';

		return $output = '<input type="submit" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['name'] ) . '" value="'. esc_attr( $options['label'] ) .'" />';
	}

	/**
	 * Outputs the billing address phone data
	 *
	 *
	 * @return string
	*/
	function cancel( $options=array() ) {
		$defaults      = array(
			'format' => 'html',
			'label'  => __( 'Cancel', 'it-l10n-ithemes-exchange' ),
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		return '<a class="ninja-shop-billing-address-requirement-cancel" href="' . it_exchange_get_page_url( 'checkout' ) . '">' . $options['label'] . '</a>';
	}

	/**
	 * Gets the HTML is the desired format
	 *
	 *
	 *
	 * @param array $options
	 * @return mixed
	*/
	function get_fields( $options ) {

		$value = empty( $options['value'] ) ? '' : esc_attr( $options['value'] );
		$class = empty( $options['class'] ) ? '' : esc_attr( $options['class'] );

		switch( $options['format'] ) {

			case 'field-id' :
				$output = $options['field_id'];
				break;
			case 'field-name':
				$output = $options['field_name'];
				break;
			case 'label':
				$output = $options['label'];
				if ( $options['required'] )
					$output .= '<span class="ninja-shop-required-star">&#42;</span>';
				break;
			case 'field':
				$output = '<input type="text" class="' . $class . '" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="' . $value . '" />';
				break;
			case 'value':
				$output = $value;
				break;
			case 'html':
			default:
				$output  = empty( $options['label'] ) ? '' : '<label for="' . esc_attr( $options['field_id'] ) . '">' . $options['label'];
				if ( $options['required'] )
					$output .= '&nbsp;<span class="ninja-shop-required-star">&#42;</span>';
				$output .= '</label>';
				$output .= '<input type="text" class="' . $class . '" id="' . esc_attr( $options['field_id'] ) . '" name="' . esc_attr( $options['field_name'] ) . '" value="' . $value . '" />';
		}

		return $output;
	}

	/**
	 * Existing radio input.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function radio_existing( $options = array() ) {

		$address = it_exchange_get_current_cart()->get_billing_address();
		$current = $address instanceof ITE_Saved_Address ? $address->get_pk() : 0;

		$options = ITUtility::merge_defaults( $options, array(
			'current' => $current,
			'name' => 'saved_address'
		) );

		return it_exchange( 'address', 'get-radio-input', $options );
	}

	/**
	 * New Radio input.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function radio_new( $options = array() ) {

		$options = ITUtility::merge_defaults( $options, array(
			'label' => __( 'New Address', 'it-l10n-ithemes-exchange' ),
			'name'  => 'saved_address',
		) );

		$address = it_exchange_get_current_cart()->get_billing_address();
		$checked = checked( ! $address instanceof ITE_Saved_Address, true, false );

		return "<label>" .
		       "<input type='radio' class='ninja-shop-new-address--billing' name='{$options['name']}' value='0'{$checked}>" .
		       $options['label'] .
		       "</label>";
	}

	/**
	 * Is the billing address a saved address.
	 *
	 *
	 *
	 * @return bool
	 */
	public function saved_address() {
		return it_exchange_get_current_cart()->get_billing_address() instanceof ITE_Saved_Address;
	}
}
