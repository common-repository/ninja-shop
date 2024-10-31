<?php
/**
 * The default template part for the transaction date in
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

<?php do_action( 'ninja_shop_content_purchases_before_transaction_date_element' ); ?>
<span class="ninja-shop-purchase-date"><?php it_exchange( 'transaction', 'date' ); ?></span>
<?php do_action( 'ninja_shop_content_purchases_after_transaction_date_element' ); ?>
