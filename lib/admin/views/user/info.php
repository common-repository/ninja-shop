<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * 
 * @package IT_Exchange
*/

if ( empty( $_REQUEST['user_id'] ) )
	$user_id = get_current_user_id();
else
	$user_id = absint( $_REQUEST['user_id'] );

$user_object = get_userdata( $user_id );
$customer    = it_exchange_get_customer( $user_id );

if ( !empty( $_POST['_it_exchange_customer_info_nonce'] ) && !wp_verify_nonce( $_POST['_it_exchange_customer_info_nonce'], 'update-it-exchange-customer-info' ) ) {

	it_exchange_add_message( 'error', __( 'Error verifying security token. Please try again.', 'it-l10n-ithemes-exchange' ) );

} else {

	if ( isset( $_REQUEST['it_exchange_customer_note'] ) )
		update_user_meta( $user_id, '_it_exchange_customer_note', $_REQUEST['it_exchange_customer_note'] );

}
?>

<form action="" method="post">

<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">

	<label><?php _e( 'Total Orders', 'it-l10n-ithemes-exchange' ); ?></label>
	<p><?php echo $customer->get_transactions_count(); ?></p>

	<label><?php _e( 'Lifetime Value', 'it-l10n-ithemes-exchange' ); ?></label>
	<p><?php echo it_exchange_format_price( $customer->get_total_spent() ); ?></p>

    <div class="notes">
        <label for="it_exchange_customer_note"><?php _e( 'Notes', 'it-l10n-ithemes-exchange' ); ?></label>
        <textarea name="it_exchange_customer_note" cols="30" rows="10"><?php echo get_user_meta( $user_id, '_it_exchange_customer_note', true ); ?></textarea>
    </div>
    <div class="avatar"><?php echo get_avatar( $user_id, 160, '', '', array( 'force_display' => true ) ); ?></div>
</div>

<?php wp_nonce_field( 'update-it-exchange-customer-info', '_it_exchange_customer_info_nonce' ); ?>

<div class="update-user-info">
    <input type="submit" class="button button-primary button-large" name="update_it_exchange_customer" value="<?php _e( 'Update Customer Info', 'it-l10n-ithemes-exchange' ) ?>" />
</div>

</form>
