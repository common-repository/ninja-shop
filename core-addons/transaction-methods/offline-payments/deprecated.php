<?php
/**
 * Contains deprecated functions.
 *
 * 
 * @license GPLv2
 */

/**
 * Call back for settings page
 *
 * This is set in options array when registering the add-on and called from it_exchange_enable_addon()
 *
 *
 * @return void
 */
function it_exchange_offline_payments_settings_callback() {
	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();
	$IT_Exchange_Offline_Payments_Add_On->print_settings_page();
}

/**
 * Outputs wizard settings for Offline Payments
 *
 *
 * @todo  make this better, probably
 *
 * @param object $form Current IT Form object
 *
 * @return void
 */
function it_exchange_print_offline_payments_wizard_settings( $form ) {
	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();
	$settings                            = it_exchange_get_option( 'addon_offline_payments', true );
	$form_values                         = ITUtility::merge_defaults( ITForm::get_post_data(), $settings );
	$hide_if_js                          = it_exchange_is_addon_enabled( 'offline-payments' ) ? '' : 'hide-if-js';
	?>
	<div class="field offline-payments-wizard <?php echo $hide_if_js; ?>">
		<?php if ( empty( $hide_if_js ) ) { ?>
			<input class="enable-offline-payments" type="hidden" name="it-exchange-transaction-methods[]"
			       value="offline-payments"/>
		<?php } ?>
		<?php $IT_Exchange_Offline_Payments_Add_On->get_offline_payment_form_table( $form, $form_values ); ?>
	</div>
	<?php
}

/**
 * Saves offline payments settings when the Wizard is saved
 *
 *
 *
 * @return void
 */
function it_exchange_save_offline_payments_wizard_settings( $errors ) {
	if ( ! empty( $errors ) ) {
		return $errors;
	}

	$IT_Exchange_Offline_Payments_Add_On = new IT_Exchange_Offline_Payments_Add_On();

	return $IT_Exchange_Offline_Payments_Add_On->offline_payments_save_wizard_settings();
}

/**
 * This proccesses an offline transaction.
 *
 *
 *
 * @param string $status             passed by WP filter.
 * @param object $transaction_object The transaction object
 */
function it_exchange_offline_payments_addon_process_transaction( $status, $transaction_object ) {

	// If this has been modified as true already, return.
	if ( $status ) {
		return $status;
	}

	// Verify nonce
	if ( ! empty( $_REQUEST['_offline_payments_nonce'] ) && ! wp_verify_nonce( $_REQUEST['_offline_payments_nonce'], 'offline-payments-checkout' ) ) {
		it_exchange_add_message( 'error', __( 'Transaction Failed, unable to verify security token.', 'it-l10n-ithemes-exchange' ) );

		return false;

	} else {

		$settings = it_exchange_get_option( 'addon_offline_payments' );

		$uniqid = it_exchange_get_offline_transaction_uniqid();

		// Get customer ID data
		$it_exchange_customer = it_exchange_get_current_customer();

		return it_exchange_add_transaction( 'offline-payments', $uniqid, $settings['offline-payments-default-status'], $it_exchange_customer->id, $transaction_object );

	}

	return false;

}

/**
 * Returns the button for making the payment
 *
 *
 *
 * @param array $options
 *
 * @return string
 */
function it_exchange_offline_payments_addon_make_payment_button( $options ) {

	if ( 0 >= it_exchange_get_cart_total( false ) ) {
		return '';
	}

	$disable_on_submit = ' onSubmit="document.getElementById(\'offline-payments-button\').disabled=true;" ';
	$payment_form      = '<form id="offline_payment_form" action="' . it_exchange_get_page_url( 'transaction' ) . '" ' . $disable_on_submit . 'method="post">';
	$payment_form .= '<input type="hidden" name="it-exchange-transaction-method" value="offline-payments" />';
	$payment_form .= wp_nonce_field( 'offline-payments-checkout', '_offline_payments_nonce', true, false );

	$payment_form .= '<input type="submit" id="offline-payments-button" name="offline_payments_purchase" value="' . it_exchange_get_transaction_method_name_from_slug( 'offline-payments' ) . '" />';

	$payment_form .= '</form>';

	return $payment_form;

}

/**
 * Replace Offline name with what is set in admin settings
 *
 *
 *
 * @param string $name the name passed in from the WP filter API
 *
 * @return string
 */
function it_exchange_get_offline_payments_name( $name ) {
	$options = it_exchange_get_option( 'addon_offline_payments' );
	if ( ! empty( $options['offline-payments-title'] ) && ! is_admin() ) {
		$name = $options['offline-payments-title'];
	}

	return $name;
}

