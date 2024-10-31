<?php

namespace NinjaShop\Block;

include_once 'class.store-block.php';
add_action( 'init', [ StoreBlock::class, 'register_block_type' ] );


add_action( 'enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'ninja-shop-blocks-js',
        plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
        filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
        true
	);

	$terms = get_terms( [
		'taxonomy' => 'it_exchange_category',
		'hide_empty' => false,
	] );

	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$categories = $terms;
	} else {
		$categories = [];
	}

	$terms_tags = get_terms( [
		'taxonomy' => 'it_exchange_tag',
		'hide_empty' => false,
	] );

	if ( ! empty( $terms_tags ) && ! is_wp_error( $terms_tags ) ) {
		$tags = $terms_tags;
	} else {
		$tags = [];
	}

	wp_localize_script(
		'ninja-shop-blocks-js',
		'ninjaShopBlocks',
		[
			'tags'       => $tags,
			'categories' => $categories
		]
	);
} );

add_action( 'enqueue_block_assets', function() {
	wp_enqueue_style( 'it-exchange-public-css', \IT_Exchange::$url . '/lib/assets/styles/ninja-shop.css', array( 'dashicons' ) );
	wp_enqueue_style(
        'ninja-shop-block-css',
		plugins_url( '/dist/blocks.style.build.css', dirname( __FILE__ ) ),
		array()
	);
} );