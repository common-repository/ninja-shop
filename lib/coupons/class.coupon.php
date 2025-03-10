<?php
/**
 * This file holds the class for an Ninja Shop Coupon
 *
 * @package IT_Exchange
 * 
 */

/**
 * Merges a WP Post with Ninja Shop Coupon data
 *
 *
 */
class IT_Exchange_Coupon implements ITE_Object, ArrayAccess, Countable, Iterator {

	const TYPE_PERCENT = '%';
	const TYPE_FLAT = 'amount';

	const APPLY_CART = 'cart';
	const APPLY_PRODUCT = 'per-product';

	// WP Post Type Properties
	var $ID;
	var $post_author;
	var $post_date;
	var $post_date_gmt;
	var $post_content;
	var $post_title;
	var $post_excerpt;
	var $post_status;
	var $comment_status;
	var $ping_status;
	var $post_password;
	var $post_name;
	var $to_ping;
	var $pinged;
	var $post_modified;
	var $post_modified_gmt;
	var $post_content_filtered;
	var $post_parent;
	var $guid;
	var $menu_order;
	var $post_type;
	var $post_mime_type;
	var $comment_count;

	/**
	 * @param array $coupon_data any custom data registered by the coupon addon
	 *
	 *
	 *
	 * @internal
	 */
	var $coupon_data = array();

	/**
	 * Coupon code property. Use get_code() instead.
	 *
	 * @deprecated 1.33
	 *
	 * @var string
	 */
	var $code;

	/**
	 * Constructor. Loads post data and coupon data
	 *
	 *
	 *
	 * @param mixed $post wp post id or post object. optional.
	 *
	 * @throws Exception
	 */
	public function __construct( $post = false ) {

		// If not an object, try to grab the WP object
		if ( ! is_object( $post ) ) {
			$post = get_post( (int) $post );
		}

		// Ensure that $post is a WP_Post object
		if ( is_object( $post ) && ! $post instanceof WP_Post ) {
			$post = false;
		}

		// Ensure this is a coupon post type
		if ( 'it_exchange_coupon' != get_post_type( $post ) ) {
			$post = false;
		}

		// Return a WP Error if we don't have the $post object by this point
		if ( ! $post ) {
			throw new Exception( __( 'The IT_Exchange_Coupon class must have a WP post object or ID passed to its constructor', 'it-l10n-ithemes-exchange' ) );
		}

		// Grab the $post object vars and populate this objects vars
		foreach ( (array) get_object_vars( $post ) as $var => $value ) {
			$this->$var = $value;
		}

		/**
		 * Allows for additional properties to be set on the coupon object.
		 *
		 * Custom coupon types should register their coupon type with their custom class.
		 *
		 * @deprecated 1.33
		 *
		 * @param array   $properties
		 * @param WP_Post $post
		 */
		$additional = apply_filters( 'ninja_shop_coupon_additional_data', array(), $post );
		foreach ( $additional as $key => $value ) {
			$this->coupon_data[ $key ] = $value;
			$this->$key                = $value;
		}

		$this->coupon_data['ID']    = $this->ID;
		$this->coupon_data['id']    = $this->ID;
		$this->coupon_data['title'] = $this->post_title;

		reset( $this->coupon_data );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Coupon() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * @inheritDoc
	 */
	public static function get_object_type() { return it_exchange_object_type_registry()->get( 'coupon' ); }

	/**
	 * Get the type of this coupon.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_type() { return ''; }

	/**
	 * Get the application method.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_application_method() { return ''; }

	/**
	 * Get the type of the discount.
	 *
	 * Either '%' or 'amount', but you should evaluate against the constants provided.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_amount_type() { return ''; }

	/**
	 * Get the total amount of the discount.
	 *
	 * ie, the 5 in 5% or the 10 in $10 off.
	 *
	 *
	 *
	 * @return float
	 */
	public function get_amount_number() { return 0.00; }

	/**
	 * This method is called when a coupon is used for a transaction.
	 *
	 *
	 *
	 * @param object $transaction_object
	 *
	 * @return bool
	 */
	public function use_coupon( $transaction_object ) {

		if ( $this->is_used_by( $transaction_object->cart_id ) ) {
			return false;
		}

		$this->record_used_by( $transaction_object->cart_id );

		/**
		 * Fires when a coupon is used.
		 *
		 *
		 *
		 * @param IT_Exchange_Coupon $this
		 * @param object             $transaction_object
		 */
		do_action( 'ninja_shop_use_coupon', $this, $transaction_object );

		$this->increment_usage( $transaction_object );

		return true;
	}

	/**
	 * Unuse a coupon.
	 *
	 * This method is called when a coupon is no longer cleared for delivery.
	 *
	 *
	 *
	 * @param object $transaction_object
	 *
	 * @return bool
	 */
	public function unuse_coupon( $transaction_object ) {

		if ( ! $this->is_used_by( $transaction_object->cart_id ) ) {
			return false;
		}

		$this->delete_used_by( $transaction_object->cart_id );

		/**
		 * Fires when a coupon is un-used.
		 *
		 * Typically this is as a result of a order cancellation.
		 *
		 *
		 *
		 * @param IT_Exchange_Coupon $this
		 * @param object             $transaction_object
		 */
		do_action( 'ninja_shop_unuse_coupon', $this, $transaction_object );
		$this->decrement_usage( $transaction_object );

		return true;
	}

	/**
	 * Get the total uses of this coupon.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_total_uses() {
		return count( $this->get_uses() );
	}

	/**
	 * Record that this coupon was used by a transaction.
	 *
	 *
	 *
	 * @param string $cart_id
	 */
	protected function record_used_by( $cart_id ) {
		add_post_meta( $this->get_ID(), '_used_by', $cart_id );
	}

	/**
	 * Delete the record that this coupon was used by a transaction.
	 *
	 *
	 *
	 * @param string $cart_id
	 */
	protected function delete_used_by( $cart_id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$mid = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_id FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = %s AND meta_value = %s",
			$this->get_ID(), '_used_by', $cart_id ) );

