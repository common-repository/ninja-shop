<?php
/**
 * Item Serializer.
 *
 * 
 * @license GPLv2
 */

namespace iThemes\Exchange\REST\Route\v1\Cart;

/**
 * Class Item_Serializer
 *
 * @package iThemes\Exchange\REST\Route\v1\Cart
 */
class Item_Serializer {

	/** @var \Closure */
	private $extend;

	/** @var \ITE_Line_Item_Type */
	private $type;

	/** @var array */
	private $schema;

	/**
	 * Item_Serializer constructor.
	 *
	 * @param \ITE_Line_Item_Type $type
	 */
	public function __construct( \ITE_Line_Item_Type $type ) {
		$this->type = $type;
	}

	/**
	 * Extend the Item Serializer without subclassing.
	 *
	 *
	 *
	 * @param \Closure $extend
	 *
	 * @return $this
	 */
	public function extend( \Closure $extend ) {
		$this->extend = $extend;

		return $this;
	}

	/**
	 * Get the schema for this item serializer.
	 *
	 *
	 *
	 * @return array
	 */
	public function get_schema() {

		if ( ! $this->schema ) {
			$this->schema = $this->generate_schema();
		}

		return $this->schema;
	}

	/**
	 * Serialize a line item.
	 *
	 *
	 *
	 * @param \ITE_Line_Item $item
	 * @param \ITE_Cart      $cart
	 *
	 * @return array
	 */
	public function serialize( \ITE_Line_Item $item, \ITE_Cart $cart ) {

		$schema = $this->get_schema();

		$data = array(
			'id'           => $item->get_id(),
			'type'         => $item->get_type(),
			'name'         => $item->get_name(),
			'description'  => $item->get_description(),
			'amount'       => $item->get_amount(),
			'quantity'     => array(
				'selected' => $item->get_quantity(),
				'editable' => false,
			),
			'total'        => $item->get_total(),
			'summary_only' => $item->is_summary_only(),
		);

		if ( $item instanceof \ITE_Quantity_Modifiable_Item && $item->is_quantity_modifiable() ) {
			$data['quantity']['max']      = ( $max = $item->get_max_quantity_available() ) && is_numeric( $max ) ? (int) $max : '';
			$data['quantity']['editable'] = \ITE_Line_Item_Types::get( $item->get_type() )->is_editable_in_rest();
		}

		if ( $item instanceof \ITE_Aggregate_Line_Item ) {
			$data['children'] = array();

			foreach ( $item->get_line_items()->non_summary_only() as $child ) {

				$type = \ITE_Line_Item_Types::get( $child->get_type() );

				if ( $type && $type->is_show_in_rest() ) {
					$data['children'][] = $type->get_rest_serializer()->serialize( $child, $cart );
				}
			}
		}

		foreach ( $data as $key => $_ ) {
			if ( ! isset( $schema['properties'][ $key ] ) ) {
				unset( $data[ $key ] );
			}
		}

		if ( $this->extend ) {
			$data = call_user_func( $this->extend, $data, $item, $schema, $cart );
		}

		return $data;
	}

	/**
	 * Generate the schema.
	 *
	 *
	 *
	 * @return array
	 */
	protected function generate_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => "cart-item-{$this->type->get_type()}",
			'type'       => 'object',
			'properties' => array(
				'id'           => array(
					'description' => __( 'The unique id for this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'type'         => array(
					'description' => __( 'The type of this item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'         => array(
					'description' => __( 'The name of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'description'  => array(
					'description' => __( 'The description of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'amount'       => array(
					'description' => __( 'The cost of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'quantity'     => array(
					'description' => __( 'The quantity purchased of this line item.', 'it-l10n-ithemes-exchange' ),
					'context'     => array( 'view', 'edit' ),
					'oneOf'       => array(
						array(
							'type'       => 'object',
							'context'    => array( 'view', 'edit' ),
							'properties' => array(
								'selected' => array(
									'description' => __( 'Selected quantity for the line item.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'integer',
									'context'     => array( 'view', 'edit' ),
									'default'     => 1,
								),
								'max'      => array(
									'description' => __( 'Maximum purchase quantity for the line item.', 'it-l10n-ithemes-exchange' ),
									'readonly'    => true,
									'context'     => array( 'view', 'edit' ),
									'oneOf'       => array(
										array( 'type' => 'integer' ),
										array(
											'type'        => 'string',
											'enum'        => array( '' ),
											'description' => __( 'Unlimited quantity.', 'it-l10n-ithemes-exchange' )
										)
									)
								),
								'editable' => array(
									'description' => __( 'Whether the item quantity can be edited.', 'it-l10n-ithemes-exchange' ),
									'type'        => 'boolean',
									'context'     => array( 'view', 'edit' ),
									'readonly'    => true,
								),
							)
						),
						array( 'type' => 'integer' )
					)
				),
				'total'        => array(
					'description' => __( 'The total amount of this line item.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'number',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'summary_only' => array(
					'description' => __( 'Should the line item only be displayed in the totals section.', 'it-l10n-ithemes-exchange' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
			),
		);

		if ( $this->type->is_aggregate() ) {
			$schema['properties']['children'] = array(
				'description' => __( 'Child line items of this item.', 'it-l10n-ithemes-exchange' ),
				'type'        => 'array',
				'items'       => array(
					'oneOf' => array(),
				),
				'readonly'    => true,
				'context'     => array( 'view', 'edit' ),
			);

			foreach ( \ITE_Line_Item_Types::aggregatables() as $aggregatable ) {

				if ( $aggregatable->is_show_in_rest() ) {
					$schema['properties']['children']['items']['oneOf'][] = array(
						'$ref' => \iThemes\Exchange\REST\url_for_schema( "cart-item-{$aggregatable->get_type()}" )
					);
				}
			}
		}

		foreach ( $this->type->get_additional_schema_props() as $prop => $schema_prop ) {
			$schema['properties'][ $prop ] = $schema_prop;
		}

		return $schema;
	}

}
