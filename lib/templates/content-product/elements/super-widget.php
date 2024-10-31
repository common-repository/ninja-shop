<?php
/**
 * The default template part for the product
 * super widget in the content-product template
 * part's product-info loop.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-product/elements
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_product_before_super_widget_element' ); ?>
<?php it_exchange( 'product', 'super-widget' ); ?>
<?php do_action( 'ninja_shop_content_product_after_super_widget_element' ); ?>
