<?php
/**
 * Contains the mutable sendable wrapper class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Sendable_Mutable_Wrapper
 */
class IT_Exchange_Sendable_Mutable_Wrapper implements IT_Exchange_Sendable {

	/**
	 * @var IT_Exchange_Sendable
	 */
	protected $sendable;

	/**
	 * @var string|null
	 */
	protected $subject;

	/**
	 * @var string|null
	 */
	protected $body;

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	protected $additional_ccs = array();

	/**
	 * @var IT_Exchange_Email_Recipient[]
	 */
	protected $additional_bccs = array();

	/**
	 * @var array
	 */
	protected $additional_context = array();

	/**
	 * IT_Exchange_Sendable_Mutable_Wrapper constructor.
	 *
	 * @param IT_Exchange_Sendable $sendable
	 */
	public function __construct( IT_Exchange_Sendable $sendable ) {
		$this->sendable = $sendable;
	}

	/**
	 * Get the subject line.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_subject() {

		if ( ! $this->subject ) {
			return $this->sendable->get_subject();
		}

		return $this->subject;
	}

	/**
	 * Override the subject of this email.
	 *
	 *
	 *
	 * @param string $subject
	 *
	 * @return self
	 */
	public function override_subject( $subject ) {
		$this->subject = $subject;

		return $this;
	}

	/**
	 * Get the body.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_body() {

		if ( ! $this->body ) {
			return $this->sendable->get_body();
		}

		return $this->body;
	}

	/**
	 * Override the body of this email.
	 *
	 *
	 *
	 * @param string $body
	 *
	 * @return self
	 */
	public function override_body( $body ) {
		$this->body = $body;

		return $this;
	}

	/**
	 * Get the email template.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Template
	 */
	public function get_template() {
		return $this->sendable->get_template();
	}

	/**
	 * Get the recipient for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient
	 */
	public function get_recipient() {
		return $this->sendable->get_recipient();
	}

	/**
	 * Get the CCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_ccs() {
		return array_merge( $this->additional_ccs, $this->sendable->get_ccs() );
	}

	/**
	 * Add a Cc to this email.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_cc( IT_Exchange_Email_Recipient $recipient ) {
		$this->additional_ccs[] = $recipient;

		return $this;
	}

	/**
	 * Get the BCCs for this email.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Recipient[]
	 */
	public function get_bccs() {
		return array_merge( $this->additional_bccs, $this->sendable->get_bccs() );
	}

	/**
	 * Add a Bcc to this email.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Recipient $recipient
	 *
	 * @return self
	 */
	public function add_bcc( IT_Exchange_Email_Recipient $recipient ) {
		$this->additional_bccs[] = $recipient;

		return $this;
	}

	/**
	 * Get the context for this email.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_context() {
		return $this->additional_context + $this->sendable->get_context();
	}

	/**
	 * Add additional context.
	 *
	 *
	 *
	 * @param string $key
	 * @param mixed  $context
	 *
	 * @return self
	 */
	public function add_context( $key, $context ) {

		if ( ! is_string( $key ) || trim( $key ) === '' ) {
			throw new InvalidArgumentException( '$key must be a non-empty string.' );
		}

		if ( ! array_key_exists( $key, $this->get_context() ) ) {
			$this->additional_context[ $key ] = $context;
		}

		return $this;
	}

	/**
	 * Get the original sendable object.
	 *
	 *
	 *
	 * @return IT_Exchange_Sendable
	 */
	public function get_original() {
		return $this->sendable;
	}

	/**
	 * String representation of object
	 * @link  http://php.net/manual/en/serializable.serialize.php
	 * @return string the string representation of the object or null
	 *
	 */
	public function serialize() {
		return serialize( array(
			'sendable' => $this->sendable,
			'subject'  => $this->subject,
			'body'     => $this->body,
			'ccs'      => $this->additional_ccs,
			'bccs'     => $this->additional_bccs,
			'context'  => $this->additional_context
		) );
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

		$this->sendable           = $data['sendable'];
		$this->subject            = $data['subject'];
		$this->body               = $data['body'];
		$this->additional_ccs     = $data['ccs'];
		$this->additional_bccs    = $data['bccs'];
		$this->additional_context = $data['context'];
	}
}