/**
 * Process Offline Payments Recurring Payments cancellations
 *
 *
 *
 * @return void
 */
function it_exchange_process_offline_payments_recurring_payment_cancel() {
	if ( ! empty( $_REQUEST['offline-payments-recurring-payment'] ) && 'cancel' === $_REQUEST['offline-payments-recurring-payment'] ) {
		if ( ! empty( $_REQUEST['post'] ) && $post_id = absint( $_REQUEST['post'] ) ) {
			$transaction = it_exchange_get_transaction( $post_id );
			$status      = $transaction->update_transaction_meta( 'subscriber_status', 'cancel' );
		}
	}
}

/**
 * Output the Cancel URL for the Payments screen
 *
 *
 *
 * @param object $transaction iThemes Transaction object
 *
 * @return void
 */
function it_exchange_offline_payments_checkout_after_payment_details_cancel_url( $transaction ) {

	_deprecated_function( __FUNCTION__, '1.35.5' );

	$cart_object = get_post_meta( $transaction->ID, '_it_exchange_cart_object', true );
	if ( ! empty( $cart_object->products ) ) {
		foreach ( $cart_object->products as $product ) {
			$autorenews = $transaction->get_transaction_meta( 'subscription_autorenew_' . $product['product_id'], true );
			if ( $autorenews ) {
				$status = $transaction->get_transaction_meta( 'subscriber_status', true );
				switch ( $status ) {

					case false: //active
					case '':
						$output = '<a href="' . esc_url( add_query_arg( 'offline-payments-recurring-payment', 'cancel' ) ) . '">' . __( 'Cancel Recurring Payment', 'it-l10n-ithemes-exchange' ) . '</a>';
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
 * Class for Offline
 *
 *
 */
class IT_Exchange_Offline_Payments_Add_On {

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
	 *
	 */
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : sanitize_text_field( $_GET['page'] );
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : sanitize_text_field( $_GET['add-on-settings'] );

		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page && 'offline-payments' == $this->_current_add_on ) {
			add_action( 'ninja_shop_save_add_on_settings_offline-payments', array( $this, 'save_settings' ) );
			do_action( 'ninja_shop_save_add_on_settings_offline-payments' );
		}

		add_filter( 'ninja_shop_storage_get_defaults_exchange_addon_offline_payments', array( $this, 'set_default_settings' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Offline_Payments_Add_On() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	function print_settings_page() {
		$settings     = it_exchange_get_option( 'addon_offline_payments', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'      => apply_filters( 'ninja_shop_add_on_offline_payments', 'it-exchange-add-on-offline-payments-settings' ),
			'enctype' => apply_filters( 'ninja_shop_add_on_offline_payments_settings_form_enctype', false ),
			'action'  => 'admin.php?page=it-exchange-addons&add-on-settings=offline-payments',
		);
		$form         = new ITForm( $form_values, array( 'prefix' => 'it-exchange-add-on-offline-payments' ) );

		if ( ! empty ( $this->status_message ) ) {
			ITUtility::show_status_message( $this->status_message );
		}
		if ( ! empty( $this->error_message ) ) {
			ITUtility::show_error_message( $this->error_message );
		}
		include( 'view-add-on-settings.php' );
	}

	function get_offline_payment_form_table( $form, $settings = array() ) {
		$default_status_options = it_exchange_offline_payments_get_default_status_options();

		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}

		if ( ! empty( $_GET['page'] ) && 'it-exchange-setup' == $_GET['page'] ) : ?>
			<h3><?php _e( 'Offline Payments', 'it-l10n-ithemes-exchange' ); ?></h3>
		<?php endif; ?>
		<p><?php _e( 'Offline payments allow customers to purchase products from your site using check or cash. Transactions can be set as pending until you receive payment.', 'it-l10n-ithemes-exchange' ); ?></p>
		<p><?php _e( 'Video:', 'it-l10n-ithemes-exchange' ); ?>&nbsp;<a
				href="http://ithemes.com/tutorials/using-offline-payments-in-exchange/"
				target="_blank"><?php _e( 'Setting Up Offline Payments in Ninja Shop', 'it-l10n-ithemes-exchange' ); ?></a>
		</p>
		<p><?php _e( 'To process payments offline, complete the settings below.', 'it-l10n-ithemes-exchange' ); ?></p>
		<table class="form-table">
			<?php do_action( 'ninja_shop_offline_payments_settings_table_top' ); ?>
			<tr valign="top">
				<th scope="row"><label for="offline-payments-title"><?php _e( 'Title', 'it-l10n-ithemes-exchange' ) ?>
						<span class="tip"
						      title="<?php _e( 'What would you like to title this payment option? eg: Check', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				</th>
				<td>
					<?php $form->add_text_box( 'offline-payments-title', array( 'class' => 'normal-text' ) ); ?>                </td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label
						for="offline-payments-instructions"><?php _e( 'Instructions after purchase', 'it-l10n-ithemes-exchange' ) ?>
						<span class="tip"
						      title="<?php _e( 'This will be the notification customers see after using this method of payment.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				</th>
				<td>
					<?php $form->add_text_area( 'offline-payments-instructions', array(
						'cols'  => 50,
						'rows'  => 5,
						'class' => 'normal-text'
					) ); ?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label
						for="offline-payments-default-status"><?php _e( 'Default Payment Status', 'it-l10n-ithemes-exchange' ) ?>
						<span class="tip"
						      title="<?php _e( 'This is the default payment status applied to all offline payment transactions.', 'it-l10n-ithemes-exchange' ); ?>">i</span></label>
				</th>
				<td>
					<?php $form->add_drop_down( 'offline-payments-default-status', $default_status_options ); ?>
				</td>
			</tr>
			<?php do_action( 'ninja_shop_offline_payments_settings_table_bottom' ); ?>
			<?php do_action( 'ninja_shop_addon_settings_page_bottom' ); ?>
		</table>
		<?php
	}

	/**
	 * Save settings
	 *
	 *
	 * @return void
	 */
	function save_settings() {
		$defaults   = it_exchange_get_option( 'addon_offline_payments' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		// Check nonce
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ninja-shop-offline-payments-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', 'it-l10n-ithemes-exchange' );

			return;
		}

		$errors = apply_filters( 'ninja_shop_add_on_manual_transaction_validate_settings', $this->get_form_errors( $new_values ), $new_values );
		if ( ! $errors && it_exchange_save_option( 'addon_offline_payments', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', 'it-l10n-ithemes-exchange' ) );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', 'it-l10n-ithemes-exchange' );
		}
	}

	function offline_payments_save_wizard_settings() {
		if ( ! isset( $_REQUEST['it_exchange_settings-wizard-submitted'] ) ) {
			return;
		}

		$offline_payments_settings = array();

		$default_wizard_offline_payments_settings = apply_filters( 'default_wizard_offline_payments_settings', array(
			'offline-payments-title',
			'offline-payments-instructions',
			'offline-payments-default-status'
		) );

		foreach ( $default_wizard_offline_payments_settings as $var ) {

			if ( isset( $_REQUEST[ 'it_exchange_settings-' . $var ] ) ) {
				$offline_payments_settings[ $var ] = sanitize_text_field( $_REQUEST[ 'it_exchange_settings-' . $var ] );
			}

		}

		$settings = wp_parse_args( $offline_payments_settings, it_exchange_get_option( 'addon_offline_payments' ) );

		if ( $error_msg = $this->get_form_errors( $settings ) ) {

			return $error_msg;

		} else {
			it_exchange_save_option( 'addon_offline_payments', $settings );
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
	 * @return void
	 */
	function get_form_errors( $values ) {
		$errors = array();
		if ( empty( $values['offline-payments-title'] ) ) {
			$errors[] = __( 'The Title field cannot be left blank', 'it-l10n-ithemes-exchange' );
		}
		if ( empty( $values['offline-payments-instructions'] ) ) {
			$errors[] = __( 'Please leave some instructions for customers checking out with this transaction method', 'it-l10n-ithemes-exchange' );
		}

		$valid_status_options = it_exchange_offline_payments_get_default_status_options();
		if ( empty( $values['offline-payments-default-status'] ) || empty( $valid_status_options[ $values['offline-payments-default-status'] ] ) ) {
			$errors[] = __( 'Please select a valid default transaction status.', 'it-l10n-ithemes-exchange' );
		}

		return $errors;
	}

	/**
	 * Sets the default options for manual payment settings
	 *
	 *
	 * @return array settings
	 */
	function set_default_settings( $defaults ) {
		$defaults['offline-payments-title']          = __( 'Pay with check', 'it-l10n-ithemes-exchange' );
		$defaults['offline-payments-instructions']   = __( 'Thank you for your order. We will contact you shortly for payment.', 'it-l10n-ithemes-exchange' );
		$defaults['offline-payments-default-status'] = 'pending';

		return $defaults;
	}
}
