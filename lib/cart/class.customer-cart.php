<?php
/**
 * Cart class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Cart
 */
class ITE_Cart {

	/** @var \ITE_Cart_Repository */
	private $repository;

	/** @var ITE_Line_Item_Collection */
	private $items;

	/** @var ITE_Cart_Validator[] */
	private $cart_validators = array();

	/** @var ITE_Line_Item_Validator[] */
	private $item_validators = array();

	/** @var ITE_Location_Validator[] */
	private $location_validators = array();

	/** @var string */
	private $cart_id;

	/** @var IT_Exchange_Customer|null */
	private $customer;

	/** @var ITE_Cart_Feedback */
	private $feedback;

	/** @var bool */
	private $doing_merge = false;

	/**
	 * ITE_Cart constructor.
	 *
	 * @param ITE_Cart_Repository       $repository
	 * @param string                    $cart_id
	 * @param IT_Exchange_Customer|null $customer
	 */
	public function __construct( ITE_Cart_Repository $repository, $cart_id, IT_Exchange_Customer $customer = null ) {
		$this->repository = $repository;
		$this->cart_id    = $cart_id;

		if ( ! $customer && $this->is_current() ) {
			$customer = it_exchange_get_current_customer();

			if ( ! $customer instanceof IT_Exchange_Customer ) {
				$customer = null;
			}
		} elseif ( $this->has_meta( 'guest-email' ) ) {
			$customer = it_exchange_get_customer( $this->get_meta( 'guest-email' ) );
		}

		$this->customer = $customer;
		$this->feedback = new ITE_Cart_Feedback();

		foreach ( self::validators() as $validator ) {
			if ( $validator instanceof ITE_Cart_Validator ) {
				$this->add_cart_validator( $validator );
			} elseif ( $validator instanceof ITE_Line_Item_Validator ) {
				$this->add_item_validator( $validator );
			} elseif ( $validator instanceof ITE_Location_Validator ) {
				$this->add_location_validator( $validator );
			}
		}
	}

	/**
	 * Create a new cart.
	 *
	 * This should only be called once for each cart session. If this cart is backed by the current session, the cart ID
	 * will be set in the session.
	 *
	 *
	 *
	 * @param \ITE_Cart_Repository|null  $repository     Specify the repository to used. If null, the session
	 *                                                   repository will be used.
	 * @param \IT_Exchange_Customer|null $customer       Specify the customer to use. If null, and this is the active
	 *                                                   cart, the current customer will be used.
	 *
	 * @return \ITE_Cart
	 */
	public static function create( ITE_Cart_Repository $repository = null, IT_Exchange_Customer $customer = null ) {

		$repository = $repository ?: new ITE_Cart_Session_Repository(
			it_exchange_get_session(), new ITE_Line_Item_Repository_Events()
		);

		if ( ! $customer && ( $c = it_exchange_get_current_customer() ) && $c instanceof IT_Exchange_Customer ) {
			$customer = $c;
		}

		$is_current = $repository instanceof ITE_Cart_Session_Repository && $repository->backed_by_active_session();
		$cart_id    = it_exchange_create_cart_id();

		if ( $is_current ) {
			it_exchange_update_cart_data( 'cart_id', $cart_id );
		}

		$cart = new self( $repository, $cart_id, $customer );

		if ( $customer instanceof IT_Exchange_Guest_Customer ) {
			$cart->set_guest( $customer );
		}

		if ( $cart->get_billing_address() ) {
			$compare = new ITE_In_Memory_Address( $cart->get_billing_address()->to_array() );
			$address = $cart->get_billing_address();

			if ( ! $cart->validate_location( $address ) ) {
				$cart->set_billing_address( null );
			} elseif ( ! $address->equals( $compare ) ) { // Handle changes by reference
				$cart->set_billing_address( $address );
			}
		}

		if ( $cart->get_shipping_address() ) {
			$compare = new ITE_In_Memory_Address( $cart->get_billing_address()->to_array() );
			$address = $cart->get_shipping_address();

			if ( ! $cart->validate_location( $cart->get_shipping_address() ) ) {
				$cart->set_shipping_address( null );
			} elseif ( ! $address->equals( $compare ) ) { // Handle changes by reference
				$cart->set_shipping_address( $address );
			}
		}

		/**
		 * Fires when a new cart is created.
		 *
		 *
		 *
		 * @param \ITE_Cart $cart
		 */
		do_action( 'ninja_shop_create_cart', $cart );

		return $cart;
	}

	/**
	 * Get the cart ID.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->cart_id;
	}

	/**
	 * Get the customer this cart belongs to.
	 *
	 *
	 *
	 * @return IT_Exchange_Customer|null
	 */
	public function get_customer() {
		return $this->customer;
	}

	/**
	 * Set the customer object for this cart.
	 *
	 * This is not advisable to use under most circumstances. This change is only
	 * persisted in memory and not to the DB.
	 *
	 * @internal
	 *
	 *
	 *
	 * @param IT_Exchange_Customer $customer
	 */
	public function _set_customer( IT_Exchange_Customer $customer ) {
		$this->customer = $customer;
	}

	/**
	 * Check if the cart is the current active cart.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_current() {
		return $this->get_id() === it_exchange_get_cart_id();
	}

	/**
	 * Is this the main cart for a customer.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_main() {

		$repo = $this->get_repository();

		if ( $repo instanceof ITE_Cart_Cached_Session_Repository ) {
			return (bool) $repo->get_model()->is_main;
		}

		if ( $repo instanceof ITE_Cart_Session_Repository ) {
			$model = ITE_Session_Model::from_cart_id( $this->get_id() );

			return $model && $model->is_main;
		}

		return false;
	}

	/**
	 * Returns true if the cart is undergoing a merge.
	 *
	 *
	 *
	 * @return boolean
	 */
	public function is_doing_merge() {
		return $this->doing_merge;
	}

	/**
	 * Get the time this cart expires at.
	 *
	 *
	 *
	 * @return DateTime|null
	 */
	public function expires_at() { return $this->get_repository()->expires_at(); }

