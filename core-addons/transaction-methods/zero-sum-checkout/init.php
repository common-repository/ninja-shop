<?php
/**
 * Zero Sum Transaction Method
 * For situations when the Cart Total is 0 (free), we still want to record the transaction!
 *
 * 
 * @package IT_Exchange
*/

add_action( 'ninja_shop_register_gateways', function( ITE_Gateways $gateways ) {

	require_once dirname( __FILE__ ) . '/class.gateway.php';
	require_once dirname( __FILE__ ) . '/handlers/class.purchase.php';

	$gateways::register( new ITE_Zero_Sum_Checkout_Gateway() );
} );

/**
 * This proccesses a zer-sum transaction.
 *
 *
 *
 * @param string $status passed by WP filter.
 * @param object $transaction_object The transaction object
*/
function it_exchange_zero_sum_checkout_addon_process_transaction( $status, $transaction_object ) {
	// If this has been modified as true already, return.
	if ( $status )
		return $status;

	// Verify nonce
	if ( ! empty( $_REQUEST['_zero_sum_checkout_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_zero_sum_checkout_nonce'], 'zero-sum-checkout-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'it-l10n-ithemes-exchange' ) );
		return false;
	} else {
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

		// Get customer ID data
		$it_exchange_customer = it_exchange_get_current_customer();

		return it_exchange_add_transaction( 'zero-sum-checkout', $uniqid, 'Completed', $it_exchange_customer->id, $transaction_object );
	}

	return false;
}
//add_action( 'ninja_shop_do_transaction_zero-sum-checkout', 'it_exchange_zero_sum_checkout_addon_process_transaction', 10, 2 );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 *
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param object $transaction
 * @return boolean
*/
function ninja_shop_zero_sum_checkout_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
	$valid_stati = array( 'Completed' );
	return in_array( it_exchange_get_transaction_status( $transaction ), $valid_stati );
}
add_filter( 'ninja_shop_zero-sum-checkout_transaction_is_cleared_for_delivery', 'ninja_shop_zero_sum_checkout_transaction_is_cleared_for_delivery', 10, 2 );

function it_exchange_get_zero_sum_checkout_transaction_uniqid() {
	$uniqid = uniqid( '', true );

	if( !it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) )
		$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();

	return $uniqid;
}

/**
 * Verifies if Unique ID is actually Unique
 *
 *
 *
 * @param string $unique id
 * @return boolean true if it is, false otherwise
*/
function it_exchange_verify_zero_sum_checkout_transaction_unique_uniqid( $uniqid ) {
	return ! it_exchange_get_transaction_by_method_id( 'zero-sum-checkout', $uniqid );
}

/**
 * Returns the button for making the payment
 *
 *
 *
 * @param array $options
 * @return string
*/
function it_exchange_zero_sum_checkout_addon_make_payment_button( $options ) {

	if ( 0 < it_exchange_get_cart_total( false ) )
		return;

	$products = it_exchange_get_cart_data( 'products' );

	$payment_form = '<form id="zero_sum_checkout_form" action="' . it_exchange_get_page_url( 'transaction' ) . '" method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="zero-sum-checkout" />';
	$payment_form .= wp_nonce_field( 'zero-sum-checkout-checkout', '_zero_sum_checkout_nonce', true, false );

	$payment_form .= '<input type="submit" id="zero-sum-checkout-button" name="zero_sum_checkout_purchase" value="' . apply_filters( 'zero_sum_checkout_button_label', 'Complete Purchase' ) .'" />';

	$payment_form .= '</form>';

	return $payment_form;
}
//add_filter( 'ninja_shop_get_zero-sum-checkout_make_payment_button', 'it_exchange_zero_sum_checkout_addon_make_payment_button', 10, 2 );

/*
 * Handles expired transactions that are zero sum checkout
 * If this product autorenews and is zero-sum, it should auto-renew unless the susbcriber status has been deactivated already
 * If it autorenews, it creates a zero-sum child transaction
 *
 *
 * @param bool $true Default True bool, passed from recurring payments expire schedule
 * @param int $product_id Ninja Shop Product ID
 * @param object $transaction Ninja Shop Transaction Object
 * @return bool True if expired, False if not Expired
*/
function it_exchange_zero_sum_checkout_handle_expired( $true, $product_id, $transaction ) {
	$transaction_method = it_exchange_get_transaction_method( $transaction->ID );

	if ( 'zero-sum-checkout' === $transaction_method ) {

		$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product_id, true );
		$status = $transaction->get_transaction_meta( 'subscriber_status', true );
		if ( $autorenews && empty( $status ) ) { //if the subscriber status is empty, it hasn't been set, which really means it's active for zero-sum-checkouts
			//if the transaction autorenews and is zero sum, we want to create a new child transaction until deactivated
			it_exchange_zero_sum_checkout_add_child_transaction( $transaction );
			return false;
		}

	}

	return $true;

}
//add_filter( 'ninja_shop_recurring_payments_handle_expired', 'it_exchange_zero_sum_checkout_handle_expired', 10, 3 );

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 *
 *
 * @param int $parent_txn Parent Transaction ID
 *
 * @return bool
