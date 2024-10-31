<?php
/**
 * The default template part for the download
 * title in the content-downloads template part's
 * download-info loop.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-downloads/elements
 * directory located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_download_before_download_title_element' ); ?>
<h4><?php it_exchange( 'transaction', 'product-download', array( 'attribute' => 'title' ) ); ?></h4>
<?php do_action( 'ninja_shop_content_download_after_download_title_element' ); ?>
