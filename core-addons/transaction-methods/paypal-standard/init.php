<?php
/**
 * Hooks for PayPal Standard (insecure) add-on
 *
 * @package IT_Exchange
 * 
 */

if ( ! defined( 'PAYPAL_LIVE_URL' ) ) {
	define( 'PAYPAL_LIVE_URL', 'https://www.paypal.com/' );
}

if ( ! defined( 'PAYPAL_PAYMENT_LIVE_URL' ) ) {
	define( 'PAYPAL_PAYMENT_LIVE_URL', 'https://www.paypal.com/cgi-bin/webscr' );
}
if ( ! defined( 'PAYPAL_PAYMENT_SANDBOX_URL' ) ) {
	define( 'PAYPAL_PAYMENT_SANDBOX_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr' );
}

add_action( 'ninja_shop_register_gateways', function( ITE_Gateways $gateways ) {

	require_once dirname( __FILE__ ) . '/handlers/class.purchase.php';
	require_once dirname( __FILE__ ) . '/handlers/class.webhook.php';
	require_once dirname( __FILE__ ) . '/class.gateway.php';

	$gateways::register( new ITE_PayPal_Standard_Gateway() );
} );

/**
 * Mark this transaction method as okay to manually change transactions
 *
 *
 */
add_filter( 'ninja_shop_paypal-standard_transaction_status_can_be_manually_changed', '__return_true' );

/**
 * Returns status options
 *
 *
 * @return array
 */
