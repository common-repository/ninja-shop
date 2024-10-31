<?php
/**
 * This is the default template part for the
 * billing element in the billing-address
 * purchase-requriements in the super-widget-billing/elements/shipping template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/super-widget-billing/elements/
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_super_widget_billing_address_purchase_requirement_before_shipping_element' ); ?>
<div class="ninja-shop-billing ninja-shop-clear-left">
	<?php it_exchange( 'billing', 'shipping' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_billing_address_purchase_requirement_after_shipping_element' ); ?>
