<?php
/**
 * This is the default template part for the
 * guest_checkout element in the not-logged-in loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 *
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/not-logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_before_guest_checkout_element' ); ?>
<div class="<?php echo it_exchange_is_checkout_mode( 'guest-checkout' ) ? '' : 'it-exchange-hidden'; ?> checkout-purchase-requirement-guest-checkout">
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_begin_guest_checkout_element' ); ?>
	<div class="ninja-shop-guest-checkout-form-wrapper it-exchange-clearfix">
		<?php echo it_exchange_guest_checkout_get_heading(); ?>
		<?php do_action( 'ninja_shop_content_checkout_logged_in_checkout_requirement_guest_checkout_before_form' ); ?>
		<form action="" method="post" >
			<?php
			do_action( 'ninja_shop_content_checkout_logged_in_checkout_requirement_guest_checkout_begin_form' );
			// Include template parts for each of the above loops
			$loops = array( 'fields', 'actions' );
			foreach( it_exchange_get_template_part_loops( 'content-checkout-logged-in-purchase-requirements-guest-checkout', 'form', $loops ) as $loop ) :
				it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/guest-checkout/loops/' . $loop );
			endforeach;
			do_action( 'ninja_shop_content_checkout_logged_in_checkout_requirement_guest_checkout_end_form' );
			?>
			<input type="hidden" name="it-exchange-init-guest-checkout" value="1" />
		</form>
		<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_guest_checkout_after_form' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_end_guest_checkout_element' ); ?>
	<?php it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/loops/not-logged-in/links' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_after_guest_checkout_element' ); ?>
