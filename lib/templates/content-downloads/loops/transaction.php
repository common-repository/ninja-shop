<?php
/**
 * The default product loop for the
 * content-purchases.php template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy this file's
 * content to the exchange/content-download/loops
 * directory located in your theme.
*/
?>

<?php if ( it_exchange( 'transactions', 'found' ) ) : ?>
	<?php it_exchange_set_global( 'downloads_found', false ); ?>
	<?php do_action( 'ninja_shop_content_downloads_before_transactions_loop' ); ?>
	<?php while( it_exchange( 'transactions', 'exist' ) ) : ?>
		<?php do_action( 'ninja_shop_content_downloads_begin_transactions_loop' ); ?>
		<?php it_exchange_get_template_part( 'content-downloads/loops/product' ); ?>
		<?php do_action( 'ninja_shop_content_downloads_end_transactions_loop' ); ?>
    <?php endwhile; ?>
	<?php do_action( 'ninja_shop_content_downloads_after_transactions_loop' ); ?>
	<?php if ( ! it_exchange_get_global( 'downloads_found' ) ) : ?>
		<div class="ninja-shop-clearfix"></div>
		<p><?php _e( 'No downloadable files available.', 'it-l10n-ithemes-exchange' ); ?></p>
	<?php endif; ?>
<?php else: ?>
	<p><?php _e( 'No downloads found.', 'it-l10n-ithemes-exchange' ); ?></p>
<?php endif; ?>
