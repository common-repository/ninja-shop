<?php
/**
 * Product class for THEME API
 *
 *
*/

class IT_Theme_API_Product implements IT_Theme_API {

	/**
	 * API context
	 * @var string $_context
	 *
	*/
	private $_context = 'product';

	/**u
	 * Maps api tags to methods
	 * @var array $_tag_map
	 *
	*/
	var $_tag_map = array(
		'found'               => 'found',
		'title'               => 'title',
		'permalink'           => 'permalink',
		'excerpt'             => 'excerpt',
		'description'         => 'description',
		'content'             => 'extended_description',
		'extendeddescription' => 'extended_description',
		'author'              => 'author',
		'baseprice'           => 'base_price',
		'purchasequantity'    => 'purchase_quantity',
		'inventory'           => 'inventory',
		'availability'        => 'availability',
		'isavailable'         => 'is_available',
		'visibility'          => 'visibility',
		'isvisible'           => 'is_visible',
		'images'              => 'product_images',
		'gallery'             => 'product_gallery',
		'featuredimage'       => 'featured_image',
		'downloads'           => 'downloads',
		'purchaseoptions'     => 'purchase_options',
		'superwidget'         => 'superwidget',
		'buynow'              => 'buy_now',
		'addtocart'           => 'add_to_cart',
		'buynowvar'           => 'buy_now_var',
		'addtocartvar'        => 'add_to_cart_var',
	);

	/**
	 * Current product in Ninja Shop Global
	 * @var IT_Exchange_Product $product
	 *
	*/
	public $product;

