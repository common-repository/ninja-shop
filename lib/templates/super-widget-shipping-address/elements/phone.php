<?php
/**
 * This is the default template part for the
 * phone element in the shipping-address
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
<?php do_action( 'ninja_shop_super_widget_shipping_address_purchase_requirement_before_phone_element' ); ?>
<div class="ninja-shop-phone">
	<?php it_exchange( 'shipping', 'phone' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_shipping_address_purchase_requirement_after_phone_element' ); ?>
