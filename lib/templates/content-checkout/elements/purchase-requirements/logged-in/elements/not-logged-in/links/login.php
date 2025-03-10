<?php
/**
 * This is the default template part for the
 * login link element in the not-logged-in links loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/not-logged-in/links
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_links_before_login_element' ); ?>
<div class="<?php echo ! it_exchange_is_checkout_mode( 'login' ) ? '' : 'ninja-shop-hidden'; ?> ninja-shop-content-checkout-logged-in-purchase-requirement-login-link ninja-shop-logged-in-purchase-requirement-link-div">
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_links_begin_login_element' ); ?>
	<a class="ninja-shop-login-requirement-login ninja-shop-button" href="<?php echo it_exchange_get_page_url( 'login' ); ?>"><input type="button" value="<?php _e( 'Log in', 'it-l10n-ithemes-exchange' ); ?>" /></a>
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_links_end_login_element' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_links_after_login_element' ); ?>