	/**
	 * Constructor
	 *
	 *
	*/
	function __construct() {
		// Set the current global product as a property
		$this->product = empty( $GLOBALS['it_exchange']['product'] ) ? false : $GLOBALS['it_exchange']['product'];
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Theme_API_Product() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Returns the context. Also helps to confirm we are an Ninja Shop theme API class
	 *
	 *
	 *
	 * @return string
	*/
	function get_api_context() {
		return $this->_context;
	}

	/**
	 * Returns boolean value if we have a product or not
	 *
	 *
	 *
	 * @return boolean
	*/
	function found( $options=array() ) {
		return (boolean) $this->product;
	}

	/**
	 * The product title
	 *
	 *
	 * @return string
	*/
	function title( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'title' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'title' );

		// Repeats checks for when flags were not passed.
		if ( it_exchange_product_supports_feature( $this->product->ID, 'title' )
				&& it_exchange_product_has_feature( $this->product->ID, 'title' ) ) {

			$result   = '';
			$title    = it_exchange_get_product_feature( $this->product->ID, 'title' );

			$defaults = array(
				'wrap'   => 'h1',
				'format' => 'html',
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			/**
			 * Filter the options used to render the product title.
			 *
			 *
			 *
			 * @param array               $options
			 * @param IT_Exchange_Product $product
			 */
			$options = apply_filters( 'ninja_shop_api_theme_product_title_options', $options, $this->product );

			if ( 'html' == $options['format'] )
				$result .= '<' . $options['wrap'] . ' class="entry-title">';

			$result .= $title;

			if ( 'html' == $options['format'] )
				$result .= '</' . $options['wrap'] . '>';

			return $result;
		}
		return false;
	}

	/**
	 * The permalink
	 *
	 *
	 * @return mixed
	*/
	function permalink( $options=array() ) {

		$permalink = empty( $this->product->ID ) ? false : get_permalink( $this->product->ID );

		if ( $options['has'] )
			return (boolean) $permalink;

		$result = '';
		$defaults   = array(
			'before' => '<a href="',
			'after'  => '">' . it_exchange( 'product', 'get-title', 'format=' ) . '</a>',
			'format' => 'html',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $permalink;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;

	}

	/**
	 * The product base price
	 *
	 *
	 * @return mixed
	*/
	function base_price( $options=array() ) {

		// Return boolean if has flag was set
		if ( isset( $options['supports'] ) && $options['supports'] ) {
			return it_exchange_product_supports_feature( $this->product->ID, 'base-price' );
		}

		// Return boolean if has flag was set
		if ( isset( $options['has'] ) && $options['has'] ) {
			return it_exchange_product_has_feature( $this->product->ID, 'base-price' );
		}

		if ( $this->product->supports_feature( 'base-price' ) && $this->product->has_feature('base-price' ) ) {

			$result     = '';
			$defaults   = array(
				'before'     => '<span class="ninja-shop-base-price">',
				'after'      => '</span>',
				'format'     => 'html',
				'price'      => false,
				'free-label' => __( 'Free', 'it-l10n-ithemes-exchange' ),
                'show-sale'  => it_exchange_is_product_sale_active( $this->product ),
			);
			$options = ITUtility::merge_defaults( $options, $defaults );

			// Grab product feature price
			$base_price = empty( $options['price'] ) ? $this->product->get_feature( 'base-price' ) : $options['price'];
			$show_sale = $options['show-sale'];

			if ( ( (float) $base_price === 0.00 || empty( $base_price ) ) && $options['free-label'] ) {
				$price = "<span class='free-label'>{$options['free-label']}</span>";
			} else {
				$price = it_exchange_format_price( $base_price );
			}

			/**
			 * Filter the output of the product's purchase price.
             *
             *
             *
             *
             * @param string $price      The price ready for output. May contain HTML.
             * @param int    $product_id The product ID.
             * @param bool   $sale       Whether this is filtering the sale price amount or base price amount.
             * @param bool   $show_sale  Whether the sale price will be displayed.
			 */
			$price = apply_filters( 'ninja_shop_api_theme_product_base_price', $price, $this->product->ID, false, $show_sale );

			if ( $show_sale ) {
                $sale_price = $this->product->get_feature( 'sale-price' );

                if ( ( (float) $sale_price === 0.00 || empty( $sale_price ) ) && $options['free-label'] ) {
                    $sale_price = "<span class='free-label'>{$options['free-label']}</span>";
                } else {
                    $sale_price = it_exchange_format_price( $sale_price );
                }

                // This filter is documented in api/theme/product.php
                $sale_price = apply_filters( 'ninja_shop_api_theme_product_base_price', $sale_price, $this->product->ID, true, $show_sale );

                $price = "<del>{$price}</del>&nbsp;<ins>{$sale_price}</ins>";
            }

			if ( 'html' === $options['format'] ) {
				$result .= $options['before'];
			}

			/**
			 * Filter the entire output of the product's purchase price.
             *
             * This includes both the sale price and base price parts.
             *
             *
             *
             * @param string $price      The price ready for output. May contain HTML.
             * @param int    $product_id The product ID.
			 */
			$result .= apply_filters( 'ninja_shop_api_theme_product_base_price_full', $price, $this->product->ID );

			if ( 'html' === $options['format'] ) {
				$result .= $options['after'];
			}

			return $result;
		}

		return false;
	}

	/**
	 * The product's large description
	 *
	 *
	 * @return string
	*/
	function description( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'description' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'description' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'description' )
				&& it_exchange_product_has_feature( $this->product->ID, 'description' ) ) {

			$result = '';
			$description = it_exchange_get_product_feature( $this->product->ID, 'description' );

			$defaults   = array(
				'max-length' => false,
				'max-words'  => false,
				'ellipsis'   => '...',
				'more-text'  => __( '(more info)', 'it-l10n-ithemes-exchange' )
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			if ( ! empty( $options['max-length'] ) && is_numeric( $options['max-length'] ) && strlen( $description ) > $options['max-length'] ) {
				$settings     = it_exchange_get_option( 'settings_general' );
				$description_wysiwyg = $settings['wysiwyg-for-product-description'];

				if ( 0 === $description_wysiwyg ) {
					$result = substr( wp_strip_all_tags( $description ), 0, $options['max-length'] );
					$result .= $options['ellipsis'] . ' <a href="' . get_permalink( $this->product->ID ) . '">' . $options['more-text'] . '</a>';
				} else {
					$result = substr( wp_kses_post( $description ), 0, $options['max-length'] );
					$result .= $options['ellipsis'] . ' <a href="' . get_permalink( $this->product->ID ) . '">' . $options['more-text'] . '</a>';
				}
			} else if ( ! empty( $options['max-words'] ) ) {

				$more = $options['ellipsis'] . ' <a href="' . get_permalink( $this->product->ID ) . '">' . $options['more-text'] . '</a>';

				$result = wp_trim_words( $description, $options['max-words'], $more );
			}
			else {

				global $IT_Exchange_Pages;

				if ( has_filter( 'the_content', array( $IT_Exchange_Pages, 'fallback_filter_for_page_template' ) ) )
					$has_filter = true;
				else
					$has_filter = false;

				if ( $has_filter )
					remove_filter( 'the_content', array( $IT_Exchange_Pages, 'fallback_filter_for_page_template' ) );

				$result = apply_filters( 'the_content', $description );

				if ( $has_filter )
					add_filter( 'the_content', array( $IT_Exchange_Pages, 'fallback_filter_for_page_template' ) );
			}

			return $result;
		}

		return false;
	}

	/**
	 * The extended description
	 *
	 *
	 *
	 * @return string
	*/
	function extended_description( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'extended-description' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'extended-description' );

		$result        = false;
		$extended_desc = it_exchange_get_product_feature( $this->product->ID, 'extended-description' );
		$defaults      = array(
			'before' => '<div>',
			'after'  => '</div>',
			'format' => 'html',
		);
		$options      = ITUtility::merge_defaults( $options, $defaults );

		if ( 'html' == $options['format'] )
			$result .= $options['before'];

		$result .= $extended_desc;

		if ( 'html' == $options['format'] )
			$result .= $options['after'];

		return $result;
	}