	/**
	 * Can this cart be purchased by a guest customer.
	 *
	 *
	 *
	 * @return bool
	 */
	public function can_be_purchased_by_guest() {

		if ( ! it_exchange_is_guest_checkout_enabled() ) {
			return false;
		}

		foreach ( $this->get_items() as $item ) {
			if ( ! it_exchange_can_line_item_be_purchased_by_guest( $item, $this ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Is this a guest purchase.
	 *
	 *
	 *
	 * @return bool
	 */
	public function is_guest() {
		return $this->has_meta( 'guest-email' );
	}

	/**
	 * Set the guest customer for this cart.
	 *
	 *
	 *
	 * @param IT_Exchange_Guest_Customer $customer
	 */
	public function set_guest( IT_Exchange_Guest_Customer $customer ) {
		$this->customer = $customer;
		$this->set_meta( 'guest-email', $customer->get_email() );
	}

	/**
	 * Get the customer's shipping address.
	 *
	 *
	 *
	 * @return ITE_Location|null
	 */
	public function get_shipping_address() {

		if ( ! $this->requires_shipping() ) {
			return null;
		}

		return $this->get_repository()->get_shipping_address();
	}

	/**
	 * Set the customer's shipping address.
	 *
	 *
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	public function set_shipping_address( ITE_Location $location = null ) {

		$previous = $this->get_shipping_address();

		if ( $location && ( ! $previous || ! $location->equals( $previous ) ) ) {
			$valid = $this->validate_location( $location );

			if ( ! $valid ) {
				return false;
			}
		}

		if ( $result = $this->get_repository()->set_shipping_address( $location ) ) {
			/**
			 * Fires when the cart's shipping address has been updated.
			 *
			 *
			 *
			 * @param \ITE_Cart          $cart
			 * @param \ITE_Location|null $previous
			 * @param \ITE_Location|null $location
			 */
			do_action( 'ninja_shop_set_cart_shipping_address', $this, $previous, $location );
		}

		return $result;
	}

	/**
	 * Get the customer's billing address.
	 *
	 *
	 *
	 * @return ITE_Location|null
	 */
	public function get_billing_address() {
		return $this->get_repository()->get_billing_address();
	}

	/**
	 * Set the customer's billing address.
	 *
	 *
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	public function set_billing_address( ITE_Location $location = null ) {

		$previous = $this->get_billing_address();

		if ( $location && ( ! $previous || ! $location->equals( $previous ) ) ) {
			$valid = $this->validate_location( $location );

			if ( ! $valid ) {
				return false;
			}
		}

		if ( $result = $this->get_repository()->set_billing_address( $location ) ) {
			/**
			 * Fires when the cart's billing address has been updated.
			 *
			 *
			 *
			 * @param \ITE_Cart          $cart
			 * @param \ITE_Location|null $previous
			 * @param \ITE_Location|null $location
			 */
			do_action( 'ninja_shop_set_cart_billing_address', $this, $previous, $location );
		}

		return $result;
	}

