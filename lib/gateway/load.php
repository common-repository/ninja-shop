<?php
/**
 * Load the gateway API.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/util/interface.payment-source.php';
require_once dirname( __FILE__ ) . '/util/class.card.php';
require_once dirname( __FILE__ ) . '/util/class.account.php';

require_once dirname( __FILE__ ) . '/interface.request.php';
require_once dirname( __FILE__ ) . '/interface.request-handler.php';
require_once dirname( __FILE__ ) . '/class.request-factory.php';

require_once dirname( __FILE__ ) . '/requests/class.purchase.php';
require_once dirname( __FILE__ ) . '/requests/class.tokenize.php';
require_once dirname( __FILE__ ) . '/requests/class.update-token.php';
require_once dirname( __FILE__ ) . '/requests/class.webhook.php';
require_once dirname( __FILE__ ) . '/requests/class.refund.php';

require_once dirname( __FILE__ ) . '/handlers/class.purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.dialog-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.iframe-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.redirect-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/class.post-purchase.php';
require_once dirname( __FILE__ ) . '/handlers/interface.js-tokenize.php';
require_once dirname( __FILE__ ) . '/handlers/interface.update-token.php';

require_once dirname( __FILE__ ) . '/class.gateway.php';
require_once dirname( __FILE__ ) . '/class.gateways.php';

add_action( 'ninja_shop_enabled_addons_loaded', function () {

	/**
	 * Register payment gateways.
	 *
	 *
	 *
	 * @param \ITE_Gateways $gateways
	 */
	do_action( 'ninja_shop_register_gateways', new ITE_Gateways() );
} );
