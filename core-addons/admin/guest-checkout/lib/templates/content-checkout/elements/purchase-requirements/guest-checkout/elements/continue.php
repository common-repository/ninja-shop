<?php
/**
 * This is the default template part for the
 * continue element in the content-checkout guest-checkout purchase requirements template
 * part located in the core-addons/admin/guest-checkout/lib/templates directory
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

<?php do_action( 'ninja_shop_content_checkout_guest_checkout_purchase_requirement_before_continue_elements' ); ?>
<div class="continue-action it-exchange-left">
	<?php echo it_exchange_guest_checkout_get_purchase_requirement_continue_action(); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_guest_checkout_purchase_requirement_after_continue_elements' ); ?>
