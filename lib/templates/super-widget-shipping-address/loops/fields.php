<?php
/**
 * This is the default template for the
 * super-widget-shipping-address fields loop.
 *
 * 
 * @version 1.1.0
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, copy over this file
 * to the exchange/super-widget-shipping-address/loops directory
 * located in your theme.
 */
?>

<?php do_action( 'ninja_shop_super_widget_shipping_address_before_fields_loop' ); ?>
<div class="ninja-shop-sw-address-form ninja-shop-sw-address-form--shipping" <?php echo it_exchange( 'shipping', 'saved' ) ? ' style="display: none;"' : ''; ?>>
	<?php do_action( 'ninja_shop_super_widget_shipping_address_begin_fields_loop' ); ?>
	<?php $fields = array(
		'first_name',
		'last_name',
		'address_1',
		'address_2',
		'city',
		'country',
		'state',
		'zip',
		'nonce'
	); ?>
	<?php foreach ( it_exchange_get_template_part_elements( 'super_widget_shipping_address', 'fields', $fields ) as $field ) : ?>
		<?php
		/**
		 * Theme and add-on devs should add code to this loop by
		 * hooking into it_exchange_get_template_part_elements filter
		 * and adding the appropriate template file to their theme or add-on
		 */
		it_exchange_get_template_part( 'super-widget', 'shipping-address/elements/' . $field );
		?>
	<?php endforeach; ?>
	<?php do_action( 'ninja_shop_super_widget_shipping_address_end_fields_loop' ); ?>
</div>
<?php do_action( 'ninja_shop_super_widget_shipping_address_after_fields_loop' ); ?>
