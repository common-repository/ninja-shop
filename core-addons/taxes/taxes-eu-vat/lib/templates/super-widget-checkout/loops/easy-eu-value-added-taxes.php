<?php
/**
 * This is the default template for the Easy EU Value Added Taxes
 * element in the totals loop of the content-cart
 * template part. It was added by Easy EU Value Added Taxes add-on.
 *
 * 
 * @version 1.1.0
 * @package exchange-addon-easy-eu-value-added-taxes
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_super_widget_checkout_before_easy_eu_valued_added_taxes_element' ); ?>
<div class="cart-taxes cart-totals-row">
	<?php do_action( 'ninja_shop_super_widget_checkout_begin_easy_eu_valued_added_taxes_element' ); ?>
	<?php it_exchange( 'eu-value-added-taxes', 'vat-number' ); ?>
	<?php do_action( 'ninja_shop_super_widget_checkout_end_easy_eu_valued_added_taxes_element' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_checkout_after_easy_eu_valued_added_taxes_element' ); ?>