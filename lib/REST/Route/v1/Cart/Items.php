<?php
/**
 * Route to return line items.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

use iThemes\Exchange\REST as r;
use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Deletable;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Cart
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Items extends Base implements Getable, Postable, Deletable {

	/** @var Item_Serializer */
	protected $serializer;

	/** @var \ITE_Line_Item_Type */
	protected $type;

	/**
	 * Cart constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Cart\Item_Serializer $serializer
	 * @param \ITE_Line_Item_Type                               $type
	 */
	public function __construct( Item_Serializer $serializer, \ITE_Line_Item_Type $type ) {
		$this->serializer = $serializer;
		$this->type       = $type;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart       = $request->get_route_object( 'cart_id' );
		$serializer = $this->serializer;

		return new \WP_REST_Response( array_map( function ( \ITE_Line_Item $item ) use ( $serializer, $cart ) {
			return $serializer->serialize( $item, $cart );
		}, $cart->get_items( $this->type->get_type() )->to_array() ) );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		// Sanity check, an Items route should not be instantiated with a Item Type that cannot be shown in rest.
		if ( ! $this->type->is_show_in_rest() ) {
			return Errors::view_line_item_not_supported( $this->type->get_type() );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$item = $this->type->create_from_request( $request );

		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( $item ) {

			/** @var \ITE_Cart $cart */
			$cart = $request->get_route_object( 'cart_id' );

			$response = new \WP_REST_Response( $this->serializer->serialize( $item, $cart ) );
			$response->set_status( \WP_Http::CREATED );
			$response->header( 'Location', r\get_rest_url(
				new Item( $this->type, $this->serializer ), array(
					'cart_id' => $cart->get_id(),
					'item_id' => $item->get_id()
				)
			) );

			return $response;
		}

		return new \WP_Error(
			'it_exchange_rest_unexpected_error',
			__( 'An unexpected error occurred creating a new line item.', 'it-l10n-ithemes-exchange' ),
			array( 'status', 500 )
		);
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return Errors::create_line_item_not_supported( $this->type->get_type() );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function handle_delete( Request $request ) {

		/** @var \ITE_Cart $cart */
		$cart = $request->get_route_object( 'cart_id' );
		$cart->remove_all( $this->type->get_type() );

		return new \WP_REST_Response( null, 204 );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_delete( Request $request, AuthScope $scope ) {
		if ( ! $this->type->is_editable_in_rest() ) {
			return Errors::delete_line_item_not_supported( $this->type->get_type() );
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return "items/{$this->type->get_type()}/"; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }

	/**
	 * Get the item type this endpoint represents.
	 *
	 *
	 *
	 * @return \ITE_Line_Item_Type
	 */
	public function get_type() { return $this->type; }
}
