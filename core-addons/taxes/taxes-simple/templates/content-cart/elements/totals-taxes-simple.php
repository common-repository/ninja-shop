<?php
/**
 * This is the default template for the Taxes
 * element in the totals loop of the content-cart
 * template part. It was added by Simple Taxes core add-on.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_cart_before_totals_taxes_simple_element' ); ?>
<div class="it-exchange-cart-totals-title it-exchange-table-column">
	<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_simple_element_label' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php echo it_exchange_add_simple_taxes_get_label( 'tax' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_simple_element_label' ); ?>
</div>
<div class="it-exchange-cart-totals-amount it-exchange-table-column">
	<?php do_action( 'ninja_shop_content_cart_begin_totals_taxes_simple_element_value' ); ?>
	<div class="it-exchange-table-column-inner">
		<?php esc_attr_e( it_exchange_addon_get_simple_taxes_for_cart() ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_cart_end_totals_taxes_simple_element_value' ); ?>
</div>
<?php do_action( 'ninja_shop_content_cart_after_totals_taxes_simple_element' ); ?>