*/
function it_exchange_zero_sum_checkout_add_child_transaction( $parent_txn ) {

	$parent = it_exchange_get_transaction( $parent_txn );

	if ( ! $parent ) {
		return false;
	}

	$uniqid = it_exchange_get_zero_sum_checkout_transaction_uniqid();
	it_exchange_add_subscription_renewal_payment( $parent, $uniqid, 'Completed', 0 );

	return true;
}

/**
 * Output the Cancel URL for the Payments screen
 *
 *
 *
 * @param object $transaction iThemes Transaction object
 * @return void
*/
function it_exchange_zero_sum_checkout_after_payment_details_cancel_url( $transaction ) {

	_deprecated_function( __FUNCTION__, '1.35.5' );

	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	if ( !empty( $cart_object->products ) ) {
		foreach ( $cart_object->products as $product ) {
			$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
			if ( $autorenews ) {
				$status = $transaction->get_transaction_meta( 'subscriber_status', true );
				switch( $status ) {

					case false: //active
					case '':
						$output = '<a href="' . esc_url( add_query_arg( 'zero-sum-recurring-payment', 'cancel' ) ) . '">' . __( 'Cancel Recurring Payment', 'it-l10n-ithemes-exchange' ) . '</a>';
						break;

					case 'deactivated':
					default:
						$output = __( 'Recurring payment has been deactivated', 'it-l10n-ithemes-exchange' );
						break;

				}
				?>
				<div class="transaction-autorenews clearfix spacing-wrapper">
					<div class="recurring-payment-cancel-options left">
						<div class="recurring-payment-status-name"><?php echo $output; ?></div>
					</div>
				</div>
				<?php
				continue;
			}
		}
	}
}

/**
 * Process Zero Sum Recurring Payments cancellations
 *
 *
 *
 * @return void
*/
function it_exchange_process_zero_sum_recurring_payment_cancel() {
	if ( !empty( $_REQUEST['zero-sum-recurring-payment'] ) && 'cancel' === $_REQUEST['zero-sum-recurring-payment'] ) {
		if ( !empty( $_REQUEST['post'] ) && $post_id = absint( $_REQUEST['post'] ) ) {
			$transaction = it_exchange_get_transaction( $post_id );
			$status = $transaction->update_transaction_meta( 'subscriber_status', 'cancel' );
		}
	}
}
add_action( 'admin_init', 'it_exchange_process_zero_sum_recurring_payment_cancel' );

add_filter( 'ninja_shop_auto_activate_non_renewing_zero-sum-checkout_subscriptions', '__return_false' );

/**
 * Mark all transaction subscriptions as active when a transaction is made.
 *
 *
 *
 * @param int $transaction_id
 */
function it_exchange_zero_sum_mark_subscriptions_as_active_on_purchase( $transaction_id ) {

	if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction_id ) ) {
		return;
	}

	if ( ! function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {
		return;
	}

	if ( it_exchange_get_transaction_method( $transaction_id ) !== 'zero-sum-checkout' ) {
		return;
	}

	$subs = it_exchange_get_transaction_subscriptions( it_exchange_get_transaction( $transaction_id ) );

	$status = defined( 'IT_Exchange_Subscription::STATUS_COMPLIMENTARY' ) ? IT_Exchange_Subscription::STATUS_COMPLIMENTARY : IT_Exchange_Subscription::STATUS_ACTIVE;

	try {
		foreach ( $subs as $sub ) {
			add_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
			$sub->set_status( $status );
			remove_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
		}
	}
	catch ( Exception $e ) {
	    it_exchange_log( 'Unexpected exception while marking zero subscription as active on purchase of {txn_id}: {exception}', array(
            'exception' => $e,
            'txn_id'    => $transaction_id,
            '_group'    => 'gateway',
        ) );
	}
}

add_action( 'ninja_shop_add_transaction_success', 'it_exchange_zero_sum_mark_subscriptions_as_active_on_purchase', 20 );

/**
 * Mark subscriptions as active when the transaction is marked as cleared for delivery.
 *
 *
 *
 * @param IT_Exchange_Transaction $transaction
 * @param string                  $old_status
 * @param bool                    $old_cleared
 */
function it_exchange_zero_sum_mark_subscriptions_as_active_on_clear( $transaction, $old_status, $old_cleared ) {

	if ( ! function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {
		return;
	}

	if ( it_exchange_get_transaction_method( $transaction ) !== 'zero-sum-checkout' ) {
		return;
	}

	$new_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction );

	$status = defined( 'IT_Exchange_Subscription::STATUS_COMPLIMENTARY' ) ? IT_Exchange_Subscription::STATUS_COMPLIMENTARY : IT_Exchange_Subscription::STATUS_ACTIVE;

	if ( $new_cleared && ! $old_cleared ) {

		$subs = it_exchange_get_transaction_subscriptions( $transaction );

		foreach ( $subs as $sub ) {
			$sub_status = $sub->get_status();

			if ( empty( $sub_status ) ) {
				add_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
				$sub->set_status( $status );
				remove_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
			}
		}
	}

}

add_action( 'ninja_shop_update_transaction_status', 'it_exchange_zero_sum_mark_subscriptions_as_active_on_clear', 10, 3 );
