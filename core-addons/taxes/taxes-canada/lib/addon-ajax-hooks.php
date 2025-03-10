<?php
/**
 * Includes all of our AJAX functions
 * 
 * @package exchange-addon-easy-canadian-sales-taxes
*/

/**
 * AJAX function called to add new content access rule rows
 *
 *
 * @return string HTML output of content access rule row div
*/
function it_exchange_easy_canadian_sales_taxes_addon_ajax_add_new_rate() {
	
	$return = '';
	
	if ( isset( $_REQUEST['count'] ) ) { //use isset() in case count is 0
		
		$count = $_REQUEST['count'];

		die( it_exchange_easy_canadian_sales_taxes_get_tax_row_settings( $count ) );		
	
	}
	
	die( $return );
}
add_action( 'wp_ajax_it-exchange-easy-canadian-sales-taxes-addon-add-new-rate', 'it_exchange_easy_canadian_sales_taxes_addon_ajax_add_new_rate' );