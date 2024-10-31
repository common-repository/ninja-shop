<?php
/**
 * Contains shortcodes.
 *
 * @author    iThemes
 * 
 */

/**
 * Class IT_Exchange_Shortcodes
 */
class IT_Exchange_SW_Shortcode {

	/**
	 * @var \IT_Exchange_Product
	 */
	private $product;

	/**
	 * @var array
	 */
	private $hide_parts = array();

	/**
	 * @var array
	 */
	private $add_parts = array();

	/**
	 * IT_Exchange_Shortcodes constructor.
	 */
	public function __construct() {
		add_shortcode( 'ninja_shop_sw', array( $this, 'callback' ) );
		add_shortcode( 'it_exchange_sw', array( $this, 'callback' ) );
		add_action( 'ninja_shop_enabled_addons_loaded', array( $this, 'register_feature' ) );
		add_action( 'media_buttons', array( $this, 'insert_button' ) );
		add_action( 'admin_footer', array( $this, 'thickbox' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Check if this page has the shortcode in it.
	 *
	 *
	 *
	 * @return bool
	 */
	public static function has_shortcode() {

		global $post;

		return ( $post && has_shortcode( $post->post_content, 'ninja_shop_sw' ) );
	}

	/**
	 * Register the feature with Exchange.
	 *
	 * @sine 1.32
	 */
	public function register_feature() {

		$desc = __( 'Allows products to be embedded in a shortcode.', 'it-l10n-ithemes-exchange' );

		it_exchange_register_product_feature( 'sw-shortcode', $desc );
	}

	/**
	 * Insert the embed a SW button.
	 *
	 * This is embedded only for public post types, and NOT for products.
	 *
	 *
	 */
	public function insert_button() {

		// media buttons can be displayed on the front-end.
		// bail if we aren't in the admin
		if ( ! is_admin() ) {
			return;
		}

		$screen = get_current_screen();

		$post_type = ! empty( $screen->post_type ) ? $screen->post_type : get_post_type();

		/**
		 * Filter whether to show the Embed Super Widget button.
		 *
		 *
		 *
		 * @param bool   $show
		 * @param string $post_type
		 */
		if ( ! apply_filters( 'ninja_shop_show_embed_sw_button', true, $post_type ) ) {
			return;
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( $post_type_object && $post_type_object->public && $post_type != 'it_exchange_prod' ) {
			add_thickbox();
			$id    = 'it-exchange-insert-sw-shortcode';
			$class = 'thickbox button it-exchange-insert-sw-shortcode';
			$title = __( "Embed Super Widget", 'it-l10n-ithemes-exchange' );
			echo '<a href="#TB_inline?width=150height=250&inlineId=' . $id . '" class="' . $class . '" title="' . $title . '"> ' . $title . '</a>';
		}
	}

	/**
	 * Enqueue scripts for the product dropdown.
     *
     *
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();

		if ( $screen && $screen->base !== 'post' && $screen->base !== 'add' ) {
			return;
		}

		$post_type = ! empty( $screen->post_type ) ? $screen->post_type : get_post_type();

		// This filter is documented in lib/shortcodes/shortcodes.php
		if ( ! apply_filters( 'ninja_shop_show_embed_sw_button', true, $post_type ) ) {
			return;
		}

		wp_enqueue_script( 'it-exchange-select2' );
		wp_enqueue_style( 'it-exchange-select2' );
    }

	/**
	 * Render the thickbox for inserting a SW shortcode.
	 *
	 *
	 */
	public function thickbox() {

		$screen = get_current_screen();

		if ( $screen && $screen->base !== 'post' && $screen->base !== 'add' ) {
		    return;
        }

		$post_type = ! empty( $screen->post_type ) ? $screen->post_type : get_post_type();

		// This filter is documented in lib/shortcodes/shortcodes.php
		if ( ! apply_filters( 'ninja_shop_show_embed_sw_button', true, $post_type ) ) {
			return;
		}

		$post_type = get_post_type_object( $post_type );

		if ( ! $post_type || ! $post_type->public || $post_type->name === 'it_exchange_prod' ) {
			return;
		}

		$product_types = it_exchange_get_addons( array(
			'category' => 'product-type'
		) );

		foreach ( $product_types as $product_type => $addon ) {
			if ( ! it_exchange_product_type_supports_feature( $product_type, 'sw-shortcode' ) ) {
				unset( $product_types[ $product_type ] );
			}
		}
		?>

		<script type="text/javascript">
			jQuery(document).ready(function ($) {

				var $select = $( '#it-exchange-sw-product' );
				$select.select2( {
					ajax: {
						url     : '<?php echo esc_js( rest_url( 'it_exchange/v1/products/' ) ); ?>',
						dataType: 'json',

						beforeSend: function ( xhr ) {
							xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>' );
						},

						data: function ( params ) {
							return {
								search : params.term,
								context: 'embed',
								type   : <?php echo wp_json_encode( array_keys( $product_types ) ) ?>,
							}
						},

						processResults: function ( response ) {
							var results = [];

							for ( var i = 0; i < response.length; i++ ) {
								results[i] = {
									id  : response[i].id,
									text: response[i].title
								}
							}

							return {
								results: results
							}
						},
					},

					minimumInputLength: 3,
				} );

				$(document).on('click', '#it-exchange-sw-insert', function (e) {
					var prod = $select.val();

					if (prod.length === 0) {
						alert("<?php echo esc_js( __( 'You must select a product', 'it-l10n-ithemes-exchange' ) ); ?>");
						return;
					}

					var desc = '', title = '';

					if ($("#it-exchange-sw-description").is(':checked')) {
						desc = ' description="yes"';
					}

					if ($("#it-exchange-sw-title").is(':checked')) {
						title = ' title="yes"';
					}

					var short = '[ninja_shop_sw product="' + prod + '"' + desc + title + ']';

					window.send_to_editor(short);
					tb_remove();
				});
			});
		</script>

		<?php
		// @NOTE Conflict between Thickbox and Select2.
		?>
		<style media="screen">
			.select2-container--open {
				z-index: 999999; /* Bring select2 above Thickbox. */
			}
			#it-exchange-insert-sw-shortcode select {
				width: 200px; /* Static width to avoid collapse. */
			}
		</style>
		<div id="it-exchange-insert-sw-shortcode" style="display: none">
			<div class="wrap">

				<div>
					<label for="it-exchange-sw-product"><?php _e( 'Select a Product', 'it-l10n-ithemes-exchange' ); ?></label><br>
					<select id="it-exchange-sw-product"></select>

					<br><br>

					<input type="checkbox" id="it-exchange-sw-title">
					<label for="it-exchange-sw-title">
						<?php _e( 'Include product title?', 'it-l10n-ithemes-exchange' ); ?>
					</label>

					<br><br>

					<input type="checkbox" id="it-exchange-sw-description">
					<label for="it-exchange-sw-description">
						<?php _e( 'Include product description?', 'it-l10n-ithemes-exchange' ); ?>
					</label>
				</div>

				<div style="padding: 15px 15px 15px 0">
					<input type="button" class="button-primary" id="it-exchange-sw-insert" value="<?php _e( 'Embed', 'it-l10n-ithemes-exchange' ); ?>" />
					&nbsp;&nbsp;&nbsp;
					<a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;">
						<?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?>
					</a>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 *
	 * Super widget shortcode callback.
	 *
	 *
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function callback( $atts ) {

		if ( $this->product ) {
			if ( current_user_can( 'edit_it_product', $GLOBALS['post']->ID ) ) {
				return __( "Only one Super Widget can be embedded per-page.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		$atts = shortcode_atts( array( 'product' => null, 'description' => 'no', 'title' => 'no' ), $atts, 'ninja_shop_sw' );

		$product = it_exchange_get_product( $atts['product'] );

		if ( ! $product ) {
			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "Invalid product ID.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		if ( ! it_exchange_product_type_supports_feature( it_exchange_get_product_type( $product->ID ), 'sw-shortcode' ) ) {

			if ( current_user_can( 'edit_post', $GLOBALS['post']->ID ) ) {
				return __( "This product does not support being embedded in shortcodes.", 'it-l10n-ithemes-exchange' );
			}

			return '';
		}

		if ( is_archive() || is_home() ) {
			return '';
		}

		it_exchange_set_product( $product->ID );
		$this->product = $product;

		if ( $atts['description'] !== 'yes' ) {
			$this->hide_parts[] = 'description';
		}

		if ( $atts['title'] === 'yes' ) {
			$this->add_parts[] = 'title';
		}

		add_filter( 'ninja_shop_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		add_filter( 'ninja_shop_get_content_product_product_info_loop_elements', array( $this, 'modify_templates' ) );
		add_filter( 'ninja_shop_super_widget_args', array( $this, 'prevent_hide_css' ) );
		add_filter( 'ninja_shop_api_theme_product_title_options', array( $this, 'modify_product_title_tag' ) );

		ob_start();

		it_exchange_get_template_part( 'content-product/loops/product-info' );

		$html = ob_get_clean();

		remove_filter( 'ninja_shop_super_widget_empty_product_id', array( $this, 'set_sw_product_id' ) );
		remove_filter( 'ninja_shop_get_content_product_product_info_loop_elements', array( $this, 'modify_templates' ) );
		remove_filter( 'ninja_shop_api_theme_product_title_options', array( $this, 'modify_product_title_tag' ) );

		return $html;
	}

	/**
	 * Set the product ID for use in the SW when no product found.
	 *
	 *
	 *
	 * @param int $product
	 *
	 * @return int|bool
	 */
	public function set_sw_product_id( $product ) {

		if ( $this->product ) {
			return $this->product->ID;
		}

		return $product;
	}

	/**
	 * Modify template parts.
	 *
	 *
	 *
	 * @param array $parts
	 *
	 * @return array
	 */
	public function modify_templates( $parts ) {

		if ( $this->add_parts ) {
			$parts = array_merge( $this->add_parts, $parts );
		}

		foreach ( $this->hide_parts as $part ) {

			$index = array_search( $part, $parts );

			if ( $index !== false ) {
				unset( $parts[ $index ] );
			}
		}

		return $parts;
	}

	/**
	 * Prevent the SW from enqueuing the css to hide itself when the sidebar is being used.
	 *
	 *
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function prevent_hide_css( $args ) {
		$args['enqueue_hide_script'] = false;

		return $args;
	}

	/**
	 * Modify the product title tag.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function modify_product_title_tag( $options ) {
		$options['wrap'] = 'h2';

		return $options;
	}
}

new IT_Exchange_SW_Shortcode();
