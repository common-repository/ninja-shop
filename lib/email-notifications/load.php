<?php
/**
 * Load the email notifications component.
 *
 * 
 * @license GPLv2
 */

require_once dirname( __FILE__ ) . '/deprecated.php';

require_once dirname( __FILE__ ) . '/class.customizer.php';
require_once dirname( __FILE__ ) . '/class.customize-active-callback.php';

require_once dirname( __FILE__ ) . '/notifications/class.email-notification.php';
require_once dirname( __FILE__ ) . '/notifications/class.admin-email-notification.php';
require_once dirname( __FILE__ ) . '/notifications/class.customer-email-notification.php';

require_once dirname( __FILE__ ) . '/class.email-template.php';
require_once dirname( __FILE__ ) . '/sendable/interface.sender-aware.php';
require_once dirname( __FILE__ ) . '/sendable/interface.sendable.php';
require_once dirname( __FILE__ ) . '/sendable/class.mutable-wrapper.php';
require_once dirname( __FILE__ ) . '/sendable/class.email.php';
require_once dirname( __FILE__ ) . '/sendable/class.simple-email.php';

require_once dirname( __FILE__ ) . '/recipients/interface.email-recipient.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-transaction.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-customer.php';
require_once dirname( __FILE__ ) . '/recipients/class.email-recipient-email.php';

require_once dirname( __FILE__ ) . '/sender/interface.php';
require_once dirname( __FILE__ ) . '/sender/class.null.php';
require_once dirname( __FILE__ ) . '/sender/class.wp-mail.php';
require_once dirname( __FILE__ ) . '/sender/class.postmark.php';
require_once dirname( __FILE__ ) . '/sender/class.sparkpost.php';
require_once dirname( __FILE__ ) . '/sender/class.mailjet.php';
require_once dirname( __FILE__ ) . '/sender/class.exception.php';

require_once dirname( __FILE__ ) . '/middleware/class.handler.php';
require_once dirname( __FILE__ ) . '/middleware/interface.php';
require_once dirname( __FILE__ ) . '/middleware/class.formatter.php';
require_once dirname( __FILE__ ) . '/middleware/class.contextualizer.php';
require_once dirname( __FILE__ ) . '/middleware/class.style-links.php';
require_once dirname( __FILE__ ) . '/middleware/class.auto-linker.php';

require_once dirname( __FILE__ ) . '/tag-replacers/interface.php';
require_once dirname( __FILE__ ) . '/tag-replacers/class.base.php';
require_once dirname( __FILE__ ) . '/tag-replacers/class.curly.php';

require_once dirname( __FILE__ ) . '/tag/interface.php';
require_once dirname( __FILE__ ) . '/tag/class.base.php';
require_once dirname( __FILE__ ) . '/tag/load.php';

require_once dirname( __FILE__ ) . '/class.email-notifications.php';

new IT_Exchange_Email_Customizer();

/**
 * Retrieve the email notifications object.
 *
 *
 *
 * @return IT_Exchange_Email_Notifications
 */
function it_exchange_email_notifications() {

	static $notifications = null;

	if ( $notifications ) {
		return $notifications;
	}

	$replacer = new IT_Exchange_Email_Curly_Tag_Replacer();

	/**
	 * Filter the tag replacer.
	 *
	 * The return value must implement IT_Exchange_Email_Tag_Replacer.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	$filtered = apply_filters( 'ninja_shop_email_notifications_tag_replacer', $replacer );

	if ( $filtered instanceof IT_Exchange_Email_Tag_Replacer ) {
		$replacer = $filtered;
	}

	/**
	 * Fires when replacement tags should be registered.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag_Replacer $replacer
	 */
	do_action( 'ninja_shop_email_notifications_register_tags', $replacer );

	$middleware = new IT_Exchange_Email_Middleware_Handler();
	$middleware
		->push( new IT_Exchange_Email_Middleware_Formatter(), 'formatter' )
		->push( new IT_Exchange_Email_Middleware_Contextualizer() )
		->push( $replacer, 'replacer' )
		->push( new IT_Exchange_Email_Middleware_Style_Links(), 'style-links' );

	/**
	 * Fires when add-ons should register additional middleware.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Middleware_Handler
	 */
	do_action( 'ninja_shop_email_notifications_register_middleware', $middleware );

	if ( defined( 'IT_EXCHANGE_DISABLE_EMAILS' ) && IT_EXCHANGE_DISABLE_EMAILS ) {
		$sender = new IT_Exchange_Email_Null_Sender();
	} else {
		$sender = new IT_Exchange_WP_Mail_Sender( $middleware );

		if ( class_exists( 'Postmark_Mail' ) ) {

			$settings = get_option( 'postmark_settings', '' );
			$settings = json_decode( $settings, true );

			if ( $settings['api_key'] && $settings['enabled'] ) {
				$sender = new IT_Exchange_Email_Postmark_Sender( $middleware, _wp_http_get_object(), array(
					'server-token' => $settings['api_key']
				) );
			}
		} elseif ( class_exists( 'SparkPost' ) && SparkPost::get_option( 'enable_sparkpost' ) ) {

			$api_key = SparkPost::get_option( 'password' );

			if ( $api_key ) {
				$sender = new IT_Exchange_Email_SparkPost_Sender( $middleware, $replacer, _wp_http_get_object(), array(
					'api-key' => $api_key
				) );
			}
		} elseif ( class_exists( 'WP_Mailjet' ) && get_option( 'mailjet_enabled' ) ) {

			$public  = get_option( 'mailjet_username' );
			$private = get_option( 'mailjet_password' );

			if ( $public && $private ) {
				/*$sender = new IT_Exchange_Email_Mailjet_Sender( $middleware, _wp_http_get_object(), array(
					'public'  => $public,
					'private' => $private
				) );*/
			}
		}
	}

	/**
	 * Filter the sender object.
	 *
	 * The return value must implement IT_Exchange_Email_Sender
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Sender             $sender
	 * @param IT_Exchange_Email_Middleware_Handler $middleware
	 * @param IT_Exchange_Email_Tag_Replacer       $replacer
	 */
	$filtered = apply_filters( 'ninja_shop_email_notifications_sender', $sender, $middleware, $replacer );

	if ( $filtered instanceof IT_Exchange_Email_Sender ) {
		$sender = $filtered;
	}

	$notifications = new IT_Exchange_Email_Notifications( $sender, $replacer );

	return $notifications;
}

