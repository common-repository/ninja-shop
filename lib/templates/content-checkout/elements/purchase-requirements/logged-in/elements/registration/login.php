<?php
/**
 * This is the default template part for the
 * login element in the registration loop for the
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
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_before_login_element' ); ?>
<div class="ninja-shop-registration-login">
    <a class="ninja-shop-login-requirement-login" href="<?php echo it_exchange_get_page_url( 'login' ); ?>"><?php _e( 'Log in', 'it-l10n-ithemes-exchange' ); ?></a>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_registration_after_login_element' ); ?>
