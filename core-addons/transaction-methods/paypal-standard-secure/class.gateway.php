<?php
/**
 * PayPal Standard Secure Gateway.
 *
 * @since   2.0.0
 * @license GPLv2
 */

/**
 * Class ITE_PayPal_Standard_Secure_Gateway
 */
class ITE_PayPal_Standard_Secure_Gateway extends ITE_Gateway {

	/** @var ITE_Gateway_Request_Handler[] */
	private $handlers = array();

	/** @var array */
	private $fields = array();

	/**
	 * @inheritDoc
	 */
	public function __construct() {
		parent::__construct();

		$factory = new ITE_Gateway_Request_Factory();

		$this->handlers[] = new ITE_PayPal_Standard_Secure_Purchase_Handler( $this, $factory );
		$this->handlers[] = new ITE_PayPal_Standard_Secure_Webhook_Handler();
		$this->handlers[] = new ITE_PayPal_Standard_Secure_Refund_Request_Handler( $this );

		if ( class_exists( 'ITE_Pause_Subscription_Request' ) ) {
			$this->handlers[] = new ITE_PayPal_Standard_Secure_Pause_Subscription_Handler();
		}

		if ( class_exists( 'ITE_Resume_Subscription_Request' ) ) {
			$this->handlers[] = new ITE_PayPal_Standard_Secure_Resume_Subscription_Handler();
		}

		if ( class_exists( 'ITE_Cancel_Subscription_Request' ) ) {
			$this->handlers[] = new ITE_PayPal_Standard_Secure_Cancel_Subscription_Handler();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() { return __( 'PayPal Standard - Secure', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'paypal-standard-secure'; }

	/**
	 * @inheritDoc
	 */
	public function get_addon() { return it_exchange_get_addon( 'paypal-standard-secure' ); }

	/**
	 * @inheritDoc
	 */
	public function get_handlers() { return $this->handlers; }

	/**
	 * @inheritDoc
	 */
	public function requires_cart_after_purchase() { return true; }

	/**
	 * @inheritDoc
	 */
	public function get_statuses() {

		$statuses = array(
			'Pending'   => array(
				'label'      => _x( 'Pending', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
				'cleared'    => false,
				'selectable' => true,
			),
			'Completed' => array(
				'label'      => _x( 'Paid', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
				'cleared'    => true,
				'selectable' => true,
			),
			'Reversed'  => array(
				'label'      => _x( 'Reversed', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
				'cleared'    => false,
				'selectable' => true,
			),
			'Refunded'  => array(
				'label'      => _x( 'Refunded', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
				'cleared'    => false,
				'selectable' => true,
			),
			'Voided'    => array(
				'label'      => _x( 'Voided', 'Transaction Status', 'it-l10n-ithemes-exchange' ),
				'cleared'    => false,
				'selectable' => true,
			)
		);

		return $statuses;
	}

	/**
	 * @inheritDoc
	 */
	public function is_sandbox_mode() { return $this->settings()->get( 'sandbox-mode' ); }

	/**
	 * @inheritDoc
	 */
	public function get_webhook_param() {

		/**
		 * Filter the PayPal Standard Secure webhook param.
		 *
		 * @since 1.0.0
		 *
		 * @param string $param
		 */
		return apply_filters( 'it_exchange_paypal-standard-secure_webhook', 'it_exchange_paypal-standard-secure' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_ssl_mode() { return self::SSL_SUGGESTED; }

	/**
	 * @inheritDoc
	 */
	public function get_wizard_settings() {

		$fields = array(
			'preamble',
			'step1',
			'live-email-address',
			'step2',
			'live-api-username',
			'live-api-password',
			'live-api-signature',

		);

		$wizard = array();

		foreach ( $this->get_settings_fields() as $field ) {
			if ( in_array( $field['slug'], $fields ) ) {
				$wizard[] = $field;
			}
		}

		return $wizard;
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_fields() {

		if ( $this->fields ) {
			return $this->fields;
		}

		$this->fields = array(
			array(
				'type' => 'html',
				'slug' => 'preamble',
				'html' =>
					'<p>' .
					__( 'Although this PayPal version for Ninja Shop takes more effort and time, it is well worth it for the security options for your store.', 'it-l10n-ithemes-exchange' ) . ' ' .
					__( 'To get PayPal set up for use with Ninja Shop, you\'ll need to add the following information from your PayPal account.', 'it-l10n-ithemes-exchange' ) .
					'<p>' .
					sprintf(
						__( 'Don\'t have a PayPal account yet? %1$sGo set one up here%2$s.', 'it-l10n-ithemes-exchange' ),
						'<a href="http://paypal.com" target="_blank">', '</a>'
					) . '</p>',
			),
			array(
				'type' => 'html',
				'slug' => 'step1',
				'html' => '<h4>' . __( 'Step 1. Fill out your PayPal email address', 'it-l10n-ithemes-exchange' ) . '</h4>'
			),
			array(
				'type'     => 'email',
				'label'    => __( 'PayPal Email Address', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'live-email-address',
				'tooltip'  => __( 'We need this to tie payments to your account.', 'it-l10n-ithemes-exchange' ),
				'required' => true,
			),
			array(
				'type' => 'html',
				'slug' => 'step2',
				'html' => '<h4>' . __( 'Step 2. Fill out your PayPal API credentials', 'it-l10n-ithemes-exchange' ) . '</h4>'
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal API Username', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'live-api-username',
				'desc'     => __( 'At PayPal, see: Profile &rarr; My Selling Tools &rarr; API Access &rarr; Update &rarr; View API Signature (or Request API Credentials).', 'it-l10n-ithemes-exchange' ),
				'required' => true,
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal API Password', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'live-api-password',
				'required' => true,
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal API Signature', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'live-api-signature',
				'required' => true,
			),
			array(
				'type' => 'html',
				'slug' => 'step3-5',
				'html' => $this->get_step_3_to_5(),
			),
			array(
				'type'    => 'text_box',
				'label'   => __( 'Purchase Button Label', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'purchase-button-label',
				'tooltip' => __( 'This is the text inside the button your customers will press to purchase with PayPal.', 'it-l10n-ithemes-exchange' ),
				'default' => __( 'Pay with PayPal', 'it-l10n-ithemes-exchange' ),
			),
			array(
				'type'    => 'check_box',
				'label'   => __( 'Enable PayPal Sandbox Mode', 'it-l10n-ithemes-exchange' ),
				'slug'    => 'sandbox-mode',
				'desc'    => sprintf(
					__( 'Video: %1$sCreating a PayPal Sandbox account%2$s', 'it-l10n-ithemes-exchange' ),
					'<a href="http://ithemes.com/tutorials/creating-a-paypal-sandbox-test-account">',
					'</a>'
				),
				'default' => false,
			),
			array(
				'type'     => 'email',
				'label'    => __( 'PayPal Sandbox Email Address', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'sandbox-email-address',
				'show_if'  => array( 'field' => 'sandbox-mode', 'value' => true, 'compare' => '=' ),
				'required' => true,
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal Sandbox API Username', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'sandbox-api-username',
				'show_if'  => array( 'field' => 'sandbox-mode', 'value' => true, 'compare' => '=' ),
				'required' => true,
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal Sandbox API Password', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'sandbox-api-password',
				'show_if'  => array( 'field' => 'sandbox-mode', 'value' => true, 'compare' => '=' ),
				'required' => true,
			),
			array(
				'type'     => 'text_box',
				'label'    => __( 'PayPal Sandbox API Signature', 'it-l10n-ithemes-exchange' ),
				'slug'     => 'sandbox-api-signature',
				'show_if'  => array( 'field' => 'sandbox-mode', 'value' => true, 'compare' => '=' ),
				'required' => true,
			),
		);

		return $this->fields;
	}

	/**
	 * @inheritDoc
	 */
	public function get_settings_name() { return 'addon_paypal_standard_secure'; }

	/**
	 * Get Step 3-5 HTML.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	private function get_step_3_to_5() {
		ob_start();
		?>
        <h4><?php _e( 'Step 3. Setup PayPal Auto Return', 'it-l10n-ithemes-exchange' ); ?></h4>
        <p><?php _e( 'PayPal Auto Return must be configured in Account Profile &rarr; Website Payment Preferences in your PayPal Account', 'it-l10n-ithemes-exchange' ); ?></p>
        <p><?php _e( 'Please log into your account, set Auto Return to ON and add this URL to your Return URL Settings so your customers are redirected to your site to complete the transactions.', 'it-l10n-ithemes-exchange' ); ?></p>
        <code><?php echo it_exchange_get_page_url( 'transaction' ); ?></code>

        <h4><?php _e( 'Step 4. Setup PayPal Payment Data Transfer (PDT)', 'it-l10n-ithemes-exchange' ); ?></h4>
        <p><?php _e( 'PayPal PDT must be turned <strong>ON</strong> in Account Profile &rarr; Website Payment Preferences in your PayPal Account', 'it-l10n-ithemes-exchange' ); ?></p>
        <h4><?php _e( 'Step 5. Optional Configuration', 'it-l10n-ithemes-exchange' ); ?></h4>
		<?php

		return ob_get_clean();
	}
}