	/**
	 * The product's WP excerpt
	 *
	 *
	 * @return string
	*/
	function excerpt( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'wp-excerpt' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'wp-excerpt' )
				&& it_exchange_product_has_feature( $this->product->ID, 'wp-excerpt' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'wp-excerpt' );
		return false;
	}

	/**
	 * The product author
	 *
	 *
	 * @return string
	*/
	function author( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'author' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'author' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'author' )
				&& it_exchange_product_has_feature( $this->product->ID, 'author' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'author' );
		return false;
	}

	/**
	 * The product purchase quantity (max purchase option by customer)
	 *
	 *
	 * @return integer
	*/
	function purchase_quantity( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'purchase-quantity' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'purchase-quantity' );

		// Set options
		$defaults      = array(
			'before'          => '',
			'after'           => '',
			'format'          => 'html',
			'class'           => 'product-purchase-quantity',
			'unlimited-label' => __( 'Unlimited', 'it-l10n-ithemes-exchange' ),
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		$class     = empty( $options['class'] ) ? '' : ' class="' . esc_attr( $options['class'] ) .'"';
		$var_key   = it_exchange_get_field_name( 'product_purchase_quantity' );

		// Is the checkbox on add/edit products unchecked to allow quantities greater than 1
		if ( it_exchange_product_supports_feature( $this->product->ID, 'purchase-quantity' ) )
			$max_quantity = it_exchange_get_product_feature( $this->product->ID, 'purchase-quantity' );
		else
			return '';
		$max_quantity = empty( $max_quantity ) ? $options['unlimited-label'] : $max_quantity;

		// Lets do some inventory checking and make sure that if we're supporing inventory, that we don't allow max to be greater than inventory
		if ( it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ) {
			// If we support inventory, but we don't have any, and we've been passed the HTML format, return empty string
			if ( ! $inventory = it_exchange_get_product_feature( $this->product->ID, 'inventory' ) )
				return '';

			// Lets check product availability and return and empty string if its not available.
			if ( ! it_exchange( 'product', 'is-available' ) )
				return '';

			if ( trim( $max_quantity ) === '' ) {
				$max_quantity = $inventory;
			} else if ( $inventory && (int) $max_quantity > 0 && (int) $max_quantity > $inventory ) {
				$max_quantity = $inventory;
			}
		}

		// Return requested format
		switch ( $options['format'] ) {
			case 'max-quantity' :
				return $max_quantity;
				break;
			case 'html' :
			default :
				/*
				 * If there's no sales price, show the base price. Otherwise, show the sale price.
				 */
				$sale_price = $this->product->get_feature( 'sale-price' );

                if ( (float) $sale_price === 0.00 || empty( $sale_price ) ) {
                    $html = $this->base_price( array( 'show-sale' => false ) );
                } else {
                    $html = '<span class="ninja-shop-base-price">' . it_exchange_format_price( $sale_price ) . '</span>';
                }

				$html .= '<label for="' . esc_attr( $var_key ) . '">' . __( 'Quantity', 'it-l10n-ithemes-exchange' ) . '</label>';
				$html .= '<input' . $class . ' type="number" id="' . esc_attr( $var_key ) . '" name="' . esc_attr( $var_key ) . '" value="1" min="1" max="' . ( ! empty( $max_purchase_quantity ) ? $max_purchase_quantity : '' ) . '" />' . "\n";
				$html .= '<input type="hidden" name="' . it_exchange_get_field_name( 'product_max_purchase_quantity' ) . '[' . esc_attr( $this->product->ID ) . ']" value="' . ( $max_quantity ) . '" />';
				return $html;
				break;
		}
	}

	/**
	 * The product's current inventory
	 *
	 *
	 * @return integer
	*/
	function inventory( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'inventory' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'inventory' );

		if ( false !== it_exchange_product_supports_feature( $this->product->ID, 'inventory' )
				&& false !== it_exchange_product_has_feature( $this->product->ID, 'inventory' ) )
			return it_exchange_get_product_feature( $this->product->ID, 'inventory' );
		return false;
	}

	/**
	 * The product's dates purchase availability
	 *
	 * Use type of 'start', 'end', 'both', either in options
	 *
	 *
	 * @return string
	*/
	function availability( $options=array() ) {

		$defaults = array(
			'type' => 'start',
		);
		$options  = ITUtility::merge_defaults( $options, $defaults );

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'availability' );

		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'availability', $options );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'availability', $options ) )
			return it_exchange_get_product_feature( $this->product->ID, 'availability', $options );
		return true;
	}

	/**
	 * Uses start and end availability dates to now to determine if the product is currently available
	 *
     *
	 *
	 * @return boolean
	*/
	function is_available( $options=array() ) {
		return it_exchange_is_product_available( $this->product->ID );
	}

	/**
	 * The product's visibility.
	 *
	 *
	 *
	 * @param array $options
	 *
	 * @return string
	*/
	function visibility( $options = array() ) {

		if ( empty( $options['product_id'] ) ) {
			return false;
		}

		$product_id = $options['product_id'];

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return ( false !== get_post_meta( $product_id, '_it-exchange-visibility', true ) );
		}

		return get_post_meta( $product_id, '_it-exchange-visibility', true );
	}

	/**
	 * Uses start and end availability dates to now to determine if the product is currently available
	 *
     *
	 *
	 * @return boolean
	*/
	function is_visible( $options=array() ) {
		return it_exchange_is_product_visible( $this->product->ID );
	}

	/**
	 * The product's featured image
	 *
	 *
	 *
	 * @return string
	*/
	function featured_image( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'product-images' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'product-images' )
				&& it_exchange_product_has_feature( $this->product->ID, 'product-images' ) ) {

			$defaults = array(
				'size' => 'large'
			);

			$options = ITUtility::merge_defaults( $options, $defaults );

			// Running through array values in case in case product image is wrong (not 0)
			$product_images = array_values( it_exchange_get_product_feature( $this->product->ID, 'product-images' ) );

			$feature_image = array(
				'id'    =>  $product_images[0],
				'thumb' => wp_get_attachment_thumb_url( $product_images[0] ),
				'large' => wp_get_attachment_url( $product_images[0] )
			);

			if ( 'thumbnail' === $options['size'] )
				$img_src = $feature_image['thumb'];
			else
				$img_src = $feature_image['large'];

			ob_start();
			?>
				<div class="ninja-shop-feature-image-<?php echo get_the_id(); ?> ninja-shop-featured-image">
					<div class="featured-image-wrapper">
						<img alt="" src="<?php echo $img_src ?>" data-src-large="<?php echo $feature_image['large'] ?>" data-src-thumb="<?php echo $feature_image['thumb'] ?>" />
					</div>
				</div>
			<?php
			$output = ob_get_clean();

			return $output;
		}

		return false;
	}

	/**
	 * Return product's images for all image sizes.
	 *
	 *
	 *
	 * @return string
	*/
	function product_images( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'product-images' );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'product-images' )
				&& it_exchange_product_has_feature( $this->product->ID, 'product-images' ) ) {

			$defaults = array(
				'id'   => null,
				'size' => 'all' // NOTE These do nothing right now. Going to rething the options later. - Koop
			);

			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = array();

			// Get the image sizes.
			$image_sizes = get_intermediate_image_sizes();

			// Add full to the $image_size array.
			array_push( $image_sizes, 'full' );

			$product_images = it_exchange_get_product_feature( $this->product->ID, 'product-images' );

			foreach( $product_images as $image_id ) {
				foreach ( $image_sizes as $size ) {
					$image['id'] = $image_id;
					$image[$size] = wp_get_attachment_image_src( $image_id, $size );
				}
				$images[] = $image;
			}

			$output = $images;

			return $output;
		}
		return false;
	}

	/**
	 * The product's product image gallery
	 *
	 *
	 *
	 * @return string
	*/
	function product_gallery( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'product-images' );

		// Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'product-images' );

		// Vidembed conflict. Temp fix until vidembed fixes their issue.
		remove_filter( 'image_downsize', 'ithemes_filter_image_downsize', 10, 3 );

		if ( it_exchange_product_supports_feature( $this->product->ID, 'product-images' )
				&& it_exchange_product_has_feature( $this->product->ID, 'product-images' ) ) {

			$settings = it_exchange_get_option( 'settings_general' );

			$zoom = ( 1 == $settings['enable-gallery-zoom'] ) ? $settings['product-gallery-zoom-action'] : 'false';

			$popup = ( 1 == $settings['enable-gallery-popup'] ) ? 'true' : 'false';

			$defaults = array(
				'size'   => 'thumbnail', // thumbnail or large
				'output' => 'gallery',   // gallery or thumbnails
				'zoom'   => $zoom,       // hover or click
				'switch' => 'click',     // hover or click
				'images' => false,
			);

			$options = ITUtility::merge_defaults( $options, $defaults );
			$output = NULL;

			$product_images = empty( $options['images'] ) ? it_exchange_get_product_feature( $this->product->ID, 'product-images' ) : (array) $options['images'];

			switch( $options['output'] ) {

				case 'thumbnails' :
					if ( !empty( $product_images ) ) {
						ob_start();
						?>
							<div class="ninja-shop-product-images-gallery-<?php echo get_the_id(); ?> ninja-shop-product-images-gallery ninja-shop-gallery-thumbnails">
								<?php if ( count( $product_images ) > 1 ) : ?>
									<ul class="ninja-shop-thumbnail-images-<?php echo get_the_ID(); ?> ninja-shop-thumbnail-images">
										<?php foreach( $product_images as $image_id ) : ?>
											<?php
												$img_url = wp_get_attachment_url( $image_id );
												$img_thumb_url = wp_get_attachment_thumb_url( $image_id );
											?>
											<li class="ninja-shop-product-image-thumb-<?php echo $image_id; ?>">
												<span><img alt="" src="<?php echo $img_thumb_url; ?>" data-src-large="<?php echo $img_url; ?>" data-src-thumb="<?php echo $img_thumb_url; ?>"></span>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php
						$output = ob_get_clean();

					}
				break;

				case 'gallery' :
				default :
					if ( ! empty( $product_images ) ) {

						$first = reset( $product_images );

						$featured = array(
							'full'  => wp_get_attachment_image_src( $first, 'full' ),
							'large' => wp_get_attachment_image_src( $first, 'large' ),
							'thumb' => wp_get_attachment_image_src( $first, 'thumbnail' ),
						);

						ob_start();
						if ( 'hover' === $options['zoom'] ) {
							$options['zoom'] = 'mouseover';
						}
						?>
							<div id="ninja-shop-product-images-gallery-<?php echo get_the_id(); ?>" class="ninja-shop-product-images-gallery ninja-shop-gallery-full" data-popup="<?php echo $popup; ?>" data-zoom="<?php echo $options["zoom"]; ?>" data-switch="<?php echo $options['switch']; ?>">
								<div class="ninja-shop-feature-image-<?php echo get_the_ID(); ?> ninja-shop-featured-image">
									<div class="featured-image-wrapper">
										<img alt="" class="featured-image" src="<?php echo $featured['large'][0] ?>" data-src-full="<?php echo $featured['full'][0] ?>" data-src-large="<?php echo $featured['large'][0] ?>" data-height-large="<?php echo $featured['large'][2] ?>" data-featured-position="">
									</div>
								</div>
								<?php if ( count( $product_images ) > 1 ) : ?>
									<ul class="ninja-shop-thumbnail-images-<?php echo get_the_ID(); ?> ninja-shop-thumbnail-images">
										<?php $img_iteration = 0; ?>
										<?php foreach( $product_images as $image_id ) : ?>
											<?php
												if ( $img_iteration == 0 )
													$img_class = 'current';
												else
													$img_class = '';

												$thumbnail = array(
													'full'  => wp_get_attachment_image_src( $image_id, 'full' ),
													'large' => wp_get_attachment_image_src( $image_id, 'large' ),
													'thumb' => wp_get_attachment_image_src( $image_id, 'thumbnail' ),
												);

												$dumped[] = $thumbnail;
											?>
											<li class="ninja-shop-product-image-thumb-<?php echo $image_id; ?>">
												<span class="<?php echo $img_class; ?>"><img alt="" src="<?php echo $thumbnail['thumb'][0] ?>" data-src-full="<?php echo $thumbnail['full'][0] ?>" data-src-large="<?php echo $thumbnail['large'][0] ?>" data-height-large="<?php echo $thumbnail['large'][2] ?>" data-src-thumb="<?php echo $thumbnail['thumb'][0] ?>" data-featured-padding="" /></span>
											</li>
											<?php $img_iteration++; ?>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						<?php
						$output = ob_get_clean();
					}
				break;
			}

			$output = apply_filters( 'ninja_shop_product_gallery', $output );

			return $output;
		}
		return false;
	}

	/**
	 * Returns downloads for product.
	 *
	 * If has option is true, returns boolean
	 *
	 *
	 *
	 * @return boolean
	*/
	function downloads( $options=array() ) {

		// Return boolean if has flag was set
		if ( $options['supports'] )
			return it_exchange_product_supports_feature( $this->product->ID, 'downloads' );

        // Return boolean if has flag was set
		if ( $options['has'] )
			return it_exchange_product_has_feature( $this->product->ID, 'downloads' );

		// If we made it here, we're doing a loop of downloads for the current product.
		// This will init/reset the downloads global and loop through them. the /api/theme/download.php file will handle individual downloads.
		if ( empty( $GLOBALS['it_exchange']['downloads'] ) ) {
			$GLOBALS['it_exchange']['downloads'] = it_exchange_get_product_feature( $this->product->ID, 'downloads' );
			$GLOBALS['it_exchange']['download'] = reset( $GLOBALS['it_exchange']['downloads'] );
			return true;
		} else {
			if ( next( $GLOBALS['it_exchange']['downloads'] ) ) {
				$GLOBALS['it_exchange']['download'] = current( $GLOBALS['it_exchange']['downloads'] );
				return true;
			} else {
				$GLOBALS['it_exchange']['downloads'] = array();
				end( $GLOBALS['it_exchange']['downloads'] );
				$GLOBALS['it_exchange']['download'] = false;
				return false;
			}
		}
	}

	/**
	 * Returns the buy now or add_to_cart form. Or both.
	 *
	 * Options:
	 * - buy-now-before:          Gets added before the buy-now form
	 * - buy-now-after:           Gets added after the buy-now form
	 * - buy-now-class:           A CSS class applied to the buy-now button
	 * - buy-now-label:           The HTML value of the buy now button.
	 * - buy-now-button-type:     The button-type: submit or button. Default is submit
	 * - buy-now-button-name:     The default is false. No name attribute is provided when false
	 * - add-to-cart-before:      Gets added before the buy-now form
	 * - add-to-cart-after:       Gets added after the buy-now form
	 * - add-to-cart-class:       A CSS class applied to the buy-now button
	 * - add-to-cart-label:       The HTML value of the buy now button.
	 * - add-to-cart-button-type: The button-type: submit or button. Default is submit
	 * - add-to-cart-button-name: The default is false. No name attribute is provided when false
	 *
	 *
	 * @return string
	*/
	function purchase_options( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;

		$defaults      = array(
			'type'                      => false,
			'class'                     => false,
			'buy-now-before'            => '',
			'buy-now-after'             => '',
			'buy-now-class'             => false,
			'buy-now-label'             => __( 'Buy Now', 'it-l10n-ithemes-exchange' ),
			'buy-now-button-type'       => 'submit',
			'buy-now-button-name'       => false,
			'buy-now-edit-quantity'     => true,
			'add-to-cart-before'        => '',
			'add-to-cart-after'         => '',
			'add-to-cart-class'         => false,
			'add-to-cart-label'         => __( 'Add to Cart', 'it-l10n-ithemes-exchange' ),
			'add-to-cart-button-type'   => 'submit',
			'add-to-cart-button-name'   => false,
			'add-to-cart-edit-quantity' => true,
			'out-of-stock-text'         => __( 'Product is currently out of stock.', 'it-l10n-ithemes-exchange' ),
			'not-available-text'        => __( 'Product is currently not available.', 'it-l10n-ithemes-exchange' ),
            'no-guest-purchase-text'    => __( 'This product cannot be purchased by a guest customer.', 'it-l10n-ithemes-exchange' ),
			'product-in-stock'          => null,
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// If we are tracking inventory, lets make sure we have some available
        if ( $options['product-in-stock'] !== null ) {
            $product_in_stock = (bool) $options['product-in-stock'];
        } elseif ( it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ) {
			$product_in_stock = it_exchange_product_has_feature( $this->product->ID, 'inventory' );
		} else {
			$product_in_stock = true;
		}

		/**
		 * Filter whether the product purchase options should display as in stock.
         *
         *
         *
         * @param bool                 $product_in_stock
         * @param \IT_Exchange_Product $product
		 */
		$product_in_stock = apply_filters( 'ninja_shop_purchase_options_product_in_stock', $product_in_stock, $this->product );

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'get-is-available' );

		// Do we have multi-item cart add-on enabled?
		$multi_item_cart = it_exchange_is_multi_item_cart_allowed();

		$output = '';

		if ( ! $product_in_stock ) {
			return '<p class="out-of-stock">' . $options['out-of-stock-text'] . '</p>';
		}

		if ( ! $product_is_available ) {
			return '<p class="not-availabe">' . $options['not-available-text'] . '</p>';
		}

		$item = ITE_Cart_Product::create( $this->product );
		$cart = it_exchange_get_current_cart();

		if ( ! it_exchange_can_line_item_be_purchased_by_guest( $item, $cart ) && it_exchange_doing_guest_checkout() ) {
		    return '<p class="no-guest-purchase">' . $options['no-guest-purchase-text'] . '</p>';
        }

		$class = $options['class'];

		// Set add-to-cart options
		$options['before']        = $options['add-to-cart-before'];
		$options['after']         = $options['add-to-cart-after'];
		$options['class']         = $class . ' ' . $options['add-to-cart-class'];
		$options['label']         = $options['add-to-cart-label'];
		$options['button-type']   = $options['add-to-cart-button-type'];
		$options['button-name']   = $options['add-to-cart-button-name'];
		$options['edit-quantity'] = $options['add-to-cart-edit-quantity'];

		// Add add-to-cart form to output if multicart and/or template asked for it.
		if ( $multi_item_cart
			&& ( empty( $options['type'] ) || 'add-to-cart' == $options['type'] ) )
			$output .= it_exchange( 'product', 'get-add-to-cart', $options );

		// Set buy-now options
		$options['before']        = $options['buy-now-before'];
		$options['after']         = $options['buy-now-after'];
		$options['class']         = $class . ' ' . $options['buy-now-class'];
		$options['label']         = $options['buy-now-label'];
		$options['button-type']   = $options['buy-now-button-type'];
		$options['button-name']   = $options['buy-now-button-name'];
		$options['edit-quantity'] = $options['buy-now-edit-quantity'];

		// Add add-to-cart form to output if not multicart or is multicart and no products in cart
		// and/or template asked for it
		if ( ( ! $multi_item_cart || ( $multi_item_cart && 0 === it_exchange_get_cart_products_count() ) )
			 && ( empty( $options['type'] ) || 'buy-now' == $options['type'] ) )
			$output .= it_exchange( 'product', 'get-buy-now', $options );

		// Return output
		return $output;
	}

	/**
	 * Outputs the super widget.
	 *
	 * Options:
	 * - class : HTML Class to add to div surrounding the super widget
	 *
	 *
	 * @params array $options
	 * @return void
	*/
	function superwidget( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] ) {
			return true;
		}

		// Return boolean if has flag was set
		if ( $options['has'] ) {
			return true;
		}

		// Parse options
		$result        = false;

		$defaults      = array(
			'class'    => false,
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		$args['before_widget'] = '<div class="ninja-shop-product-sw single-product-super-widget ' . esc_attr( $options['class'] ) . '">';
		$args['after_widget'] = '</div>';
		$args['enqueue_hide_script'] = false;

		the_widget( 'IT_Exchange_Super_Widget', array(), $args );

	}

	function buy_now( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;

		// Setting this filter to true will disable the Buy Now Button
		if ( apply_filters( 'ninja_shop_disable_buy_now', false, $this->product ) )
			return '';

		// Parse options
		$result        = false;

		$defaults      = array(
			'before'              => '',
			'after'               => '',
			'class'               => false,
			'label'               => __( 'Buy Now', 'it-l10n-ithemes-exchange' ),
			'button-type'         => 'submit',
			'button-name'         => false,
			'out-of-stock-text'   => __( 'Out of stock.', 'it-l10n-ithemes-exchange' ),
			'not-available-text'  => __( 'Product not available right now.', 'it-l10n-ithemes-exchange' ),
			'edit-quantity'       => true,
			'product-in-stock'    => null,
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// Allow options to be filtered
		$options = apply_filters( 'ninja_shop_product_theme_api_buy_now_options', $options, $this->product->ID );

		// If we are tracking inventory, lets make sure we have some available
		if ( $options['product-in-stock'] !== null ) {
			$product_in_stock = (bool) $options['product-in-stock'];
		} elseif ( it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ) {
			$product_in_stock = it_exchange_product_has_feature( $this->product->ID, 'inventory' );
		} else {
			$product_in_stock = true;
		}

		// This filter is documented in api/theme/product.php
		$product_in_stock = apply_filters( 'ninja_shop_purchase_options_product_in_stock', $product_in_stock, $this->product );

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'get-is-available' );

		$output = '';

		$class          = empty( $options['class'] ) ? 'buy-now-button' : 'buy-now-button ' . esc_attr( $options['class'] );
		$var_key        = it_exchange_get_field_name( 'buy_now' );
		$var_value      = $this->product->ID;
		$button_name    = empty( $options['button-name'] ) ? '' : ' name="' . esc_attr( $options['button-name'] ) . '"';
		$button         = '<input' . $button_name . ' type="' . esc_attr( $options['button-type'] ) . '" value="' . esc_attr( $options['label'] ) . '" class="' . esc_attr( $class ) . '" />';
		$hidden_fields  = '<input type="hidden" name="it-exchange-action" value="buy_now" />';
		$hidden_fields .= '<input class="buy-now-product-id" type="hidden" name="' . esc_attr( $var_key ). '" value="' . esc_attr( $var_value ). '" />';
		$hidden_fields .= wp_nonce_field( 'ninja-shop-purchase-product-' . $this->product->ID, '_wpnonce', true, false );

		if ( ! $product_in_stock )
			return '<p class="out-of-stock">' . $options['out-of-stock-text'] . '</p>';

		if ( ! $product_is_available )
			return '<p>' . $options['not-available-text'] . '</p>';

		$result  = '<form action="" method="post" class="ninja-shop-sw-purchase-options ninja-shop-sw-buy-now ' . esc_attr( $class ) . '">';
		$result .= $hidden_fields;

		if ( $options['edit-quantity'] )
			$result .= it_exchange( 'product', 'get-purchase-quantity' );

		$result .= $button;
		$result .= '</form>';

		return $result;
	}

	function add_to_cart( $options=array() ) {

		// Return boolean if has flag was set. Just keeping this here since its in all other product.php methods
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;

		// Setting this filter to true will disable the Buy Now Button
		if ( apply_filters( 'ninja_shop_disable_add_to_cart', false, $this->product ) )
			return '';

		// Parse options
		$result        = false;

		$defaults      = array(
			'before'              => '',
			'after'               => '',
			'class'               => false,
			'label'               => __( 'Add to Cart', 'it-l10n-ithemes-exchange' ),
			'button-type'         => 'submit',
			'button-name'         => false,
			'out-of-stock-text'   => __( 'Out of stock.', 'it-l10n-ithemes-exchange' ),
			'not-available-text'  => __( 'Product not available right now.', 'it-l10n-ithemes-exchange' ),
			'edit-quantity'       => true,
			'max-quantity-text'   => __( 'Max Quantity Reached', 'it-l10n-ithemes-exchange' ),
			'product-in-stock'    => null,
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		// If we are tracking inventory, lets make sure we have some available
		if ( $options['product-in-stock'] !== null ) {
			$product_in_stock = (bool) $options['product-in-stock'];
		} elseif ( it_exchange_product_supports_feature( $this->product->ID, 'inventory' ) ) {
			$product_in_stock = it_exchange_product_has_feature( $this->product->ID, 'inventory' );
		} else {
			$product_in_stock = true;
		}

		// This filter is documented in api/theme/product.php
		$product_in_stock = apply_filters( 'ninja_shop_purchase_options_product_in_stock', $product_in_stock, $this->product );

		// If we're supporting availability dates, check that
		$product_is_available = it_exchange( 'product', 'get-is-available' );

		// Do we have multi-item cart add-on enabled?
		$multi_item_cart = it_exchange_is_multi_item_cart_allowed();

		// Init empty hidden field variables
		$buy_now_hidden_fields = $add_to_cart_hidden_fields = '';

		$class          = empty( $options['class'] ) ? 'add-to-cart-button' : 'add-to-cart-button ' . esc_attr( $options['class'] );
		$var_key        = it_exchange_get_field_name( 'add_product_to_cart' );
		$var_value      = $this->product->ID;
		$button_name    = empty( $options['button-name'] ) ? '' : ' name="' . esc_attr( $options['button-name'] ) . '"';
		$button         = '<input' . $button_name . ' type="' . esc_attr( $options['button-type'] ) . '" value="' . esc_attr( $options['label'] ) . '" class="' . esc_attr( $class ) . '" />';
		$hidden_fields  = '<input type="hidden" name="it-exchange-action" value="add_product_to_cart" />';
		$hidden_fields .= '<input class="add-to-cart-product-id" type="hidden" name="' . esc_attr( $var_key ). '" value="' . esc_attr( $var_value ). '" />';
		$hidden_fields .= wp_nonce_field( 'ninja-shop-purchase-product-' . $this->product->ID, '_wpnonce', true, false );


		if ( it_exchange_product_supports_feature( $this->product->ID, 'purchase-quantity' ) && it_exchange_product_has_feature( $this->product->ID, 'purchase-quantity' ) ) {

			$quantity = it_exchange_get_cart_product_quantity_by_product_id( $this->product->ID );
			$max_quantity = it_exchange_get_product_feature( $this->product->ID, 'purchase-quantity' );

			if ( $quantity < $max_quantity )
				$can_add_more = true;
			else
				$can_add_more = false;

		} else {

			$can_add_more = true;

		}

		if ( !$can_add_more )
			return '<p>' . $options['max-quantity-text'] . '</p>';

		if ( ! $product_in_stock )
			return '<p class="out-of-stock">' . $options['out-of-stock-text'] . '</p>';

		if ( ! $product_is_available )
			return '<p>' . $options['not-available-text'] . '</p>';

		if ( ! $multi_item_cart )
			return '';

		$result  = '<form action="" method="post" class="ninja-shop-sw-purchase-options ninja-shop-sw-add-to-cart ' . esc_attr( $class ) . '">';
		$result .= $hidden_fields;

		if ( $options['edit-quantity'] )
			$result .= it_exchange( 'product', 'get-purchase-quantity' );

		$result .= $button;
		$result .= '</form>';

		return $result;
	}

	/**
	 * Returns a buy_now var
	 *
	 *
	 *
	 * @param array $options
	 * @return string
	*/
	function buy_now_var( $options ) {

		// Return boolean if has flag was set.
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;

		// Parse options
		$defaults      = array(
			'format'      => 'key',
		);
		$options   = ITUtility::merge_defaults( $options, $defaults );

		if ( 'key' == $format )
			return it_exchange_get_field_name( 'buy_now' );
		else
			return $this->product->ID;
	}

	/**
	 * Returns a add_to_cart var
	 *
	 *
	 *
	 * @param array $options
	 * @return string
	*/
	function add_to_cart_var( $options ) {

		// Return boolean if has flag was set.
		if ( $options['supports'] )
			return true;

		// Return boolean if has flag was set
		if ( $options['has'] )
			return true;

		// Parse options
		$defaults = array(
			'format' => 'key',
		);
		$options = ITUtility::merge_defaults( $options, $defaults );

		if ( 'key' == $format )
			return it_exchange_get_field_name( 'add_product_to_cart' );
		else
			return $this->product->ID;
	}
}
