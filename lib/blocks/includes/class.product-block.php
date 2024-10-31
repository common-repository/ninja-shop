<?php

namespace NinjaShop\Block;

class ProductBlock
{
	protected static $product;

	public static function register_block_type()
	{
		register_block_type( 'ninja-shop/product', array(
			'attributes' => array(
				'product' => array(
					'type' => 'integer',
				),
			),
			'editor_style'    => 'ninja-shop-product',
			'render_callback' => array( self::class, 'server_side_render' ),
		) );
	}

	public static function server_side_render( $atts )
	{
		it_exchange_set_product( $atts[ 'product' ] );
		ob_start();
		echo '<div id="ninja-shop-store" class="ninja-shop-wrap ninja-shop-account">';
			echo '<ul class="ninja-shop-products">';
				it_exchange_get_template_part( 'content-store/elements/product' );
			echo '</ul>';
		echo '</div>';
		return ob_get_clean();
	}
	
	public static function filter_get_product_args( $args )
	{
		$args[ 'numberposts' ] = 1;
		$args[ 'include' ] = [ self::$product ];
		return $args;
	}
}