function it_exchange_paypal_standard_get_default_status_options() {
	$options = array(
		'Pending'   => _x( 'Pending', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'Completed' => _x( 'Paid', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'Reversed'  => _x( 'Reversed', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'Refunded'  => _x( 'Refunded', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
		'Voided'    => _x( 'Voided', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
	);

	return $options;
}

add_filter( 'ninja_shop_get_status_options_for_paypal-standard_transaction', 'it_exchange_paypal_standard_get_default_status_options' );

/**
 * Outputs wizard settings for PayPal
 *
 *
 * @todo  make this better, probably
 *
 * @param object $form Current IT Form object
 *
 * @return void
 */
function it_exchange_print_paypal_standard_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings                           = it_exchange_get_option( 'addon_paypal_standard', true );
	$form_values                        = ITUtility::merge_defaults( ITForm::get_post_data(), $settings );

	// Alter setting keys for wizard
	foreach ( $form_values as $key => $value ) {
		$form_values[ 'paypal-standard-' . $key ] = $value;
		unset( $form_values[ $key ] );
	}

	$hide_if_js = it_exchange_is_addon_enabled( 'paypal-standard' ) ? '' : 'hide-if-js';
	?>
	<div class="field paypal-standard-wizard <?php echo $hide_if_js; ?>">
		<?php if ( empty( $hide_if_js ) ) { ?>
			<input class="enable-paypal-standard" type="hidden" name="it-exchange-transaction-methods[]" value="paypal-standard" />
		<?php } ?>
		<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $form_values ); ?>
	</div>
	<?php
}

//add_action( 'ninja_shop_print_paypal-standard_wizard_settings', 'it_exchange_print_paypal_standard_wizard_settings' );

/**
 * Stripe URL to perform refunds
 *
 *
 *
 * @param string $url passed by WP filter.
 * @param string $url transaction URL
 *
 * @return string
 */
function it_exchange_refund_url_for_paypal_standard( $url ) {

	return 'https://paypal.com/';

}

add_filter( 'ninja_shop_refund_url_for_paypal-standard', 'it_exchange_refund_url_for_paypal_standard' );

/**
 * Determine if the transaction currently trying to process has already been processed
 *
 *
 *
 * @param bool|int $processed If this transaction has already been processed.
 * @param bool|int $processed False or Ninja Shop Transaction ID for this transaction
 *
 * @return int|bool
 */
function handle_purchase_cart_request_already_processed_for_paypal_standard( $processed ) {

	if ( ! empty( $processed ) ) {
		return $processed;
	}

	if ( ! empty( $_REQUEST['it-exchange-transaction-method'] ) && 'paypal-standard' === $_REQUEST['it-exchange-transaction-method'] ) {

		if ( ! empty( $_REQUEST['paypal-standard-nonce'] ) && wp_verify_nonce( $_REQUEST['paypal-standard-nonce'], 'pps-nonce' ) ) {

			if ( ! empty( $_REQUEST['tx'] ) ) { //if PDT is enabled
				$transaction_id = sanitize_text_field( $_REQUEST['tx'] );
			} else if ( ! empty( $_REQUEST['txn_id'] ) ) { //if PDT is not enabled
				$transaction_id = sanitize_text_field( $_REQUEST['txn_id'] );
			} else {
				$transaction_id = null;
			}

			$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $transaction_id );
			if ( ! empty( $transactions ) ) {
				foreach ( $transactions as $transaction ) { //really only one
					return $transaction->ID;
				}
			}
		}

	}

	return false;

}

add_filter( 'handle_purchase_cart_request_already_processed_for_paypal-standard', 'handle_purchase_cart_request_already_processed_for_paypal_standard' );

/**
 * This proccesses a paypal transaction.
 *
 *
 *
 * @param string $status             passed by WP filter.
 * @param object $transaction_object The transaction object
 *
 * @return int|bool
 *
 * @throws IT_Exchange_Locking_Exception
 */
function it_exchange_process_paypal_standard_addon_transaction( $status, $transaction_object ) {

	if ( $status ) { //if this has been modified as true already, return.
		return $status;
	}

	if ( empty( $_REQUEST['it-exchange-transaction-method'] ) || 'paypal-standard' !== $_REQUEST['it-exchange-transaction-method'] ) {
		return false;
	}

	if ( empty( $_REQUEST['paypal-standard-nonce'] ) || ! wp_verify_nonce( $_REQUEST['paypal-standard-nonce'], 'pps-nonce' ) ) {
		return false;
	}

	if ( ! empty( $_REQUEST['tx'] ) ) { //if PDT is enabled
		$paypal_id = sanitize_text_field( $_REQUEST['tx'] );
	} else if ( ! empty( $_REQUEST['txn_id'] ) ) { //if PDT is not enabled
		$paypal_id = sanitize_text_field( $_REQUEST['txn_id'] );
	} else {
		$paypal_id = null;
	}

	if ( ! empty( $_REQUEST['cm'] ) ) {
		$transient_transaction_id = sanitize_text_field( $_REQUEST['cm'] );
	} else if ( ! empty( $_REQUEST['custom'] ) ) {
		$transient_transaction_id = sanitize_text_field( $_REQUEST['custom'] );
	} else {
		$transient_transaction_id = null;
	}

	if ( ! empty( $_REQUEST['amt'] ) ) { //if PDT is enabled
		$transaction_amount = sanitize_text_field( $_REQUEST['amt'] );
	} else if ( ! empty( $_REQUEST['mc_gross'] ) ) { //if PDT is not enabled
		$transaction_amount = sanitize_text_field( $_REQUEST['mc_gross'] );
	} else {
		$transaction_amount = null;
	}

	if ( ! empty( $_REQUEST['st'] ) ) { //if PDT is enabled
		$paypal_status = sanitize_text_field( $_REQUEST['st'] );
	} else if ( ! empty( $_REQUEST['payment_status'] ) ) { //if PDT is not enabled
		$paypal_status = sanitize_text_field( $_REQUEST['payment_status'] );
	} else {
		$paypal_status = null;
	}

	if ( $transient_transaction_id ) {
		$lock = "pps-$transient_transaction_id";
	} else {
		$lock = null;
	}

	try {
		if ( ! empty( $paypal_id ) && ! empty( $transient_transaction_id ) && null !== $transaction_amount && ! empty( $paypal_status ) ) {

			if ( $lock ) {
				it_exchange_lock( $lock, 2 );
			}

			$it_exchange_customer = it_exchange_get_current_customer();

			if ( number_format( $transaction_amount, '2', '', '' ) != number_format( $transaction_object->total, '2', '', '' ) ) {
				throw new Exception( __( 'Error: Amount charged is not the same as the cart total!', 'it-l10n-ithemes-exchange' ) );
			}

			if ( $txn_id = it_exchange_paypal_standard_addon_get_ite_transaction_id( $transient_transaction_id ) ) {

				$transaction = it_exchange_get_transaction( $txn_id );
				$transaction->update_transaction_meta( 'method_id', $paypal_id );

			} else {

				$transient_data = it_exchange_get_transient_transaction( 'pps', $transient_transaction_id );
				if ( ! empty( $transient_data['transaction_id'] ) ) {
					//Already created transaction, by IPN probably
					$txn_id = $transient_data['transaction_id'];
				} else {
					//Transaction shouldn't have been created yet...
					if ( false === $txn_id = it_exchange_paypal_standard_addon_get_ite_transaction_id( $paypal_id ) ) {

						if ( ! empty( $transaction_object->products ) ) {

							foreach ( $transaction_object->products as $key => $product ) {

								if ( it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'trial-enabled' ) ) ) {

									$allow_trial = true;

									if ( is_user_logged_in() ) {
										if ( function_exists( 'it_exchange_get_session_data' ) ) {
											$member_access = it_exchange_get_session_data( 'member_access' );
											$children      = (array) it_exchange_membership_addon_get_all_the_children( $product['product_id'] );
											$parents       = (array) it_exchange_membership_addon_get_all_the_parents( $product['product_id'] );
											foreach ( $member_access as $prod_id => $txn_id ) {
												if ( $prod_id == $product['product_id'] || in_array( $prod_id, $children ) || in_array( $prod_id, $parents ) ) {
													$allow_trial = false;
													break;
												}
											}
										}
									}

									if ( $allow_trial ) {
										//make sure the product has the trial enabled
										$transient_data['transaction_object']->total     = '0.00'; //should be 0.00 ... since this is a free trial!
										$transient_data['transaction_object']->sub_total = '0.00'; //should be 0.00 ... since this is a free trial!
									}
								}
							}
						}

						//If the transient didn't exist and there isn't a transaction with this ID already, create it.
						$txn_id = it_exchange_add_transaction( 'paypal-standard', $paypal_id, $paypal_status, $it_exchange_customer->id, $transaction_object );
						if ( ! empty( $transient_data ) ) {
							it_exchange_update_transient_transaction( 'pps', $transient_transaction_id, $transient_data['customer_id'], $transient_data['transaction_object'], $txn_id );
						}
					}
				}

			}

		} else if ( is_null( $paypal_id ) && is_null( $transient_transaction_id ) && is_null( $transaction_amount ) && is_null( $paypal_status ) ) {

			//Check to see if the transient transaction was for a free trial membership and then proceed as necessary...
			$transient_transaction_id = it_exchange_get_session_data( 'pps_transient_transaction_id' );
			$lock                     = "pps-{$transient_transaction_id[0]}";

			if ( $lock ) {
				it_exchange_lock( $lock, 2 );
			}

			it_exchange_clear_session_data( 'pps_transient_transaction_id' );

			if ( ! empty( $transient_transaction_id[0] ) ) {
				if ( false === $txn_id = it_exchange_paypal_standard_addon_get_ite_transaction_id( $transient_transaction_id[0] ) ) {

					$transient_data = it_exchange_get_transient_transaction( 'pps', $transient_transaction_id[0] );

					if ( ! empty( $transient_data ) ) {

						if ( ! empty( $transient_data['transaction_object']->products ) ) {

							foreach ( $transient_data['transaction_object']->products as $key => $product ) { //really only one product

								if ( it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'trial-enabled' ) ) ) {

									$allow_trial = true;

									if ( is_user_logged_in() ) {
										if ( function_exists( 'it_exchange_get_session_data' ) ) {
											$member_access = it_exchange_get_session_data( 'member_access' );
											$children      = (array) it_exchange_membership_addon_get_all_the_children( $product['product_id'] );
											$parents       = (array) it_exchange_membership_addon_get_all_the_parents( $product['product_id'] );
											foreach ( $member_access as $prod_id => $txn_id ) {
												if ( $prod_id == $product['product_id'] || in_array( $prod_id, $children ) || in_array( $prod_id, $parents ) ) {
													$allow_trial = false;
													break;
												}
											}
										}
									}

									if ( $allow_trial ) {
										//make sure the product has the trial enabled
										$transient_data['transaction_object']->total     = '0.00'; //should be 0.00 ... since this is a free trial!
										$transient_data['transaction_object']->sub_total = '0.00'; //should be 0.00 ... since this is a free trial!
									}

									$txn_id = it_exchange_add_transaction( 'paypal-standard', $transient_transaction_id[0], 'Completed', it_exchange_get_current_customer_id(), $transient_data['transaction_object'] );
									it_exchange_update_transient_transaction( 'pps', $transient_transaction_id[0], $transient_data['customer_id'], $transient_data['transaction_object'], $txn_id ); //update transient with ITE txn_id, to help IPN set subscriber ID.
								}
							}
						}
					}
				}
			}
		}

		if ( empty( $txn_id ) ) {
			it_exchange_add_message( 'error', __( 'Unknown error while processing with PayPal. Please try again later.', 'it-l10n-ithemes-exchange' ) );

			return false;
		}

	}
	catch ( Exception $e ) {

		if ( $e instanceof IT_Exchange_Locking_Exception ) {
			throw $e;
		}

		it_exchange_add_message( 'error', $e->getMessage() );

		return false;
	}

	it_exchange_release_lock( $lock );

	return $txn_id;
}

