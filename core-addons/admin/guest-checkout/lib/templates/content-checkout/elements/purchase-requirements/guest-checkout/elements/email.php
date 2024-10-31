<?php
/**
 * This is the default template part for the
 * email element in the guest-checkout template
 * part found in core-addons/admin/guest-checkout.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/purchase-requirements/guest-checkout/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_checkout_guest_checkout_purchase_requirement_before_email_elements' ); ?>
<div class="email-name it-exchange-left">
	<?php echo it_exchange_guest_checkout_get_email_field(); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_guest_checkout_purchase_requirement_after_email_elements' ); ?>
