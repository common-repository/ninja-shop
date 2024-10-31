<?php
/**
 * This is the default template part for the
 * username element in the content-registration
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-registration/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_registration_before_username_element' ); ?>
<div class="ninja-shop-registration-username">
	<?php it_exchange( 'registration', 'username' ); ?>
</div>
<?php do_action( 'ninja_shop_content_registration_after_username_element' ); ?>
