<?php
/**
 * The cart template for the Super Widget.
 *
 * 
 * @version 1.1.0
 * @link    http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 */
?>

<?php do_action( 'ninja_shop_super_widget_cart_before_wrap' ); ?>
<div class="ninja-shop-sw-processing ninja-shop-sw-processing-cart">
	<?php it_exchange_get_template_part( 'messages' ); ?>
	<?php do_action( 'ninja_shop_super_widget_cart_begin_wrap' ); ?>
	<?php if ( it_exchange( 'cart', 'has-cart-items' ) ) : ?>
		<?php if ( ( it_exchange_get_the_product_id() && it_exchange_is_current_product_in_cart() )
		           || it_exchange( 'cart', 'get-focus', array( 'type' => 'coupon' ) )
		           || it_exchange( 'cart', 'get-focus', array( 'type' => 'quantity' ) )
		)
			: ?>
			<?php do_action( 'ninja_shop_super_widget_cart_before_form' ); ?>
			<?php it_exchange( 'cart', 'form-open', array( 'class' => 'ninja-shop-sw-update-cart-' . it_exchange( 'cart', 'get-focus' ) ) ); ?>
			<?php do_action( 'ninja_shop_super_widget_cart_begin_form' ); ?>
			<div class="cart-items-wrapper">
				<?php _e( 'Item Added to Cart', 'it-l10n-ithemes-exchange' ); ?>

				<?php while ( it_exchange( 'cart', 'cart-items' ) ) : ?>
					<?php it_exchange_get_template_part( 'super-widget-cart/loops/items' ); ?>
				<?php endwhile; ?>

				<?php if ( it_exchange( 'coupons', 'has-applied', array( 'type' => 'cart' ) ) ): ?>
					<?php /* @TODO coupons : The following block is commented out to remove coupon functionality */ ?>
					<?php /* (DELETE THIS LINE TO REVERT)
						<div class="cart-discount cart-totals-row">
							<?php it_exchange_get_template_part( 'super-widget-cart/loops/discounts' ); ?>
						</div>
					(DELETE THIS LINE TO REVERT) */ ?>
				<?php endif; ?>
			</div>

			<?php
			it_exchange_get_template_part( 'super-widget-cart/elements/coupons' );

			// If quantity is the current focus, include the quantity template
			if ( it_exchange( 'cart', 'focus', 'type=quantity' ) ) {
				it_exchange_get_template_part( 'super-widget-cart/elements/update-quantity' );
			}

			// If multi-item cart is allowed, include the multi-item-cart actions
			if ( it_exchange_is_multi_item_cart_allowed() ) {
				it_exchange_get_template_part( 'super-widget-cart/elements/multi-item-cart-actions' );
			}
			?>
			<?php do_action( 'ninja_shop_super_widget_cart_end_form' ); ?>
			<?php it_exchange( 'cart', 'form-close' ); ?>
			<?php do_action( 'ninja_shop_super_widget_cart_after_form' ); ?>
		<?php else : ?>
			<?php if ( it_exchange_is_multi_item_cart_allowed() ) : ?>
				<?php it_exchange_get_template_part( 'super-widget-cart/elements/summary' ); ?>
			<?php else : ?>
				<?php it_exchange_get_template_part( 'super-widget', 'login' ); ?>
			<?php endif; ?>
		<?php endif; ?>
	<?php elseif ( ! it_exchange_get_the_product_id() ) : ?>
		<?php it_exchange_get_template_part( 'super-widget-cart/elements/empty-cart-notice' ); ?>
	<?php endif; ?>
	<?php do_action( 'ninja_shop_super_widget_cart_end_wrap' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_cart_after_wrap' ); ?>
