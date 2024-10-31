<?php
/**
 * This is the default template part for the line
 * item child element.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-confirmation/elements/ directory
 * located in your theme.
 */
?>
<div class="ninja-shop-table-row ninja-shop-line-item ninja-shop-<?php it_exchange( 'line-item', 'type', 'label=0' ); ?>-item">
	<?php it_exchange_get_template_part( 'content-confirmation/elements/line-item-name' ); ?>
	<?php it_exchange_get_template_part( 'content-confirmation/elements/line-item-quantity-total' ); ?>
</div>
