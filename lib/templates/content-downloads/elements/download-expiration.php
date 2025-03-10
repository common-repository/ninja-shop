<?php
/**
 * The default template part for the download's
 * expiration in the content-downloads template
 * part's download-data loop.
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

<?php do_action( 'ninja_shop_content_download_before_download_expiration_element' ); ?>
<?php it_exchange_get_template_part( 'content-downloads/elements/download-expiration-date' ); ?>
<?php it_exchange_get_template_part( 'content-downloads/elements/download-limit' ); ?>
<?php it_exchange_get_template_part( 'content-downloads/elements/download-url' ); ?>
<?php do_action( 'ninja_shop_content_download_after_download_expiration_element' ); ?>