/**
 * Register email notifications.
 *
 *
 */
function it_exchange_register_email_notifications() {

	$settings = it_exchange_get_option( 'settings_email' );

	$notifications = it_exchange_email_notifications();

	$GLOBALS['IT_Exchange_Email_Notifications'] = $notifications;

	$r = $notifications->get_replacer();

	$notifications
		->register_notification( new IT_Exchange_Admin_Email_Notification(
			__( 'Admin Order Notification', 'it-l10n-ithemes-exchange' ), 'admin-order', null, array(
				'defaults' => array(
					'subject' => sprintf( __( 'You made a sale! Yabba Dabba Doo! %s', 'it-l10n-ithemes-exchange' ), $r->format_tag( 'receipt_id' ) ),
					'body'    => sprintf( __( "Your friend %s just bought all this awesomeness from your store! \r\n\r\n Order: %s \r\n\r\n %s", 'it-l10n-ithemes-exchange' ),
						$r->format_tag( 'customer_fullname' ), $r->format_tag( 'receipt_id' ), $r->format_tag( 'order_table' ) ),
				),
				'group'    => __( 'Core', 'it-l10n-ithemes-exchange' ),
				'previous' => array(
					'subject' => empty( $settings['admin-email-subject'] ) ? '' : $settings['admin-email-subject'],
					'body'    => empty( $settings['admin-email-template'] ) ? '' : $settings['admin-email-template'],
				),
			)
		) )
		->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'Purchase Receipt', 'it-l10n-ithemes-exchange' ), 'receipt', new IT_Exchange_Email_Template( 'receipt' ), array(
				'defaults'    => array(
					'subject' => sprintf( __( 'Receipt for Purchase: %s', 'it-l10n-ithemes-exchange' ), $r->format_tag( 'receipt_id' ) ),
					'body'    => sprintf( __( "Hello %s, \r\n\r\n Thank you for your order. Your order's details are below.", 'it-l10n-ithemes-exchange' ), $r->format_tag( 'first_name' ) ),
				),
				'group'       => __( 'Core', 'it-l10n-ithemes-exchange' ),
				'description' =>
					__( "The customer's shipping and billing address, as well as the cart details, payment method, download links, total and purchase date are already included in the template.",
						'it-l10n-ithemes-exchange' ),
				'previous'    => array(
					'subject' => empty( $settings['receipt-email-subject'] ) ? '' : $settings['receipt-email-subject'],
					'body'    => empty( $settings['receipt-email-template'] ) ? '' : $settings['receipt-email-template'],
				),
			)
		) );

	/**
	 * Filter whether child transactions are being used.
	 *
	 *
	 *
	 * @param bool $in_use
	 */
	if ( apply_filters( 'ninja_shop_using_child_transactions', false ) ) {
		$notifications->register_notification( new IT_Exchange_Customer_Email_Notification(
			__( 'Renewal Purchase Receipt', 'it-l10n-ithemes-exchange' ), 'renewal-receipt', new IT_Exchange_Email_Template( 'receipt' ), array(
				'defaults'    => array(
					'subject' => sprintf( __( 'Receipt for renewal payment: %s', 'it-l10n-ithemes-exchange' ), $r->format_tag( 'receipt_id' ) ),
					'body'    => sprintf( __( "Hello %s, \r\n\r\n Thank you for your renewal. Your order's details are below.", 'it-l10n-ithemes-exchange' ), $r->format_tag( 'first_name' ) ),
				),
				'group'       => __( 'Core', 'it-l10n-ithemes-exchange' ),
				'description' =>
					__( "The customer's shipping and billing address, as well as the cart details, payment method, download links, total and purchase date are already included in the template.",
						'it-l10n-ithemes-exchange' ),
				'previous'    => array(
					'body' => empty( $settings['receipt-email-template'] ) ? '' : $settings['receipt-email-template'],
				),
			)
		) );
	}

	$notifications->register_notification( new IT_Exchange_Customer_Email_Notification(
		__( 'New Public Transaction Activity', 'it-l10n-ithemes-exchange' ), 'customer-order-note', new IT_Exchange_Email_Template( 'order-note' ), array(
			'defaults'    => array(
				'subject' => sprintf( __( 'New note about your order %s', 'it-l10n-ithemes-exchange' ), $r->format_tag( 'receipt_id' ) ),
				'body'    => sprintf( __( "Hello %s, \r\n\r\n A new note has been added to your order.", 'it-l10n-ithemes-exchange' ), $r->format_tag( 'first_name' ) )
			),
			'group'       => __( 'Core', 'it-l10n-ithemes-exchange' ),
			'description' =>
				__( 'The order note and cart details are already included in the template.', 'it-l10n-ithemes-exchange' ),
		)
	) );

	/**
	 * Fires when add-ons should register additional email notifications.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Notifications $notifications
	 */
	do_action( 'ninja_shop_register_email_notifications', $notifications );
}

add_action( 'ninja_shop_enabled_addons_loaded', 'it_exchange_register_email_notifications' );
