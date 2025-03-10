<?php
/**
 * This is the default template part for the
 * actions loop in the content-cart
 * template part.
 *
 * 
 *
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/loops/ directory
 * located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_cart_before_actions' ); ?>
<div id="ninja-shop-cart-actions">
<?php do_action( 'ninja_shop_content_cart_before_actions_loop' ); ?>
<?php do_action( 'ninja_shop_content_cart_begin_actions_loop' ); ?>
<?php foreach ( it_exchange_get_template_part_elements( 'content_cart', 'actions', array( 'apply-coupon', 'update', 'checkout', 'continue-shopping' ) ) as $action ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		*/
		it_exchange_get_template_part( 'content-cart/elements/' . $action );
		?>
<?php endforeach; ?>
<?php do_action( 'ninja_shop_content_cart_end_actions_loop' ); ?>
<?php do_action( 'ninja_shop_content_cart_after_actions_loop' ); ?>
</div>
<?php do_action( 'ninja_shop_content_cart_after_actions' ); ?>
