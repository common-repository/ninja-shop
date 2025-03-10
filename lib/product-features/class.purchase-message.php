<?php
/**
 * This will control email messages with any product types that register email message support.
 * By default, it registers a metabox on the product's add/edit screen and provides HTML / data for the frontend.
 *
 * 
 * @package IT_Exchange
*/


class IT_Exchange_Product_Feature_Purchase_Message {

	/**
	 * Constructor. Registers hooks
	 *
	 *
	*/
	function __construct() {
		if ( is_admin() ) {
			add_action( 'load-post-new.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'load-post.php', array( $this, 'init_feature_metaboxes' ) );
			add_action( 'ninja_shop_save_product', array( $this, 'save_feature_on_product_save' ) );
		}
		add_action( 'ninja_shop_enabled_addons_loaded', array( $this, 'add_feature_support_to_product_types' ) );
		add_action( 'ninja_shop_update_product_feature_purchase-message', array( $this, 'save_feature' ), 9, 2 );
		add_filter( 'ninja_shop_get_product_feature_purchase-message', array( $this, 'get_feature' ), 9, 2 );
		add_filter( 'ninja_shop_product_has_feature_purchase-message', array( $this, 'product_has_feature') , 9, 2 );
		add_filter( 'ninja_shop_product_supports_feature_purchase-message', array( $this, 'product_supports_feature') , 9, 2 );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Product_Feature_Purchase_Message() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Register the product feature and add it to enabled product-type addons
	 *
	 *
	*/
	function add_feature_support_to_product_types() {
		// Register the product feature
		$slug        = 'purchase-message';
		$description = 'Purchase Message associated with a product';
		it_exchange_register_product_feature( $slug, $description );

		// Add it to all enabled product-type addons
		$products = it_exchange_get_enabled_addons( array( 'category' => 'product-type' ) );
		foreach( $products as $key => $params ) {
			it_exchange_add_feature_support_to_product_type( 'purchase-message', $params['slug'] );
		}
	}

	/**
	 * Register's the metabox for any product type that supports the feature
	 *
	 *
	 * @return void
	*/
	function init_feature_metaboxes() {

		global $post;

		if ( isset( $_REQUEST['post_type'] ) ) {
			$post_type = sanitize_text_field( $_REQUEST['post_type'] );
		} else {
			if ( isset( $_REQUEST['post'] ) )
				$post_id = (int) $_REQUEST['post'];
			elseif ( isset( $_REQUEST['post_ID'] ) )
				$post_id = (int) $_REQUEST['post_ID'];
			else
				$post_id = 0;

			if ( $post_id )
				$post = get_post( $post_id );

			if ( isset( $post ) && !empty( $post ) )
				$post_type = $post->post_type;
		}

		if ( !empty( $_REQUEST['it-exchange-product-type'] ) )
			$product_type = sanitize_text_field( $_REQUEST['it-exchange-product-type'] );
		else
			$product_type = it_exchange_get_product_type( $post );

		if ( !empty( $post_type ) && 'it_exchange_prod' === $post_type ) {
			if ( !empty( $product_type ) &&  it_exchange_product_type_supports_feature( $product_type, 'purchase-message' ) )
				add_action( 'ninja_shop_product_metabox_callback_' . $product_type, array( $this, 'register_metabox' ) );
		}

	}

	/**
	 * Registers the feature metabox for a specific product type
	 *
	 * Hooked to it_exchange_product_metabox_callback_[product-type] where product type supports the feature
	 *
	 *
	 * @return void
	*/
	function register_metabox() {
		add_meta_box( 'it-exchange-product-purchase-message', __( 'Purchase Message', 'it-l10n-ithemes-exchange' ), array( $this, 'print_metabox' ), 'it_exchange_prod', 'normal' );
	}

	/**
	 * This echos the feature metabox.
	 *
	 *
	 * @return void
	*/
	function print_metabox( $post ) {
		// Grab the Ninja Shop Product object from the WP $post object
		$product = it_exchange_get_product( $post );

		// Set the value of the feature for this product
		$product_feature_value = it_exchange_get_product_feature( $product->ID, 'purchase-message' );

		// Set description
		$description = __( 'Any text placed here will be appended to the receipt when this product is purchased. You might want to use this to add special instructions for particular products.', 'it-l10n-ithemes-exchange' );
		$description = apply_filters( 'ninja_shop_product_purchase-message_metabox_description', $description );

		?>
			<?php if ( $description ) : ?>
				<p class="intro-description"><?php echo $description; ?></p>
			<?php endif; ?>
			<p>
				<textarea name="it-exchange-product-purchase-message"><?php echo esc_textarea( $product_feature_value ); ?></textarea>
			</p>
		<?php
	}

	/**
	 * This saves the value
	 *
	 *
	 * @param object $post wp post object
	 * @return void
	*/
	function save_feature_on_product_save() {
		// Abort if we can't determine a product type
		if ( ! $product_type = it_exchange_get_product_type() )
			return;

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : absint( $_POST['ID'] );
		if ( ! $product_id )
			return;

		// Abort if this product type doesn't support this feature
		if ( ! it_exchange_product_type_supports_feature( $product_type, 'purchase-message' ) )
			return;

		// Abort if key for feature option isn't set in POST data
		if ( ! isset( $_POST['it-exchange-product-purchase-message'] ) )
			return;

		// Get new value from post
		$new_value = sanitize_textarea_field( $_POST['it-exchange-product-purchase-message'] );

		// Save new value
		it_exchange_update_product_feature( $product_id, 'purchase-message', $new_value );
	}

	/**
	 * This updates the feature for a product
	 *
	 *
	 * @param integer $product_id the product id
	 * @param mixed $new_value the new value
	 * @return bolean
	*/
	function save_feature( $product_id, $new_value ) {
		update_post_meta( $product_id, 'it-exchange-product-purchase-message', $new_value );
	}

	/**
	 * Return the product's features
	 *
	 *
	 * @param mixed $existing the values passed in by the WP Filter API. Ignored here.
	 * @param integer product_id the WordPress post ID
	 * @return string product feature
	*/
	function get_feature( $existing, $product_id ) {
		$value = get_post_meta( $product_id, 'it-exchange-product-purchase-message', true );
		return $value;
	}

	/**
	 * Does the product have the feature?
	 *
	 *
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_has_feature( $result, $product_id ) {
		// Does this product type support this feature?
		if ( false === $this->product_supports_feature( false, $product_id ) )
			return false;
		return (boolean) $this->get_feature( false, $product_id );
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 *
	 * @param mixed $result Not used by core
	 * @param integer $product_id
	 * @return boolean
	*/
	function product_supports_feature( $result, $product_id ) {
		// Does this product type support this feature?
		$product_type = it_exchange_get_product_type( $product_id );
		return it_exchange_product_type_supports_feature( $product_type, 'purchase-message' );
	}
}
$IT_Exchange_Product_Feature_Purchase_Message = new IT_Exchange_Product_Feature_Purchase_Message();