//add_action( 'ninja_shop_do_transaction_paypal-standard', 'it_exchange_process_paypal_standard_addon_transaction', 10, 2 );

/**
 * Grab the paypal customer ID for a WP user
 *
 *
 *
 * @param integer $customer_id the WP customer ID
 *
 * @return string
 */
function it_exchange_get_paypal_standard_addon_customer_id( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_id', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 *
 *
 * @param integer $customer_id        the WP user ID
 * @param integer $paypal_standard_id the paypal customer ID
 *
 * @return boolean
 */
function it_exchange_set_paypal_standard_addon_customer_id( $customer_id, $paypal_standard_id ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_id', $paypal_standard_id );
}

/**
 * Grab the paypal customer email for a WP user
 *
 *
 *
 * @param integer $customer_id the WP customer ID
 *
 * @return string
 */
function it_exchange_get_paypal_standard_addon_customer_email( $customer_id ) {
	return get_user_meta( $customer_id, '_it_exchange_paypal_standard_email', true );
}

/**
 * Add the paypal customer email as user meta on a WP user
 *
 *
 *
 * @param integer $customer_id           the WP user ID
 * @param string  $paypal_standard_email the paypal customer email
 *
 * @return boolean
 */
function it_exchange_set_paypal_standard_addon_customer_email( $customer_id, $paypal_standard_email ) {
	return update_user_meta( $customer_id, '_it_exchange_paypal_standard_email', $paypal_standard_email );
}

/**
 * This is the function registered in the options array when it_exchange_register_addon was called for paypal
 *
 * It tells Exchange where to find the settings page
 *
 * @return void
 */
function it_exchange_paypal_standard_settings_callback() {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$IT_Exchange_PayPal_Standard_Add_On->print_settings_page();
}

/**
 * This is the function prints the payment form on the Wizard Settings screen
 *
 * @return void
 */
function paypal_standard_print_wizard_settings( $form ) {
	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();
	$settings                           = it_exchange_get_option( 'addon_paypal_standard', true );
	?>
	<div class="field paypal_standard-wizard hide-if-js">
		<?php $IT_Exchange_PayPal_Standard_Add_On->get_paypal_standard_payment_form_table( $form, $settings ); ?>
	</div>
	<?php
}

/**
 * Saves paypal settings when the Wizard is saved
 *
 *
 *
 * @return void
 */
function it_exchange_save_paypal_standard_wizard_settings( $errors ) {
	if ( ! empty( $errors ) ) {
		return $errors;
	}

	$IT_Exchange_PayPal_Standard_Add_On = new IT_Exchange_PayPal_Standard_Add_On();

	return $IT_Exchange_PayPal_Standard_Add_On->paypal_standard_save_wizard_settings();
}

//add_action( 'ninja_shop_save_paypal-standard_wizard_settings', 'it_exchange_save_paypal_standard_wizard_settings' );

/**
 * Default settings for paypal_standard
 *
 *
 * @deprecated 2.0.0
 *
 * @param array $values
 *
 * @return array
 */
function it_exchange_paypal_standard_addon_default_settings( $values ) {
	$defaults = array(
		'live-email-address'    => '',
		'purchase-button-label' => __( 'Pay with PayPal', 'it-l10n-ithemes-exchange' ),
	);

	return ITUtility::merge_defaults( $values, $defaults );
}

/**
 * Returns the button for making the PayPal faux payment button
 *
 *
 *
 * @param array $options
 *
 * @return string HTML button
 */
function it_exchange_paypal_standard_addon_make_payment_button( $options ) {

	if ( 0 >= it_exchange_get_cart_total( false ) ) {
		return;
	}

	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	$payment_form = '';

	if ( $paypal_email = $paypal_settings['live-email-address'] ) {
		$payment_form .= '<form action="" method="post">';
		$payment_form .= '<input type="submit" class="it-exchange-paypal-standard-button" name="paypal_standard_purchase" value="' . $paypal_settings['purchase-button-label'] . '" />';
		$payment_form .= '</form>';
	}

	return $payment_form;

}

//add_filter( 'ninja_shop_get_paypal-standard_make_payment_button', 'it_exchange_paypal_standard_addon_make_payment_button', 10, 2 );

/**
 * Process the faux PayPal Standard form
 *
 *
 *
 * @param array $options
 *
 * @return string HTML button
 */
function it_exchange_process_paypal_standard_form() {

	$paypal_settings = it_exchange_get_option( 'addon_paypal_standard' );

	if ( ! empty( $_REQUEST['paypal_standard_purchase'] ) ) {

		$customer = it_exchange_get_current_customer();
		$temp_id  = it_exchange_create_unique_hash();

		$transaction_object = it_exchange_generate_transaction_object();

		it_exchange_add_transient_transaction( 'pps', $temp_id, $customer->id, $transaction_object );
		it_exchange_update_session_data( 'pps_transient_transaction_id', $temp_id );

		if ( $url = it_exchange_paypal_standard_addon_get_payment_url( $temp_id ) ) {
			wp_redirect( $url );
			die();
		} else {
			it_exchange_add_message( 'error', __( 'Error processing PayPal form. Missing valid PayPal information.', 'it-l10n-ithemes-exchange' ) );
			$url = ! wp_get_referer() ? it_exchange_get_page_url( 'checkout' ) : wp_get_referer();
			wp_redirect( $url );
			die();
		}

	}

}

add_action( 'template_redirect', 'it_exchange_process_paypal_standard_form', 11 );

/**
 * Returns the button for making the PayPal real payment button
 *
 *
 *
 * @param string $temp_id Temporary ID we reference late with IPN
 *
 * @return string HTML button
 */
function it_exchange_paypal_standard_addon_get_payment_url( $temp_id ) {

	if ( 0 >= it_exchange_get_cart_total( false ) ) {
		return;
	}

	$general_settings = it_exchange_get_option( 'settings_general' );
	$paypal_settings  = it_exchange_get_option( 'addon_paypal_standard' );

	$paypal_payment_url = '';

	if ( $paypal_email = $paypal_settings['live-email-address'] ) {

		$subscription         = false;
		$it_exchange_customer = it_exchange_get_current_customer();

		remove_filter( 'the_title', 'wptexturize' ); // remove this because it screws up the product titles in PayPal

		if ( 1 === it_exchange_get_cart_products_count() ) {
			$cart = it_exchange_get_cart_products();
			foreach ( $cart as $product ) {
				if ( it_exchange_product_supports_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
					if ( it_exchange_product_has_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) ) ) {
						$trial_enabled        = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'trial-enabled' ) );
						$trial_interval       = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'trial-interval' ) );
						$trial_interval_count = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'trial-interval-count' ) );
						$auto_renew           = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'auto-renew' ) );
						$interval             = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'interval' ) );
						$interval_count       = it_exchange_get_product_feature( $product['product_id'], 'recurring-payments', array( 'setting' => 'interval-count' ) );

						switch ( $interval ) {
							case 'year':
								$unit = 'Y';
								break;
							case 'week':
								$unit = 'W';
								break;
							case 'day':
								$unit = 'D';
								break;
							case 'month':
							default:
								$unit = 'M';
								break;

						}
						$duration = apply_filters( 'ninja_shop_paypal_standard_addon_subscription_duration', $interval_count, $product );

						$trial_unit     = null;
						$trial_duration = null;

						if ( $trial_enabled ) {
							$allow_trial = true;
							//Should we all trials?
							if ( 'membership-product-type' === it_exchange_get_product_type( $product['product_id'] ) ) {
								if ( is_user_logged_in() ) {
									if ( function_exists( 'it_exchange_get_session_data' ) ) {
										$member_access = it_exchange_get_session_data( 'member_access' );
										$children      = (array) it_exchange_membership_addon_get_all_the_children( $product['product_id'] );
										$parents       = (array) it_exchange_membership_addon_get_all_the_parents( $product['product_id'] );
										foreach ( $member_access as $prod_id => $txn_id ) {
											if ( $prod_id === $product['product_id'] || in_array( $prod_id, $children ) || in_array( $prod_id, $parents ) ) {
												$allow_trial = false;
												break;
											}
										}
									}
								}
							}

							$allow_trial = apply_filters( 'ninja_shop_paypal_standard_addon_get_payment_url_allow_trial', $allow_trial, $product['product_id'] );

							if ( $allow_trial && 0 < $trial_interval_count ) {
								switch ( $trial_interval ) {
									case 'year':
										$trial_unit = 'Y';
										break;
									case 'week':
										$trial_unit = 'W';
										break;
									case 'day':
										$trial_unit = 'D';
										break;
									case 'month':
									default:
										$trial_unit = 'M';
										break;
								}
								$trial_duration = apply_filters( 'ninja_shop_paypal_standard_addon_subscription_trial_duration', $trial_interval_count, $product );
							}
						}

						$subscription = true;
						$product_id   = $product['product_id'];
					}
				}
			}
		}

		if ( $subscription ) {
			//https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-standard/integration-guide/Appx_websitestandard_htmlvariables/#id08A6HI00JQU
			//a1, t1, p1 are for the first trial periods which is not supported with the Recurring Payments add-on
			//a2, t2, p2 are for the second trial period, which is not supported with the Recurring Payments add-on
			//a3, t3, p3 are required for the actual subscription details
			$paypal_args = array(
				'cmd' => '_xclick-subscriptions',
				'a3'  => number_format( it_exchange_get_cart_total( false ), 2, '.', '' ),
				//Regular subscription price.
				'p3'  => $duration,
				//Subscription duration. Specify an integer value in the allowable range for the units of duration that you specify with t3.
				't3'  => $unit,
				//Regular subscription units of duration. (D, W, M, Y) -- we only use M,Y by default
				'src' => 1,
				//Recurring payments.
			);

			if ( ! empty( $trial_unit ) && ! empty( $trial_duration ) ) {
				$paypal_args['a1'] = 0;
				$paypal_args['p1'] = $trial_duration;
				$paypal_args['t1'] = $trial_unit;
			}

		} else {

			$paypal_args = array(
				'cmd'      => '_xclick',
				'amount'   => number_format( it_exchange_get_cart_total( false ), 2, '.', '' ),
				'quantity' => '1',
			);

		}

		$nonce = wp_create_nonce( 'pps-nonce' );

		$query = array(
			'business'      => $paypal_email,
			'item_name'     => strip_tags( it_exchange_get_cart_description() ),
			'return'        => add_query_arg( array(
				'it-exchange-transaction-method' => 'paypal-standard',
				'paypal-standard-nonce'          => $nonce
			), it_exchange_get_page_url( 'transaction' ) ),
			'currency_code' => $general_settings['default-currency'],
			'notify_url'    => get_home_url() . '/?' . it_exchange_get_webhook( 'paypal-standard' ) . '=1',
			'no_note'       => '1',
			'shipping'      => '0',
			'email'         => $it_exchange_customer->data->user_email,
			'rm'            => '2',
			'cancel_return' => ( it_exchange_is_multi_item_cart_allowed() ? it_exchange_get_page_url( 'cart' ) : get_home_url() ),
			'custom'        => $temp_id,
			'bn'            => 'iThemes_SP'
		);

		$purchase_requirements = it_exchange_get_purchase_requirements();
		// If we have the shipping info, we may as well include it in the fields sent to PayPal
		if ( ! empty( $purchase_requirements['shipping-address'] ) ) {
			$shipping_address          = it_exchange_get_cart_shipping_address();
			$query['address_override'] = '1';
			$query['no_shipping']      = '2';
			$query['first_name']       = ! empty( $shipping_address['first-name'] ) ? $shipping_address['first-name'] : '';
			$query['last_name']        = ! empty( $shipping_address['last-name'] ) ? $shipping_address['last-name'] : '';
			$query['address1']         = ! empty( $shipping_address['address1'] ) ? $shipping_address['address1'] : '';
			$query['address2']         = ! empty( $shipping_address['address2'] ) ? $shipping_address['address2'] : '';
			$query['city']             = ! empty( $shipping_address['city'] ) ? $shipping_address['city'] : '';
			$query['state']            = ! empty( $shipping_address['state'] ) ? $shipping_address['state'] : '';
			$query['zip']              = ! empty( $shipping_address['zip'] ) ? $shipping_address['zip'] : '';
			$query['country']          = ! empty( $shipping_address['country'] ) ? $shipping_address['country'] : '';
		} else {
			$query['no_shipping'] = '1';
		}

		$query = array_merge( $paypal_args, $query );
		$query = apply_filters( 'ninja_shop_paypal_standard_query', $query );

		$paypal_payment_url = PAYPAL_PAYMENT_LIVE_URL . '?' . http_build_query( $query );

	} else {

		it_exchange_add_message( 'error', __( 'ERROR: Invalid PayPal Setup' ) );
		$paypal_payment_url = it_exchange_get_page_url( 'cart' );

	}

	return $paypal_payment_url;

}