	/**
	 * Validate a location against all registered location validators.
	 *
	 *
	 *
	 * @param \ITE_Location $location
	 *
	 * @return bool
	 */
	protected final function validate_location( ITE_Location $location ) {

		foreach ( $this->location_validators as $validator ) {
			if ( ! $validator->can_validate() || $validator->can_validate()->contains( $location ) ) {
				if ( ! $validator->validate_for_cart( $location, $this ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Add a line item to the cart.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 * @param bool           $coerce
	 *
	 * @return ITE_Line_Item|null Updated item if added. Null if failed. Errors encountered during adding an item will
	 *                            be added to the cart feedback.
	 *
	 * @throws \ITE_Line_Item_Coercion_Failed_Exception
	 * @throws \ITE_Cart_Coercion_Failed_Exception
	 */
	public function add_item( ITE_Line_Item $item, $coerce = true ) {

		$this->set_services_for_item( $item );

		$method  = "add_{$item->get_type()}_item";
		$add_new = true;

		if ( ! method_exists( $this, $method ) ) {
			$add_new = true;
			$success = $this->get_repository()->save_item( $item );
		} elseif ( ( $success = $this->{$method}( $item, $add_new ) ) && $add_new ) {
			$this->get_repository()->save_item( $item );
		}

		if ( ! $success ) {
			return null;
		}

		$this->_clear_item_cache();

		if ( $coerce ) {
			$this->coerce( $item );
		}

		if ( ! $this->validate() ) {
			return null;
		}

		$item = $this->get_item( $item->get_type(), $item->get_id() );

		if ( ! $item ) {
			return null;
		}

		if ( ! $this->is_doing_merge() ) {
			it_exchange_log( 'Added {type} item {id}, {name}, to the cart {cart_id}.', ITE_Log_Levels::INFO, array(
				'_group'  => 'cart',
				'type'    => $item->get_type(),
				'name'    => $item->get_name(),
				'id'      => $item->get_id(),
				'cart_id' => $this->get_id(),
			) );
		}

		if ( ! $add_new ) {
			return $item;
		}

		/**
		 * Fires when a line item is added to the cart.
		 *
		 *
		 *
		 * @param \ITE_Line_Item $item
		 * @param \ITE_Cart      $cart
		 */
		do_action( 'ninja_shop_add_line_item_to_cart', $item, $this );

		$item = $this->get_item( $item->get_type(), $item->get_id() );

		/**
		 * Fires when a line item is added to the cart.
		 *
		 * The dynamic portion of this hook refers to the line item type.
		 *
		 *
		 *
		 * @param \ITE_Line_Item $item
		 * @param \ITE_Cart      $cart
		 */
		do_action( "ninja_shop_add_{$item->get_type()}_to_cart", $item, $this );

		return $item;
	}

	/**
	 * Get the line items contained in the cart.
	 *
	 *
	 *
	 * @param string $type    If empty, all line items will be returned.
	 * @param bool   $flatten Whether to flatten aggregate line items.
	 *
	 * @return ITE_Line_Item_Collection|ITE_Line_Item[]
	 *
	 * @throws InvalidArgumentException If $type is invalid.
	 */
	public function get_items( $type = '', $flatten = false ) {

		if ( $type ) {
			self::assert_type( $type );
		}

		if ( $this->items ) {
			$items = clone $this->items;
		} else {
			$items       = $this->get_repository()->all_items();
			$this->items = clone $items;
		}

		if ( $flatten ) {
			$items = $items->flatten();

			// Avoid double flattening.
			foreach ( $items as $item ) {
				if ( $item instanceof ITE_Cart_Aware ) {
					$item->set_cart( $this );
				}
			}
		} else {
			$items = $items->set_cart( $this );
		}

		if ( $type ) {
			$items = $items->with_only( $type );
		}

		return $items;
	}

	/**
	 * Retrieve a line item from the cart.
	 *
	 *
	 *
	 * @param string     $type
	 * @param string|int $id
	 *
	 * @return \ITE_Line_Item|null
	 *
	 * @throws InvalidArgumentException If $type is invalid.
	 */
	public function get_item( $type, $id ) {

		$items = $this->get_items( $type );
		$item  = $items->get( $type, $id );

		if ( $item ) {
			return $item;
		}

		$item = $items->flatten()->get( $type, $id );

		return $item;
	}

	/**
	 * Remove an item from the cart.
	 *
	 *
	 *
	 * @param string|ITE_Line_Item $type_or_item Either the line item type, or line item object.
	 * @param string|int           $id           Empty if passing an object, or the item id if searching.
	 *
	 * @return bool False if item could not be found or removed.
	 *
	 * @throws \InvalidArgumentException If invalid type given.
	 */
	public function remove_item( $type_or_item, $id = '' ) {

		$item = $type_or_item instanceof ITE_Line_Item ? $type_or_item : $this->get_item( $type_or_item, $id );

		if ( ! $item ) {
			return false;
		}

		$method      = "remove_{$item->get_type()}_item";
		$remove_item = true;
		$deleted     = true;

		if ( method_exists( $this, $method ) ) {
			$deleted = $this->{$method}( $item, $remove_item );

			if ( ! $deleted ) {
				return false;
			}
		}

		if ( $remove_item ) {
			$deleted = $this->get_repository()->delete_item( $item );
		}

		if ( $deleted ) {

			if ( ! $this->is_doing_merge() ) {
				it_exchange_log( 'Removed {type} item with id {id}, {name}, from the cart {cart_id}.', ITE_Log_Levels::INFO, array(
					'_group'  => 'cart',
					'type'    => $item->get_type(),
					'name'    => $item->get_name(),
					'id'      => $item->get_id(),
					'cart_id' => $this->get_id(),
				) );
			}

			$this->_clear_item_cache();

			/**
			 * Fires when a line item is removed from the cart.
			 *
			 *
			 *
			 * @param \ITE_Line_Item $item
			 * @param \ITE_Cart      $cart
			 */
			do_action( 'ninja_shop_remove_line_item_from_cart', $item, $this );

			/**
			 * Fires when a line item is removed from the cart.
			 *
			 * The dynamic portion of this hook refers to the line item type.
			 *
			 *
			 *
			 * @param \ITE_Line_Item $item
			 * @param \ITE_Cart      $cart
			 */
			do_action( "ninja_shop_remove_{$item->get_type()}_from_cart", $item, $this );
		}

		return $deleted;
	}

	/**
	 * Remove all line items, or all line items of a given type from the cart.
	 *
	 *
	 *
	 * @param string $type    The item type. Optionally. If unspecified, all item types will be removed.
	 * @param bool   $flatten Whether to remove all items, including aggregates' children.
	 *
	 * @return bool
	 *
	 * @throws \InvalidArgumentException If invalid type given.
	 */
	public function remove_all( $type = '', $flatten = false ) {

		$items = $this->get_items( $type, $flatten );

		foreach ( $items as $item ) {
			$this->remove_item( $item );
		}

		if ( ! $this->is_doing_merge() ) {
			it_exchange_log( "Removed all items of type '{type}' from the cart {cart_id}", ITE_Log_Levels::INFO, array(
				'type'    => $type,
				'cart_id' => $this->get_id(),
				'_group'  => 'cart',
			) );
		}

		return true;
	}

	/**
	 * Save a line item.
	 *
	 *
	 *
	 * @param ITE_Line_Item $item
	 *
	 * @return bool
	 */
	public function save_item( ITE_Line_Item $item ) {
		$r = $this->get_repository()->save_item( $item );

		if ( $r ) {
			$this->_clear_item_cache();
		}

		return $r;
	}

	/**
	 * Save multiple line items at once.
	 *
	 *
	 *
	 * @param ITE_Line_Item[]|ITE_Line_Item_Collection $items
	 *
	 * @return bool
	 */
	public function save_many_items( $items ) {

		$items = $items instanceof ITE_Line_Item_Collection ? $items->to_array() : $items;
		$r     = $this->get_repository()->save_many_items( $items );

		if ( $r ) {
			$this->_clear_item_cache();
		}

		return $r;
	}

	/**
	 * Fetch a new instance of a line item object.
	 *
	 *
	 *
	 * @param ITE_Line_Item $item
	 *
	 * @return ITE_Line_Item|null
	 */
	public function refresh_item( ITE_Line_Item $item ) {
		$item = $this->get_repository()->get_item( $item->get_type(), $item->get_id() );

		if ( $item ) {

			$this->items->replace( $item );

			return $item;
		}

		return null;
	}

	/**
	 * Set any services on the item.
	 *
	 *
	 *
	 * @param ITE_Line_Item $item
	 */
	protected function set_services_for_item( ITE_Line_Item $item ) {

		if ( $item instanceof ITE_Cart_Repository_Aware ) {
			$item->set_cart_repository( $this->get_repository() );
		}

		if ( $item instanceof ITE_Cart_Aware ) {
			$item->set_cart( $this );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $child ) {
				$this->set_services_for_item( $child );
			}
		}
	}

	/**
	 * Clear the internal line item cache.
	 *
	 * This is automatically cleared when removing or adding items.
	 */
	public function _clear_item_cache() {
		$this->items = null;
	}

	/**
	 * Get the currency code the cart is being purchased in.
	 *
	 * For example, USD, EUR.
	 *
	 *
	 *
	 * @return string
	 */
	public function get_currency_code() {

		$general = it_exchange_get_option( 'settings_general' );

		return $general['default-currency'];
	}

	/**
	 * Check if the cart contains items of a given type. Either as child line items or top-level line items.
	 *
	 *
	 *
	 * @param string $type
	 *
	 * @return bool
	 */
	public function has_item_type( $type ) {

		if ( $this->get_items( $type )->count() > 0 ) {
			return true;
		}

		if ( $this->get_items()->flatten()->with_only( $type )->count() > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all item types.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_item_types() {

		$all_items  = $this->get_items()->flatten();
		$item_types = array();

		foreach ( $all_items as $item ) {
			if ( ! in_array( $item->get_type(), $item_types, true ) ) {
				$item_types[] = $item->get_type();
			}
		}

		sort( $item_types );

		return $item_types;
	}

	/**
	 * Does the cart contain a recurring fee.
	 *
	 *
	 *
	 * @return bool Returns false if no fees or if only non-recurring fees.
	 */
	public function contains_recurring_fee() {
		$fees = $this->get_items()->flatten()->with_only( 'fee' );

		if ( ! $fees->count() ) {
			return false;
		}

		/** @var ITE_Fee_Line_Item $fee */
		foreach ( $fees as $fee ) {
			if ( $fee->is_recurring() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Does the cart contain a non-recurring fee.
	 *
	 *
	 *
	 * @return bool Returns false if no fees or if only recurring fees.
	 */
	public function contains_non_recurring_fee() {
		$fees = $this->get_items()->flatten()->with_only( 'fee' );

		if ( ! $fees->count() ) {
			return false;
		}

		/** @var ITE_Fee_Line_Item $fee */
		foreach ( $fees as $fee ) {
			if ( ! $fee->is_recurring() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Callback to perform custom processing when a cart product line item is added to the cart.
	 *
	 *
	 *
	 * @param \ITE_Cart_Product $product
	 * @param bool              $add_item
	 *
	 * @return bool
	 */
	protected function add_product_item( ITE_Cart_Product $product, &$add_item ) {

		if ( ! $product->get_id() ) {
			ITE_Cart_Product::generate_cart_product_id( $product );
		}

		if ( $dupe = $this->get_item( 'product', $product->get_id() ) ) {

			if ( $this->is_doing_merge() ) {
				$add_item = false;

				return true; // Don't combine quantities when doing a merge
			}

			$dupe->set_quantity( $product->get_quantity() + $dupe->get_quantity() );
			$this->save_item( $dupe );

			$add_item = false;
		}

		return true;
	}

	/**
	 * Callback to perform custom processing when a tax line item is added to the cart.
	 *
	 *
	 *
	 * @param ITE_Tax_Line_Item $tax
	 * @param bool              $add_item
	 *
	 * @return bool
	 */
	protected function add_tax_item( ITE_Tax_Line_Item $tax, &$add_item ) {
		foreach ( $this->get_items() as $item ) {
			if ( $item instanceof ITE_Taxable_Line_Item && $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
				$this->save_item( $item );
			}
		}

		$add_item = false;

		return true;
	}

	/**
	 * Callback to perform custom processing when a coupon line item is added to the cart.
	 *
	 *
	 *
	 * @param \ITE_Coupon_Line_Item $coupon
	 * @param bool                  $add_item
	 *
	 * @return bool
	 */
	protected function add_coupon_item( ITE_Coupon_Line_Item $coupon, &$add_item ) {

		try {
			$coupon->get_coupon()->validate( $this );
		} catch ( Exception $e ) {
			$this->get_feedback()->add_error( $e->getMessage(), $coupon );

			return false;
		}

		$products = $this->get_items( 'product' );

		if ( ! $products->count() ) {
			return false;
		}

		/** @var ITE_Cart_Product $product */
		foreach ( $products as $product ) {
			if ( $coupon->get_coupon()->valid_for_product( $product ) ) {
				$product->add_item( $coupon->create_scoped_for_product( $product ) );
				$this->save_item( $product );
			}
		}

		$add_item = true;

		return true;
	}

	/**
	 * When a top level coupon is removed, remove all coupons items for it.
	 *
	 *
	 *
	 * @param ITE_Coupon_Line_Item $coupon
	 * @param bool                 $remove_item
	 *
	 * @return bool
	 */
	protected function remove_coupon_item( ITE_Coupon_Line_Item $coupon, &$remove_item ) {

		if ( $coupon->get_aggregate() ) {
			return true;
		}

		$aggregatables = $this->get_items( 'coupon', true )->filter( function ( ITE_Coupon_Line_Item $item ) use ( $coupon ) {
			return $item->get_id() !== $coupon->get_id() && $item->get_coupon()->get_code() === $coupon->get_coupon()->get_code();
		} );

		foreach ( $aggregatables as $aggregatable ) {
			$this->remove_item( $aggregatable );
		}

		return true;
	}

	/**
	 * Get the cart subtotal.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return float
	 */
	public function get_subtotal( array $options = array() ) {

		$subtotal = 0;
		$items    = $this->get_items()->non_summary_only();

		if ( ! $items->count() ) {
			return 0;
		}

		foreach ( $items as $item ) {
			if (
				! $item instanceof ITE_Cart_Product ||
				empty( $options['feature'] ) ||
				$item->get_product()->get_feature( $options['feature'] )
			) {
				$subtotal += $item->get_total();
			}
		}

		/**
		 * Filter the cart subtotal.
		 *
		 *
		 *
		 *
		 * @param float     $subtotal
		 * @param array     $options
		 * @param \ITE_Cart $cart
		 */
		return (float) apply_filters( 'ninja_shop_get_cart_subtotal', $subtotal, $options, $this );
	}

	/**
	 * Get the cart total.
	 *
	 *
	 *
	 * @param bool $try_frozen Try to get the frozen cart total if possible. Otherwise, will fall back to calculating
	 *                         the total.
	 *
	 * @return float
	 */
	public function get_total( $try_frozen = false ) {

		if ( $try_frozen && $this->has_meta( 'frozen_total' ) ) {
			return (float) $this->get_meta( 'frozen_total' );
		}

		$total = $this->get_subtotal();
		$total += $this->get_items( '', true )->summary_only()->total();

		/**
		 * Filter the cart total.
		 *
		 *
		 *
		 *
		 * @param float     $total
		 * @param \ITE_Cart $cart
		 */
		$total = apply_filters( 'ninja_shop_get_cart_total', $total, $this );

		return (float) max( 0, $total );
	}

	/**
	 * Calculate the total of all line items or a given line item type.
	 *
	 * This calculation is not cached.
	 *
	 *
	 *
	 * @param string $type
	 * @param bool   $unravel
	 *
	 * @return float
	 */
	public function calculate_total( $type = '', $unravel = true ) {
		return $this->get_items( $type, $unravel )->total();
	}

	/**
	 * Validate the current state of the cart.
	 *
	 *
	 *
	 * @param ITE_Cart_Feedback|null $feedback Optionally, specify feedback other than cart feedback for the validation
	 *                                         messages to be added to.
	 *
	 * @return bool
	 */
	public function validate( ITE_Cart_Feedback $feedback = null ) {

		if ( func_num_args() !== 1 ) {
			$feedback = $this->feedback;
		}

		foreach ( $this->cart_validators as $cart_validator ) {
			if ( ! $cart_validator->validate( $this, $feedback ) ) {
				return false;
			}
		}

		$items = $this->get_items();

		foreach ( $this->item_validators as $item_validator ) {
			foreach ( $items as $item ) {
				if ( $item_validator->accepts( $item->get_type() ) && ! $item_validator->validate( $item, $this, $feedback ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Coerce the cart to a valid state.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $new_item
	 *
	 * @return bool
	 */
	public function coerce( ITE_Line_Item $new_item = null ) {

		$feedback = $this->feedback;
		$valid    = true;

		try {
			foreach ( $this->cart_validators as $cart_validator ) {
				if ( ! $cart_validator->coerce( $this, $new_item, $feedback ) ) {
					$valid = false;
				}
			}
		} catch ( ITE_Cart_Coercion_Failed_Exception $e ) {
			if ( $new_item ) {
				it_exchange_log( 'Failed to coerce cart {cart_id} with new {type} item {id}, {name}: {exception}', ITE_Log_Levels::WARNING, array(
					'cart_id'   => $this->get_id(),
					'exception' => $e,
					'type'      => $new_item->get_id(),
					'name'      => $new_item->get_name(),
					'id'        => $new_item->get_id(),
					'_group'    => 'cart',
				) );
			} else {
				it_exchange_log( 'Failed to coerce cart {cart_id}: {exception}', ITE_Log_Levels::WARNING, array(
					'cart_id'   => $this->get_id(),
					'exception' => $e,
					'_group'    => 'cart',
				) );
			}
			$this->get_feedback()->add_error( $e->getMessage() );

			return false;
		}

		$items = $this->get_items();

		try {
			foreach ( $this->item_validators as $item_validator ) {
				foreach ( $items as $item ) {
					if ( $item_validator->accepts( $item->get_type() ) && ! $item_validator->coerce( $item, $this, $feedback ) ) {
						$valid = false;
					}
				}
			}
		} catch ( ITE_Line_Item_Coercion_Failed_Exception $e ) {
			if ( $new_item ) {
				it_exchange_log(
					'Failed to coerce {type} item {id}, {name} in cart {cart_id} with new {new_type} item {new_id}, {new_name}: {exception}',
					ITE_Log_Levels::WARNING, array(
					'cart_id'   => $this->get_id(),
					'exception' => $e,
					'type'      => $item->get_id(),
					'name'      => $item->get_name(),
					'id'        => $item->get_id(),
					'new_type'  => $new_item->get_id(),
					'new_name'  => $new_item->get_name(),
					'new_id'    => $new_item->get_id(),
					'_group'    => 'cart',
				) );
			} else {
				it_exchange_log( 'Failed to coerce {type} item {id}, {name} in cart {cart_id}: {exception}', ITE_Log_Levels::WARNING, array(
					'cart_id'   => $this->get_id(),
					'exception' => $e,
					'type'      => $item->get_id(),
					'name'      => $item->get_name(),
					'id'        => $item->get_id(),
					'_group'    => 'cart',
				) );
			}
			$this->get_feedback()->add_error( $e->getMessage(), $item );

			return false;
		}

		return $valid;
	}

	/**
	 * Does this cart require shipping.
	 *
	 *
	 *
	 * @return bool
	 */
	public function requires_shipping() {
		$has_product = $this->get_items( 'product' )->filter( function ( ITE_Cart_Product $item ) {
				return $item->get_product()->has_feature( 'shipping' );
			} )->count() > 0;

		if ( $has_product ) {
			return true;
		}

		if ( $this->is_current() && apply_filters( 'ninja_shop_shipping_address_purchase_requirement_enabled', false ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Set the shipping method for the cart.
	 *
	 * This function does not handle updating the session, so behavior is consistent among cart types.
	 * See IT_Exchange_Shipping::update_cart_shipping_method() for how the session should be updated.
	 *
	 * For example, when setting a single method for the entire cart.
	 *
	 * $cart->set_shipping_method( 'exchange-free-shipping' );
	 *
	 * Or when using multiple methods.
	 *
	 * $cart->set_shipping_method( 'multiple-methods' ); // Only needs to be done once
	 * $cart->set_shipping_method( 'exchange-free-shipping', $product_a );
	 * $cart->set_shipping_method( 'exchange-flat-rate-shipping', $product_b );
	 *
	 *
	 *
	 * @param string                        $method New shipping method slug. Or empty to remove.
	 * @param \ITE_Aggregate_Line_Item|null $for    Update the shipping method for a given item only. For use with
	 *                                              multiple methods per-cart.
	 *
	 * @return bool
	 */
	public function set_shipping_method( $method, ITE_Aggregate_Line_Item $for = null ) {

		if ( $for ) {

			$old_method = $this->get_shipping_method( $for, false );
			$old_method = $old_method ? $old_method->slug : false;

			$for->get_line_items()->with_only( 'shipping' )->delete();

			if ( $old_method ) {
				$this->get_items( 'shipping' )->filter( function ( ITE_Shipping_Line_Item $shipping ) use ( $old_method ) {
					return $shipping->get_method_slug() === $old_method;
				} )->delete();
			}

			if ( empty( $method ) ) {
				return true;
			}

			$args = it_exchange_get_registered_shipping_method_args( $method );

			if ( empty( $args['provider'] ) ) {
				return false;
			}

			$provider = it_exchange_get_registered_shipping_provider( $args['provider'] );
			$method   = it_exchange_get_registered_shipping_method( $method );

			if ( ! $method ) {
				return false;
			}

			$global = $this->add_item( ITE_Base_Shipping_Line_Item::create( $method, $provider, true ) );

			$item = ITE_Base_Shipping_Line_Item::create( $method, $provider );
			$item->set_scoped_from( $global );
			$item->set_cart_repository( $this->get_repository() );

			$for->add_item( $item );
			$this->save_item( $for );

			if ( $this->is_current() ) {
				it_exchange_update_multiple_shipping_method_for_cart_product( $for->get_id(), $method->slug );
			}

			it_exchange_log( 'Cart {cart_id} shipping method updated to {method} for {type} with id {id}, {name}.', ITE_Log_Levels::INFO, array(
				'cart_id' => $this->get_id(),
				'method'  => $method->slug,
				'type'    => $for->get_type(),
				'id'      => $for->get_id(),
				'name'    => $for->get_name(),
				'_group'  => 'cart',
			) );

			return true;
		}

		$this->remove_all( 'shipping', true );

		if ( $this->is_current() ) {
			it_exchange_update_cart_data( 'shipping-method', $method );
		}

		if ( $method === 'multiple-methods' ) {

			$this->set_meta( '_multiple-shipping-methods', true );

			if ( $this->is_current() ) {
				it_exchange_remove_cart_data( 'multiple-shipping-methods' );
			}

			/** @var ITE_Cart_Product $product */
			foreach ( $this->get_items( 'product' ) as $product ) {
				if ( $product->get_product()->has_feature( 'shipping' ) ) {
					$this->get_shipping_method( $product ); // Set defaults
				}
			}

			it_exchange_log( 'Cart {cart_id} shipping method updated to {method}.', ITE_Log_Levels::INFO, array(
				'cart_id' => $this->get_id(),
				'method'  => $method,
				'_group'  => 'cart',
			) );

			return true;
		}

		$args = it_exchange_get_registered_shipping_method_args( $method );

		if ( empty( $args['provider'] ) ) {
			return false;
		}

		$provider = it_exchange_get_registered_shipping_provider( $args['provider'] );
		$method   = it_exchange_get_registered_shipping_method( $method );

		if ( ! $method ) {
			return false;
		}

		$this->remove_meta( '_multiple-shipping-methods' );

		$global = $this->add_item( ITE_Base_Shipping_Line_Item::create( $method, $provider, true ) );

		/** @var ITE_Cart_Product $item */
		foreach ( $this->get_items( 'product' ) as $item ) {
			if ( $item->get_product()->has_feature( 'shipping' ) ) {
				$per_item = ITE_Base_Shipping_Line_Item::create( $method, $provider );
				$per_item->set_cart_repository( $this->get_repository() );
				$per_item->set_scoped_from( $global );
				$item->add_item( $per_item );
				$this->save_item( $item );
			}
		}

		it_exchange_log( 'Cart {cart_id} shipping method updated to {method}.', ITE_Log_Levels::INFO, array(
			'cart_id' => $this->get_id(),
			'method'  => $method,
			'_group'  => 'cart',
		) );

		return true;
	}

	/**
	 * Get the shipping method for the cart.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $for
	 * @param bool           $find_default Find and set the default shipping method if none is selected.
	 *
	 * @return \IT_Exchange_Shipping_Method|null|\stdClass
	 */
	public function get_shipping_method( \ITE_Line_Item $for = null, $find_default = true ) {

		if ( $for ) {
			if ( $for instanceof ITE_Cart_Product ) {
				$slug = it_exchange_get_multiple_shipping_method_for_cart_product( $for, $this );

				$method = it_exchange_get_registered_shipping_method( $slug );
			} else {
				$method = it_exchange_get_shipping_method_for_item( $for );
			}
		} else {

			$items   = $this->get_items( 'shipping', true );
			$uniqued = $items->unique( function ( ITE_Shipping_Line_Item $item ) {
				return $item->get_method()->slug;
			} );

			if ( $uniqued->count() > 1 || $this->has_meta( '_multiple-shipping-methods' ) ) {
				$method        = new stdClass();
				$method->slug  = 'multiple-methods';
				$method->label = __( 'Multiple Shipping Methods', 'it-l10n-ithemes-exchange' );
			} elseif ( $uniqued->count() === 1 ) {
				$method = $uniqued->first()->get_method();
			} else {
				$method = null;
			}
		}

		if ( $method || ! $find_default ) {
			return $method ?: null;
		}

		if ( ! $this->requires_shipping() ) {
			return null;
		}

		return $this->find_and_set_forced_shipping_method( $for );
	}

	/**
	 * Set the forced shipping method for either the whole cart or a given item.
	 *
	 *
	 *
	 * @param ITE_Line_Item|null $for
	 *
	 * @return |IT_Exchange_Shipping_Method|null
	 */
	protected function find_and_set_forced_shipping_method( ITE_Line_Item $for = null ) {

		if ( $for instanceof ITE_Cart_Product ) {
			$enabled = it_exchange_get_enabled_shipping_methods_for_product( $for->get_product(), 'slug', $this );

			if ( is_array( $enabled ) && count( $enabled ) === 1 ) {
				$slug = reset( $enabled );
				$this->set_shipping_method( $slug, $for );

				return it_exchange_get_registered_shipping_method( $slug );
			}

			return null;
		}

		$method = null;

		// If there is only one possible shipping method for the cart, set it and return it.
		$cart_methods    = it_exchange_get_available_shipping_methods_for_cart( true, $this );
		$product_methods = it_exchange_get_available_shipping_methods_for_cart_products( $this );

		$cart_methods_count         = count( $cart_methods );
		$cart_product_methods_count = count( $product_methods );

		if ( $cart_product_methods_count === 1 && $cart_methods_count ) {
			$method = reset( $cart_methods );
			$this->set_shipping_method( $method->slug );
		} elseif ( $cart_methods_count === 0 ) {

			$this->set_shipping_method( 'multiple-methods' );

			/** @var ITE_Cart_Product $product */
			foreach ( $this->get_items( 'product' ) as $product ) {
				$this->find_and_set_forced_shipping_method( $product );
			}

			$method        = new stdClass();
			$method->slug  = 'multiple-methods';
			$method->label = __( 'Multiple Shipping Methods', 'it-l10n-ithemes-exchange' );
		}

		return $method ?: null;
	}

	/**
	 * Get all meta stored on the cart.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_all_meta() {
		return $this->get_repository()->get_all_meta();
	}

	/**
	 * Determine if the cart has a given meta key.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function has_meta( $key ) {
		return $this->get_repository()->has_meta( $key );
	}

	/**
	 * Retrieve metadata from the cart.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return mixed
	 *
	 * @throws OutOfBoundsException
	 */
	public function get_meta( $key ) {
		return $this->get_repository()->get_meta( $key );
	}

	/**
	 * Set a meta value for the cart.
	 *
	 *
	 *
	 * @param string $key   Meta key.
	 * @param mixed  $value Values should be unslashed.
	 *
	 * @return bool
	 */
	public function set_meta( $key, $value ) {

		$previous = $this->has_meta( $key ) ? $this->get_meta( $key ) : null;

		if ( $previous === $value ) {
			return true;
		}

		if ( $this->get_repository()->set_meta( $key, $value ) ) {

			/**
			 * Fires when cart meta is set.
			 *
			 *
			 *
			 * @param string    $key
			 * @param mixed     $value
			 * @param \ITE_Cart $this
			 * @param mixed     $previous
			 */
			do_action( 'ninja_shop_set_cart_meta', $key, $value, $this, $previous );

			return true;
		}

		return false;
	}

	/**
	 * Remove metadata from the cart.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function remove_meta( $key ) {
		if ( $this->get_repository()->remove_meta( $key ) ) {

			/**
			 * Fires when cart meta is removed.
			 *
			 *
			 *
			 * @param string    $key
			 * @param \ITE_Cart $this
			 */
			do_action( 'ninja_shop_remove_cart_meta', $key, $this );

			return true;
		}

		return false;
	}

	/**
	 * Prepare the cart for purchase.
	 *
	 *
	 */
	public function prepare_for_purchase() {

		/**
		 * Fires when the cart totals should be finalized.
		 *
		 *
		 *
		 * @param \ITE_Cart $cart
		 */
		do_action( 'ninja_shop_finalize_cart_totals', $this );
	}

	/**
	 * Is the cart ready to be purchased.
	 *
	 *
	 *
	 * @return ITE_Cart_Feedback Returns a new cart feedback object with error messages.
	 */
	public function get_requirements_for_purchase() {

		$feedback = new ITE_Cart_Feedback();

		$this->validate( $feedback );

		if ( $this->requires_shipping() ) {
			$s = $this->get_shipping_address();

			if ( ! $s || empty( $s['address1'] ) ) {
				$feedback->add_error( __( 'Please enter a shipping address.', 'it-l10n-ithemes-exchange' ) );

				return $feedback;
			}

			$method = $this->get_shipping_method();

			if ( ! $method ) {
				$feedback->add_error( __( 'Please select a shipping method.', 'it-l10n-ithemes-exchange' ) );

				return $feedback;
			}

			if ( $method->slug === 'multiple-methods' ) {
				$required_shipping = $this->get_items( 'product' )->filter( function ( ITE_Cart_Product $product ) {
					return $product->get_product()->has_feature( 'shipping' );
				} );

				$missing = false;

				/** @var ITE_Cart_Product $product */
				foreach ( $required_shipping as $product ) {
					if ( ! $this->get_shipping_method( $product ) ) {
						$missing = true;
						$feedback->add_error(
							sprintf(
								__( 'Please select a shipping method for %s.', 'it-l10n-ithemes-exchange' ), $product->get_name()
							),
							$product
						);
					}
				}

				if ( $missing ) {
					return $feedback;
				}
			}
		}

		return $feedback;
	}

	/**
	 * Get a description of the items in this cart.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	 */
	public function get_description( array $options = array() ) {

		$items = $this->get_items()->non_summary_only();

		if ( ! $items->count() ) {
			return '';
		}

		$parts = it_exchange_get_line_item_collection_description( $items, $this, false );

		return apply_filters( 'ninja_shop_get_cart_description', implode( ', ', $parts ), $parts, $options );
	}

	/**
	 * Empty the cart.
	 *
	 * This will remove all items, not just products. The cart will also be destroyed.
	 *
	 *
	 */
	public function empty_cart() {

		/**
		 * Fires when the cart is about to be emptied.
		 *
		 *
		 *
		 * @param \ITE_Cart $cart
		 */
		do_action( 'ninja_shop_empty_cart', $this );

		$items = $this->get_items();
		$this->remove_all();

		/**
		 * Fires when the cart was just emptied.
		 *
		 *
		 *
		 * @param \ITE_Cart                 $cart
		 * @param \ITE_Line_Item_Collection $items Items removed from the cart.
		 */
		do_action( 'ninja_shop_emptied_cart', $this, $items );

		$this->destroy();
	}

	/**
	 * Merge another cart into this cart.
	 *
	 *
	 *
	 * @param \ITE_Cart $cart
	 * @param bool      $coerce
	 */
	public function merge( ITE_Cart $cart, $coerce = true ) {

		$this->doing_merge = true;
		$cart->doing_merge = true;

		/**
		 * Fires before a cart has been merged into another cart.
		 *
		 *
		 *
		 * @param \ITE_Cart $this The primary cart.
		 * @param \ITE_Cart $cart The cart being merged.
		 * @param bool      $coerce
		 */
		do_action( 'ninja_shop_merge_cart', $this, $cart, $coerce );

		foreach ( $cart->get_items() as $item ) {
			$this->add_item( $item, false );
		}

		$cart->remove_all();

		$this->set_billing_address( $cart->get_billing_address() );
		$this->set_shipping_address( $cart->get_shipping_address() );

		if ( $coerce ) {
			$this->coerce();
		}

		/**
		 * Fires after a cart has been merged into another cart.
		 *
		 *
		 *
		 * @param \ITE_Cart $this The primary cart.
		 * @param \ITE_Cart $cart The cart being merged.
		 * @param bool      $coerce
		 */
		do_action( 'ninja_shop_merged_cart', $this, $cart, $coerce );

		$this->doing_merge = false;
		$cart->doing_merge = false;

		it_exchange_log( 'Cart {cart_id} merged with {other_cart_id}.', ITE_Log_Levels::INFO, array(
			'cart_id'       => $this->get_id(),
			'other_cart_id' => $cart->get_id(),
			'_group'        => 'cart',
		) );
	}

	/**
	 * Destroy the cart.
	 *
	 *
	 */
	public function destroy() {
		$this->repository->destroy( $this->cart_id );
		$this->cart_id = null;
	}

	/**
	 * Mark a cart as having been purchased.
	 *
	 * This is used to prevent a cart from being deleted before an IPN has had time to process.
	 *
	 * This only effects Session backed carts. Carts marked as purchased will be deleted every 7 days.
	 *
	 *
	 *
	 * @param bool $purchased
	 *
	 * @return bool
	 */
	public function mark_as_purchased( $purchased = true ) {

		$repo = $this->get_repository();
		$this->set_meta( 'frozen_total', $this->get_total() );

		if ( $repo instanceof ITE_Cart_Cached_Session_Repository ) {
			return $repo->get_model()->mark_purchased( $purchased );
		}

		if ( $repo instanceof ITE_Cart_Session_Repository ) {
			$model = ITE_Session_Model::from_cart_id( $this->get_id() );

			return $model && $model->mark_purchased( $purchased );
		}

		return true;
	}

	/**
	 * Clone this cart, saving its contents to a new repository.
	 *
	 *
	 *
	 * @param \ITE_Cart_Repository $repository
	 * @param bool                 $new_ids
	 *
	 * @return \ITE_Cart
	 */
	public function with_new_repository( ITE_Cart_Repository $repository, $new_ids = false ) {

		if ( $new_ids ) {
			foreach ( $this->get_items() as $item ) {
				$repository->save_item( $item->clone_with_new_id() );
			}
		} else {
			$repository->save_many_items( $this->get_items()->to_array() );
		}

		$repository->set_billing_address( $this->get_billing_address() );
		$repository->set_shipping_address( $this->get_shipping_address() );

		foreach ( $this->get_all_meta() as $key => $value ) {
			if ( $key[0] !== '_' ) {
				$repository->set_meta( $key, $value );
			}
		}

		$clone             = clone $this;
		$clone->repository = $repository;

		if ( $new_ids ) {
			it_exchange_log( 'Cart cloned with new repository {repository} with new ids', ITE_Log_Levels::DEBUG, array(
				'repository' => get_class( $repository ),
				'_group'     => 'cart',
			) );
		} else {
			it_exchange_log( 'Cart cloned with new repository {repository}', ITE_Log_Levels::DEBUG, array(
				'repository' => get_class( $repository ),
				'_group'     => 'cart',
			) );
		}

		return $clone;
	}

	/**
	 * Generate an authentication secret.
	 *
	 *
	 *
	 * @param int $life The key lifetime.
	 *
	 * @return string
	 *
	 * @throws \UnexpectedValueException
	 */
	public final function generate_auth_secret( $life = 300 ) {

		$e = null;

		try {
			$secret = \Firebase\JWT\JWT::encode( array(
				'exp'     => time() + $life,
				'cart_id' => $this->get_id()
			), wp_salt() );
		} catch ( Exception $e ) {

		}

		if ( empty( $secret ) ) {
			it_exchange_log( 'Unable to generate cart hash for {cart_id} {exception}', ITE_Log_Levels::ALERT, array(
				'cart_id'   => $this->get_id(),
				'exception' => $e,
				'_group'    => 'cart',
			) );
			throw new UnexpectedValueException( "Unable to generate cart hash for {$this->get_id()}." );
		}

		return $secret;
	}

	/**
	 * Validate an authentication secret.
	 *
	 *
	 *
	 * @param string $secret
	 *
	 * @return bool
	 */
	public final function validate_auth_secret( $secret ) {

		try {
			$decoded = \Firebase\JWT\JWT::decode( $secret, wp_salt(), array( 'HS256' ) );
		} catch ( Exception $e ) {
			return false;
		}

		return hash_equals( $this->get_id(), $decoded->cart_id );
	}

	/**
	 * Get cart feedback.
	 *
	 *
	 *
	 * @return \ITE_Cart_Feedback
	 */
	public function get_feedback() {
		return $this->feedback;
	}

	/**
	 * Add a cart wide validator.
	 *
	 *
	 *
	 * @param \ITE_Cart_Validator $validator
	 *
	 * @return $this
	 */
	public function add_cart_validator( ITE_Cart_Validator $validator ) {
		$this->cart_validators[ $validator->get_name() ] = $validator;

		return $this;
	}

	/**
	 * Remove a cart wide validator.
	 *
	 *
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function remove_cart_validator( $name ) {
		unset( $this->cart_validators[ $name ] );

		return $this;
	}

	/**
	 * Add a line item validator.
	 *
	 *
	 *
	 * @param \ITE_Line_Item_Validator $validator
	 *
	 * @return $this
	 */
	public function add_item_validator( ITE_Line_Item_Validator $validator ) {
		$this->item_validators[ $validator->get_name() ] = $validator;

		return $this;
	}

	/**
	 * Remove a line item validator.
	 *
	 *
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function remove_item_validator( $name ) {
		unset( $this->item_validators[ $name ] );

		return $this;
	}

	/**
	 * Add a location validator.
	 *
	 *
	 *
	 * @param \ITE_Location_Validator $validator
	 *
	 * @return $this
	 */
	public function add_location_validator( ITE_Location_Validator $validator ) {
		$this->location_validators[ $validator->get_name() ] = $validator;

		return $this;
	}

	/**
	 * Remove a location validator.
	 *
	 *
	 *
	 * @param string $name
	 *
	 * @return $this
	 */
	public function remove_location_validator( $name ) {
		unset( $this->location_validators[ $name ] );

		return $this;
	}

	/**
	 * Get the repository being used for persistence.
	 *
	 *
	 *
	 * @return \ITE_Cart_Repository
	 */
	public function get_repository() {
		return $this->repository;
	}

	/**
	 * Assert that the given type is valid.
	 *
	 *
	 *
	 * @param string $type
	 *
	 * @throws InvalidArgumentException
	 */
	protected static function assert_type( $type ) {
		if ( ! is_string( $type ) || trim( $type ) === '' ) {
			throw new InvalidArgumentException( '$type must be non-zero length string.' );
		}
	}

	/**
	 * Get all available validators.
	 *
	 *
	 *
	 * @return (\ITE_Line_Item_Validator|\ITE_Cart_Validator|\ITE_Location_Validator)[]
	 */
	private static function validators() {
		$validators = array(
			new ITE_Guest_Customer_Purchase_Validator(),
			new ITE_Multi_Item_Cart_Validator(),
			new ITE_Multi_Item_Product_Validator(),
			new ITE_Product_Inventory_Validator(),
			new ITE_Product_Availability_Validator(),
			new ITE_Coupon_Item_Validator(),
		);

		/**
		 * Filter the available validators.
		 *
		 *
		 *
		 * @param array $validators
		 */
		return apply_filters( 'ninja_shop_cart_validators', $validators );
	}
}
