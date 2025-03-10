<?php
/**
 * This is the default template for the Savings
 * element in the totals loop of the content-confirmation
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-confirmation/elements/
 * directory located in your theme.
*/
?>

<?php if ( it_exchange( 'transaction', 'has-shipping-address' ) ) { ?>
<?php do_action( 'ninja_shop_content_confirmation_before_shipping_address_element' ); ?>
<div class="ninja-shop-confirmation-shipping-address-purchase-requirement ninja-shop-column">
	<h3><?php _e( 'Shipping Address', 'it-l10n-ithemes-exchange' ); ?></h3>
    <div class="checkout-purchase-requirement-shipping-address-options">
        <div class="existing-shipping-address">
        <?php it_exchange( 'transaction', 'shipping-address' ); ?>
        </div>
    </div>
</div>
<?php do_action( 'ninja_shop_content_confirmation_after_shipping_address_element' ); ?>
<?php } ?>
