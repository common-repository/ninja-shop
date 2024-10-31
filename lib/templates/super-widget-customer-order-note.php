<?php
/**
 * Customer order note template.
 *
 * 
 * @license GPLv2
 */
?>

<?php do_action( 'ninja_shop_super_widget_customer_order_note_before_wrap' ); ?>
	<div class="customer-order-note ninja-shop-sw-processing ninja-shop-sw-processing-customer-order-note">
		<?php do_action( 'ninja_shop_super_widget_customer_order_note_begin_wrap' ); ?>
		<form class="ninja-shop-sw-customer-order-note-form">
			<label for="customer-order-note"><?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?></label>
			<textarea id="customer-order-note"><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></textarea>

			<p class="description">
				<?php _e( 'Notes about your order. Such as delivery instructions or customizations.', 'it-l10n-ithemes-exchange' ); ?>
			</p>

			<div class="ninja-shop-customer-order-note-actions">
				<a href="javascript:" class="ninja-shop-customer-order-note-cancel"><?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?></a>
				<input type="submit" class="ninja-shop-submit" value="<?php _e( 'Submit', 'it-l10n-ithemes-exchange' ); ?>">
			</div>
		</form>
		<?php do_action( 'ninja_shop_super_widget_customer_order_note_end_wrap' ); ?>
	</div>
<?php do_action( 'ninja_shop_super_widget_customer_order_note_after_wrap' ); ?>