/**
 * Adds the paypal webhook to the global array of keys to listen for
 *
 *
 *
 * @param array $webhooks existing
 *
 * @return array
 */
function it_exchange_paypal_standard_addon_register_webhook() {
	$key   = 'paypal-standard';
	$param = apply_filters( 'ninja_shop_paypal-standard_webhook', 'it_exchange_paypal-standard' );
	it_exchange_register_webhook( $key, $param );
}

//add_filter( 'init', 'it_exchange_paypal_standard_addon_register_webhook' );

/**
 * Processes webhooks for PayPal Web Standard
 *
 *
 * @todo  actually handle the exceptions
 * @todo  verify IPN mc_gross values match IPN if converting a transient transaction
 *
 * @param array $request really just passing  $_REQUEST
 */
function it_exchange_paypal_standard_addon_process_webhook( $request ) {

	// we have to request a lock before validating that the IPN is valid
	if ( ! empty( $request['custom'] ) ) {
		$tmp_txn_id = $request['custom'];
	} else if ( ! empty( $request['transaction_subject'] ) ) {
		$tmp_txn_id = $request['transaction_subject'];
	} else {
		$tmp_txn_id = false;
	}

	if ( $tmp_txn_id ) {
		$tmp_txn_id = sanitize_text_field( $tmp_txn_id );
		it_exchange_lock( "pps-$tmp_txn_id", 2 );
	}

	$payload['cmd'] = '_notify-validate';

	foreach ( $_POST as $key => $value ) {
		$payload[ $key ] = stripslashes( sanitize_textarea_field( $value ) );
	}

	$paypal_api_url = ! empty( $_REQUEST['test_ipn'] ) ? PAYPAL_PAYMENT_SANDBOX_URL : PAYPAL_PAYMENT_LIVE_URL;
	$response       = wp_remote_post( $paypal_api_url, array( 'body' => $payload, 'httpversion' => '1.1' ) );
	$body           = wp_remote_retrieve_body( $response );

	if ( 'VERIFIED' !== $body ) {

		status_header( 400 );

		error_log( sprintf( __( 'Invalid IPN sent from PayPal - PayLoad: %s', 'it-l10n-ithemes-exchange' ), maybe_serialize( $payload ) ) );
		error_log( sprintf( __( 'Invalid IPN sent from PayPal - Response: %s', 'it-l10n-ithemes-exchange' ), maybe_serialize( $response ) ) );

		return;
	}

	$subscriber_id = ! empty( $request['subscr_id'] ) ? $request['subscr_id'] : false;
	$subscriber_id = ! empty( $request['recurring_payment_id'] ) ? $request['recurring_payment_id'] : $subscriber_id;

	if ( ! empty( $request['txn_type'] ) ) {

		// this is a standard paypal payment
		if ( 'web_accept' === $request['txn_type'] ) {

			$exchange_txn_id = it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['txn_id'] );

			if ( empty( $exchange_txn_id ) ) {

				$transient_data = it_exchange_get_transient_transaction( 'pps', $request['custom'] );

				$method_id = $request['txn_id'];
				$customer = $transient_data['customer_id'];
				$status = $request['payment_status'];
				$cart = $transient_data['transaction_object'];

				$txn_id = it_exchange_add_transaction( 'paypal-standard', $method_id, $status, $customer, $cart );
				it_exchange_update_transient_transaction( 'pps', $tmp_txn_id, $customer, $cart, $txn_id );

				return;
			}

			switch ( strtolower( $request['payment_status'] ) ) {

				case 'completed' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] );
					break;
				case 'reversed' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['reason_code'] );
					break;
			}

			return;
		}

		if ( ! empty( $tmp_txn_id ) ) {

			$transient_data = it_exchange_get_transient_transaction( 'pps', $tmp_txn_id );

			$customer_id        = $transient_data['customer_id'];
			$transaction_object = $transient_data['transaction_object'];

			// the custom variable holds the transient transaction ID we generated before sending the customer to paypal
			// this is tepmorarily stored as the 'method_id'
			$custom_txn_id = ! empty( $request['custom'] ) ? it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['custom'] ) : false;

			// when a user completes their payment, we update the method ID to be paypal's transaction ID
			$real_txn_id = ! empty( $request['txn_id'] ) ? it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['txn_id'] ) : false;

			if ( ! empty( $transient_data ) && empty( $transient_data['transaction_id'] ) ) {

				/* The subscriber signup event is sent whenever a customer is subscribed
				   to a recurring payment. This includes free trials. This event, however,
				   isn't guaranteed to arrive before or after the subscription payment event.
				*/
				if ( 'subscr_signup' === $request['txn_type'] ) {

					// PayPal stores the amount the customer paid for the trial in this variable
					if ( isset( $request['amount1'] ) ) {
						$transaction_object->total     = $request['amount1'];
						$transaction_object->sub_total = $request['amount1'];
					}

					// When this event arrives, the customer has successfully paid for their membership
					// or this is a free trial
					$new_status = 'Completed';
					$method_id  = $request['custom'];

				} else if ( ! empty( $request['txn_id'] ) && ! empty( $request['payment_status'] ) ) {

					// otherwise, this is a payment event, and we should create the txn with the given status

					$new_status = $request['payment_status'];
					$method_id  = $request['txn_id'];
				}

				// determine the transaction ID in exchange, but give priority to the transient transaction ID
				if ( ! empty( $custom_txn_id ) ) {
					$exchange_txn_id = $custom_txn_id;
				} else if ( ! empty( $real_txn_id ) ) {
					$exchange_txn_id = $real_txn_id;
				}

				if ( empty( $exchange_txn_id ) && isset( $method_id ) && isset( $new_status ) ) {
					// if we don't have an exchange txn ID, this is a new transaction and create it.
					$exchange_txn_id = it_exchange_add_transaction( 'paypal-standard', $method_id, $new_status, $customer_id, $transaction_object );
				}

				it_exchange_update_transient_transaction( 'pps', $tmp_txn_id, $customer_id, $transaction_object, $exchange_txn_id );
			}
		}

		switch ( $request['txn_type'] ) {

			case 'subscr_payment':

				if ( $request['payment_status'] == 'Completed' ) {
					// if we can still retrieve the transaction by its transient transaction ID
					// then this payment is a free trial being converted to a full subscription
					if ( $temp_txn_id = it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['custom'] ) ) {

						$transaction = it_exchange_get_transaction( $temp_txn_id );
						// update the method ID to be an MD5 of paypal's internal ID
						// paypal doesn't have a txn id for the trial payment, so we generated a different ID as not to conflict
						$transaction->update_transaction_meta( 'method_id', md5( $request['txn_id'] ) );
					}

					// attempt to update the payment status for a transaction
					if ( ! it_exchange_paypal_standard_addon_update_transaction_status( $request['txn_id'], $request['payment_status'] ) ) {
						//If the transaction isn't found, we've got a new payment
						$GLOBALS['it_exchange']['child_transaction'] = true;
						it_exchange_paypal_standard_addon_add_child_transaction( $request['txn_id'], $request['payment_status'], $subscriber_id, $request['mc_gross'] );
					} else {
						//If it is found, make sure the subscriber ID is attached to it
						it_exchange_paypal_standard_addon_update_subscriber_id( $request['txn_id'], $subscriber_id );
					}

					// if we have a good payment, make sure to keep the subscription status as active
					it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'active' );
					break;
				}
				break;

			case 'subscr_signup':

				/* We need to do some free trial magic! */
				if ( it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['custom'] ) ) {
					it_exchange_paypal_standard_addon_update_subscriber_id( $request['custom'], $subscriber_id );
					it_exchange_paypal_standard_addon_update_transaction_status( $request['custom'], 'Completed' );
				} else if ( isset( $request['txn_id'] ) && it_exchange_paypal_standard_addon_get_ite_transaction_id( $request['txn_id'] ) ) {
					it_exchange_paypal_standard_addon_update_subscriber_id( $request['txn_id'], $subscriber_id );
				}

				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'active' );
				break;

			case 'recurring_payment_suspended':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'suspended' );
				break;

			case 'subscr_cancel':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
				break;

			case 'subscr_eot':
				it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'deactivated' );
				break;

		}

		if ( $tmp_txn_id ) {
			it_exchange_release_lock( "pps-$tmp_txn_id" );
		}

	} else {

		//These IPNs don't have txn_types, why PayPal!? WHY!?
		if ( ! empty( $request['reason_code'] ) ) {

			switch ( $request['reason_code'] ) {

				case 'refund' :
					it_exchange_paypal_standard_addon_update_transaction_status( $request['parent_txn_id'], $request['payment_status'] );
					it_exchange_paypal_standard_addon_add_refund_to_transaction( $request['parent_txn_id'], $request['mc_gross'] );
					if ( $subscriber_id ) {
						it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, 'cancelled' );
					}
					break;

			}

		}

	}
}

