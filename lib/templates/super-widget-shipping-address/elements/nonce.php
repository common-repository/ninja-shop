<?php
/**
 * This is the default template part for the
 * nonce element in the shipping-address
 * purchase-requriements in the super-widget-shipping-address template part.
 *
 *
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/super-widget-shipping-address/elements/
 * directory located in your theme.
*/
?>
<?php wp_nonce_field( 'ninja-shop-update-checkout-shipping-address-' . it_exchange_get_session_id(), 'ninja-shop-update-shipping-address' ); ?>
