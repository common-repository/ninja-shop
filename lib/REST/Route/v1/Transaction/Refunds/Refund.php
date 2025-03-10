<?php
/**
 * Refund Endpoint.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Transaction\Refunds;

use iThemes\Exchange\REST\Auth\AuthScope;
use iThemes\Exchange\REST\Errors;
use iThemes\Exchange\REST\Getable;
use iThemes\Exchange\REST\Manager;
use iThemes\Exchange\REST\Request;
use iThemes\Exchange\REST\Route\Base;
use iThemes\Exchange\REST\RouteObjectExpandable;

/**
 * Class Transaction
 *
 * @package iThemes\Exchange\REST\Route\v1\Transaction
 */
class Refund extends Base implements Getable, RouteObjectExpandable {

	/** @var Serializer */
	private $serializer;

	/**
	 * Transaction constructor.
	 *
	 * @param \iThemes\Exchange\REST\Route\v1\Transaction\Refunds\Serializer $serializer
	 */
	public function __construct( Serializer $serializer ) { $this->serializer = $serializer; }

	/**
	 * @inheritDoc
	 */
	public function handle_get( Request $request ) {

		/** @var \ITE_Refund $refund */
		$refund = $request->get_route_object( 'refund_id' );

		$response = new \WP_REST_Response( $this->serializer->serialize( $refund ) );

		foreach ( $this->serializer->generate_links( $refund, $this->get_manager() ) as $rel => $links ) {
			foreach ( $links as $link ) {
				$response->add_link( $rel, $link['href'], $link );
			}
		}

		return $response;
	}

	/**
	 * @inheritDoc
	 */
	public function user_can_get( Request $request, AuthScope $scope ) {

		$transaction_id = $request->get_param( 'transaction_id', 'URL' );

		/** @var \ITE_Refund $refund */
		$refund = $request->get_route_object( 'refund_id' );

		if ( ! $refund || ! $refund->transaction || $refund->transaction->ID !== (int) $transaction_id ) {
			return Errors::not_found();
		}

		if ( ! $scope->can( 'it_read_refund', $refund ) ) {
			return Errors::cannot_view();
		}

		if ( $request['context'] === 'edit' && ! $scope->can( 'it_edit_refund', $refund ) ) {
			return Errors::forbidden_context( 'edit' );
		}

		return Manager::AUTH_STOP_CASCADE;
	}

	/**
	 * @inheritDoc
	 */
	public function get_version() { return 1; }

	/**
	 * @inheritDoc
	 */
	public function get_path() { return '(?P<refund_id>\d+)/'; }

	/**
	 * @inheritDoc
	 */
	public function get_route_object_map() { return array( 'refund_id' => 'ITE_Refund::get' ); }

	/**
	 * @inheritDoc
	 */
	public function get_query_args() { return array(); }

	/**
	 * @inheritDoc
	 */
	public function get_schema() { return $this->serializer->get_schema(); }
}