//add_action( 'ninja_shop_webhook_it_exchange_paypal-standard', 'it_exchange_paypal_standard_addon_process_webhook' );

/**
 * Gets Ninja Shop's Transaction ID from PayPal Standard's Transaction ID
 *
 *
 *
 * @param integer $paypal_standard_id id of paypal transaction
 *
 * @return integer iTheme Exchange's Transaction ID
 */
function it_exchange_paypal_standard_addon_get_ite_transaction_id( $paypal_standard_id ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach ( $transactions as $transaction ) { //really only one
		return $transaction->ID;
	}

	return false;
}

/**
 * Grab a transaction from the paypal transaction ID
 *
 *
 *
 * @param int $paypal_standard_id PayPal's internal transaction ID.
 *
 * @return IT_Exchange_Transaction[] object
 */
function it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_method_id',
		'meta_value'  => $paypal_standard_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);

	return it_exchange_get_transactions( $args );
}

/**
 * Grab a transaction from the paypal transaction ID
 *
 *
 *
 * @param int $subscriber_id Subscriber ID for this transaction.
 *
 * @return IT_Exchange_Transaction[]
 */
function it_exchange_paypal_standard_addon_get_transaction_id_by_subscriber_id( $subscriber_id ) {
	$args = array(
		'meta_key'    => '_it_exchange_transaction_subscriber_id',
		'meta_value'  => $subscriber_id,
		'numberposts' => 1, //we should only have one, so limit to 1
	);

	return it_exchange_get_transactions( $args );
}

