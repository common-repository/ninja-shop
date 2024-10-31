<?php
/**
 * This is the default template part for the
 * email element in the super-widget-guest-checkout template
 * part found in core-addons/admin/guest-checkout.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-guest-checkout/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_super_widget_guest_checkout_before_email_elements' ); ?>
<div class="email-name">
	<?php echo it_exchange_guest_checkout_get_email_field(); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_guest_checkout_after_email_elements' ); ?>
