<?php
/**
 * This is the default template part for the line
 * item quantity element.
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
<?php if ( it_exchange( 'line-item', 'supports-quantity' ) ): ?>
	<div class="ninja-shop-table-column ninja-shop-line-item-quantity-column">
		<div class="ninja-shop-table-column-inner">
			<?php it_exchange( 'line-item', 'quantity', 'format=var_value' ); ?>
		</div>
	</div>
<?php else: ?>
	<div class="ninja-shop-table-column ninja-shop-column-offset" style="width: 15%;"></div>
<?php endif; ?>