/**
 * Updates a paypals transaction status based on paypal ID
 *
 *
 *
 * @param integer $paypal_standard_id id of paypal transaction
 * @param string  $new_status         new status
 *
 * @return bool
 */
function it_exchange_paypal_standard_addon_update_transaction_status( $paypal_standard_id, $new_status ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach ( $transactions as $transaction ) { //really only one
		$current_status = it_exchange_get_transaction_status( $transaction );
		if ( $new_status !== $current_status ) {
			it_exchange_update_transaction_status( $transaction, $new_status );
		}

		return true;
	}

	return false;
}

/**
 * Add a new transaction, really only used for subscription payments.
 * If a subscription pays again, we want to create another transaction in Exchange
 * This transaction needs to be linked to the parent transaction.
 *
 *
 *
 * @param integer  $method_id id of paypal transaction
 * @param string   $payment_status     new status
 * @param int|bool $subscriber_id      from PayPal (optional)
 * @param string   $amount             Amount the customer paid.
 *
 * @return bool
 */
function it_exchange_paypal_standard_addon_add_child_transaction( $method_id, $payment_status, $subscriber_id = false, $amount ) {

	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $method_id );

	if ( !empty( $transactions ) ) {
		//this transaction DOES exist, don't try to create a new one, just update the status
		it_exchange_paypal_standard_addon_update_transaction_status( $method_id, $payment_status );
	} else {

		$parent = null;

		$transactions = it_exchange_paypal_standard_addon_get_transaction_id_by_subscriber_id( $subscriber_id );

		foreach ( $transactions as $transaction ) { //really only one
			$parent = $transaction;
		}

		if ( $parent ) {

			it_exchange_add_subscription_renewal_payment( $parent, $method_id, $payment_status, $amount );

			return true;
		}
	}

	return false;
}

/**
 * Adds a refund to post_meta for a PayPal transaction
 *
 *
 *
 * @param string $method_id PayPal Transaction ID
 * @param string $amount    Refund Amount
 * @param string $refund_id ID of the refund in PayPal.
 */
function it_exchange_paypal_standard_addon_add_refund_to_transaction( $method_id, $amount, $refund_id = '' ) {

    $amount      = number_format( abs( $amount ), '2', '.', '' );
    $transaction = it_exchange_get_transaction_by_method_id( 'paypal-standard', $method_id );

    if ( ! $transaction ) {
        return;
    }

    if ( $refund_id ) {
	    $exists = ITE_Refund::query()->and_where( array(
            'transaction' => $transaction->get_ID(),
            'gateway_id'  => $refund_id,
        ) )->take( 1 )->first();

	    if ( $exists ) {
	        return;
        }
    }

	ITE_Refund::create( array(
		'transaction' => $transaction,
		'amount'      => $amount,
		'gateway_id'  => $refund_id,
	) );
}

/**
 * Updates a subscription ID to post_meta for a paypal transaction
 *
 *
 *
 * @param string $paypal_standard_id PayPal Transaction ID
 * @param string $subscriber_id      PayPal Subscriber ID
 */
function it_exchange_paypal_standard_addon_update_subscriber_id( $paypal_standard_id, $subscriber_id ) {
	$transactions = it_exchange_paypal_standard_addon_get_transaction_id( $paypal_standard_id );
	foreach ( $transactions as $transaction ) { //really only one
		do_action( 'ninja_shop_update_transaction_subscription_id', $transaction, $subscriber_id );
	}
}

