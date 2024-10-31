<?php
/**
 * This is the default template part for the
 * login element in the not-logged-in loop for the
 * purchase-requriements in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/logged-in/elements/not-logged-in
 * directory located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_before_login_element' ); ?>
<div class="<?php echo it_exchange_is_checkout_mode( 'login' ) ? '' : 'ninja-shop-hidden'; ?> checkout-purchase-requirement-login">
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_begin_login_element' ); ?>
	<?php
	$login_loops = array( 'fields', 'actions' );
	?>
	<div class="ninja-shop-login-form">
		<?php
		do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_login_before_form' );
		it_exchange( 'login', 'form-open' );
		do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_login_begin_form' );
		// Include template parts for each of the above loops
		foreach( (array) it_exchange_get_template_part_loops( 'content-checkout/elements/purchase-requirements/logged-in/loops/', 'login', $login_loops ) as $loop ) :
			it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/loops/login/' . $loop );
		endforeach;
		do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_login_end_form' );
		it_exchange( 'login', 'form-close' );
		?>
	</div>
	<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_end_login_element' ); ?>
	<?php //it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/loops/not-logged-in/links' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_not_logged_in_after_login_element' ); ?>
