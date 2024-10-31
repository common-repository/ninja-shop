'use strict';

import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, ToggleControl, RangeControl, PanelBody, ServerSideRender, Placeholder } = wp.components;

registerBlockType( 'ninja-shop/product', {
	title: 'Ninja Shop Product',
	icon: 'cart',
	keywords: [ __( 'ninja' ), __( 'shop' ), __( 'product' ) ],
	category: 'widgets',
	attributes: {
		product: {
			type: 'integer',
			value: 0,
		},
	},
	edit( props ) {
		const { attributes: { product = 0 }, setAttributes } = props;
		const products = ninjaShopBlocks.products.map(function(product){
			return { value: product.ID, label: product.post_title };
		});

		products.unshift( { value: 0, label: 'Select a product.' } );

		function selectProduct( value ) {
			setAttributes( { product: value } );
		}

		return (
			<div>
				<InspectorControls key="ninja-forms-selector-inspector-controls">
					<PanelBody title={ 'Product Settings' }>
						<SelectControl
							label={ 'Product' }
							value={ product }
							options={ products }
							onChange={ selectProduct }
						/>
					</PanelBody>
				</InspectorControls>
				<ServerSideRender
					key="ninja-shop-product-server-side-renderer"
					block="ninja-shop/product"
					attributes={ props.attributes }
				/>
			</div>
		);
	},
	save() {
		return null;
	},
} );
