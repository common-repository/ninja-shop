<?php
/**
 * The default template part for the product title in
 * the content-purchases template part's product-info loop
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-purchases/elements/ directory
 * located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_purchases_before_product_title_element' ); ?>
<span class="ninja-shop-item-title"><?php it_exchange( 'transaction', 'product-attribute', array( 'attribute' => 'title' ) ); ?></span>
<?php do_action( 'ninja_shop_content_purchases_after_product_title_element' ); ?>
