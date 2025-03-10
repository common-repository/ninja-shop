<?php
/**
 * Addresses endpoint.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Customer\Address;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Postable;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;

/**
 * Class Addresses
 *
 * @package iThemes\Exchange\REST\Route\v1\Customer\Address
 */
class Addresses extends Base implements Getable, Postable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Addresses constructor.
	 *
	 * @param Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {
		$customer_id = $request->get_param( 'customer_id', 'URL' );

		$data = array();

		foreach ( \ITE_Saved_Address::query()->and_where( 'customer', '=', $customer_id )->results() as $address ) {
			$data[] = $this->serializer->serialize( $address );
		}

		return new \WP_REST_Response( $data );
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {
		return true; // Cascades to Customer route.
	}

	/**
	 * @inheritDoc
	 */
	public function handle_post( Request $request ) {

		$customer_id = $request->get_param( 'customer_id', 'URL' );

		$data    = array_merge( $request->get_json_params(), array( 'customer' => $customer_id ) );
		$address = \ITE_Saved_Address::query()->and_where( $data )->take( 1 )->first();

		if ( $address ) {
			$response = new \WP_REST_Response( null, \WP_Http::SEE_OTHER );
		} else {
			$address = new \ITE_Saved_Address();
			$address->with_guarded('type', function( \ITE_Saved_Address $address ) use ( $data ) {
				$address->fill( $data );
			} );
			$saved = $address->save();

			if ( ! $saved || ! $address->exists() ) {
				return new \WP_Error(
					'it_exchange_rest_unable_to_create',
					__( 'Unable to create address.', 'it-l10n-ithemes-exchange' ),
					array( 'status' => \WP_Http::INTERNAL_SERVER_ERROR )
				);
			}

			$response = new \WP_REST_Response( $this->serializer->serialize( $address ), \WP_Http::CREATED );
		}

		$response->header( 'Location', \iThemes\Exchange\REST\get_rest_url(
			$this->get_manager()->get_first_route( 'iThemes\Exchange\REST\Route\v1\Customer\Address\Address' ),
			array( 'customer_id' => $customer_id, 'address_id' => $address->get_pk() )
		) );

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_post( Request $request, AuthScope $scope ) {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return 'addresses/'; }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
