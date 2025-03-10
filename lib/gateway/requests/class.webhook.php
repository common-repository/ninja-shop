<?php
/**
 * Webhook Request.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Webhook_Gateway_Request
 */
class ITE_Webhook_Gateway_Request implements ITE_Gateway_Request {

	/** @var array */
	private $webhook_data;

	/** @var array */
	private $headers;

	/**
	 * ITE_Webhook_Gateway_Request constructor.
	 *
	 * @param array $webhook_data
	 * @param array $server $_SERVER
	 */
	public function __construct( array $webhook_data, array $server = array() ) {
		$this->webhook_data = $webhook_data;
		$this->headers      = $server;

		foreach ( $server as $key => $value ) {
			if ( strpos( $key, 'HTTP_' ) === 0 ) {
				$this->headers[ str_replace( '-', '_', strtolower( substr( $key, 5 ) ) ) ] = $value;
			}
		}
	}

	/**
	 * Get webhook data.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_webhook_data() {
		return $this->webhook_data;
	}

	/**
	 * Get the raw post data.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_raw_post_data() {

		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
		}

		return $HTTP_RAW_POST_DATA;
	}

	/**
	 * Get a header value.
	 *
	 *
	 *
	 * @param string $header
	 *
	 * @return string|null
	 */
	public function get_header( $header ) {

		$header = strtolower( str_replace( '-', '_', $header ) );

		if ( isset( $this->headers[ $header ] ) ) {
			return $this->headers[ $header ];
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function get_customer() { return null; }

	/**
	 * @inheritDoc
	 */
	public static function get_name() { return 'webhook'; }
}
