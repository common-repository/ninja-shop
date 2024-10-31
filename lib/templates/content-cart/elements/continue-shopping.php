<?php
/**
 * This is the default template part for the
 * continue_shopping element in the content-cart template
 * part.
 *
 * 
 *
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/ directory
 * located in your theme.
*/
?>
<?php if ( it_exchange( 'cart', 'has-continue-shopping' ) ) : ?>
<?php do_action( 'ninja_shop_content_cart_before_continue_shopping_element' ); ?>
<?php it_exchange( 'cart', 'continue-shopping', array( 'format' => 'link' ) ); ?>
<?php do_action( 'ninja_shop_content_cart_after_continue_shopping_element' ); ?>
<?php endif; ?>
