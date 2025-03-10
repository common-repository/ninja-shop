<?php
/**
 * Default template for displaying the store.
 *
 * 
 * @package IT_Exchange
 * @version 1.1.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 *
 * Example: theme/exchange/content-store.php
*/
?>

<?php it_exchange_get_template_part( 'messages' ); ?>

<div id="it-exchange-store">
	<ul class="it-exchange-products">
		<?php if ( it_exchange( 'store', 'has-products' ) ) : ?>
			<?php while( it_exchange( 'store', 'products' ) ) : ?>
				<?php it_exchange_get_template_part( 'store', 'product' ); ?>
			<?php endwhile; ?>
		<?php else : ?>
			<p><?php _e( 'No Products Found', 'it-l10n-ithemes-exchange' ); ?></p>
		<?php endif; ?>
	</ul>
</div>
