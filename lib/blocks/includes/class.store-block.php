<?php

namespace NinjaShop\Block;

use function \get_next_posts_link;

class StoreBlock
{
	protected static $style;
	protected static $columns;
	protected static $category;
	protected static $orderby;
	protected static $order;
	protected static $posts_per_page;
	protected static $tag;

	public static function register_block_type()
	{
		register_block_type( 'ninja-shop/store', array(
			'attributes' => array(
				'style'    => array(
					'type'    => 'string',
					'default' => 'none',
				),
				'category' => array(
					'type'    => 'string',
					'default' => '',
				),
				'columns' => array(
					'type'    => 'integer',
					'default' => 1,
				),
				'orderby' => array(
					'type'    => 'string',
					'default' => 'order_number',
				),
				'order' => array(
					'type'    => 'string',
					'default' => 'DESC',
				),
				'posts_per_page' => array(
					'type'    => 'integer',
					'default' => 10,
				),
				'tag' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'editor_style'    => 'ninja-shop-store',
			'render_callback' => array( self::class, 'server_side_render' ),
		) );
	}

	public static function server_side_render( $attributes )
	{
		self::$style          = $attributes[ 'style' ];
		self::$columns        = $attributes[ 'columns' ];
		self::$category       = ! empty( $attributes[ 'category' ] ) ? json_decode( $attributes[ 'category' ] ) : [];
		self::$tag            = ! empty( $attributes[ 'tag' ] ) ? json_decode( $attributes[ 'tag' ] ) : [];
		self::$orderby        = $attributes[ 'orderby' ];
		self::$order          = $attributes[ 'order' ];
		self::$posts_per_page = $attributes[ 'posts_per_page' ];

		ob_start();

		$classes = [ 'ninja-shop-products', self::$style ];

		if( self::$columns === 1 ){
			$classes[] = 'list';
		} else {
			$classes[] = 'grid';
		}

		if ( self::$columns === 2) {
			$classes[] = 'two-columns';
		} else if ( self::$columns === 3) {
			$classes[] = 'three-columns';
		} else if ( self::$columns === 4) {
			$classes[] = 'four-columns';
		} else if ( self::$columns === 5) {
			$classes[] = 'five-columns';
		} else if ( self::$columns === 6) {
			$classes[] = 'six-columns';
		}

		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
		$args  = [
			'order'          => self::$order,
			'paged'          => $paged,
			'post_type'      => 'it_exchange_prod',
			'posts_per_page' => self::$posts_per_page,

		];

		/**
		 * Set the orderby arg
		 */
		if( self::$orderby === 'price' ){
			$args[ 'orderby' ]  = 'meta_value_num';
			$args[ 'meta_key' ] = '_it-exchange-base-price';
		} else {
			$args[ 'orderby' ] = self::$orderby;
		}

		/**
		 * Set the category arg
		 */
		if( 0 !== count( self::$category ) ){
			$args[ 'tax_query' ] = array(
				array(
					'taxonomy' => 'it_exchange_category',
					'terms' => self::$category,
					'field' => 'term_id'
				)
			);
		}

		/**
		 * Set the tag arg
		 */
		if( 0 !== count( self::$tag ) ) {
			$args[ 'tax_query' ] = array(
				array(
					'taxonomy' => 'it_exchange_tag',
					'terms' => self::$tag,
					'field' => 'term_id'
				)
			);
		}

		echo '<div class="' . implode( ' ', $classes ) . '">';

		$ninja_shop_store_block_loop = new \WP_Query($args);
		if ($ninja_shop_store_block_loop->have_posts()) :
			while ($ninja_shop_store_block_loop->have_posts()) : $ninja_shop_store_block_loop->the_post();
				it_exchange_set_product($ninja_shop_store_block_loop->post->ID);
				it_exchange_get_template_part('content-store/elements/product');
			endwhile;
		endif;

		echo '</div>';

		if ($ninja_shop_store_block_loop->max_num_pages > 1) :

			$big = 999999999;

			$pagination_args = array(
				'format' => 'page/%#%',
				'current' => max(1, get_query_var('paged')),
				'show_all' => true,
				'total' => $ninja_shop_store_block_loop->max_num_pages,
				'prev_next' => true,
				'prev_text' => __('Previous', 'ninja-shop'),
				'next_text' => __('Next', 'ninja-shop'),
			);

			echo paginate_links($pagination_args);

		endif;

		$return = ob_get_clean();

		wp_reset_query();

		return $return;
	}
}