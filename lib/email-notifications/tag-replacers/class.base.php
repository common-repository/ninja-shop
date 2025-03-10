<?php
/**
 * Contains the base tag replacer class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class IT_Exchange_Email_Tag_Replacer_Base
 */
abstract class IT_Exchange_Email_Tag_Replacer_Base implements IT_Exchange_Email_Tag_Replacer {

	/**
	 * @var array
	 */
	private $tags = array();

	/**
	 * @var array
	 */
	protected $context = array();

	/**
	 * Add a tag to be replaced.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag $tag
	 *
	 * @return self
	 */
	public function add_tag( IT_Exchange_Email_Tag $tag ) {
		$this->tags[ $tag->get_tag() ] = $tag;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function add_tags( array $tags ) {
		foreach ( $tags as $tag ) {
			$this->tags[ $tag->get_tag() ] = $tag;
		}

		return $this;
	}

	/**
	 * Get a tag object for a given tag.
	 *
	 *
	 *
	 * @param string $tag
	 *
	 * @return IT_Exchange_Email_Tag|null
	 */
	public function get_tag( $tag ) {
		return isset( $this->tags[ $tag ] ) ? $this->tags[ $tag ] : null;
	}

	/**
	 * Get all registered tags.
	 *
	 *
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Get all tags for a given notification.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Notification $notification
	 *
	 * @return IT_Exchange_Email_Tag[]
	 */
	public function get_tags_for( IT_Exchange_Email_Notification $notification ) {

		$tags = array();

		foreach ( $this->get_tags() as $tag ) {
			if ( $tag->is_available_for( $notification ) ) {
				$tags[] = $tag;
			}
		}

		return $tags;
	}

	/**
	 * Handle a sendable object before it has been sent.
	 *
	 *
	 *
	 * @param IT_Exchange_Sendable_Mutable_Wrapper $sendable
	 *
	 * @return bool True to continue, false to stop email sending.
	 */
	public function handle( IT_Exchange_Sendable_Mutable_Wrapper $sendable ) {

		$sendable->override_subject( $this->replace( $sendable->get_subject(), $sendable->get_context() ) );
		$sendable->override_body( $this->replace( $sendable->get_body(), $sendable->get_context() ) );

		return true;
	}

	/**
	 * Replace the email tags.
	 *
	 *
	 *
	 * @param string $content
	 * @param array  $context
	 *
	 * @return string
	 */
	public function replace( $content, $context ) {

		if ( ! is_array( $context ) && ! $context instanceof ArrayAccess ) {
			throw new InvalidArgumentException( '$context must be an array.' );
		}

		$this->context = $context;

		$this->back_compat_globals( $context );

		return $content;
	}

	/**
	 * Replace an individual tag.
	 *
	 *
	 *
	 * @param IT_Exchange_Email_Tag $tag
	 * @param array                 $context
	 * @param array                 $options
	 *
	 * @return string
	 */
	protected function replace_tag( IT_Exchange_Email_Tag $tag, $context, $options ) {

		if ( count( array_diff( $tag->get_required_context(), array_keys( $context ) ) ) > 0 ) {
			$r = '';
		} else {
			$r = $tag->render( $context, $options );
		}

		/**
		 * Filter the replaced email tag.
		 *
		 * The dynamic portion of this hook, `{$tag->get_tag()}`, refers to the email tag.
		 *
		 *
		 *
		 * @param string                $r
		 * @param IT_Exchange_Email_Tag $tag
		 * @param array                 $context
		 * @param array                 $options
		 */
		$r = apply_filters( "ninja_shop_email_replace_tag_{$tag->get_tag()}", $r, $tag, $context, $options );

		/**
		 * Filter the replaced email tag.
		 *
		 *
		 *
		 * @param string                $r
		 * @param IT_Exchange_Email_Tag $tag
		 * @param array                 $context
		 * @param array                 $options
		 */
		$r = apply_filters( "ninja_shop_email_replace_tag", $r, $tag, $context, $options );

		return $r;
	}

	/**
	 * Replace a legacy tag.
	 *
	 *
	 *
	 * @param string $tag
	 * @param array  $options
	 *
	 * @return string|bool
	 */
	protected function replace_legacy( $tag, $options ) {

		$functions = $this->get_legacy_functions();

		if ( ! isset( $functions[ $tag ] ) || ! is_callable( $functions[ $tag ] ) ) {
			return false;
		}

		$r = call_user_func( $functions[ $tag ], it_exchange_email_notifications(), $options, array() );

		/**
		 * Filter the shortcode response.
		 *
		 *
		 *
		 * @param string $r
		 * @param array  $atts
		 * @param string $content
		 * @param array  $data
		 * @param array  $context
		 */
		return apply_filters( "ninja_shop_email_notification_shortcode_{$tag}", $r, $options, '', $this->get_data() );
	}

	/**
	 * Get legacy tag replacement functions.
	 *
	 *
	 *
	 * @return array
	 */
	protected function get_legacy_functions() {
		return apply_filters_deprecated(
			'ninja_shop_email_notification_shortcode_functions',
			array( array(), $this->get_data() ),
			'2.0.0',
			'IT_Exchange_Email_Tag_Replacer::add_tag'
		);
	}

	/**
	 * Set globals for backwards compat.
	 *
	 * These should not be relied upon.
	 *
	 *
	 *
	 * @param array $context
	 */
	protected function back_compat_globals( $context ) {

		it_exchange_email_notifications()->transaction_id = empty( $context['transaction'] ) ? false : $context['transaction']->get_ID();
		it_exchange_email_notifications()->customer_id    = empty( $context['customer'] ) ? false : $context['customer']->id;
		it_exchange_email_notifications()->user           = it_exchange_get_customer( it_exchange_email_notifications()->customer_id );

		$GLOBALS['it_exchange']['email-confirmation-data'] = $this->get_data();
	}

	/**
	 * Get the data array. This is mainly for back-compat.
	 *
	 *
	 *
	 * @return array
	 */
	protected function get_data() {
		return array(
			0 => empty( $this->context['transaction'] ) ? null : it_exchange_get_transaction( $this->context['transaction'] ),
			1 => it_exchange_email_notifications()
		);
	}
}
