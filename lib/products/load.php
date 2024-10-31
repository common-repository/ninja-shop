<?php
/**
 * Load the products module.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/class.products-post-type.php';
require_once dirname( __FILE__ ) . '/class.product.php';
require_once dirname( __FILE__ ) . '/class.factory.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

add_action( 'ninja_shop_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Product_Object_Type() );
} );
