<?php
/**
 * This file prints the content added to the user-edit.php WordPress page
 *
 * 
 * @package IT_Exchange
*/

if ( empty( $_REQUEST['user_id'] ) ) {
	$user_id = get_current_user_id();
} else {
	$user_id = absint( $_REQUEST['user_id'] );
}

if ( isset( $_GET['it-page'] ) ) {
	$page = sanitize_text_field( $_GET['it-page'] );
} else {
	$page = 1;
}

$per_page = 25;

$user_object = get_userdata( $user_id );

$headings = array(
	__( 'Description', 'it-l10n-ithemes-exchange' ),
	__( 'Total', 'it-l10n-ithemes-exchange' ),
	__( 'Order Number', 'it-l10n-ithemes-exchange' ),
	__( 'Actions', 'it-l10n-ithemes-exchange' ),
);

$list = array();

$transactions = (array) it_exchange_get_customer_transactions( $user_id, array( 'per_page' => $per_page, 'page' => $page ), $total );

foreach( $transactions as $transaction ) {
	// View URL
	$view_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'view', 'action' => 'edit', 'post' => esc_attr( $transaction->ID ) ), get_admin_url() . '/post.php' );
	
	// Resend URL
	$resend_url = add_query_arg( array( 'it-exchange-customer-transaction-action' => 'resend', 'id' => $transaction->ID ) );
	$resend_url = remove_query_arg( 'wp_http_referer', $resend_url );
	$resend_url = wp_nonce_url( $resend_url, 'it-exchange-resend-confirmation-' . $transaction->ID );
	$resend_url = remove_query_arg( '_wpnonce', $resend_url );

	if ( it_exchange_transaction_can_be_refunded( $transaction ) ) {
	    $refund_url = get_edit_post_link( $transaction->ID, 'raw' ) . '#open-refund-manager';
    } else {
	    $refund_url = '';
    }

	// Build Transaction Link
	$transaction_url    = add_query_arg( array( 'action' => 'edit', 'post' => esc_attr( $transaction->ID ) ), get_admin_url() . '/post.php' );
	$transaction_number = it_exchange_get_transaction_order_number( $transaction->ID );
	$transaction_link   = '<a href="' . esc_url( $transaction_url ) . '">' . $transaction_number . '</a>';

	// Actions array
	$actions_array = array(
		$view_url   => __( 'View', 'it-l10n-ithemes-exchange' ),
		$resend_url => __( 'Resend Confirmation Email', 'it-l10n-ithemes-exchange' ),
	);

	if ( $refund_url ) {
		$actions_array[ $refund_url ] = sprintf(
            __( 'Refund from %s', 'it-l10n-ithemes-exchange' ),
            it_exchange_get_transaction_method_name( $transaction )
        );
    }

	$description  = it_exchange_get_transaction_description( $transaction );
	$price        = it_exchange_get_transaction_total( $transaction );
	$list[]       = array( $description, $price, $transaction_link, $actions_array );
}
?>

<div class="user-edit-block <?php echo $tab; ?>-user-edit-block">

	<div class="heading-row block-row">
		<?php $column = 0; ?>
		<?php foreach ( (array) $headings as $heading ) : ?>
			<?php $column++ ?>
			<div class="heading-column block-column block-column-<?php echo $column; ?>">
				<p class="heading"><?php echo $heading; ?></p>
			</div>
		<?php endforeach; ?>
	</div>
	<?php foreach ( (array) $list as $item_details ) : ?>
		<?php $column = 0; ?>
		<div class="item-row block-row">
			<?php foreach ( (array) $item_details as $detail ) : ?>
				<?php $column++ ?>
				<?php if ( is_array( $detail ) ) : ?>
					<div class="item-column block-column block-column-<?php echo $column; ?>">
						<?php foreach ( $detail as $action => $label ) : ?>
							<a class="button" href="<?php echo $action; ?>"><?php esc_attr_e( $label ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="item-column block-column block-column-<?php echo $column; ?>">
						<p class="item"><?php echo $detail; ?></p>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endforeach; ?>
</div>

<?php echo paginate_links( array(
	'base'    => add_query_arg( array( 'it_exchange_customer_data' => 1, 'tab' => 'transactions' ), get_edit_user_link( $user_id ) ) . '%_%',
	'format'  => '&it-page=%#%',
	'total'   => ceil( $total / $per_page ),
	'current' => $page,
	'type'    => 'list',
	'prev_text' => __( '&laquo; Newer' ),
	'next_text' => __( 'Older &raquo;' ),
) ); ?>