		if ( $mid ) {
			delete_metadata_by_mid( 'post', $mid );
		}
	}

	/**
	 * Check if this coupon was used by a transaction.
	 *
	 *
	 *
	 * @param string $cart_id
	 *
	 * @return bool
	 */
	protected function is_used_by( $cart_id ) {
		return in_array( $cart_id, $this->get_uses(), true );
	}

	/**
	 * Get all uses of this coupon.
	 *
	 * This is an associative array of cart IDs.
	 *
	 *
	 *
	 * @return array
	 */
	protected function get_uses() {
		$uses = get_post_meta( $this->get_ID(), '_used_by', false );

		if ( ! is_array( $uses ) ) {
			return array();
		}

		return $uses;
	}

	/**
	 * Increment usage of this coupon.
	 *
	 *
	 *
	 * @param object $transaction_object
	 */
	public function increment_usage( $transaction_object ) {
		// add-ons should overwrite this method
	}

	/**
	 * Decrement the usage of this coupon.
	 *
	 *
	 *
	 * @param object $transaction_object
	 */
	public function decrement_usage( $transaction_object ) {
		// add-ons should overwrite this method
	}

	/**
	 * Validate the coupon for a given cart.
	 *
	 *
	 *
	 * @param \ITE_Cart|null $cart If null, default to the current cart.
	 *
	 * @throws Exception
	 */
	public function validate( ITE_Cart $cart = null ) {

	}

	/**
	 * Whether this coupon is valid for a given product.
	 * 
	 *
	 * 
	 * @param \ITE_Cart_Product $product
	 *
	 * @return bool
	 */
	public function valid_for_product( ITE_Cart_Product $product ) {
		return false;
	}

	/**
	 * The __toString method allows a class to decide how it will react when it is converted to a string.
	 *
	 *
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->get_code();
	}

	/**
	 * Get data to save to the transaction object.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_data_for_transaction_object() {
		return array(
			'id'   => $this->get_ID(),
			'code' => $this->get_code(),
			'type' => $this->get_type(),
		);
	}

	/**
	 * Get a list of all the keys that can be provided by ::get_data_for_transaction_object().
	 *
	 *
	 *
	 * @return string[]
	 */
	public static function supported_data_for_transaction_object() {
		return array( 'id', 'code', 'type' );
	}

	/**
	 * Get the Coupon ID.
	 *
	 *
	 *
	 * @return int
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * Get the coupon code.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_code() {
		return $this->code;
	}

	/**
	 * Return the current element
	 *
	 *
	 *
	 * @return mixed Can return any type.
	 */
	public function current() {
		return current( $this->coupon_data );
	}

	/**
	 * Move forward to next element
	 *
	 *
	 *
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next( $this->coupon_data );
	}

	/**
	 * Return the key of the current element
	 *
	 *
	 *
	 * @return mixed scalar on success, or null on failure.
	 */
	public function key() {
		return key( $this->coupon_data );
	}

	/**
	 * Checks if current position is valid
	 *
	 *
	 *
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 * Returns true on success or false on failure.
	 */
	public function valid() {
		return key( $this->coupon_data ) !== null;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 *
	 */
	public function rewind() {
		reset( $this->coupon_data );
	}

	/**
	 * Set a custom property.
	 *
	 *
	 *
	 * @param string $offset
	 * @param mixed  $value
	 */
	public function offsetSet( $offset, $value ) {
		if ( is_null( $offset ) ) {
			$this->coupon_data[] = $value;
		} else {
			$this->coupon_data[ $offset ] = $value;
		}
	}

	/**
	 * Check if a custom property exists.
	 *
	 *
	 *
	 * @param string $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->coupon_data[ $offset ] );
	}

	/**
	 * Unset a custom property.
	 *
	 *
	 *
	 * @param string $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->coupon_data[ $offset ] );
	}

	/**
	 * Retrieve a custom property.
	 *
	 *
	 *
	 * @param string $offset
	 *
	 * @return mixed|null
	 */
	public function offsetGet( $offset ) {
		return isset( $this->coupon_data[ $offset ] ) ? $this->coupon_data[ $offset ] : null;
	}

	/**
	 * Get the total custom properties registered.
	 *
	 *
	 *
	 * @return int
	 */
	public function count() {
		return count( $this->coupon_data );
	}
}
