<?php
/**
 * Session Repository class.
 *
 *
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Session_Repository
 */
class ITE_Cart_Session_Repository extends ITE_Cart_Repository {

	/** @var IT_Exchange_SessionInterface */
	protected $session;

	/** @var ITE_Line_Item_Repository_Events */
	private $events;

	/**
	 * ITE_Line_Item_Session_Repository constructor.
	 *
	 * @param \IT_Exchange_SessionInterface    $session
	 * @param \ITE_Line_Item_Repository_Events $events
	 */
	public function __construct( IT_Exchange_SessionInterface $session, ITE_Line_Item_Repository_Events $events ) {
		$this->session = $session;
		$this->events  = $events;
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $type, $id ) {
		$data = $this->session->get_session_data( self::normalize_type( $type ) );

		if ( ! isset( $data[ $id ] ) ) {
			return null;
		}

		return $this->construct_item( $id, $data[ $id ] );
	}

	/**
	 * @inheritDoc
	 */
	public function get_item_aggregatables( ITE_Line_Item $item ) {

		$data = $this->session->get_session_data( self::normalize_type( $item->get_type() ) );

		if ( ! isset( $data[ $item->get_id() ] ) ) {
			return array();
		}

		if ( empty( $data[ $item->get_id() ]['_aggregate'] ) ) {
			return array();
		}

		$aggregatables = array();

		foreach ( $data[ $item->get_id() ]['_aggregate'] as $aggregatable_data ) {
			$aggregatable_type_data = $this->session->get_session_data( self::normalize_type( $aggregatable_data['type'] ) );

			$id = $aggregatable_data['id'];

			if ( empty( $aggregatable_type_data[ $id ] ) ) {
				continue;
			}

			$aggregatable = $this->construct_item( $id, $aggregatable_type_data[ $id ], $item );

			if ( $aggregatable instanceof ITE_Aggregatable_Line_Item ) {
				$aggregatables[] = $aggregatable;
			}
		}

		return $aggregatables;
	}

	/**
	 * @inheritDoc
	 */
	public function all_items( $type = '' ) {

		if ( $type ) {
			$type = self::normalize_type( $type );

			$data = $this->session->get_session_data( $type );

			$items = array();

			foreach ( $data as $id => $item_data ) {
				if ( ! empty( $item_data['_parent'] ) ) {
					continue;
				}

				$item = $this->construct_item( $id, $item_data );

				if ( $item ) {
					$items[] = $item;
				}
			}

			return new ITE_Line_Item_Collection( $items, $this );
		}

		$items    = array();
		$all_data = $this->session->get_session_data();

		foreach ( $all_data as $type => $data ) {

			if ( ! is_array( $data ) ) {
				continue;
			}

			$first = reset( $data );

			if ( ! isset( $first['_class'] ) && $type !== 'products' ) {
				continue;
			}

			foreach ( $data as $id => $item_data ) {

				if ( ! empty( $item_data['_parent'] ) || empty( $item_data['_class'] ) ) {
					continue;
				}

				$item = $this->construct_item( $id, $item_data );

				if ( $item ) {
					$items[] = $item;
				}
			}
		}

		return new ITE_Line_Item_Collection( $items, $this );
	}

