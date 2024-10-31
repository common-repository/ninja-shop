<?php
/**
 * Registers all add-ons shipped with Ninja Shop
 *
 *
 * @uses apply_filters()
 * @uses it_exchange_register_add_on()
 * @return void
*/
function it_exchange_register_core_addons() {

	// An array of add-ons provided by Ninja Shop
	$add_ons = array(

		/*
    |--------------------------------------------------------------------------
    | Offline Payments
    |--------------------------------------------------------------------------
    */

		'offline-payments' => array(
			'name'              => __( 'Offline Payments', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Process transactions offline via check or cash.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				=> '<i class="fas fa-money-bill"></i>',
			'file'              => dirname( __FILE__ ) . '/transaction-methods/offline-payments/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'auto-enable'		=> false,
			// 'settings-callback' => 'it_exchange_offline_payments_settings_callback',
		),

		/*
		|--------------------------------------------------------------------------
		| Zero Sum Checkout
		|--------------------------------------------------------------------------
		|
		| For situations when the Cart Total is 0 (free), we still want to record the transaction!
		|
		*/

		'zero-sum-checkout' => array(
			'name'              => __( 'Zero Sum Checkout', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Used for processing 0 sum checkout (free).', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'file'              => dirname( __FILE__ ) . '/transaction-methods/zero-sum-checkout/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'required',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| PayPal Payments Standard (Basic)
		|--------------------------------------------------------------------------
		*/

		'paypal-standard' => array(
			'name'              => __( 'PayPal Payments Standard - Basic', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This is the simple and fast version to get PayPal setup for your store. You might use this version just to get your store going, but we highly suggest you switch to the PayPal Payments Standard - Secure option.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'              => '<i class="fab fa-cc-paypal"></i>',
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/transaction-methods/paypal-standard/images/wizard-paypal.png' ),
			'file'              => dirname( __FILE__ ) . '/transaction-methods/paypal-standard/init.php',
			'category'          => 'transaction-methods',
			'tag'               => 'core',
			'auto-enable'		=> false,
			// 'settings-callback' => 'it_exchange_paypal_standard_settings_callback',
		),

		/*
		|--------------------------------------------------------------------------
		| Digital Download Product Types
		|--------------------------------------------------------------------------
		*/

		'digital-downloads-product-type' => array(
			'name'              => __( 'Digital Downloads', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This adds a product type for distributing digital downloads through Ninja Shop.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/product-types/digital-downloads/images/wizard-downloads.png' ),
			'file'              => dirname( __FILE__ ) . '/product-types/digital-downloads/init.php',
			'category'          => 'product-type',
			'tag'               => 'core',
			'labels'            => array(
				'singular_name' => __( 'Digital Download', 'ninja-shop' ),
			),
			'supports'          => apply_filters( 'ninja_shop_register_digital_downloads_default_features', array(
				'inventory' => false,
				'sw-shortcode' => true
			) ),
			'settings-callback' => 'it_exchange_digital_downloads_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Simple Product Types
		|--------------------------------------------------------------------------
		*/

		'simple-product-type' => array(
			'name'        => __( 'Simple Products', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This is a basic product type for selling simple items.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'file'        => dirname( __FILE__ ) . '/product-types/simple-products/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Simple Product', 'ninja-shop' ),
			),
			'supports' => array(
				'sw-shortcode' => true
			),
			'auto-enable' => false,
		),

		/*
		|--------------------------------------------------------------------------
		| Physical Product Type
		|--------------------------------------------------------------------------
		*/

		'physical-product-type' => array(
			'name'        => __( 'Physical Products', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Products you can put your hands on. Things you might want to ship.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'wizard-icon' => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/product-types/physical-products/images/wizard-physical.png' ),
			'file'        => dirname( __FILE__ ) . '/product-types/physical-products/init.php',
			'category'    => 'product-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Physical Product', 'ninja-shop' ),
			),
			'supports' => array(
				'sw-shortcode' => true
			),
			'auto-enable' => false,
		),
		//

		/*
		|--------------------------------------------------------------------------
		| Switch Product Types
		|--------------------------------------------------------------------------
		|
		| Product Type admin Metabox
		|
		*/

		'switch-product-type-metabox' => array(
			'name'        => __( 'Switch Product Types', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Gives Store Owners the ability to change a Product Type after creation of the Product via the Advanced options', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'file'        => dirname( __FILE__ ) . '/admin/product-type-metabox/init.php',
			'tag'         => 'required',
			'options'     => array( 'category' => 'admin' ),
			'auto-enable' => true,
		),

		/*
		|--------------------------------------------------------------------------
		| Multi item cart
		|--------------------------------------------------------------------------
		*/

		'multi-item-cart-option' => array(
			'name'              => __( 'Multi-item Cart', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enabling this add-on allows your customers to purchase multiple products with one transaction.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				=> '<i class="fas fa-shopping-cart"></i>',
			'file'              => dirname( __FILE__ ) . '/admin/multi-item-cart/init.php',
			'category'          => 'admin',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_multi_item_cart_settings_callback',
			'supports'          => apply_filters( 'ninja_shop_register_multi_item_cart_default_features', array(
			) ),
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Guest Checkout
		|--------------------------------------------------------------------------
		*/

		'guest-checkout' => array(
			'name'              => __( 'Guest Checkout', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enabling this add-on gives customers the ability to checkout as a guest, without registering', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				      => '<i class="fas fa-question-circle"></i>',
			'file'              => dirname( __FILE__ ) . '/admin/guest-checkout/init.php',
			'category'          => 'admin',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_guest_checkout_settings_callback',
			'supports'          => apply_filters( 'ninja_shop_register_guest_checkout_default_features', array(
			) ),
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Terms of Service
		|--------------------------------------------------------------------------
		*/

		'terms-of-service'      => array(
			'name'              => __( 'Terms of Service', 'it-l10n-ithemes-exchange'),
			'description'       => __( 'Have your customers agree to your Terms of Service when purchasing your products.', 'it-l10n-ithemes-exchange'),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'file'              => dirname( __FILE__ ) . '/admin/terms-of-service/init.php',
			'icon'              => '<i class="fas fa-handshake"></i>',
			'category'          => 'product-feature',
			'settings-callback' => array( 'ITETOS\Settings', 'display' ),
			'basename'          => plugin_basename( __FILE__ ),
			'labels'            => array(
				'singular_name' => __( 'Terms of Service', 'it-l10n-ithemes-exchange'),
			),
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Page Templates
		|--------------------------------------------------------------------------
		*/

		'page-template' => array(
			'name'              => __( 'WordPress Page Templates', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Enable the WordPress Page Templates meta box for products', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'file'              => dirname( __FILE__ ) . '/product-features/page-templates/init.php',
			'category'          => 'admin',
			'tag'               => 'required',
			'supports'          => apply_filters( 'ninja_shop_register_page_template_default_features', array(
			) ),
			'auto-enable'		=> true,
		),

		/*
		|--------------------------------------------------------------------------
		| Billing Address Purchase Requirement
		|--------------------------------------------------------------------------
		*/

		// 'billing-address-purchase-requirement' => array(
		// 	'name'        => __( 'Billing Address', 'it-l10n-ithemes-exchange' ),
		// 	'description' => __( 'Enabling this add-on allows you to collect a billing address at checkout. There are no settings for this add-on.', 'it-l10n-ithemes-exchange' ),
		// 	'author'      => 'Ninja Shop',
		// 	'author_url'  => 'http://ninjashop.site',
		// 	'file'        => dirname( __FILE__ ) . '/admin/billing-address/init.php',
		// 	'category'    => 'admin',
		// 	'tag'         => 'core',
		// 	'auto-enable'		=> false,
		// ),

		/*
		|--------------------------------------------------------------------------
		| Basic Reporting Dashboard Widget
		|--------------------------------------------------------------------------
		*/

		'basic-reporting' => array(
			'name'        => __( 'Basic Reporting Dashboard Widget', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Adds a widget to the Admin dashboard to give basic sales statistics.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'file'        => dirname( __FILE__ ) . '/admin/basic-reporting/init.php',
			'category'    => 'admin',
			'tag'         => 'required',
			'supports'    => apply_filters( 'ninja_shop_register_basic_reporting_default_features', array(
			) ),
			'auto-enable' => true,
		),

		/*
		|--------------------------------------------------------------------------
		| Product Categories (Taxonomy)
		|--------------------------------------------------------------------------
		*/

		'taxonomy-type-category' => array(
			'name'        => __( 'Product Categories', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This adds a category taxonomy for all products in Ninja Shop.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'icon'		  => '<i class="fas fa-folder-open"></i>',
			'file'        => dirname( __FILE__ ) . '/product-features/categories/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Category', 'ninja-shop' ),
			),
			'auto-enable' => false,
		),

		/*
		|--------------------------------------------------------------------------
		| Product Tags (Taxonomy)
		|--------------------------------------------------------------------------
		*/

		'taxonomy-type-tag' => array(
			'name'        => __( 'Product Tags', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'This adds a tag taxonomy for all products in Ninja Shop.', 'it-l10n-ithemes-exchange' ),
			'author'      => 'Ninja Shop',
			'author_url'  => 'http://ninjashop.site',
			'icon'		  => '<i class="fas fa-tags"></i>',
			'file'        => dirname( __FILE__ ) . '/product-features/tags/init.php',
			'category'    => 'taxonomy-type',
			'tag'         => 'core',
			'labels'      => array(
				'singular_name' => __( 'Product Tag', 'ninja-shop' ),
			),
			'auto-enable' => false,
		),

		/*
		|--------------------------------------------------------------------------
		| Simple Taxes
		|--------------------------------------------------------------------------
		*/

		'taxes-simple' => array(
			'name'              => __( 'Simple Taxes', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This gives the admin ability to apply a default tax rate to all sales.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				=> '<i class="fas fa-percent"></i>',
			'file'              => dirname( __FILE__ ) . '/taxes/taxes-simple/init.php',
			'category'          => 'taxes',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_taxes_simple_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Basic US Sales Taxes
		|--------------------------------------------------------------------------
		*/

		'basic-us-sales-taxes' => array(
			'name'              => __( 'Basic US Sales Taxes', 'LION' ),
			'description'       => __( 'Adds support for US State tax rates to Ninja Shop.', 'ninja-shop' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'https://ninjashop.site/',
			'icon'              => '<i class="fas fa-percent"></i>',
			'tag'               => 'core',
			'file'              => dirname( __FILE__ ) . '/taxes/taxes-united-states/init.php',
			'category'          => 'taxes',
			'settings-callback' => 'it_exchange_basic_us_sales_taxes_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Easy Canadian Sales Taxes
		|--------------------------------------------------------------------------
		*/

		'easy-canadian-sales-taxes' => array(
			'name'              => __( 'Easy Canadian Sales Taxes', 'LION' ),
			'description'       => __( 'Now store owners can charge the proper tax for each of their product types, regardless of where their customers live in the Canada.', 'LION' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'https://ninjashop.site/easy-canadian-sales-taxes/',
			'icon'              => '<i class="fas fa-percent"></i>',
			'tag'               => 'core',
			'wizard-icon'       => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/lib/images/taxes50px.png' ),
			'file'              => dirname( __FILE__ ) . '/taxes/taxes-canada/init.php',
			'category'          => 'taxes',
			'basename'          => plugin_basename( __FILE__ ),
			'labels'      => array(
				'singular_name' => __( 'Easy Canadian Sales Taxes', 'LION' ),
			),
			'settings-callback' => 'it_exchange_easy_canadian_sales_taxes_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Easy EU Value Added Taxes
		|--------------------------------------------------------------------------
		*/

		'easy-eu-value-added-taxes' => array(
			'name'              => __( 'Easy EU Value Added Taxes', 'LION' ),
			'description'       => __( 'Now store owners in the EU can now charge the proper Value Added Tax for each of their product types.', 'LION' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'https://ninjashop.site/easy-eu-value-added-taxes/',
			'icon'              => '<i class="fas fa-euro-sign"></i>',
			'tag'               => 'core',
			'file'              => dirname( __FILE__ ) . '/taxes/taxes-eu-vat/init.php',
			'category'          => 'taxes',
			'labels'      => array(
				'singular_name' => __( 'Easy EU Value Added Taxes', 'LION' ),
			),
			'settings-callback' => 'it_exchange_easy_eu_value_added_taxes_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Duplicate Products
		|--------------------------------------------------------------------------
		*/

		'duplicate-products' => array(
			'name'              => __( 'Duplicate Products', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'This gives the admin the ability to duplicate an existing product.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'file'              => dirname( __FILE__ ) . '/product-features/duplicate-products/init.php',
			'category'          => 'other',
			'tag'               => 'required',
			'labels'      		=> array(
				'singular_name' => __( 'Duplicate', 'ninja-shop' ),
			),
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Simple Shipping
		|--------------------------------------------------------------------------
		*/

		'simple-shipping'       => array(
			'name'              => __( 'Simple Shipping', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Flat rate and free shipping for your physical products', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				=> '<i class="fas fa-truck"></i>',
			'file'              => dirname( __FILE__ ) . '/shipping/simple-shipping/init.php',
			'category'          => 'shipping',
			'tag'               => 'core',
			'settings-callback' => 'it_exchange_simple_shipping_settings_callback',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Customer Order Notes
		|--------------------------------------------------------------------------
		*/

		'customer-order-notes'  => array(
			'name'              => __( 'Customer Order Notes', 'it-l10n-ithemes-exchange' ),
			'description'       => __( 'Allow your customers to leave a note while placing an order.', 'it-l10n-ithemes-exchange' ),
			'author'            => 'Ninja Shop',
			'author_url'        => 'http://ninjashop.site',
			'icon'				=> '<i class="fas fa-user"></i>',
			'file'              => dirname( __FILE__ ) . '/admin/customer-order-notes/init.php',
			'category'          => 'other',
			'tag'               => 'core',
			'auto-enable'		=> false,
		),

		/*
		|--------------------------------------------------------------------------
		| Opinionated Styles
		|--------------------------------------------------------------------------
		| @NOTE Hide Opinionated Styles until fully implemented
		| @LINK https://git.saturdaydrive.io/ninja-shop/ninja-shop/issues/102
		| @TODO When re-introducing, enable via the Wizard. Leave `auto-enable` as `false`.
		*/

		// 'opinionated-styles'    => array(
		// 	'name'              => __( 'Opinionated Styles', 'it-l10n-ithemes-exchange' ),
		// 	'description'       => __( 'Use default Ninja Shop styling conventions.', 'it-l10n-ithemes-exchange' ),
		// 	'author'            => 'Ninja Shop',
		// 	'author_url'        => 'http://ninjashop.site',
		// 	'icon'				=> '<i class="fas fa-palette"></i>',
		// 	'file'              => dirname( __FILE__ ) . '/opinionated-styles/init.php',
		// 	'category'          => 'other',
		// 	'tag'               => 'core',
		// 	'settings-callback' => 'it_exchange_opinionated_styles_settings_callback',
		// 	'auto-enable'		    => false,
		// ),

	);
	$add_ons = apply_filters( 'ninja_shop_core_addons', $add_ons );

	// Loop through add-ons and register each one individually
	foreach( (array) $add_ons as $slug => $params )
		it_exchange_register_addon( $slug, $params );
}
add_action( 'ninja_shop_register_addons', 'it_exchange_register_core_addons' );

/**
 * Register's Core Ninja Shop Add-on Categories
 *
 *
 * @uses it_exchange_register_add_on_category()
 * @return void
*/
function it_exchange_register_core_addon_categories() {

	// An array of our core add-on categories
	$cats = array(
		'product-type' => array(
			'name'        => __( 'Product Type', 'it-l10n-ithemes-exchange' ),
			'description' => __( 'Add-ons responsible for the differing types of products available in Ninja Shop.', 'it-l10n-ithemes-exchange' ),
			'options'     => array(
			),
		),
		'transaction-methods' => array(
			'name'        => __( 'Transaction Methods', 'ninja-shop' ),
			'description' => __( 'Add-ons that create transactions. eg: Stripe, PayPal.', 'ninja-shop' ),
			'options'     => array(
				'supports' => apply_filters( 'ninja_shop_register_transaction_method_supports', array(
					'title' => array(
						'key'       => 'post_title',
						'componant' => 'post_type_support',
						'default'   => false,
					),
					'transaction_status' => array(
						'key'       => '_it_exchange_transaction_status',
						'componant' => 'post_meta',
						'options'   => array(
							'pending'    => _x( 'Pending', 'Transaction Status', 'ninja-shop' ),
							'authorized' => _x( 'Authorized', 'Transaction Status', 'ninja-shop' ),
							'paid'       => _x( 'Paid', 'Transaction Status', 'ninja-shop' ),
							'refunded'   => _x( 'Refunded', 'Transaction Status', 'ninja-shop' ),
							'voided'     => _x( 'Voided', 'Transaction Status', 'ninja-shop' ),
						),
						'default'   => 'pending',
					)
				) ),
			),
		),
		'admin' => array(
			'name'        => __( 'Admin Add-ons', 'ninja-shop' ),
			'description' => __( 'Add-ons that create general purpose admin functionality. eg: Reports, Export.', 'ninja-shop' ),
			'options'     => array(),
		),
		'coupons' => array(
			'name'        => __( 'Coupon Add-ons', 'ninja-shop' ),
			'description' => __( 'Add-ons that create coupons for your customers.', 'ninja-shop' ),
			'options'     => array(),
		),
		'taxonomy-type' => array(
			'name'        => __( 'Taxonomy Add-ons', 'ninja-shop' ),
			'description' => __( 'Add-ons that create new taxonomies specifically for Exchange products.', 'ninja-shop' ),
			'options'     => array(),
		),
		'email' => array(
			'name'        => __( 'Email', 'ninja-shop' ),
			'description' => __( 'Add-ons that help store owners manage their email.', 'ninja-shop' ),
			'options'     => array(),
		),
		'other' => array(
			'name'        => __( 'Other', 'ninja-shop' ),
			'description' => __( 'Add-ons that don\'t fit in any other add-on category.', 'ninja-shop' ),
			'options'     => array(),
		),
	);
	$cats = apply_filters( 'ninja_shop_core_addon_categories', $cats );

	// Loop through categories and register each one individually
	foreach( (array) $cats as $slug => $params ) {
		$name        = empty( $params['name'] )        ? false   : $params['name'];
		$description = empty( $params['description'] ) ? ''      : $params['description'];
		$options     = empty( $params['options'] )     ? array() : (array) $params['options'];

		it_exchange_register_addon_category( $slug, $name, $description, $options );
	}
}
add_action( 'ninja_shop_libraries_loaded', 'it_exchange_register_core_addon_categories' );
