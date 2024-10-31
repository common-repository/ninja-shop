'use strict';

import domReady from '@wordpress/dom-ready';
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { createElement } = wp.element;
const { registerBlockType } = wp.blocks;
const { InspectorControls } = wp.editor;
const { SelectControl, TextControl, ToggleControl, RangeControl, PanelBody, ServerSideRender, Placeholder, ButtonGroup, Button, Disabled } = wp.components;

registerBlockType( 'ninja-shop/store', {
	title: __('Ninja Shop Store'),
	icon: 'cart',
	keywords: [ __( 'ninja' ), __( 'shop' ), __( 'store' ) ],
	category: 'widgets',
	attributes: {
    style: {
      type: 'string',
      default: 'none',
    },
		category: {
			type: 'string',
			default: '',
		},
		columns: {
			type: 'number',
			default: 1,
    },
    orderby: {
      type: 'string',
      default: 'order_number'
    },
    order: {
      type: 'string',
      default: 'DESC'
    },
    posts_per_page: {
      type: 'number',
      default: 10
    },
    tag: {
      type: 'string',
			default: '',
    }
	},
	edit( block ) {
    const { posts_per_page, category, order, orderby, columns, style, tag } = block.attributes;
    const categories = (Array.isArray(ninjaShopBlocks.categories) || ninjaShopBlocks.categories.length) ? ninjaShopBlocks.categories.map(item => {
      return {
        value: item.term_id,
        label: item.name
      };
    }) : [];
    categories.unshift({
      value: '', label: 'Select a Category'
    })
    const tags = (Array.isArray(ninjaShopBlocks.tags) || ninjaShopBlocks.tags.length) ? ninjaShopBlocks.tags.map(item => {
      return {
        value: item.term_id,
        label: item.name
      };
    }) : [];
    tags.unshift({
      value: '', label: 'Select a Tag'
    })
		return (
			<div>
				<InspectorControls key="ninja-forms-selector-inspector-controls">
					<PanelBody title={ __('Store Page Settings') }>
            <SelectControl
							label={ __('Style Option') }
							value={ style }
							options={ [
                { value: 'none', label: 'None' },
                { value: 'option-1', label: 'Option 1' },
                { value: 'option-2', label: 'Option 2' },
              ] }
							onChange={ (style) => block.setAttributes({ style }) }
						/>
            {
              (Array.isArray(ninjaShopBlocks.categories) && ninjaShopBlocks.categories.length > 0) &&
              <SelectControl
                multiple
                label={ __('Product Category') }
                value={ category !== '' ? JSON.parse(category) : '' }
                options={ categories }
                onChange={ (category) => block.setAttributes({ category: category !== '' ? JSON.stringify(category) : '' }) }
              />
            }
            {
              (Array.isArray(ninjaShopBlocks.tags) && ninjaShopBlocks.tags.length > 0) &&
              <SelectControl
                multiple
                label={ __('Product Tags') }
                value={ tag !== '' ? JSON.parse(tag) : '' }
                options={ tags }
                onChange={ (tag) => block.setAttributes({ tag: tag !== '' ? JSON.stringify(tag) : '' }) }
              />
            }
            <SelectControl
							label={ __('Order by') }
							value={ orderby }
							options={ [
                { value: 'menu_order', label: 'Order Number' },
                { value: 'title', label: 'Alphabetical' },
                { value: 'price', label: 'Price' },
              ] }
							onChange={ (orderby) => block.setAttributes({ orderby }) }
						/>
            <SelectControl
							label={ __('Order') }
              value={ order }
              onChange={ (order) => block.setAttributes({ order }) }
							options={ [
                { value: 'ASC', label: 'Ascending' },
                { value: 'DESC', label: 'Descending' },
              ] }
						/>
						<RangeControl
							label={ __('Columns') }
							value={ columns }
							onChange={ (columns) => block.setAttributes({ columns }) }
							min={ 1 }
							max={ 6 }
						/>
            <TextControl
							label={ __('Number of Products To Show') }
							value={ posts_per_page }
							onChange={ ( posts_per_page ) => block.setAttributes({ posts_per_page: Number(posts_per_page) }) }
              type="number"
						/>
					</PanelBody>
				</InspectorControls>
        <Disabled>
          <ServerSideRender
				  	block="ninja-shop/store"
				  	attributes={ block.attributes }
				  />
        </Disabled>
			</div>
		);
	},
	save() {
		return null;
	},
} );
