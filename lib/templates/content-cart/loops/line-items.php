<?php
/**
 * This is the default template part for the line
 * items loop.  These are additional top-level line items,
 * separate from products which have a separate loop.
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

<?php if ( ! it_exchange( 'cart', 'has-line-items', array( 'without' => 'product' ) ) ) :
	return;
endif; ?>

<?php do_action( 'ninja_shop_content_cart_before_line_items' ); ?>
	<div id="ninja-shop-line-items" class="ninja-shop-table">
		<?php do_action( 'ninja_shop_content_cart_before_line_items_loop' ); ?>

		<?php while ( it_exchange( 'cart', 'line-items', array( 'without' => 'product' ) ) ): ?>
			<?php do_action( 'ninja_shop_content_cart_begin_line_items_loop' ); ?>

			<?php it_exchange_get_template_part( 'content-cart/elements/line-item' ); ?>

			<?php if ( it_exchange( 'line-item', 'has-children' ) ): ?>
				<?php it_exchange_get_template_part( 'content-cart/loops/item-children' ); ?>
			<?php endif; ?>

			<?php do_action( 'ninja_shop_content_cart_end_line_items_loop' ); ?>
		<?php endwhile; ?>

		<?php do_action( 'ninja_shop_content_cart_after_line_items_loop' ); ?>
	</div>
<?php do_action( 'ninja_shop_content_cart_after_line_items' ); ?>
