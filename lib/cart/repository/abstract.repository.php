<?php
/**
 * Abstract Line Item Repository.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Repository
 */
abstract class ITE_Cart_Repository {

	/**
	 * Get an item from the repository.
	 *
	 *
	 *
	 * @param string $type
	 * @param string $id
	 *
	 * @return ITE_Line_Item|null
	 */
	abstract public function get_item( $type, $id );

	/**
	 * Get an item's aggregatables.
	 *
	 *
	 *
	 * @param ITE_Line_Item $item
	 *
	 * @return ITE_Aggregatable_Line_Item[]
	 */
	abstract public function get_item_aggregatables( ITE_Line_Item $item );

	/**
	 * Get all line items.
	 *
	 *
	 *
	 * @param string $type Optionally specify the type of line items to retrieve.
	 *
	 * @return ITE_Line_Item_Collection|ITE_Line_Item[]
	 */
	abstract public function all_items( $type = '' );

	/**
	 * Save an item.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return bool
	 */
	abstract public function save_item( ITE_Line_Item $item );

	/**
	 * Save multiple items.
	 *
	 *
	 *
	 * @param ITE_Line_Item[] $items
	 *
	 * @return bool
	 */
	abstract public function save_many_items( array $items );

	/**
	 * Delete a line item.
	 *
	 * If an aggregatable line item is passed, the repository should remove the line item from the aggregate,
	 * if one exists.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 *
	 * @return bool
	 */
	abstract public function delete_item( ITE_Line_Item $item );

	/**
	 * Get all meta stored on the cart.
	 *
	 *
	 *
	 * @return array
	 */
	abstract public function get_all_meta();

	/**
	 * Determine if the cart has a given meta key.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	abstract public function has_meta( $key );

	/**
	 * Retrieve metadata from the cart.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	abstract public function get_meta( $key );

	/**
	 * Set a meta value for the cart.
	 *
	 *
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	abstract public function set_meta( $key, $value );

	/**
	 * Remove metadata from the cart.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	abstract public function remove_meta( $key );

	/**
	 * Get the customer's shipping address.
	 *
	 *
	 *
	 * @return \ITE_Location|null
	 */
	abstract public function get_shipping_address();

	/**
	 * Get the customer's billing address.
	 *
	 *
	 *
	 * @return \ITE_Location|null
	 */
	abstract public function get_billing_address();

	/**
	 * Save the billing address for this purchase.
	 *
	 *
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	abstract public function set_billing_address( ITE_Location $location = null );

	/**
	 * Save the shipping address for this purchase.
	 *
	 *
	 *
	 * @param \ITE_Location|null $location
	 *
	 * @return bool
	 */
	abstract public function set_shipping_address( ITE_Location $location = null );

	/**
	 * Set the repository for a line item if necessary.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 */
	protected final function set_repository( ITE_Line_Item $item ) {
		if ( $item instanceof ITE_Cart_Repository_Aware ) {
			$item->set_cart_repository( $this );
		}

		if ( $item instanceof ITE_Aggregate_Line_Item ) {
			foreach ( $item->get_line_items() as $child ) {
				$this->set_repository( $child );
			}
		}
	}

	/**
	 * Get the date that this cart expires at.
	 *
	 *
	 *
	 * @return \DateTime|null
	 */
	public function expires_at() { return null; }

	/**
	 * Destroy the repository.
	 *
	 *
	 *
	 * @param string $cart_id
	 *
	 * @return bool
	 */
	public function destroy( $cart_id ) { return true; }
}
