<?php
/**
 * This is the default template part for the
 * register element in the content-login template
 * part.
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

<?php do_action( 'ninja_shop_content_login_before_register_element' ); ?>
<div class="ninja-shop-register-url">
	<?php it_exchange( 'login', 'register' ); ?>
</div>
<?php do_action( 'ninja_shop_content_login_after_register_element' ); ?>