/**
 * Updates a subscription status to post_meta for a paypal transaction
 *
 *
 *
 * @param string $subscriber_id PayPal Subscriber ID
 * @param string $status        Status of Subscription
 */
function it_exchange_paypal_standard_addon_update_subscriber_status( $subscriber_id, $status ) {

	if ( ! $subscriber_id ) {
		return;
	}

	$subscription = it_exchange_get_subscription_by_subscriber_id( 'paypal-standard', $subscriber_id );

	if ( ! $subscription ) {
	    return;
    }

	if ( ! $subscription->is_status( $status ) ) {
		$subscription->set_status( $status );
	}
}

/**
 * Gets the interpretted transaction status from valid paypal transaction statuses
 *
 *
 *
 * @param string $status the string of the paypal transaction
 *
 * @return string translaction transaction status
 */
function it_exchange_paypal_standard_addon_transaction_status_label( $status ) {

	switch ( strtolower( $status ) ) {

		case 'completed':
		case 'success':
		case 'canceled_reversal':
		case 'processed' :
		case 'succeeded':
			return __( 'Paid', 'it-l10n-ithemes-exchange' );
		case 'refunded':
		case 'refund':
			return __( 'Refund', 'it-l10n-ithemes-exchange' );
		case 'reversed':
			return __( 'Reversed', 'it-l10n-ithemes-exchange' );
		case 'buyer_complaint':
			return __( 'Buyer Complaint', 'it-l10n-ithemes-exchange' );
		case 'denied' :
			return __( 'Denied', 'it-l10n-ithemes-exchange' );
		case 'expired' :
			return __( 'Expired', 'it-l10n-ithemes-exchange' );
		case 'failed' :
			return __( 'Failed', 'it-l10n-ithemes-exchange' );
		case 'pending' :
			return __( 'Pending', 'it-l10n-ithemes-exchange' );
		case 'voided' :
			return __( 'Voided', 'it-l10n-ithemes-exchange' );
		default:
			return __( 'Unknown', 'it-l10n-ithemes-exchange' );
	}

}

add_filter( 'ninja_shop_transaction_status_label_paypal-standard', 'it_exchange_paypal_standard_addon_transaction_status_label' );

/**
 * Returns a boolean. Is this transaction a status that warrants delivery of any products attached to it?
 *
 *
 *
 * @param boolean                 $cleared If the txn has already been cleared by another add-on
 * @param IT_Exchange_Transaction $transaction
 *
 * @return boolean
 */
function it_exchange_paypal_standard_transaction_is_cleared_for_delivery( $cleared, $transaction ) {
	$valid_stati = array(
		'completed',
		'success',
		'canceled_reversal',
		'processed',
		'succeeded'
	);

	return in_array( strtolower( it_exchange_get_transaction_status( $transaction ) ), $valid_stati );
}

add_filter( 'ninja_shop_paypal-standard_transaction_is_cleared_for_delivery', 'it_exchange_paypal_standard_transaction_is_cleared_for_delivery', 10, 2 );

/**
 * Returns the unsubscribe action for PayPal autorenewing payments
 *
 *
 *
 * @param string $output  Should be an empty string
 * @param array  $options Array of options passed from Recurring Payments add-on
 *
 * @return string $output Unsubscribe action
 */
function it_exchange_paypal_standard_unsubscribe_action( $output, $options ) {
	$paypal_settings = it_exchange_get_option( 'addon_paypal_standard' );
	$paypal_url      = PAYPAL_PAYMENT_LIVE_URL;
	$paypal_email    = $paypal_settings['live-email-address'];

	$output = '<a class="button" href="' . $paypal_url . '?cmd=_subscr-find&alias=' . urlencode( $paypal_email ) . '">';
	$output .= $options['label'];
	$output .= '</a>';

	return $output;
}

add_filter( 'ninja_shop_paypal-standard_unsubscribe_action', 'it_exchange_paypal_standard_unsubscribe_action', 10, 2 );

/**
 * Output the Cancel URL for the Payments screen
 *
 *
 *
 * @param object $transaction iThemes Transaction object
 *
 * @return void
 */
