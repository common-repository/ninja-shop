<?php
/**
 * This is the default template part for the cart
 * purchase requirements loop. Exchange core doesn't
 * place anything in here but add-ons like shipping
 * taxing will use it.
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
<?php if ( it_exchange_get_purchase_requirements() ) : ?>
	<?php do_action( 'ninja_shop_content_checkout_before_purchase_requirements' ); ?>
	<div id="ninja-shop-checkout-purchase-requirements" class="ninja-shop-clearfix <?php echo ( false !== ( $notification = it_exchange_get_next_purchase_requirement() ) ) ? 'ninja-shop-requirements-active' : ''; ?>">
		<?php do_action( 'ninja_shop_content_checkout_before_purchase_requirements_loop' ); ?>
		<?php
		/* This loop is a bit different because we are asking add-ons to provide the list of element items by
		   registering them as purchase requirements. */
		$purchase_requirement_template_elements = (array) it_exchange_get_all_purchase_requirement_checkout_element_template_parts();
		$it_exchange_pending_purchase_requirements = it_exchange_get_pending_purchase_requirements();
		?>
		<?php
		foreach ( it_exchange_get_template_part_elements( 'content_checkout', 'purchase_requirements', $purchase_requirement_template_elements ) as $item ) : ?>
			<?php
			/**
			 * Theme and add-on devs should add code to this loop by
			 * hooking into it_exchange_get_template_part_elements filter
			 * and adding the appropriate template file to their theme or add-on
			 *
			 * This loop is different than most of our template files. We will sometimes not one to display a specific
			 * purchase requirement until the ones of higher priority have been completed. The default functionality will
			 * be to check all higher priority requirements to make sure their met. If not, don't include this template.
			 */

			$current_key = array_search( $item, $purchase_requirement_template_elements );
			$elements    = $purchase_requirement_template_elements;
			$missing_prereq = false;
			foreach( $elements as  $key => $value ) {
				if ( $key >= $current_key || ! empty( $missing_prereq ) ) {
					continue;
				}

				$missing_prereq = in_array( $value, $it_exchange_pending_purchase_requirements );
			}

			// Include if we don't have a missing prereq and if non other add-ons change it.
			if ( apply_filters( 'ninja_shop_include_purchase_requirement_template_for-' . $item, ! $missing_prereq, $purchase_requirement_template_elements ) ) {
				it_exchange_get_template_part( 'content-checkout/elements/purchase-requirements/' . $item );
			}
			?>
		<?php endforeach; ?>
		<?php do_action( 'ninja_shop_content_content_checkout_after_purchase_requirements_loop' ); ?>
	</div>
	<?php do_action( 'ninja_shop_content_checkout_after_purchase_requirements' ); ?>
<?php endif; ?>
