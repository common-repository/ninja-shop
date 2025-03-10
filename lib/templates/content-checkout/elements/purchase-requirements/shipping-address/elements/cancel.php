<?php
/**
 * This is the default template part for the
 * cancel element in the actions loop for the
 * shipping address purchase-requriements in the
 * content-checkout template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/shipping-address/elements/
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_shipping_purchase_requirement_before_cancel_element' ); ?>
<div class="ninja-shop-cancel-element">
	<?php it_exchange( 'shipping', 'cancel' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_shipping_purchase_requirement_after_cancel_element' ); ?>
