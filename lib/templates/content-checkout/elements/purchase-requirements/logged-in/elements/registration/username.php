<?php
/**
 * This is the default template part for the
 * username element in the registration loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/registration
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_before_username_element' ); ?>
<div class="ninja-shop-registration-username">
	<?php it_exchange( 'registration', 'username' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_after_username_element' ); ?>
