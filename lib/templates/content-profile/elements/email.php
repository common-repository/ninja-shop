<?php
/**
 * This is the default template part for the
 * email element in the content-profile template
 * part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-profile/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_profile_before_email_element' ); ?>
<div class="ninja-shop-customer-email">
	<?php it_exchange( 'customer', 'email' ); ?>
</div>
<?php do_action( 'ninja_shop_content_profile_after_email_element' ); ?>
