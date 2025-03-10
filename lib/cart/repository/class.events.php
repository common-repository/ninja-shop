<?php
/**
 * Consolidates repository events and filters.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Line_Item_Repository_Events
 */
class ITE_Line_Item_Repository_Events {

	/**
	 * Fire actions when a line item is saved.
	 *
	 *
	 *
	 * @param \ITE_Line_Item       $item
	 * @param \ITE_Line_Item|null  $old
	 * @param \ITE_Cart_Repository $repository
	 */
	public function on_save( ITE_Line_Item $item, ITE_Line_Item $old = null, ITE_Cart_Repository $repository ) {
		/**
		 * Fires when a line item is saved.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Line_Item|null  $old
		 * @param \ITE_Cart_Repository $repository
		 */
		do_action( 'ninja_shop_save_line_item', $item, $old, $this );

		/**
		 * Fires when a line item is saved.
		 *
		 * The dynamic portion of this hook refers to the type of the line item being saved.
		 * Ex: 'product' or 'tax'.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Line_Item|null  $old
		 * @param \ITE_Cart_Repository $repository
		 */
		do_action( "ninja_shop_save_{$item->get_type()}_item", $item, $old, $repository );
	}

	/**
	 * Fire events when a line item is deleted.
	 *
	 *
	 *
	 * @param \ITE_Line_Item       $item
	 * @param \ITE_Cart_Repository $repository
	 */
	public function on_delete( ITE_Line_Item $item, ITE_Cart_Repository $repository ) {

		/**
		 * Fires when a line item is deleted.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Cart_Repository $repository
		 */
		do_action( 'ninja_shop_delete_line_item', $item, $repository );

		/**
		 * Fires when a line item is deleted.
		 *
		 * The dynamic portion of this hook refers to the type of the line item being deleted.
		 * Ex: 'product' or 'tax'.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Cart_Repository $repository
		 */
		do_action( "ninja_shop_delete_{$item->get_type()}_item", $item, $repository );
	}

	/**
	 * Filter a newly constructed line item.
	 *
	 *
	 *
	 * @param \ITE_Line_Item       $item
	 * @param \ITE_Cart_Repository $repository
	 *
	 * @return \ITE_Line_Item
	 */
	public function on_get( ITE_Line_Item $item, ITE_Cart_Repository $repository ) {

		$class = get_class( $item );

		/**
		 * Filter a newly constructed line item.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Cart_Repository $repository
		 */
		$_item = apply_filters( 'ninja_shop_get_line_item', $item, $repository );

		if ( $_item instanceof $class && $_item->get_id() === $item->get_id() ) {
			$item = $_item;
		} else {
			_doing_it_wrong( 'it_exchange_get_line_item',
				'Filter must return a sub-class or same class as the original item.', '2.0.0'
			);
		}

		/**
		 * Filter a newly constructed line item.
		 *
		 * The dynamic portion of this hook refers to the type of the line item being retrieved.
		 * Ex: 'product' or 'tax'.
		 *
		 *
		 *
		 * @param \ITE_Line_Item       $item
		 * @param \ITE_Cart_Repository $repository
		 */
		$_item = apply_filters( "ninja_shop_get_{$item->get_type()}_item", $item, $repository );

		if ( $_item instanceof $class && $_item->get_id() === $item->get_id() ) {
			$item = $_item;
		} else {
			_doing_it_wrong( 'it_exchange_get_line_item',
				'Filter must return a sub-class or same class as the original item.', '2.0.0'
			);
		}

		return $item;
	}
}
