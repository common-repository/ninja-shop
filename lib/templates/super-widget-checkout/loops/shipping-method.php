<?php
/**
 * This is the default template for the
 * super-widget-checkout shipping-method element.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-checkout/loops directory
 * located in your theme.
*/
?>
<?php do_action( 'ninja_shop_super_widget_checkout_before_shipping_method_existing_element' ); ?>
<div class="cart-shipping-method-existing cart-totals-row">
	<?php do_action( 'ninja_shop_super_widget_checkout_begin_shipping_method_existing_element' ); ?>
	<p><strong><?php _e( 'Shipping Method:', 'it-l10n-ithemes-exchange' ); ?></strong>
	<?php if ( count( it_exchange_get_available_shipping_methods_for_cart() ) > 1 ) : ?>
		<a href="" class="ninja-shop-sw-edit-shipping-method"><?php _e( 'Edit', 'it-l10n-ithemes-exchange' ); ?></a></p>
	<?php endif; ?>
	<p><?php it_exchange( 'shipping-method', 'current' ); ?></p>
	<?php do_action( 'ninja_shop_super_widget_checkout_end_shipping_method_existing_element' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_checkout_after_shipping_method_existing_element' ); ?>
