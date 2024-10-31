<?php
/**
 * This is the default template part for the
 * login-button element in the content-login
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-login/elements
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_login_before_login_button_element' ); ?>
<div class="ninja-shop-login-button">
	<?php it_exchange( 'login', 'login-button' ); ?>
</div>
<?php do_action( 'ninja_shop_content_login_after_login_button_element' ); ?>
