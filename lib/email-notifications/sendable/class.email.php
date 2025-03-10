<?php
/**
 * Contains the email class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email
 */
class IT_Exchange_Email implements IT_Exchange_Sendable {

	/**
	 * @var IT_Exchange_Email_Recipient
	 */
	private $recipient;

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $ccs = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	private $bccs = array();

	/**
	 * @var IT_Exchange_Email_Notification
	 */
	private $notification;

	/**
	 * @var array
	 */
	private $context = array();

	/**
	 * IT_Exchange_Email constructor.
	 *
	 * @param IT_Exchange_Email_Recipient    $recipient
	 * @param IT_Exchange_Email_Notification $notification
	 * @param array                          $context
	 */
	public function __construct( IT_Exchange_Email_Recipient $recipient, IT_Exchange_Email_Notification $notification, array $context = array() ) {
		$this->recipient    = $recipient;
		$this->notification = $notification;

		foreach ( $context as $key => $val ) {
			$this->add_context( $val, $key );
		}
	}

	/**
	 * Add a CC to the email.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_cc( IT_Exchange_Email_Recipient $recipient ) {
		$this->ccs[] = $recipient;

		return $this;
	}

	/**
	 * Add a BCC to the email.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_bcc( IT_Exchange_Email_Recipient $recipient ) {
		$this->bccs[] = $recipient;

		return $this;
	}

	/**
	 * Add context to the email.
	 *
	 *
	 *
	 * @param mixed|stdClass|Serializable $context
	 * @param string                      $key
	 *
	 * @return self
	 */
	public function add_context( $context, $key ) {

		if ( ! is_string( $key ) || trim( $key ) === '' ) {
			throw new InvalidArgumentException( '$key must be a non-empty string.' );
		}

		$this->context[ $key ] = $context;

		return $this;
	}

	/**
	 * Get the subject line.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_subject() {

		$subject = $this->get_notification()->get_subject();

		return apply_filters( "ninja_shop_email_{$this->get_notification()->get_slug()}_subject", $subject, $this );
	}

	/**
	 * Get the body.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_body() {

		$body = $this->get_notification()->get_body();

		return apply_filters( "ninja_shop_email_{$this->get_notification()->get_slug()}_body", $body, $this );
	}

	/**
	 * Get the email template.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->get_notification()->get_template();
	}

	/**
	 * Get the recipient for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient
	 */
	public function get_recipient() {
		return $this->recipient;
	}

	/**
	 * Get the CCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_ccs() {
		return $this->ccs;
	}

	/**
	 * Get the BCCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_bccs() {
		return $this->bccs;
	}

	/**
	 * Get the notification this email is based on.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Notification
	 */
	public function get_notification() {
		return $this->notification;
	}

	/**
	 * Get the context for this email.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 *
	 */
	public function serialize() {

		if ( it_exchange_email_notifications()->get_notification( $this->get_notification()->get_slug() ) ) {
			$notification = $this->get_notification()->get_slug();
		} else {
			$notification = $this->get_notification();
		}

		$data = array(
			'notification' => $notification,
			'context'      => $this->get_context(),
			'ccs'          => $this->get_ccs(),
			'bccs'         => $this->get_bccs(),
			'recipient'    => $this->get_recipient()
		);

		return serialize( $data );
	}

	/**
	 * Constructs the object
	 * @link  http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param string $serialized <p>
	 *                           The string representation of the object.
	 *                           </p>
	 *
	 * @return void
	 *
	 */
	public function unserialize( $serialized ) {

		$data = unserialize( $serialized );

		if ( is_string( $data['notification'] ) ) {
			$this->notification = it_exchange_email_notifications()->get_notification( $data['notification'] );
		} else {
			$this->notification = $data['notification'];
		}

		$this->context   = $data['context'];
		$this->ccs       = $data['ccs'];
		$this->bccs      = $data['bccs'];
		$this->recipient = $data['recipient'];
	}
}
