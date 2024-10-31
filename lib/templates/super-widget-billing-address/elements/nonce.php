<?php
/**
 * This is the default template part for the
 * nonce element in the billing-address
 * purchase-requriements in the super-widget-billing-address template part.
 *
 *
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/super-widget-billing-address/elements/
 * directory located in your theme.
*/
?>

<?php wp_nonce_field( 'ninja-shop-update-checkout-billing-address-' . it_exchange_get_session_id(), 'ninja-shop-update-billing-address' ); ?>
