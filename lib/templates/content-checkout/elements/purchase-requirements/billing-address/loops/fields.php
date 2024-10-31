<?php
/**
 * This is the default template part for the
 * fields loop in the billing-address purchase-requriements
 * in the content-checkout template part.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file to the
 * /exchange/content-checkout/elements/purchase-requirements/billing-address/loops/
 * directory located in your theme.
 */
?>
<?php do_action( 'ninja_shop_content_checkout_billing_address_purchase_requirement_before_fields_loop' ); ?>
<div class="ninja-shop-checkout-address-form ninja-shop-checkout-address-form--billing" <?php echo it_exchange( 'billing', 'saved' ) ? ' style="display: none;"' : ''; ?>>
	<?php $fields = array(
		'first_name',
		'last_name',
		'address_1',
		'address_2',
		'city',
		'country',
		'state',
		'zip',
		'shipping',
		'nonce'
	); ?>
	<?php foreach ( it_exchange_get_template_part_elements( 'content_checkout/elements/purchase-requirements/billing-address/elements/', 'fields', $fields ) as $field ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		 */
		it_exchange_get_template_part( 'content', 'checkout/elements/purchase-requirements/billing-address/elements/' . $field );
		?>
	<?php endforeach; ?>
</div>
<?php do_action( 'ninja_shop_content_checkout_billing_address_purchase_requirement_after_fields_loop' ); ?>
