<?php
/**
 * Load the location module.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/interface.location.php';
require_once dirname( __FILE__ ) . '/class.table.php';
require_once dirname( __FILE__ ) . '/class.saved-address.php';
require_once dirname( __FILE__ ) . '/class.in-memory-address.php';

require_once dirname( __FILE__ ) . '/interface.zone.php';
require_once dirname( __FILE__ ) . '/class.simple-zone.php';
require_once dirname( __FILE__ ) . '/class.multidimensional-zone.php';

require_once dirname( __FILE__ ) . '/interface.validator.php';
require_once dirname( __FILE__ ) . '/class.validators.php';

require_once dirname( __FILE__ ) . '/class.state-matches-country.php';

add_filter( 'ninja_shop_cart_validators', function ( $validators ) {
	return array_merge( $validators, ITE_Location_Validators::all() );
} );

\IronBound\DB\Manager::register( new ITE_Saved_Address_Table() );

ITE_Location_Validators::add( new ITE_Location_State_Matches_Country_Validator() );
