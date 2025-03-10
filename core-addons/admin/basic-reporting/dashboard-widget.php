<?php
/**
 * The file for the dashboard reporting widget
 * @package IT_Exchange
 * 
*/
?>

<div class="columns-wrapper columns-totals">
	<div class="column column-top column-sales">
		<label><?php _e( 'Sales Today', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
		<label><?php _e( 'Sales this Month', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime( date( 'Y-m-01' ) ), 'end_time' => strtotime( 'tomorrow midnight UTC' ) ) ) ); ?></p>
		<label><?php _e( 'Sales last Month', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_total( array( 'start_time' => strtotime('first day of last month' ), 'end_time' => strtotime( 'last day of last month' ) ) ) ); ?></p>
	</div>
	<div class="column column-top column-transactions">
		<label><?php _e( 'Transactions Today', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( 'today' ), 'end_time' => ( strtotime( 'tomorrow' ) - 1 ) ) ) ); ?></p>
		<label><?php _e( 'Transactions this Month', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime( date( 'Y-m-01' ) ), 'end_time' => strtotime( 'tomorrow midnight UTC' ) ) ) ); ?></p>
		<label><?php _e( 'Transactions last Month', 'it-l10n-ithemes-exchange' ); ?></label>
		<p><?php esc_attr_e( it_exchange_basic_reporting_get_transactions_count( array( 'start_time' => strtotime('first day of last month' ), 'end_time' => strtotime( 'last day of last month' ) ) ) ); ?></p>
	</div>
</div>

<div class="recent-transactions">
<?php if ( $transactions = it_exchange_get_transactions( array( 'posts_per_page' => 5 ) ) ) : ?>
	<p><label><?php _e( 'Recent Transactions', 'it-l10n-ithemes-exchange' ); ?></label> <a href="<?php echo get_admin_url(); ?>edit.php?post_type=it_exchange_tran" class="view-all"><?php _e( 'View all', 'it-l10n-ithemes-exchange' ); ?></a></p>
		<?php foreach( $transactions as $transaction ) : ?>
			<?php $classname = it_exchange_transaction_is_cleared_for_delivery( $transaction ) ? 'cleared-for-delivery' : 'not-cleared-for-delivery'; ?>
			<div class="columns-wrapper columns-recent">
				<div class="column column-date">
					<span><?php echo esc_attr( it_exchange_get_transaction_date( $transaction ) ); ?></span>
				</div>
				<div class="column column-number">
					<span>
						<a href="<?php echo esc_url( get_edit_post_link( $transaction->ID ) ); ?>">
							<?php echo esc_attr( it_exchange_get_transaction_order_number( $transaction ) ); ?>
						</a>
					</span>
				</div>
				<div class="column column-status <?php echo $classname; ?>">
					<span><?php echo esc_attr( it_exchange_get_transaction_status_label( $transaction ) ); ?></span>
				</div>
				<div class="column column-total">
					<span><?php echo esc_attr( it_exchange_get_transaction_total( $transaction ) ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	<p><a href="<?php echo get_admin_url(); ?>edit.php?post_type=it_exchange_tran" class="view-all"><?php _e( 'View all', 'it-l10n-ithemes-exchange' ); ?></a></p>
<?php else : ?>
	<p><label><?php _e( 'No Recent Sales', 'it-l10n-ithemes-exchange' ); ?></label></p>
<?php endif; ?>
</div>
