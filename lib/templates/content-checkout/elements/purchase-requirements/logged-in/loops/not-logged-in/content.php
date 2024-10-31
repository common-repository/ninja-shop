<?php
/**
 * This is the default template part for the
 * content loop in the not-logged-in detail for the logged-in
 * purchase-requriements in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/loops/not-logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_before_content_loop' ); ?>
<div class="ninja-shop-content-checkout-logged-in-purchase-requirement-content">
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_begin_content_loop' ); ?>
	<?php foreach( it_exchange_get_template_part_elements( 'content-checkout-logged-in-purchase-requirements-not-logged-in', 'content', array( 'options', 'login', 'registration' ) ) as $element ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		 */
		it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/elements/not-logged-in/' . $element );
		?>
	<?php endforeach; ?>
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_end_content_loop' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_after_content_loop' ); ?>
