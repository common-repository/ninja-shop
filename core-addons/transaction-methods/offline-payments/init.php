<?php
/**
 * Offline Transaction Method
 *
 * 
 * @package IT_Exchange
 */

require_once dirname( __FILE__ ) . '/deprecated.php';

add_action( 'ninja_shop_register_gateways', function ( ITE_Gateways $gateways ) {
	require_once dirname( __FILE__ ) . '/handlers/class.purchase.php';
	require_once dirname( __FILE__ ) . '/handlers/class.pause-subscription.php';
	require_once dirname( __FILE__ ) . '/handlers/class.resume-subscription.php';
	require_once dirname( __FILE__ ) . '/handlers/class.cancel-subscription.php';
	require_once dirname( __FILE__ ) . '/class.gateway.php';

	$gateways::register( new ITE_Gateway_Offline_Payments() );
} );

/**
 * Mark this transaction method as okay to manually change transactions
 *
 *
 */
add_filter( 'ninja_shop_offline-payments_transaction_status_can_be_manually_changed', '__return_true' );

/**
 * Returns status options
 *
 *
 *
 * @return array
 */
function it_exchange_offline_payments_get_default_status_options() {
	$options = array(
		'pending'  => _x( 'Pending', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'paid'     => _x( 'Paid', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'refunded' => _x( 'Refunded', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'voided'   => _x( 'Voided', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
	);

	return $options;
}

add_filter( 'ninja_shop_get_status_options_for_offline-payments_transaction', 'it_exchange_offline_payments_get_default_status_options' );


/**
 * Generates a unique ID to stand in for the payment gateway ID that doesn't exist for this method
 *
 *
 *
 * @return string
 */
function it_exchange_get_offline_transaction_uniqid() {
	$uniqid = uniqid( '', true );

	if ( ! it_exchange_verify_offline_transaction_unique_uniqid( $uniqid ) ) {
		$uniqid = it_exchange_get_offline_transaction_uniqid();
	}

	return $uniqid;
}

/**
 * Verifies that the psassed string is unique since we're generating it ourselves
 *
 *
 *
 * @param string $uniqid The id we're checking
 *
 * @return boolean
 */
function it_exchange_verify_offline_transaction_unique_uniqid( $uniqid ) {
	return ! it_exchange_get_transaction_by_method_id( 'offline-payments', $uniqid );
}

/**
 * Adds manual transactions template path inf on confirmation page
 *
 *
 * @return array of possible template paths + offline-payments template path
 */
function it_exchange_offline_payments_add_template_path( $paths ) {
	if ( it_exchange_is_page( 'confirmation' ) ) {
		$paths[] = dirname( __FILE__ ) . '/templates/';
	}

	return $paths;
}

add_filter( 'ninja_shop_possible_template_paths', 'it_exchange_offline_payments_add_template_path' );

/**
 * Return instructions to be displayed when someone makes a purchase with this payment method
 *
 *
 *
 * @param string $instructions passed in via filter. Ignored here.
 *
 * @return string
 */
function it_exchange_transaction_instructions_offline_payments( $instructions ) {
	$options = it_exchange_get_option( 'addon_offline_payments' );
	if ( ! empty( $options['offline-payments-instructions'] ) ) {
		$instructions = $options['offline-payments-instructions'];
	}

	return $instructions;

}

add_filter( 'ninja_shop_transaction_instructions_offline-payments', 'it_exchange_transaction_instructions_offline_payments' );

/**
 * Gets the interpretted transaction status from valid transaction statuses
 *
 *
 *
 * @param string $status the string of the stripe transaction
 *
 * @return string translaction transaction status
 */
function it_exchange_offline_payments_addon_transaction_status_label( $status ) {

	switch ( $status ) {
		case 'succeeded':
		case 'paid':
			return __( 'Paid', 'it-l10n-ithemes-exchange' );
			break;
		case 'refunded':
			return __( 'Refunded', 'it-l10n-ithemes-exchange' );
			break;
		case 'pending':
			return __( 'Pending', 'it-l10n-ithemes-exchange' );
			break;
		case 'voided':
			return __( 'Voided', 'it-l10n-ithemes-exchange' );
			break;
		default:
			return __( 'Unknown', 'it-l10n-ithemes-exchange' );
	}

}

add_filter( 'ninja_shop_transaction_status_label_offline-payments', 'it_exchange_offline_payments_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 *
 *
 * @param boolean $cleared passed in through WP filter. Ignored here.
 * @param mixed   $transaction
 *
 * @return boolean
 */
function it_exchange_offline_payments_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
	return in_array( it_exchange_get_transaction_status( $transaction ), array( 'succeeded', 'paid' ), true );
}

add_filter( 'ninja_shop_offline-payments_transaction_is_cleared_for_delivery', 'it_exchange_offline_payments_transaction_is_cleared_for_delivery', 10, 2 );

add_filter( 'ninja_shop_auto_activate_non_renewing_offline-payments_subscriptions', '__return_false' );

/**
 * Mark all transaction subscriptions as active when a transaction is made.
 *
 *
 *
 * @param int $transaction_id
 */
function it_exchange_offline_payments_mark_subscriptions_as_active_on_purchase( $transaction_id ) {

	if ( ! it_exchange_transaction_is_cleared_for_delivery( $transaction_id ) ) {
		return;
	}

	if ( ! function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {
		return;
	}

	if ( it_exchange_get_transaction_method( $transaction_id ) !== 'offline-payments' ) {
		return;
	}

	$subs = it_exchange_get_transaction_subscriptions( it_exchange_get_transaction( $transaction_id ) );

	try {
		foreach ( $subs as $sub ) {
			add_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
			$sub->set_status( IT_Exchange_Subscription::STATUS_ACTIVE );
			remove_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
		}
	} catch ( Exception $e ) {
		it_exchange_log( 'Unexpected exception while marking offline payments subscription as active on purchase of {txn_id}: {exception}', array(
			'exception' => $e,
			'txn_id'    => $transaction_id,
			'_group'    => 'gateway',
		) );
	}
}

add_action( 'ninja_shop_add_transaction_success', 'it_exchange_offline_payments_mark_subscriptions_as_active_on_purchase', 20 );

/**
 * Mark subscriptions as active when the transaction is marked as cleared for delivery.
 *
 *
 *
 * @param IT_Exchange_Transaction $transaction
 * @param string                  $old_status
 * @param bool                    $old_cleared
 */
function it_exchange_offline_payments_mark_subscriptions_as_active_on_clear( $transaction, $old_status, $old_cleared ) {

	if ( ! function_exists( 'it_exchange_get_transaction_subscriptions' ) ) {
		return;
	}

	if ( it_exchange_get_transaction_method( $transaction ) !== 'offline-payments' ) {
		return;
	}

	$new_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction );
	$is_child    = false;

	if ( $new_cleared && ! $old_cleared ) {

		while ( $transaction->parent ) {
			$transaction = $transaction->parent;
			$is_child    = true;
		}

		$subs = it_exchange_get_transaction_subscriptions( $transaction );

		foreach ( $subs as $sub ) {
			$sub_status = $sub->get_status();

			if ( empty( $sub_status ) || ( $is_child && $sub_status === IT_Exchange_Subscription::STATUS_PAYMENT_FAILED ) ) {
				add_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
				$sub->set_status( IT_Exchange_Subscription::STATUS_ACTIVE );
				$sub->bump_expiration_date();
				remove_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
			}
		}
	}

}

add_action( 'ninja_shop_update_transaction_status', 'it_exchange_offline_payments_mark_subscriptions_as_active_on_clear', 10, 3 );

// Offline Payments doesn't need a subscriber ID.
add_filter( 'ninja_shop_offline-payments_subscription_requires_subscriber_id', '__return_false' );

/**
 * Handles expired transactions that are offline payments
 * If this product autorenews and is an offline payment, it should auto-renew
 * unless the susbcriber status has been deactivated already
 * If it autorenews, it creates an offline payment child transaction
 *
 *
 *
 * @param bool   $true        Default True bool, passed from recurring payments expire schedule
 * @param int    $product_id  Ninja Shop Product ID
 * @param object $transaction Ninja Shop Transaction Object
 *
 * @return bool True if expired, False if not Expired
 */
function it_exchange_offline_payments_handle_expired( $true, $product_id, $transaction ) {

	$transaction = it_exchange_get_transaction( $transaction );
	$product     = it_exchange_get_product( $product_id );

	$transaction_method = it_exchange_get_transaction_method( $transaction->ID );

	if ( 'offline-payments' !== $transaction_method ) {
		return $true;
	}

	$subscription = it_exchange_get_subscription_by_transaction( $transaction, $product );

	if ( $subscription->is_auto_renewing() && $subscription->get_status() === IT_Exchange_Subscription::STATUS_ACTIVE ) {

		it_exchange_offline_payments_add_child_transaction( $transaction );

		if ( it_exchange_offline_payments_default_status() !== 'paid' ) {
			add_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
			$subscription->set_status( IT_Exchange_Subscription::STATUS_PAYMENT_FAILED );
			remove_filter( 'ninja_shop_subscriber_status_activity_use_gateway_actor', '__return_true' );
		}

		return false;
	}

	return $true;
}

add_filter( 'ninja_shop_recurring_payments_handle_expired', 'it_exchange_offline_payments_handle_expired', 10, 3 );

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 *
 *
 * @param IT_Exchange_Transaction $parent_txn
 *
 * @return bool
 */
function it_exchange_offline_payments_add_child_transaction( $parent_txn ) {

	$customer_id = $parent_txn->customer_id;

	if ( ! $customer_id ) {
		return false;
	}

	$total    = $parent_txn->get_total( false );
	$one_time = $parent_txn->get_items()->flatten()->with_only( 'fee' )
	                       ->filter( function ( ITE_Fee_Line_Item $fee ) { return ! $fee->is_recurring(); } );

	$total -= $one_time->total() + $one_time->summary_only()->flatten()->total();

	$uniqid = it_exchange_get_offline_transaction_uniqid();

	it_exchange_add_subscription_renewal_payment( $parent_txn, $uniqid, it_exchange_offline_payments_default_status(), $total );

	return true;
}

/**
 * Register the offline payments message email tag replacement.
 *
 *
 *
 * @param IT_Exchange_Email_Tag_Replacer $replacer
 */
function it_exchange_offline_payments_message_register_tag( IT_Exchange_Email_Tag_Replacer $replacer ) {

	$tag = new IT_Exchange_Email_Tag_Base(
		'offline_payments_message', __( 'Offline Payments MEssage', 'it-l10n-ithemes-exchange' ),
		__( 'Adds the instructions after purchase message from the Offline Payments gateway settings.', 'it-l10n-ithemes-exchange' ),
		'it_exchange_offline_payments_email_notification_message'
	);

	$tag->add_required_context( 'transaction' );
	$tag->add_available_for( 'receipt' );

	$replacer->add_tag( $tag );
}

add_action( 'ninja_shop_email_notifications_register_tags', 'it_exchange_offline_payments_message_register_tag' );

/**
 * Render the offline payments email notification message.
 *
 * @param array $context
 *
 * @return string
 */
function it_exchange_offline_payments_email_notification_message( $context ) {

	$transaction = $context['transaction'];

	$instructions = '';

	if ( 'offline-payments' === it_exchange_get_transaction_method( $transaction ) ) {

		$options = it_exchange_get_option( 'addon_offline_payments' );

		if ( ! empty( $options['offline-payments-instructions'] ) ) {
			$instructions = $options['offline-payments-instructions'];
		}
	}

	return $instructions;
}

/**
 * Retrieve the default payment status for offline payments.
 *
 *
 *
 * @return string
 */
function it_exchange_offline_payments_default_status() {

	$settings = it_exchange_get_option( 'addon_offline_payments' );

	return $settings['offline-payments-default-status'];
}
