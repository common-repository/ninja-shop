<?php
/**
 * This is the default template part for the
 * zip element in the shipping-address
 * purchase-requriements in the content-checkout template part.
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
<?php do_action( 'ninja_shop_content_checkout_shipping_address_purchase_requirement_before_zip_element' ); ?>
<div class="ninja-shop-zip ninja-shop-left">
	<?php it_exchange( 'shipping', 'zip' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_shipping_address_purchase_requirement_after_zip_element' ); ?>
