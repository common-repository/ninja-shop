<?php
/**
 * This is the default template part for the
 * cancel element in the super-widget-guest-checkout template
 * part located in the core-addons/admin/guest-checkout/lib/templates directory
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

<?php do_action( 'ninja_shop_super_widget_guest_checkout_before_cancel_elements' ); ?>
<div class="cancel_url">
	<?php echo it_exchange_guest_checkout_get_sw_cancel_link(); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_guest_checkoutafter_cancel_elements' ); ?>
