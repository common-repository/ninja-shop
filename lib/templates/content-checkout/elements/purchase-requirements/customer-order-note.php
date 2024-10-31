<?php
/**
 * Checkout customer order note purchase requirement.
 *
 * 
 * @license GPLv2
 */
?>

<div class="ninja-shop-customer-order-note">

	<h3><?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?></h3>

	<div class="ninja-shop-customer-order-notes-summary">
		<p><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></p>

		<a href="javascript:" class="ninja-shop-edit-customer-order-notes">
			<?php _e( 'Edit Order Notes', 'it-l10n-ithemes-exchange' ); ?>
		</a>
	</div>

	<form method="POST" class="ninja-shop-customer-order-notes-form ninja-shop-hidden" action="<?php echo it_exchange_get_page_url( 'checkout' ); ?>">
		<label for="ninja-shop-customer-order-note" class="screen-reader-text ninja-shop-hidden">
			<?php _e( 'Order Notes', 'it-l10n-ithemes-exchange' ); ?>
		</label>
		<textarea id="ninja-shop-customer-order-note" name="it-exchange-customer-order-note"><?php echo esc_html( it_exchange_customer_order_notes_get_current_note() ); ?></textarea>

		<div class="ninja-shop-customer-order-note-actions">
			<a href="javascript:" class="ninja-shop-customer-order-note-cancel"><?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?></a>
			<input type="submit" name="it-exchange-edit-customer-order-note" class="ninja-shop-submit" value="<?php _e( 'Submit', 'it-l10n-ithemes-exchange' ); ?>">
		</div>
	</form>
</div>