function it_exchange_paypal_standard_after_payment_details_cancel_url( $transaction ) {
	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	if ( ! empty( $cart_object->products ) ) {
		foreach ( $cart_object->products as $product ) {
			$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
			if ( $autorenews ) {
				$subscriber_id = $transaction->get_transaction_meta( 'subscriber_id', true );
				$status        = $transaction->get_transaction_meta( 'subscriber_status', true );
				switch ( $status ) {

					case 'deactivated':
						$output = __( 'Recurring payment has been deactivated', 'it-l10n-ithemes-exchange' );
						break;

					case 'cancelled':
						$output = __( 'Recurring payment has been cancelled', 'it-l10n-ithemes-exchange' );
						break;

					case 'suspended':
						$output = __( 'Recurring payment has been suspended', 'it-l10n-ithemes-exchange' );
						break;

					case 'active':
					default:
						$output = '<a href="' . PAYPAL_LIVE_URL . '">' . __( 'Cancel Recurring Payment', 'it-l10n-ithemes-exchange' ) . ' (' . __( 'Profile ID', 'it-l10n-ithemes-exchange' ) . ': ' . $subscriber_id . ')</a>';
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

add_action( 'ninja_shop_after_payment_details_cancel_url_for_paypal-standard', 'it_exchange_paypal_standard_after_payment_details_cancel_url' );

/**
 * Convert old option keys to new option keys
 *
 * Our original option keys for this plugin were generating form field names 80+ chars in length
 *
 *
 *
 * @param  array   $options        options as pulled from the DB
 * @param  string  $key            the key for the options
 * @param  boolean $break_cache    was the flag to break cache passed?
 * @param  boolean $merge_defaults was the flag to merge defaults passed?
 *
 * @return array
 */
function it_exchange_paypal_standard_convert_option_keys( $options, $key, $break_cache, $merge_defaults ) {
	if ( 'addon_paypal_standard' != $key ) {
		return $options;
	}

	foreach ( $options as $key => $value ) {
		if ( 'paypal-standard-' == substr( $key, 0, 16 ) && empty( $opitons[ substr( $key, 16 ) ] ) ) {
			$options[ substr( $key, 16 ) ] = $value;
			unset( $options[ $key ] );
		}
	}

	return $options;
}

add_filter( 'ninja_shop_get_option', 'it_exchange_paypal_standard_convert_option_keys', 10, 4 );

/**
 * Class for Stripe
 *
 */
class IT_Exchange_PayPal_Standard_Add_On {

	/**
	 * @var boolean $_is_admin true or false
	 *
	 */
	var $_is_admin;

	/**
	 * @var string $_current_page Current $_GET['page'] value
	 *
	 */
	var $_current_page;

	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 *
	 */
	var $_current_add_on;

	/**
	 * @var string $status_message will be displayed if not empty
	 *
	 */
	var $status_message;

	/**
	 * @var string $error_message will be displayed if not empty
	 *
	 */
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 *
	 */
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : sanitize_text_field( $_GET['page'] );
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : sanitize_text_field( $_GET['add-on-settings'] );

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'paypal-standard' == $this->_current_add_on ) {
			$this->save_settings();
		}
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchane_PayPal_Standard_Add_On() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	function print_settings_page() {
		$settings     = it_exchange_get_option( 'addon_paypal_standard', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'ninja_shop_add_on_paypal-standard', 'it-exchange-add-on-paypal-standard-settings' ),
			'enctype' => apply_filters( 'ninja_shop_add_on_paypal-standard_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=paypal-standard',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-paypal_standard' ) );

		if ( ! empty ( $this->status_message ) ) {
			ITUtility::show_status_message( $this->status_message );
		}
		if ( ! empty( $this->error_message ) ) {
			ITUtility::show_error_message( $this->error_message );
		}

		?>
		<div class="wrap">
			<?php ITUtility::screen_icon( 'it-exchange' ); ?>
			<h2><?php _e( 'PayPal Standard Settings - Basic', 'it-l10n-ithemes-exchange' ); ?></h2>

			<?php do_action( 'ninja_shop_paypal-standard_settings_page_top' ); ?>
			<?php do_action( 'ninja_shop_addon_settings_page_top' ); ?>

			<?php $form->start_form( $form_options, 'it-exchange-paypal-standard-settings' ); ?>
			<?php do_action( 'ninja_shop_paypal-standard_settings_form_top' ); ?>
			<?php $this->get_paypal_standard_payment_form_table( $form, $form_values ); ?>
			<?php do_action( 'ninja_shop_paypal-standard_settings_form_bottom' ); ?>
			<p class="submit">
				<?php $form->add_submit( 'submit', array(
					'value' => __( 'Save Changes', 'it-l10n-ithemes-exchange' ),
					'class' => 'button button-primary button-large'
				) ); ?>
			</p>
			<?php $form->end_form(); ?>
			<?php do_action( 'ninja_shop_paypal-standard_settings_page_bottom' ); ?>
			<?php do_action( 'ninja_shop_addon_settings_page_bottom' ); ?>
		</div>
		<?php
	}

	function get_paypal_standard_payment_form_table( $form, $settings = array() ) {

		$general_settings = it_exchange_get_option( 'settings_general' );

		if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
			<h3><?php _e( 'PayPal Standard - Basic (Fastest Setup)', 'it-l10n-ithemes-exchange' ); ?></h3>
		<?php endif;

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}

		?>
		<div class="it-exchange-addon-settings it-exchange-paypal-addon-settings">
			<p>
				<?php _e( 'This is the simple and fast version to get PayPal setup for your store. You use this version just to get your store going, but we highly suggest you switch to the PayPal Payments Standard - Secure option. To get PayPal set up for use with Ninja Shop, you\'ll need to add the following information from your PayPal account.', 'it-l10n-ithemes-exchange' ); ?>
				<br /><br />
				<?php _e( 'Video:', 'it-l10n-ithemes-exchange' ); ?>&nbsp;<a href="http://ithemes.com/tutorials/setting-up-paypal-standard-basic/" target="_blank"><?php _e( 'Setting Up PayPal Standard Basic', 'it-l10n-ithemes-exchange' ); ?></a>
			</p>

			<p><?php _e( 'Don\'t have a PayPal account yet?', 'it-l10n-ithemes-exchange' ); ?>
				<a href="http://paypal.com" target="_blank"><?php _e( 'Go set one up here', 'it-l10n-ithemes-exchange' ); ?></a>.
			</p>
			<h4><?php _e( 'What is your PayPal email address?', 'it-l10n-ithemes-exchange' ); ?></h4>

			<p>
				<label for="live-email-address"><?php _e( 'PayPal Email Address', 'it-l10n-ithemes-exchange' ); ?>
					<span class="tip" title="<?php _e( 'We need this to tie payments to your account.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) {
					$form->add_text_box( 'paypal-standard-live-email-address' );
				} else {
					$form->add_text_box( 'live-email-address' );
				}
				?>
			</p>

			<p>
				<label for="purchase-button-label"><?php _e( 'Purchase Button Label', 'it-l10n-ithemes-exchange' ); ?>
					<span class="tip" title="<?php _e( 'This is the text inside the button your customers will press to purchase with PayPal Standard', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				<?php
				if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) {
					$form->add_text_box( 'paypal-standard-purchase-button-label' );
				} else {
					$form->add_text_box( 'purchase-button-label' );
				}
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 *
	 * @return void
	 */
	function save_settings() {
		$defaults   = it_exchange_get_option( 'addon_paypal_standard' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ninja-shop-paypal-standard-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );

			return;
		}

		$errors = apply_filters( 'ninja_shop_add_on_paypal_standard_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_paypal_standard', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'it-l10n-ithemes-exchange' ) );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'it-l10n-ithemes-exchange' );
		}

		do_action( 'ninja_shop_save_add_on_settings_paypal-standard' );

	}

	/**
	 * Save the PayPal configuration done from the setup wizard.
	 *
	 * @return array|void
	 */
	function paypal_standard_save_wizard_settings() {
		if ( empty( $_REQUEST['it_exchange_settings-wizard-submitted'] ) ) {
			return;
		}

		$paypal_standard_settings = array();

		$fields                                  = array(
			'live-email-address',
			'purchase-button-label',
		);
		$default_wizard_paypal_standard_settings = apply_filters( 'default_wizard_paypal-standard_settings', $fields );

		foreach ( $default_wizard_paypal_standard_settings as $var ) {

			if ( isset( $_REQUEST[ 'it_exchange_settings-paypal-standard-' . $var ] ) ) {
				$paypal_standard_settings[ $var ] = sanitize_text_field( $_REQUEST[ 'it_exchange_settings-paypal-standard-' . $var ] );
			}

		}

		$settings = wp_parse_args( $paypal_standard_settings, it_exchange_get_option( 'addon_paypal_standard' ) );

		if ( $error_msg = $this->get_form_errors( $settings ) ) {

			return $error_msg;

		} else {
			it_exchange_save_option( 'addon_paypal_standard', $settings );
			$this->status_message = __( 'Settings Saved.', 'it-l10n-ithemes-exchange' );
		}

		return;
	}

	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 *
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	function get_form_errors( $values ) {

		$errors = array();
		if ( empty( $values['live-email-address'] ) ) {
			$errors[] = __( 'Please include your PayPal Email Address', 'it-l10n-ithemes-exchange' );
		}

		return $errors;
	}
}
