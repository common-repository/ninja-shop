<?php
/**
 * This is the default template for the
 * super-widget-cart update-quantity element.
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

<?php do_action( 'ninja_shop_super_widget_cart_before_update_quantity_element' ); ?>
<?php do_action( 'ninja_shop_super_widget_cart_before_quantity_wrap' ); ?>
<div class="quantity-wrapper">
	<?php do_action( 'ninja_shop_super_widget_cart_begin_quantity_wrap' ); ?>
	<div class="quantity">
		<?php it_exchange( 'cart', 'update', 'class=ninja-shop-update-quantity-button&label=' . __( 'Update Quantity', 'it-l10n-ithemes-exchange' ) ); ?>
	</div>

	<?php
	// Include the single-item-cart quantity actions template part
	it_exchange_get_template_part( 'super-widget-cart/elements/single-item-cart-cancel' );
	?>

	<?php do_action( 'ninja_shop_super_widget_cart_end_quantity_wrap' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_cart_afterquantity__wrap' ); ?>
<?php do_action( 'ninja_shop_super_widget_cart_after_update_quantity_element' ); ?>
