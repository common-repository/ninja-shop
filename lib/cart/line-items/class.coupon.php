<?php
/**
 * Contains the coupon line item class.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Coupon_Line_Item
 */
class ITE_Coupon_Line_Item extends ITE_Line_Item implements ITE_Aggregatable_Line_Item, ITE_Scopable_Line_Item,
	ITE_Taxable_Line_Item, ITE_Cart_Aware {

	/** @var IT_Exchange_Coupon|null|false */
	private $coupon;

	/** @var ITE_Aggregate_Line_Item|ITE_Cart_Product */
	private $aggregate;

	/** @var ITE_Line_Item_Collection */
	private $aggregatables;

	/** @var ITE_Cart_Repository */
	private $repository;

	/** @var ITE_Cart */
	private $cart;

	/** @var ITE_Coupon_Line_Item|null */
	private $scoped_from;

	/**
	 * Create a coupon line item.
	 *
	 *
	 *
	 * @param \IT_Exchange_Coupon    $coupon
	 * @param \ITE_Cart_Product|null $product
	 *
	 * @return \ITE_Coupon_Line_Item
	 * @throws \InvalidArgumentException
	 */
	public static function create( IT_Exchange_Coupon $coupon, ITE_Cart_Product $product = null ) {

		if ( ! $coupon->get_type() ) {
			throw new InvalidArgumentException(
				sprintf( 'Coupon of class %s needs to provide a valid get_type().', get_class( $coupon ) )
			);
		}

		$bag = new ITE_Array_Parameter_Bag( array(
			'id'   => $coupon->get_ID(),
			'type' => $coupon->get_type(),
		) );

		if ( $product ) {
			$id = md5( $coupon->get_code() . '-' . $product->get_id() );
		} else {
			$id = md5( $coupon->get_code() );
		}

		$self = new self( $id, $bag, new ITE_Array_Parameter_Bag() );

		if ( $product ) {
			$self->set_aggregate( $product );
		}

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public function clone_with_new_id( $include_frozen = true ) {
		return new self(
			$this->get_id(),
			$this->bag,
			$include_frozen ? $this->frozen : new ITE_Array_Parameter_Bag()
		);
	}

	/**
	 * Create a duplicate of this coupon, scoped for a given product.
	 *
	 *
	 *
	 * @param \ITE_Cart_Product $product
	 *
	 * @return \ITE_Coupon_Line_Item
	 */
	public function create_scoped_for_product( ITE_Cart_Product $product ) {
		$coupon = self::create( $this->get_coupon(), $product );

		if ( $this->repository ) {
			$coupon->set_cart_repository( $this->repository );
		}

		if ( $this->cart ) {
			$coupon->set_cart( $this->cart );
		}

		$coupon->set_scoped_from( $this );

		return $coupon;
	}

	/**
	 * @inheritDoc
	 */
	public function is_scoped() { return (bool) $this->scoped_from; }

	/**
	 * @inheritDoc
	 */
	public function scoped_from() {
		if ( $this->is_scoped() ) {
			return $this->scoped_from;
		}

		throw new UnexpectedValueException( 'Non scoped line item.' );
	}

	/**
	 * @inheritDoc
	 */
	public function set_scoped_from( ITE_Scopable_Line_Item $scoped_from ) {
		$this->scoped_from = $scoped_from;
	}

	/**
	 * @inheritDoc
	 */
	public function shared_params_in_scope() {

		// 'type' is a shared param, so to avoid recursion, grab shared params from scoped.
		if ( $this->is_scoped() ) {
			return $this->scoped_from()->shared_params_in_scope();
		}

		$coupon_class = it_exchange_get_coupon_type_class( $this->get_param( 'type' ) );

		return $coupon_class::supported_data_for_transaction_object();
	}

	/**
	 * Calculate the number of items this coupon applies to.
	 *
	 *
	 *
	 * @return int
	 */
	protected function calculate_num_items() {

		if ( $this->get_coupon()->get_application_method() === IT_Exchange_Coupon::APPLY_PRODUCT ) {
			return 1;
		}

		if ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_PERCENT ) {
			return 1;
		}

		$cart = $this->cart ? $this->cart : it_exchange_get_current_cart();
		$i    = 0;

		foreach ( $cart->get_items( 'product' ) as $product ) {
			if ( $this->get_coupon()->valid_for_product( $product ) ) {
				$i += $product->get_quantity();
			}
		}

		return $i;
	}

	/**
	 * @inheritDoc
	 * @throws \UnexpectedValueException
	 */
	public function get_line_items() {

		if ( ! $this->repository ) {
			throw new UnexpectedValueException( sprintf(
				'Repository service not available. See %s.', __CLASS__ . '::set_line_item_repository'
			) );
		}

		if ( $this->aggregatables ) {
			return $this->aggregatables;
		}

		$items = $this->repository->get_item_aggregatables( $this );

		return $this->aggregatables = new ITE_Line_Item_Collection( $items, $this->repository );
	}

	/**
	 * @inheritDoc
	 */
	public function add_item( ITE_Aggregatable_Line_Item $item ) {

		$item->set_aggregate( $this );

		$this->get_line_items()->add( $item );

		if ( $item instanceof ITE_Taxable_Line_Item ) {
			foreach ( $this->get_taxes() as $tax ) {
				if ( $tax->applies_to( $item ) ) {
					$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
				}
			}
		}

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function remove_item( $type, $id ) {
		return (bool) $this->get_line_items()->remove( $type, $id );
	}

	/**
	 * @inheritDoc
	 */
	public function is_tax_exempt( ITE_Tax_Provider $for ) {
		return $this->get_aggregate() ? $this->get_aggregate()->is_tax_exempt( $for ) : false;
	}

	/**
	 * @inheritDoc
	 */
	public function get_tax_code( ITE_Tax_Provider $for ) {

		$code = '';

		if ( $for->inherit_tax_code_from_aggregate() ) {

			$aggregate = $this->get_aggregate();

			if ( $aggregate instanceof ITE_Taxable_Line_Item ) {
				$code = $aggregate->get_tax_code( $for );
			}
		}

		return $code ?: $for->get_tax_code_for_item( $this );
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxable_amount() {
		return $this->get_amount();
	}

	/**
	 * @inheritDoc
	 */
	public function get_taxes() {
		return $this->get_line_items()->with_only_instances_of( 'ITE_Tax_Line_Item' );
	}

	/**
	 * @inheritDoc
	 */
	public function add_tax( ITE_Tax_Line_Item $tax ) {
		$this->add_item( $tax );

		foreach ( $this->get_line_items()->taxable() as $item ) {
			if ( $tax->applies_to( $item ) ) {
				$item->add_tax( $tax->create_scoped_for_taxable( $item ) );
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function remove_tax( $id ) {
		return $this->remove_item( 'tax', $id );
	}

	/**
	 * @inheritDoc
	 */
	public function remove_all_taxes() {
		foreach ( $this->get_taxes() as $tax ) {
			$this->remove_tax( $tax->get_id() );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_name() { return __( 'Savings', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_description() {
		if ( $this->frozen->has_param( 'description' ) ) {
			return $this->frozen->get_param( 'description' );
		} else {
			return $this->get_coupon()->get_code();
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_quantity() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_amount() {

		if ( $this->frozen->has_param( 'amount' ) ) {
			return $this->frozen->get_param( 'amount' );
		}

		if ( ! $this->get_aggregate() ) {
			return 0.00;
		}

		if ( $this->get_aggregate() && ! $this->get_coupon()->valid_for_product( $this->get_aggregate() ) ) {
			return 0.00;
		}

		$amount_number = $this->get_coupon()->get_amount_number();
		$num_items     = $this->calculate_num_items();

		if ( $num_items === 0 ) {
			return 0.00;
		}

		$amount = $amount_number / $num_items;

		if ( $this->get_aggregate() && $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_FLAT ) {
			$amount *= $this->get_aggregate()->get_quantity();
		}

		if ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_FLAT ) {
			return - $amount;
		} elseif ( $this->get_coupon()->get_amount_type() === IT_Exchange_Coupon::TYPE_PERCENT ) {
			$product = $this->get_aggregate();

			if ( ! $product ) {
				return 0.00;
			}

			$as_decimal         = $amount / 100;
			$amount_to_discount = $product->get_amount_to_discount() * $product->get_quantity();

			return - ( $as_decimal * $amount_to_discount );
		} else {
			return 0.00;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function freeze() {
		parent::freeze();

		foreach ( $this->get_coupon()->get_data_for_transaction_object() as $k => $v ) {
			$this->set_param( $k, $v );
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type( $label = false ) { return $label ? __( 'Coupon', 'it-l10n-ithemes-exchange' ) : 'coupon'; }

	/**
	 * @inheritDoc
	 */
	public function is_summary_only() {
		return $this->frozen->has_param( 'summary_only' ) ? $this->frozen->get_param( 'summary_only' ) : true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_id() { return $this->get_param( 'id' ); }

	/**
	 * @inheritDoc
	 */
	public function set_cart_repository( ITE_Cart_Repository $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Get the coupon.
	 *
	 *
	 *
	 * @return \IT_Exchange_Coupon
	 */
	public function get_coupon() {

		if ( $this->coupon === null ) {
			$this->coupon = it_exchange_get_coupon( $this->get_param( 'id' ), $this->get_param( 'type' ) );
		}

		return $this->coupon ?: null;
	}

	/**
	 * @inheritdoc
	 */
	public function set_cart( ITE_Cart $cart ) { $this->cart = $cart; }

	/**
	 * @inheritDoc
	 */
	public function set_aggregate( ITE_Aggregate_Line_Item $aggregate ) { $this->aggregate = $aggregate; }

	/**
	 * @inheritDoc
	 */
	public function get_aggregate() { return $this->aggregate; }

	/**
	 * @inheritDoc
	 */
	public function __destruct() {
		unset( $this->aggregate, $this->aggregatables, $this->repository, $this->cart );
	}

	/**
	 * @inheritDoc
	 */
	public function __clone() {
		parent::__clone();

		$this->aggregatables = null;

		if ( $this->coupon ) {
			$this->coupon = clone $this->coupon;
		}
	}
}
