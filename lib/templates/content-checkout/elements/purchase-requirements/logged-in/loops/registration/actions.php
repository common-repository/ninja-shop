<?php
/**
 * This is the default template part for the
 * actions loop in the registration loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/loops/registration
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_before_actions_loop' ); ?>
<div class="ninja-shop-customer-actions">
<?php do_action( 'ninja_shop_content_registration_begin_actions_loop' ); ?>
	<?php foreach ( it_exchange_get_template_part_elements( 'content_checkout/elements/purchase-requirements/logged-in/elements/registration/', 'actions', array( 'save', 'login' ) ) as $action ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		*/
		it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/elements/registration/' . $action );
		?>
	<?php endforeach; ?>
<?php do_action( 'ninja_shop_content_registration_end_actions_loop' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_after_actions_loop' ); ?>
