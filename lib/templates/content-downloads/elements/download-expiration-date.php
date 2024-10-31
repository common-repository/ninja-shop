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

<?php if ( it_exchange( 'transaction', 'get-product-download-hash', array( 'attribute' => 'expires' ) ) ) : ?>
	<?php do_action( 'ninja_shop_content_downloads_before_download_expiration_date_element' ); ?>
	<span class="ninja-shop-download-expiration">
		<?php _e( 'Expires on', 'it-l10n-ithemes-exchange' ); ?> <?php it_exchange( 'transaction', 'product-download-hash', array( 'attribute' => 'expiration-date' ) ); ?>
	</span>
	<?php do_action( 'ninja_shop_content_downloads_after_download_expiration_date_element' ); ?>
<?php else : ?>
	<?php do_action( 'ninja_shop_content_downloads_before_download_expiration_date_element' ); ?>
	<span class="ninja-shop-download-expiration">
		<?php _e( 'No expiration date', 'it-l10n-ithemes-exchange' ); ?>
	</span>
	<?php do_action( 'ninja_shop_content_downloads_after_download_expiration_date_element' ); ?>
<?php endif; ?>
