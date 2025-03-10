<?php
/**
 * This is the default template for the
 * super-widget-cart total element.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-cart/elements directory
 * located in your theme.
*/
?>

<?php do_action( 'ninja_shop_super_widget_cart_before_total_element' ); ?>
<div class="cart-total cart-totals-row">
	<?php do_action( 'ninja_shop_super_widget_cart_begin_total_element' ); ?>
	<?php _e( 'Total:', 'it-l10n-ithemes-exchange' ); ?> <?php esc_attr_e( it_exchange_get_cart_total() ); ?>
	<?php do_action( 'ninja_shop_super_widget_cart_end_total_element' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_cart_after_total_element' ); ?>
