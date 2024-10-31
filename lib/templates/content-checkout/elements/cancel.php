<?php
/**
 * This is the default template part for the
 * empty_cart element in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_checkout_before_cancel_element' ); ?>
<div class="ninja-shop-checkout_cancel">
	<?php it_exchange( 'checkout', 'cancel', array( 'label' => __( 'Edit Cart', 'it-l10n-ithemes-exchange' ) ) ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_after_cancel_element' ); ?>
