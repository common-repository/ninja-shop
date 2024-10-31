<?php
/**
 * Load the refunds module.
 *
 * 
 * @license GPLv2
 */

use IronBound\DB\Extensions\Meta\BaseMetaTable;
use IronBound\DB\Manager;

require_once dirname( __FILE__ ) . '/class.refund.php';
require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.object-type.php';

Manager::register( new ITE_Refunds_Table(), '', 'ITE_Refund' );
Manager::register( new BaseMetaTable( Manager::get( 'ninja-shop-refunds' ), array(
	'primary_id_column' => 'refund_id'
) ) );

add_action( 'ninja_shop_register_object_types', function ( ITE_Object_Type_Registry $registry ) {
	$registry->register( new ITE_Refund_Object_Type() );
} );
