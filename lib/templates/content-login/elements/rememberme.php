<?php
/**
 * This is the default template part for the
 * rememberme element in the content-login template
 * part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-login/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_login_before_rememberme_element' ); ?>
<div class="ninja-shop-rememberme">
	<?php it_exchange( 'login', 'rememberme' ); ?>
</div>
<?php do_action( 'ninja_shop_content_login_after_rememberme_element' ); ?>
