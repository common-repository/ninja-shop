<?php
/**
 * This is the default template for the Savings
 * element in the totals loop of the content-checkout
 * template part.
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

<?php do_action( 'ninja_shop_content_checkout_before_totals_savings_element' ); ?>
<?php if ( it_exchange( 'coupons', 'supported', 'type=cart' ) && it_exchange( 'coupons', 'has-applied', 'type=cart' ) ) : ?>
	<div class="ninja-shop-cart-totals-title ninja-shop-table-column">
	<?php do_action( 'ninja_shop_content_checkout_begin_totals_savings_element_label' ); ?>
		<div class="ninja-shop-table-column-inner">
			<?php _e( 'Savings', 'it-l10n-ithemes-exchange' ); ?>
		</div>
	<?php do_action( 'ninja_shop_content_checkout_end_totals_savings_element_label' ); ?>
	</div>
	<div class="ninja-shop-cart-totals-amount ninja-shop-table-column">
		<div class="ninja-shop-table-column-inner">
			<?php it_exchange( 'coupons', 'total-discount', array( 'type' => 'cart' ) ); ?>
		</div>
	<?php do_action( 'ninja_shop_content_checkout_end_totals_savings_element_value' ); ?>
	</div>
<?php endif ?>
<?php do_action( 'ninja_shop_content_checkout_totals_after_savings_element' ); ?>