	/**
	 * @inheritDoc
	 */
	public function save_item( ITE_Line_Item $item ) {

		$old = $this->get_item( $item->get_type(), $item->get_id() );

		$type = self::normalize_type( $item->get_type() );
		$this->session->add_session_data( $type, array( $item->get_id() => $this->get_data( $item ) ) );

		$this->events->on_save( $item, $old, $this );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function save_many_items( array $items ) {

		$data = array();
		$olds = array();

		foreach ( $items as $item ) {
			$data[ $item->get_type() ][ $item->get_id() ] = $this->get_data( $item );
			$olds[ $item->get_type() ][ $item->get_id() ] = $this->get_item( $item->get_type(), $item->get_id() );
		}

		foreach ( $data as $type => $item_data ) {
			$this->session->add_session_data( self::normalize_type( $type ), $item_data );
		}

		foreach ( $items as $item ) {
			$this->events->on_save( $item, $olds[ $item->get_type() ][ $item->get_id() ], $this );
		}

		return true;
	}


	/**
	 * @inheritDoc
	 */
	public function delete_item( ITE_Line_Item $item ) {

		$this->do_delete_item( $item );

		return true;
	}

	/**
	 * Delete an item from the repository.
	 *
	 * Responsibilities:
	 *
	 * - Delete self
	 * - Delete any child items
	 * - remove self from parent item
	 *
	 * @param ITE_Line_Item $item
	 * @param bool          $remove_from_parent Whether to remove this item from the parent's aggregatables list.
	 *
	 * @return bool
	 */
	protected function do_delete_item( ITE_Line_Item $item, $remove_from_parent = true ) {

		if ( $remove_from_parent && $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
			$item->get_aggregate()->remove_item( $item->get_type(), $item->get_id() );
			$this->save_item( $item->get_aggregate() );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->do_delete_item( $aggregatable, false );
			}
		}

		$type = self::normalize_type( $item->get_type() );

		$items = $this->session->get_session_data( $type );
		unset( $items[ $item->get_id() ] );
		$this->session->update_session_data( $type, $items );

		$this->events->on_delete( $item, $this );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_all_meta() {
		$params = $this->session->get_session_data( 'cart_meta' );

		return ! is_array( $params ) ? array() : $params;
	}

	/**
	 * @inheritDoc
	 */
	public function has_meta( $key ) {

		$params = $this->get_all_meta();

		return array_key_exists( $key, $params );
	}

	/**
	 * @inheritDoc
	 */
	public function get_meta( $key ) {

		if ( ! $this->has_meta( $key ) ) {
			throw new OutOfBoundsException( "Parameter {$key} does not exist." );
		}

		$params = $this->get_all_meta();

		return $params[ $key ];
	}

	/**
	 * @inheritDoc
	 */
	public function set_meta( $key, $value ) {

		$params = $this->get_all_meta();

		$params[ $key ] = $value;

		$this->session->update_session_data( 'cart_meta', $params );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_meta( $key ) {

		$params = $this->get_all_meta();

		unset( $params[ $key ] );

		$this->session->update_session_data( 'cart_meta', $params );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_shipping_address() {

		$address = $this->get_shipping_address_data_for_customer( it_exchange_get_current_customer() );

		// If shipping error and form was submitted, use POST values as most recent
		if ( ! empty( $_REQUEST['ninja-shop-update-shipping-address'] ) && ! empty( $GLOBALS['it_exchange']['shipping-address-error'] ) ) {

			$keys      = array_keys( $address );
			$post_data = array();

			foreach ( $keys as $key ) {
				if ( ! empty( $_REQUEST[ 'it-exchange-shipping-address-' . $key ] ) ) {
					$post_data[ $key ] = sanitize_text_field( $_REQUEST[ 'it-exchange-shipping-address-' . $key ] );
				} else {
					$post_data[ $key ] = '';
				}
			}

			$address = ITUtility::merge_defaults( $post_data, $address );
		}

		$saved = empty( $address['id'] ) ? null : ITE_Saved_Address::get( $address['id'] );
		unset( $address['id'] );

		$filtered = apply_filters( 'ninja_shop_get_cart_shipping_address', $address );

		if ( $saved ) {
			$filtered = new ITE_In_Memory_Address( $filtered );

			if ( $filtered->equals( $saved ) ) {
				return $saved;
			} else {
				return $filtered;
			}
		}

		return $filtered && is_array( $filtered ) ? new ITE_In_Memory_Address( $filtered ) : null;
	}

	/**
	 * @inheritDoc
	 */
	public function get_billing_address() {

		$address = $this->get_billing_address_data_for_customer( it_exchange_get_current_customer() );

		// If billing error and form was submitted, use POST values as most recent
		if ( ! empty( $_REQUEST['ninja-shop-update-billing-address'] ) && ! empty( $GLOBALS['it_exchange']['billing-address-error'] ) ) {

			$keys      = array_keys( $address );
			$post_data = array();

			foreach ( $keys as $key ) {
				if ( ! empty( $_REQUEST[ 'ninja-shop-billing-address-' . $key ] ) ) {
					$post_data[ $key ] = sanitize_text_field( $_REQUEST[ 'ninja-shop-billing-address-' . $key ] );
				} else {
					$post_data[ $key ] = '';
				}
			}

			$address = ITUtility::merge_defaults( $post_data, $address );
		}

		$saved = empty( $address['id'] ) ? null : ITE_Saved_Address::get( $address['id'] );
		unset( $address['id'] );

		$filtered = apply_filters( 'ninja_shop_get_cart_billing_address', $address );

		if ( $saved ) {
			$filtered = new ITE_In_Memory_Address( $filtered );

			if ( $filtered->equals( $saved ) ) {
				return $saved;
			} else {
				return $filtered;
			}
		}

		return $filtered && is_array( $filtered ) ? new ITE_In_Memory_Address( $filtered ) : null;
	}

	/**
	 * Get the billing address for a customer.
	 *
	 *
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return array
	 */
	protected function get_billing_address_data_for_customer( $customer ) {

		$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

		// Default values for first time use.
		$defaults = array(
			'first-name'   => isset( $customer_data->first_name ) ? $customer_data->first_name : '',
			'last-name'    => isset( $customer_data->last_name ) ? $customer_data->last_name : '',
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => isset( $customer_data->user_email ) ? $customer_data->user_email : '',
			'phone'        => '',
		);

		// If data exists in the session, use that as the most recent
		$session_data = $this->session->get_session_data( 'billing-address' );

		if ( ! empty( $session_data ) ) {
			return ITUtility::merge_defaults( $session_data, $defaults );
		}

		// See if the customer has a billing address saved. If so, overwrite defaults with saved billing address
		if ( $customer && empty( $defaults['address1'] ) && ( $address = $customer->get_billing_address() ) ) {
			return array_merge(
				$defaults,
				$address->to_array(),
				$address instanceof ITE_Saved_Address ? array( 'id' => $address->get_pk() ) : array()
			);
		}

		return $defaults;
	}

	/**
	 * Get the shipping address for a customer.
	 *
	 *
	 *
	 * @param \IT_Exchange_Customer $customer
	 *
	 * @return array
	 */
	protected function get_shipping_address_data_for_customer( $customer ) {

		$customer_data = empty( $customer->data ) ? new stdClass() : $customer->data;

		// Default values for first time use.
		$defaults = array(
			'first-name'   => isset( $customer_data->first_name ) ? $customer_data->first_name : '',
			'last-name'    => isset( $customer_data->last_name ) ? $customer_data->last_name : '',
			'company-name' => '',
			'address1'     => '',
			'address2'     => '',
			'city'         => '',
			'state'        => '',
			'zip'          => '',
			'country'      => '',
			'email'        => isset( $customer_data->user_email ) ? $customer_data->user_email : '',
			'phone'        => '',
		);

		// If data exists in the session, use that as the most recent
		$session_data = $this->session->get_session_data( 'shipping-address' );

		if ( ! empty( $session_data ) ) {
			return ITUtility::merge_defaults( $session_data, $defaults );
		}

		// See if the customer has a billing address saved. If so, overwrite defaults with saved billing address
		if ( $customer && ( $address = $customer->get_shipping_address() ) ) {
			return array_merge(
				$defaults,
				$address->to_array(),
				$address instanceof ITE_Saved_Address ? array( 'id' => $address->get_pk() ) : array()
			);
		}

		return $defaults;
	}

	/**
	 * @inheritDoc
	 */
	public function set_billing_address( ITE_Location $location = null ) {

		$data = $location ? $location->to_array() : array();

		if ( $location instanceof ITE_Saved_Address ) {
			$data['id'] = $location->get_pk();
		}

		$this->session->update_session_data( 'billing-address', $data );

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function set_shipping_address( ITE_Location $location = null ) {

		$data = $location ? $location->to_array() : array();

		if ( $location instanceof ITE_Saved_Address ) {
			$data['id'] = $location->get_pk();
		}

		$this->session->update_session_data( 'shipping-address', $data );

		return true;
	}

	/**
	 * Get the data that will be committed.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return array
	 */
	protected final function get_data( ITE_Line_Item $item ) {

		$additional = array(
			'_class' => get_class( $item )
		);

		if ( $item instanceof ITE_Aggregatable_Line_Item && $item->get_aggregate() ) {
			$additional['_parent'] = array(
				'type' => $item->get_aggregate()->get_type(),
				'id'   => $item->get_aggregate()->get_id(),
			);
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $aggregatable ) {
				$this->save_item( $aggregatable );
				$additional['_aggregate'][] = array(
					'type' => $aggregatable->get_type(),
					'id'   => $aggregatable->get_id(),
				);
			}
		}

		if ( $item instanceof ITE_Scopable_Line_Item && $item->is_scoped() ) {
			$this->save_item( $item->scoped_from() );
			$additional['_scoped_from'] = $item->scoped_from()->get_id();
		}

		$data = $item instanceof ITE_Cart_Product ? $item->bc() : array(
			'_params' => $item->get_params(),
		);

		$data['_frozen'] = $item->frozen()->get_params();

		return array_merge( $additional, $data );
	}

	/**
	 * Construct an item.
	 *
	 *
	 *
	 * @param string|int               $id
	 * @param array                    $data
	 * @param \ITE_Aggregate_Line_Item $aggregate Provide the aggregate instance to prevent an infinite loop
	 *                                            where the aggregate constructs its aggregatables, and the
	 *                                            aggregatables construct the aggregate.
	 *
	 * @return \ITE_Line_Item|null
	 */
	protected final function construct_item( $id, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {

		if ( ! isset( $data['_class'] ) ) {
			return null;
		}

		$class = $data['_class'];
		$_data = $data;
		unset( $data['_class'], $data['_parent'], $data['_aggregate'], $data['_scoped_from'] );

		if ( ! class_exists( $class ) ) {
			return null;
		}

		if ( $class === 'ITE_Cart_Product' ) {
			$data = $this->back_compat_cart_product( $data );
		}

		$params = isset( $data['_params'] ) && is_array( $data['_params'] ) ? $data['_params'] : array();
		$frozen = isset( $data['_frozen'] ) && is_array( $data['_frozen'] ) ? $data['_frozen'] : array();

		if ( $class === 'ITE_Cart_Product' ) {
			$params = $this->back_compat_filter_cart_product( $params );
		}

		$item = new $class( $id, new ITE_Array_Parameter_Bag( $params ), new ITE_Array_Parameter_Bag( $frozen ) );

		if ( ! $item ) {
			return null;
		}

		$this->set_additional_properties( $item, $_data, $aggregate );

		return $this->events->on_get( $item, $this );
	}

	/**
	 * Back-compat filter the cart product.
	 *
	 *
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function back_compat_filter_cart_product( $data ) {
		return apply_filters_deprecated( 'it_exchange_get_cart_product', array(
			$data,
			$data['product_cart_id'],
			array()
		), '2.0.0' );
	}

	/**
	 * Back-compat for cart products.
	 *
	 *
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	private function back_compat_cart_product( $data ) {

		if ( isset( $data['itemized_data'] ) && is_serialized( $data['itemized_data'] ) ) {
			$data['itemized_data'] = unserialize( $data['itemized_data'] );
		}

		if ( isset( $data['additional_data'] ) && is_serialized( $data['additional_data'] ) ) {
			$data['additional_data'] = unserialize( $data['additional_data'] );
		}

		$data = array( '_params' => $data, '_frozen' => isset( $data['_frozen'] ) ? $data['_frozen'] : array() );

		unset( $data['_params']['_frozen'] );

		return $data;
	}

	/**
	 * Set the additional properties on the newly constructed item.
	 *
	 *
	 *
	 * @param \ITE_Line_Item           $item
	 * @param array                    $data
	 * @param \ITE_Aggregate_Line_Item $aggregate Provide the aggregate instance to prevent an infinite loop
	 *                                            where the aggregate constructs its aggregatables, and the
	 *                                            aggregatables construct the aggregate.
	 */
	protected final function set_additional_properties( ITE_Line_Item $item, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {
		$this->set_repository( $item );
		$this->set_aggregate( $item, $data, $aggregate );
		$this->set_scoped_from( $item, $data );
	}

	/**
	 * Set the aggregate on a line item if necessary.
	 *
	 *
	 *
	 * @param \ITE_Line_Item                $item
	 * @param array                         $data
	 * @param \ITE_Aggregate_Line_Item|null $aggregate
	 */
	protected final function set_aggregate( ITE_Line_Item $item, array $data, ITE_Aggregate_Line_Item $aggregate = null ) {

		if ( $item instanceof ITE_Aggregatable_Line_Item && ! empty( $data['_parent'] ) ) {

			if ( ! $aggregate && ! empty( $data['_parent']['type'] ) && ! empty( $data['_parent']['id'] ) ) {
				$aggregate = $this->get_item( $data['_parent']['type'], $data['_parent']['type'] );
			}

			if ( $aggregate instanceof ITE_Aggregate_Line_Item ) {
				$item->set_aggregate( $aggregate );
			}
		}
	}

	/**
	 * Set the scoped from line item for this item.
	 *
	 *
	 *
	 * @param ITE_Line_Item $item
	 * @param array         $data
	 */
	protected final function set_scoped_from( ITE_Line_Item $item, array $data ) {
		if ( $item instanceof ITE_Scopable_Line_Item && ! empty( $data['_scoped_from'] ) ) {
			$scoped_from = $this->get_item( $item->get_type(), $data['_scoped_from'] );

			if ( $scoped_from instanceof ITE_Scopable_Line_Item ) {
				$item->set_scoped_from( $scoped_from );
			}
		}
	}

	/**
	 * Normalize the type.
	 *
	 *
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	protected static function normalize_type( $type ) {
		switch ( $type ) {
			case 'product':
				$type = 'products'; // back-compat
				break;
		}

		return $type;
	}

	/**
	 * Check if this repository is backed by the active session.
	 *
	 *
	 *
	 * @return bool
	 */
	public function backed_by_active_session() {
		return $this->session === it_exchange_get_session();
	}

	/**
	 * @inheritDoc
	 */
	public function expires_at() {

		if ( ! $this->backed_by_active_session() ) {
			return parent::expires_at();
		}

		return IT_Exchange_DB_Sessions::get_instance()->expires_at();
	}

	/**
	 * @inheritDoc
	 */
	public function destroy( $cart_id ) {

		it_exchange_clear_session_data( 'cart_meta' );
		it_exchange_remove_cart_id();

		return true;
	}
}
