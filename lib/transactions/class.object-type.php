<?php
/**
 * Transaction Object Type.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Transaction_Object_Type
 */
class ITE_Transaction_Object_Type extends ITE_Table_With_Meta_Object_Type implements ITE_RESTful_Object_Type {

	/**
	 * @inheritDoc
	 */
	protected function get_model() { return new IT_Exchange_Transaction(); }

	/**
	 * @inheritDoc
	 */
	public function get_label() { return __( 'Transaction', 'it-l10n-ithemes-exchange' ); }

	/**
	 * @inheritDoc
	 */
	public function get_slug() { return 'transaction'; }

	/**
	 * @inheritDoc
	 */
	public function get_collection_route() {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\v1\Transaction\Transactions'
		);
	}

	/**
	 * @inheritDoc
	 */
	public function get_object_route( $object_id ) {
		return \iThemes\Exchange\REST\get_rest_manager()->get_first_route(
			'iThemes\Exchange\REST\Route\v1\Transaction\Transaction'
		);
	}
}
