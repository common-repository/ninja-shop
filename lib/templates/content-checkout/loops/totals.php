<?php
/**
 * This is the default template part for the
 * totals loop in the content-checkout
 * template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/content-checkout/loops/ directory
 * located in your theme.
*/
?>

<?php do_action( 'ninja_shop_content_checkout_before_totals' ); ?>
<div id="ninja-shop-cart-totals" class="ninja-shop-table">
<?php do_action( 'ninja_shop_content_checkout_before_totals_loop' ); ?>
	<?php do_action( 'ninja_shop_content_checkout_begi_totalsn_loop' ); ?>
		<?php foreach ( it_exchange_get_template_part_elements( 'content_checkout', 'totals', array( 'totals-subtotal', 'totals-savings', 'totals-taxes', 'totals-total' ) ) as $totals ) : ?>
			<?php if ( $totals !== 'totals-taxes' ): ?>
				<div class="ninja-shop-table-row ninja-shop-cart-<?php echo $totals; ?>">
			<?php endif; ?>

				<?php
				/**
				 * Theme and add-on devs should add code to this loop by
				 * hooking into it_exchange_get_template_part_elements filter
				 * and adding the appropriate template file to their theme or add-on
				*/
				it_exchange_get_template_part( 'content-checkout/elements/' . $totals );
				?>

			<?php if ( $totals !== 'totals-taxes' ): ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php do_action( 'ninja_shop_content_checkout_end_totals_loop' ); ?>
<?php do_action( 'ninja_shop_content_checkout_before_totals_loop' ); ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_after_totals' ); ?>
