<?php
/**
 * This is the default template part for the core logged-in
 * purchase requirement element in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/elements/purchase-requirements directory
 * located in your theme.
*/
?>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_before_element' ); ?>
<div class="ninja-shop-checkout-logged-in-purchase-requirement">
	<?php if ( ! is_user_logged_in() ) : ?>
		<?php it_exchange_add_session_data( 'login_redirect', it_exchange_get_page_url( 'checkout' ) ); ?>
		<?php
		// Content loop contains form options / login form and registration form. The Options loop contains additional action options (like switching login/registration views);
		$elements = it_exchange_get_template_part_loops( 'content-checkout-logged-in-purchase-requirment', 'not-logged-in', array( 'content' ) ); // deprecated first param. Here only for back_compat
		foreach( (array) it_exchange_get_template_part_loops( 'content-checkout-logged-in-purchase-requirement', 'not-logged-in', $elements ) as $loop ) :
			it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/loops/not-logged-in/' . $loop );
		endforeach;
		?>
	<?php else : ?>
		<?php
		$elements = it_exchange_get_template_part_loops( 'content-checkout-logged-in-purchase-requirment', 'logged-in', array( 'content' ) ); // deprecated first param. Here only for back_compat
		foreach( (array) it_exchange_get_template_part_loops( 'content-checkout-logged-in-purchase-requirement', 'logged-in', $elements ) as $loop ) :
			it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/logged-in/loops/logged-in/' . $loop );
		endforeach;
		?>
	<?php endif; ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_logged_in_purchase_requirement_after_element' ); ?>
