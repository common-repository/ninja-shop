<?php

/**
 * This class get initiated when a shipping add-on is enabled
 * @package IT_Exchagne
 * 
 */
class IT_Exchange_Shipping {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// We need to include the abstract methods class regardless
		include_once( dirname( __FILE__ ) . '/class-method.php' );
		include_once( dirname( __FILE__ ) . '/class-shipping-feature.php' );

		add_action( 'ninja_shop_enabled_addons_loaded', array( $this, 'maybe_init' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	public function IT_Exchange_Shipping() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	public function maybe_init() {
		$enabled_shipping_addons = (boolean) it_exchange_get_enabled_addons( array( 'category' => 'shipping' ) );
		if ( ! $enabled_shipping_addons ) {
			return;
		} //If not shipping addons, just exit this class

		// Init core shipping features
		include_once( dirname( __FILE__ ) . '/shipping-features/init.php' );

		add_action( 'template_redirect', array( $this, 'update_cart_shipping_method' ), 99 );

		add_action( 'ninja_shop_print_general_settings_tab_links', array( $this, 'print_shipping_tab_link' ) );
		add_filter( 'ninja_shop_general_settings_tab_callback_shipping', array( $this, 'register_settings_tab_callback' ) );

		// Setup purchase requirement
		add_action( 'init', array( $this, 'init_shipping_address_purchase_requirements' ) );
		add_filter( 'ninja_shop_get_next_purchase_requirement_shipping-address_notification', array( $this, 'shipping_address_requirement_notification' ) );

		// Template part filters
		add_filter( 'ninja_shop_get_content_checkout_totals_elements', array( $this, 'add_shipping_to_template_totals_loops' ) );
		add_filter( 'ninja_shop_get_super-widget-checkout_after-cart-items_loops', array( $this, 'add_shipping_address_to_sw_template_totals_loops' ) );
		add_filter( 'ninja_shop_get_super-widget-checkout_after-cart-items_loops', array( $this, 'add_shipping_method_to_sw_template_totals_loops' ) );
		add_filter( 'ninja_shop_get_super-widget-checkout_after-cart-items_loops', array( $this, 'add_shipping_to_template_totals_loops' ) );

		// Ajax Request to update shipping address
		add_action( 'ninja_shop_processing_super_widget_ajax_update-shipping', array( $this, 'process_ajax_request' ) );

		// Process the update address request
		add_action( 'template_redirect', array( $this, 'process_update_address_request' ) );

		// Clear the cart address when the cart is cleared
		add_action( 'ninja_shop_empty_cart', array( $this, 'clear_cart_address' ) );

		// Updates the general settings states field in the admin
		add_action( 'ninja_shop_admin_country_states_sync_for_shipping-general', array( $this, 'update_general_settings_state_field' ) );

		// Enqueue the JS for the checkout page
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_page_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_js' ) );

		// Add var to checkout header
		add_action( 'wp_head', array( $this, 'add_js_to_checkout_header' ) );

		// Remove Shipping information from cart data when cart is emptied or when item is added to cart
		add_action( 'ninja_shop_empty_cart', array( $this, 'clear_cart_shipping_data_on_empty' ) );
		add_action( 'ninja_shop_add_product_to_cart', array( $this, 'clear_cart_shipping_data' ), 10, 2 );
		add_action( 'ninja_shop_remove_product_from_cart', array( $this, 'clear_cart_shipping_data' ), 10, 2 );
		add_action( 'ninja_shop_set_cart_shipping_address', array( $this, 'clear_cart_shipping_method' ) );

		add_action( 'ninja_shop_replace_order_table_tag_before_total_row', array( $this, 'add_shipping_to_order_table_tag_before_total_row' ), 10, 2 );

	}

	/**
	 * Init Shipping Address Purchase Requirement
	 *
	 */
	public function init_shipping_address_purchase_requirements() {

		if ( is_admin() ) {
			return;
		}

		if ( ! it_exchange_get_current_cart( false ) ) {
			return;
		}

		self::register_shipping_address_purchase_requirement();
		self::register_shipping_method_purchase_requirement();
	}

	/**
	 * Registers the shipping address purchase requirement
	 *
	 * Use the it_exchange_register_purchase_requirement public function to tell exchange
	 * that your add-on requires certain conditionals to be set prior to purchase.
	 * For more details see api/misc.php
	 *
	 *
     *
	 * @return void
	 */
	public static function register_shipping_address_purchase_requirement() {
		// User must have a shipping address to purchase
		$properties = array(
			'requirement-met'        => function() {
				$address = it_exchange_get_cart_shipping_address();

				if ( ! $address || empty( $address['address1'] ) ) {
				    return false;
                }

                $methods = it_exchange_get_available_shipping_methods_for_cart_products();

				if ( ! $methods ) {
				    return false;
                }

                return true;
			},
			'sw-template-part'       => 'shipping-address',
			'checkout-template-part' => 'shipping-address',
			'notification'           => __( 'You must enter a shipping address before you can checkout', 'it-l10n-ithemes-exchange' ),
			'priority'               => 5.12
		);

		if ( it_exchange_get_current_cart()->requires_shipping() ) {
			it_exchange_register_purchase_requirement( 'shipping-address', $properties );
		}
	}

	/**
     * Adjust the shipping address purchase requirement notification to distinguish between no shipping address entered
     * at all and no shipping methods available to that shipping address.
     *
     *
     *
	 * @param string $notification
	 *
	 * @return string
	 */
	public function shipping_address_requirement_notification( $notification ) {

		$address = it_exchange_get_cart_shipping_address();

		if ( ! $address || empty( $address['address1'] ) ) {
			return $notification;
		}

		$methods = it_exchange_get_available_shipping_methods_for_cart_products();

		if ( $methods ) {
		    return $notification;
		}

		return __( 'No shipping methods are available for your selected address.', 'it-l10n-ithemes-exchange' );
    }

	/**
	 * Registers the shipping method purchase requirement
	 *
	 * Use the it_exchange_register_purchase_requirement public function to tell exchange
	 * that your add-on requires certain conditionals to be set prior to purchase.
	 * For more details see api/misc.php
	 *
	 *
     *
	 * @return void
	 */
	public static function register_shipping_method_purchase_requirement() {
		// User must have a shipping address to purchase
		$properties = array(
			'requirement-met'        => __CLASS__ . '::method_purchase_requirement_complete', // This is a PHP callback
			'sw-template-part'       => 'shipping-method',
			'checkout-template-part' => 'shipping-method',
			'notification'           => __( 'You must select a shipping method before you can checkout', 'it-l10n-ithemes-exchange' ),
			'priority'               => 5.13,
		);

		$enabled = apply_filters( 'ninja_shop_shipping_address_purchase_requirement_enabled', false );

		if ( $enabled || it_exchange_get_current_cart()->requires_shipping() ) {
			it_exchange_register_purchase_requirement( 'shipping-method', $properties );
		}
	}

	/**
	 * Determine if the shipping method purchase requirement is complete.
	 *
	 *
	 *
	 * @return bool
	 */
	public static function method_purchase_requirement_complete() {
		$method = it_exchange_get_cart_shipping_method();

		if ( ! $method ) {
			return false;
		}

		if ( $method !== 'multiple-methods' ) {
		    return true;
        }

        $cart = it_exchange_get_current_cart();

		/** @var ITE_Cart_Product $product */
		foreach ( $cart->get_items( 'product' ) as $product ) {
		    if ( $product->get_product()->has_feature( 'shipping' ) && ! $cart->get_shipping_method( $product ) ) {
		        return false;
            }
        }

		return true;
	}

	/**
	 * Prints the Shipping tab on the Exchange Settings admin page
	 *
	 *
	 *
	 * @param  string $current_tab the current tab being requested
	 *
	 * @return void
	 */
	public function print_shipping_tab_link( $current_tab ) {
		$active = 'shipping' == $current_tab ? 'nav-tab-active' : '';
		?>
		<a class="nav-tab <?php echo $active; ?>" href="<?php echo admin_url( 'admin.php?page=it-exchange-settings&tab=shipping' ); ?>"><?php _e( 'Shipping', 'it-l10n-ithemes-exchange' ); ?></a><?php
	}

	/**
	 * Register the callback for the settings page
	 *
	 * I hate that this was setup like this. Seems like an uneeded public function
	 *
	 *
	 *
	 * @return string the callback
	 */
	public function register_settings_tab_callback() {
		return apply_filters( 'ninja_shop_shipping_register_settings_tab_callback', array(
			$this,
			'print_shipping_tab'
		) );
	}

	/**
	 * Prints the contents of the Shipping Tab
	 *
	 * First looks to see if a registered shipping provider's settins are being requested
	 * If so, it inits those fields.
	 * If not, it loads the general shipping settings
	 *
	 *
	 *
	 * @return void
	 */
	public function print_shipping_tab() {
		$settings = it_exchange_get_option( 'addon_shipping', true );

		?>
		<div class="wrap">
			<?php
			ITUtility::screen_icon( 'it-exchange' );
			// Print Admin Settings Tabs
			$GLOBALS['IT_Exchange_Admin']->print_general_settings_tabs();

			// Print shipping provider tabs
			$this->print_provider_settings_tabs();

			// Print active shipping page
			$provider          = ( ! empty( $_GET['provider'] ) && it_exchange_is_shipping_provider_registered( $_GET['provider'] ) ) ? it_exchange_get_registered_shipping_provider( $_GET['provider'] ) : 'shipping-general';
			$prefix            = is_object( $provider ) ? $provider->slug : 'shipping-general';
			$action            = add_query_arg( array(
				'page' => 'it-exchange-settings',
				'tab'  => 'shipping'
			), admin_url( 'admin.php' ) );
			$action            = is_object( $provider ) ? add_query_arg( array( 'provider' => $provider->slug ), $action ) : $action;
			$fields            = is_object( $provider ) ? $provider->provider_settings : $this->get_general_settings_fields();
			$country_states_js = is_object( $provider ) ? $provider->country_states_js : $this->get_general_settings_country_states_js();

			// Set admin setting form class options
			$options = array(
				'prefix'       => $prefix,
				'form-options' => array(
					'action'            => esc_url( $action ),
					'country-states-js' => $country_states_js,
				),
				'form-fields'  => $fields,
			);
			it_exchange_print_admin_settings_form( $options );
			?>
		</div>
		<?php
	}

	/**
	 * Prints the tabs for all registered shipping providers
	 *
	 *
	 *
	 * @return html
	 */
	public function print_provider_settings_tabs() {

		// Return empty string if there aren't any registered shipping providers
		if ( ! $providers = it_exchange_get_registered_shipping_providers() ) {
			return '';
		}

		// Set the currently requested shipping provider tab. Defaults to General
		$current = empty( $_GET['provider'] ) ? false : sanitize_text_field( $_GET['provider'] );
		$current = ( ! empty( $current ) && ! it_exchange_is_shipping_provider_registered( $current ) ) ? false : $current;

		// Print the HTML
		?>
		<div class="it-exchange-secondary-tabs it-exchange-shipping-provider-tabs">
			<a class="shipping-provider-link <?php echo ( empty( $current ) ) ? 'it-exchange-current' : ''; ?>" href="<?php echo esc_url( add_query_arg( array(
				'page' => 'it-exchange-settings',
				'tab'  => 'shipping'
			), admin_url( 'admin.php' ) ) ); ?>">
				<?php _e( 'General', 'it-l10n-ithemes-exchange' ); ?>
			</a>
			<?php
			foreach ( $providers as $provider ) {
				$provider = it_exchange_get_registered_shipping_provider( $provider['slug'] );
				if ( empty( $provider->has_settings_page ) ) {
					continue;
				}
				?>
				<a class="shipping-provider-link<?php echo ( $current == $provider->get_slug() ) ? ' it-exchange-current' : ''; ?>" href="<?php echo esc_url( add_query_arg( array(
					'page'     => 'it-exchange-settings',
					'tab'      => 'shipping',
					'provider' => $provider->get_slug()
				), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( $provider->get_label() ); ?></a><?php
			}
			?>
		</div>
		<?php
	}

	/**
	 * This returns the country-state-js options for general settings
	 *
	 *
	 *
	 * @return array
	 */
	public function get_general_settings_country_states_js() {
		$country_state_option = array(
			'country-id'     => 'product-ships-from-country',
			'states-id'      => '#product-ships-from-state',
			'states-wrapper' => '#product-ships-from-state-wrapper',
		);

		return $country_state_option;
	}

	/**
	 * This returns the settings fields array for general shipping settings
	 *
	 *
	 *
	 * @return array
	 */
	public function get_general_settings_fields() {
		$form_fields = array(
			array(
				'type'  => 'heading',
				'label' => __( 'General Shipping Settings', 'it-l10n-ithemes-exchange' ),
				'slug'  => 'general-shipping-label',
			),
		);

		$from_address     = array();
		$shipping_methods = it_exchange_get_registered_shipping_methods();
		$features         = array();
		foreach ( (array) $shipping_methods as $method => $class ) {
			$method = it_exchange_get_registered_shipping_method( $method );
			if ( ! empty( $method->shipping_features ) && $method->enabled ) {
				foreach ( $method->shipping_features as $feature ) {
					$features[ $feature ] = $feature;
				}
			}
		}
		if ( in_array( 'core-from-address', $features ) ) {
			$from_address = array(
				array(
					'type'    => 'text_box',
					'label'   => __( 'Products Ship From', 'it-l10n-ithemes-exchange' ),
					'slug'    => 'product-ships-from-address1',
					'tooltip' => __( 'The default from address used when shipping your products.', 'it-l10n-ithemes-exchange' ),
					'default' => '',
					'options' => array(
						'class'       => 'large-text',
						'placeholder' => __( 'Address 1', 'it-l10n-ithemes-exchange' ),
					),
				),
				array(
					'type'    => 'text_box',
					'label'   => '',
					'slug'    => 'product-ships-from-address2',
					'default' => '',
					'options' => array(
						'class'       => 'large-text',
						'placeholder' => __( 'Address 2', 'it-l10n-ithemes-exchange' ),
					),
				),
				array(
					'type'    => 'text_box',
					'label'   => '',
					'slug'    => 'product-ships-from-city',
					'default' => '',
					'options' => array(
						'class'       => 'large-text',
						'placeholder' => __( 'City', 'it-l10n-ithemes-exchange' ),
					),
				),
				array(
					'type'    => 'drop_down',
					'label'   => '',
					'slug'    => 'product-ships-from-country',
					'default' => 'US',
					'options' => it_exchange_get_data_set( 'countries' ),
				),
				array(
					'type'    => 'drop_down',
					'label'   => '',
					'slug'    => 'product-ships-from-state',
					'default' => 'NC',
					'options' => it_exchange_get_data_set( 'states', array( 'country' => 'US' ) ),
				),
				array(
					'type'    => 'text_box',
					'label'   => '',
					'slug'    => 'product-ships-from-zip',
					'default' => '',
					'options' => array(
						'class'       => 'normal-text',
						'placeholder' => __( 'Zip', 'it-l10n-ithemes-exchange' ),
					),
				),
				array(
					'type'    => 'yes_no_drop_down',
					'label'   => __( 'Can individual products override the default Ships From Address?', 'it-l10n-ithemes-exchange' ),
					'slug'    => 'products-can-override-ships-from',
					'tooltip' => __( 'Selecting "yes" will place these fields on the Add/Edit product screen.', 'it-l10n-ithemes-exchange' ),
					'default' => '1',
				),
			);
		}
		$form_fields = array_merge( $form_fields, $from_address );

		$form_fields = array_merge( $form_fields, array(
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Can individual products override the global Shipping Methods setting?', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'products-can-override-shipping-methods',
				'tooltip' => __( 'Selecting "yes" will allow you to set available Shipping Methods for a product from it\'s Add/Edit product screen.', 'it-l10n-ithemes-exchange' ),
				'default' => '0',
			),
		) );

		$measurements = array();
		if ( in_array( 'core-dimensions', $features ) || in_array( 'core-weight', $features ) ) {
			$measurements = array(
				array(
					'type'    => 'drop_down',
					'label'   => __( 'Measurements Format', 'it-l10n-ithemes-exchange' ),
					'slug'    => 'measurements-format',
					'tooltip' => __( 'Use standard for pounds and inches. Use metric for kilograms and centimeters.', 'it-l10n-ithemes-exchange' ),
					'default' => 'standard',
					'options' => array(
						'standard' => __( 'Standard', 'it-l10n-ithemes-exchange' ),
						'metric'   => __( 'Metric', 'it-l10n-ithemes-exchange' ),
					),
				),
			);
		}

		$form_fields = array_merge( $form_fields, array(
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Exclude non-shippable products from per-order price calculations', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'exclude_non_shippable',
				'default' => 0
			),
			array(
				'type'    => 'yes_no_drop_down',
				'label'   => __( 'Is shipping taxable?', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'taxable',
				'default' => '1'
			),
		) );

		$form_fields = array_merge( $form_fields, $measurements );

		return $form_fields;
	}

	/**
	 * Add Shipping to the content-cart totals and content-checkout loop
	 *
	 *
	 *
	 * @param array $elements list of existing elements
	 *
	 * @return array
	 */
	public function add_shipping_to_template_totals_loops( $elements ) {

		// Abort of total number of shipping methods available to cart is 0
		if ( ! $this->should_show_shipping_totals() ) {
			return $elements;
		}

		// Locate the discounts key in elements array (if it exists)
		$index = array_search( 'discounts', $elements );
		if ( false === $index ) {
			$index = count( $elements ) - 1;
		}

		array_splice( $elements, $index, 0, 'totals-shipping' );

		return $elements;
	}

	/**
	 * Add Shipping to the super-widget-checkout totals loop
	 *
	 *
	 *
	 * @param array $loops list of existing elements
	 *
	 * @return array
	 */
	public function add_shipping_address_to_sw_template_totals_loops( $loops ) {

		// Abort of total number of shipping methods available to cart is 0
		if ( ! $this->should_show_shipping_totals() ) {
			return $loops;
		}

		$index = array_search( 'billing-address', $loops );
		if ( false === $index ) {
			$index = - 1;
		}

		// Shipping Address
		array_splice( $loops, $index, 0, 'shipping-address' );

		return $loops;
	}

	/**
	 * Should the shipping totals be displayed.
	 *
	 *
	 *
	 * @return bool
	 */
	protected function should_show_shipping_totals() {

		if ( it_exchange_get_cart_shipping_method() === 'multiple-methods' ) {
			return true;
		}

		if ( count( it_exchange_get_available_shipping_methods_for_cart() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add Shipping Method to the super-widget-checkout totals loop
	 *
	 *
	 *
	 * @param array $loops list of existing elements
	 *
	 * @return array
	 */
	public function add_shipping_method_to_sw_template_totals_loops( $loops ) {

		// Abort of total number of shipping methods available to cart is 0
		if ( count( it_exchange_get_available_shipping_methods_for_cart() ) < 1 ) {
			return $loops;
		}

		// Locate the Billing Address or discounts key in elements array (if it exists) and insert before
		$index = array_search( 'billing-address', $loops );
		$index = ( false === $index ) ? array_search( 'shipping-address', $loops ) : $index;
		if ( false === $index ) {
			$index = - 1;
		} else {
			$index ++;
		}

		// Shipping Address
		array_splice( $loops, $index, 0, 'shipping-method' );

		return $loops;
	}

	/**
	 * Process Adding the shipping address to the SW via ajax
	 *
	 * Processes the POST request. If data is good, it updates the DB (where we store the data)
	 * permanantly as well as the session where we store it for the template part.
	 *
	 *
	 *
	 * @return void
	 */
	public function process_ajax_request() {
		// Parse data
		$name     = empty( $_POST['shippingName'] ) ? false : sanitize_text_field( $_POST['shippingName'] );
		$address1 = empty( $_POST['shippingAddress1'] ) ? false : sanitize_text_field( $_POST['shippingAddress1'] );
		$address2 = empty( $_POST['shippingAddress2'] ) ? false : sanitize_text_field( $_POST['shippingAddress2'] );
		$city     = empty( $_POST['shippingCity'] ) ? false : sanitize_text_field( $_POST['shippingCity'] );
		$state    = empty( $_POST['shippingState'] ) ? false : sanitize_text_field( $_POST['shippingState'] );
		$zip      = empty( $_POST['shippingZip'] ) ? false : sanitize_text_field( $_POST['shippingZip'] );
		$country  = empty( $_POST['shippingCountry'] ) ? false : sanitize_text_field( $_POST['shippingCountry'] );
		$customer = empty( $_POST['shippingCustomer'] ) ? false : sanitize_text_field( $_POST['shippingCustomer'] );

		$required_fields = apply_filter( 'ninja_shop_required_shipping_address_fields', array(
			'name',
			'address1',
			'city',
			'state',
			'zip',
			'country',
			'customer'
		) );

		$states = it_exchange_get_data_set( 'states', array( 'country' => $country ) );
		if ( empty( $states ) && $key = array_search( 'state', $required_fields ) ) {
			unset( $required_fields[ $key ] );
		}

		foreach ( $required_fields as $field ) {
			if ( ! $$field ) {
				$invalid = true;
				break;
			}
		}

		// Update object with what we have
		$address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country', 'customer' );
		it_exchange_update_cart_data( 'shipping-address', $address );
		unset( $address['customer'] );

		// Register fail or success
		if ( $invalid ) {
			it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
			die( '0' );
		} else {
			it_exchange_save_shipping_address( $address, $customer );
			die( '1' );
		}
	}

	/**
	 * Process Adding the shipping address to the checkout page via POST request
	 *
	 * Processes the POST request. If data is good, it updates the DB (where we store the data)
	 * permanantly as well as the session where we store it for the template part.
	 *
	 *
	 *
	 * @return void
	 */
	public function process_update_address_request() {

		// Abandon if not processing
		if ( ! it_exchange_is_page( 'checkout' ) || empty( $_POST['it-exchange-shipping-add-address-from-checkout'] ) ) {
			return;
		}

		// Parse data
		$name     = empty( $_POST['it-exchange-addon-shipping-name'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-name'] );
		$address1 = empty( $_POST['it-exchange-addon-shipping-address-1'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-address-1'] );
		$address2 = empty( $_POST['it-exchange-addon-shipping-address-2'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-address-2'] );
		$city     = empty( $_POST['it-exchange-addon-shipping-city'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-city'] );
		$state    = empty( $_POST['it-exchange-addon-shipping-state'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-state'] );
		$zip      = empty( $_POST['it-exchange-addon-shipping-zip'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-zip'] );
		$country  = empty( $_POST['it-exchange-addon-shipping-country'] ) ? false : sanitize_text_field( $_POST['it-exchange-addon-shipping-country'] );

		$required_fields = apply_filters( 'ninja_shop_required_shipping_address_fields', array(
			'name',
			'address1',
			'city',
			'state',
			'zip',
			'country'
		) );

		$states = it_exchange_get_data_set( 'states', array( 'country' => $country ) );
		if ( empty( $states ) && $key = array_search( 'state', $required_fields ) ) {
			unset( $required_fields[ $key ] );
		}

		foreach ( $required_fields as $field ) {
			if ( ! $$field ) {
				$invalid = true;
				break;
			}
		}

		// Update object with what we have
		$address = compact( 'name', 'address1', 'address2', 'city', 'state', 'zip', 'country' );
		it_exchange_update_cart_data( 'shipping-address', $address );

		// Register fail or success
		if ( $invalid ) {
			it_exchange_add_message( 'error', __( 'Please fill out all required fields' ) );
		} else {
			it_exchange_save_shipping_address( $address );
			it_exchange_add_message( 'notice', __( 'Shipping Address Updated' ) );
		}
	}

	/**
	 * Clears the shipping address value when the cart is emptied
	 *
	 *
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return void
	 */
	public function clear_cart_address( ITE_Cart $cart ) {
		if ( $cart->is_current() ) {
			it_exchange_remove_cart_data( 'shipping-address' );
		}
	}

	/**
	 * Adjusts the cart total
	 *
	 *
     * @deprecated 2.0.0
	 *
	 * @param float $total The total passed to us by Exchange.
	 *
	 * @return float
	 */
	public function modify_shipping_total( $total ) {

	    _deprecated_function( __FUNCTION__, '2.0.0' );

		$shipping = it_exchange_get_cart_shipping_cost( false, false );

		return $total + $shipping;
	}

	/**
	 * Enqueue Checkout Page Javascript
	 *
	 *
	 *
	 *
	 * @return void
	 */
	public function enqueue_checkout_page_scripts() {
		if ( it_exchange_is_page( 'checkout' ) ) {
			// Register select to autocomplte
			$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/jquery.select-to-autocomplete.min.js' );
			wp_register_script( 'jquery-select-to-autocomplete', $script, array( 'jquery', 'jquery-ui-autocomplete' ) );

			// Load Shipping Address purchase requirement JS on checkout page.
			$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/js/shipping-purchase-requirement.js' );
			wp_enqueue_script( 'it-exchange-shipping-purchase-requirement', $script, array(
				'jquery',
				'jquery-ui-autocomplete',
				'it-exchange-country-states-sync',
				'jquery-select-to-autocomplete'
			), false, true );

			$style = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/assets/styles/autocomplete.css' );
			wp_register_style( 'it-exchange-autocomplete-style', $style );
			wp_enqueue_style( 'it-exchange-autocomplete-style' );
		}
	}

	/**
	 * Enqueue JS for settings page
	 *
	 *
	 */
	public function enqueue_settings_js() {
		$current_screen = get_current_screen();
		if ( ! empty( $current_screen->base ) && 'ninja-shop_page_it-exchange-settings' == $current_screen->base && ! empty( $_GET['tab'] ) && 'shipping' == $_GET['tab'] ) {
			$script = ITUtility::get_url_from_file( dirname( dirname( __FILE__ ) ) . '/admin/js/settings-shipping.js' );
			wp_enqueue_script( 'it-exchange-settings-shipping', $script, array( 'jquery' ) );
		}
	}

	/**
	 * This public function hooks into the AJAX call generated in general settings for country/states sync
	 *
	 *
	 *
	 * @return void
	 */
	public function update_general_settings_state_field() {
		$base_country = empty( $_POST['ite_base_country_ajax'] ) ? false : sanitize_text_field( $_POST['ite_base_country_ajax'] );
		$base_state   = empty( $_POST['ite_base_state_ajax'] ) ? '' : sanitize_text_field( $_POST['ite_base_state_ajax'] );
		$states       = it_exchange_get_data_set( 'states', array( 'country' => $base_country ) );

		if ( empty( $states ) ) {
			?>
			<input type="text" id="product-ships-from-state" name="shipping-general-product-ships-from-state" maxlength="3" placeholder="<?php _e( 'State', 'it-l10n-ithemes-exchange' ); ?>" class="small-text" value="<?php esc_attr_e( $base_state ); ?>" />&nbsp;
			<?php $open_tag = '<a href="http://en.wikipedia.org/wiki/ISO_3166-2" target="_blank">'; ?>
			<span class="description"><?php printf( __( 'Please use the 2-3 character %sISO 3166-2 Country Subdivision Code%s', 'it-l10n-ithemes-exchange' ), $open_tag, '</a>' ); ?></span>
			<?php
		} else {
			?>
			<select id="product-ships-from-state" name="shipping-general-product-ships-from-state">
			<?php
			foreach ( (array) $states as $key => $value ) {
				?>
				<option value="<?php esc_attr_e( $key ); ?>" <?php selected( $key, $base_state ); ?>><?php esc_html_e( $value ); ?></option><?php
			}
			?></select><?php
		}
		die();
	}

	/**
	 * Update the cart's shipping method.
     *
     *
	 */
	public function update_cart_shipping_method() {

		if ( ! empty( $_GET['ite-checkout-refresh'] ) ) {

			$cart = it_exchange_get_current_cart();

			$cart_product_id = empty( $_POST['cart-product-id'] ) ? '' : absint( $_POST['cart-product-id'] );
			$shipping_method = empty( $_POST['shipping-method'] ) ? '0' : sanitize_text_field( $_POST['shipping-method'] );

			$item = $cart_product_id ? $cart->get_item( 'product', $cart_product_id ) : null;
			$cart->set_shipping_method( $shipping_method, $item );
			it_exchange_get_template_part( 'content-checkout' );
			die();
		}
		// TEMP LOGIC
		if ( isset( $_POST['it-exchange-shipping-method'] ) ) {
		    it_exchange_get_current_cart()->set_shipping_method( sanitize_text_field( $_POST['it-exchange-shipping-method'] ) );
			it_exchange_add_message( 'notice', __( 'Shipping method updated', 'it-l10n-ithemes-exchange' ) );
		}
	}

	/**
	 * Adds some JS vars to the header or the checkout page
	 *
	 *
	 *
	 * @return void
	 */
	public function add_js_to_checkout_header() {
		if ( ! it_exchange_is_page( 'checkout' ) ) {
			return;
		}

		?>
		<script type="text/javascript">
			var ITExchangeCheckoutRefreshAjaxURL = '<?php echo esc_js( get_home_url() ); ?>/?ite-checkout-refresh=1';
		</script>
		<?php
	}

	/**
	 * Clear cart shipping data when the cart is emptied.
	 *
	 *
	 *
	 * @param \ITE_Cart $cart
	 */
	public function clear_cart_shipping_data_on_empty( ITE_Cart $cart ) {
		$cart->remove_all( 'shipping',true );

		if ( $cart->is_current() ) {
			it_exchange_remove_cart_data( 'shipping-address' );
			it_exchange_remove_cart_data( 'shipping-method' );
			it_exchange_remove_cart_data( 'multiple-shipping-methods' );
		}
	}

	/**
	 * Removes all cart_data related to shipping when an item is added or removed from the cart.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 */
	public function clear_cart_shipping_data( ITE_Line_Item $item = null, ITE_Cart $cart = null ) {

		$cart = $cart ?: it_exchange_get_current_cart();

		if ( $cart->is_current() ) {
			it_exchange_remove_cart_data( 'shipping-address' );
			it_exchange_remove_cart_data( 'shipping-method' );
			it_exchange_remove_cart_data( 'multiple-shipping-methods' );
		}

		$cart->remove_all( 'shipping', true );
	}

	/**
	 * Removes teh cart shipping method
	 *
	 *
	 *
	 * @param \ITE_Cart $cart
	 *
	 * @return void
	 */
	public function clear_cart_shipping_method( ITE_Cart $cart = null ) {

		$cart = $cart ?: it_exchange_get_current_cart();

		if ( $cart->is_current() ) {
			it_exchange_remove_cart_data( 'shipping-method' );
			it_exchange_remove_cart_data( 'multiple-shipping-methods' );
		}

		$cart->remove_all( 'shipping', true );
	}

	/**
	 *
	 *
	 *
	 *
	 * @return void
	 */
	public function add_shipping_to_order_table_tag_before_total_row( $email_obj, $options ) {
		$shipping = it_exchange_get_transaction_shipping_total( $email_obj->transaction_id, true );
		if ( ! empty( $shipping ) ) {
			?>
			<tr>
				<td colspan="2" style="padding: 10px;border:1px solid #DDD;"><?php _e( 'Shipping', 'LION' ); ?></td>
				<td style="padding: 10px;border:1px solid #DDD;"><?php echo $shipping; ?></td>
			</tr>
			<?php
		}
	}

}

$GLOBALS['it_exchange']['shipping_object'] = new IT_Exchange_Shipping();

