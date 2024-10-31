<?php
/**
 * This is the default template part for the
 * coupon code element in the coupons loop of the
 * content-checkout template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_checkout_before_coupon_code_element' ); ?>
<div class="ninja-shop-cart-coupon-code ninja-shop-table-column">
	<?php do_action( 'ninja_shop_content_checkout_begin_coupon_code_element' ); ?>
	<div class="ninja-shop-table-column-inner">
		<?php it_exchange( 'coupons', 'code' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_checkout_end_coupon_code_element' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_after_coupon_code_element' ); ?>
