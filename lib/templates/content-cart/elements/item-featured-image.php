<?php
/**
 * The main template file for the Featured Image
 * element in the cart-items loop for content-cart
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-cart/elements/
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_cart_before_item_featured_image_element' ); ?>
<div class="ninja-shop-cart-item-thumbnail ninja-shop-table-column">
	<?php do_action( 'ninja_shop_content_cart_begin_item_featured_image_element' ); ?>
	<div class="ninja-shop-table-column-inner">
		<?php it_exchange( 'cart-item', 'featured-image' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_cart_end_item_featured_image_element' ); ?>
</div>
<?php do_action( 'ninja_shop_content_cart_after_item_featured_image_element' ); ?>
