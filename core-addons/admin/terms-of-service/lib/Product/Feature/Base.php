<?php
/**
 * Base product feature.
 *
 * @author iThemes
 * 
 */

namespace ITETOS\Product\Feature;

/**
 * Class Base
 * @package ITETOS\Product\Feature
 */
class Base extends \IT_Exchange_Product_Feature_Abstract {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$args = array(
			'slug'          => 'terms-of-service',
			'description'   => __( 'Add an additional set of terms to the Terms of Use if this product is in the cart.', 'it-l10n-ithemes-exchange' ),
			'metabox_title' => __( 'Terms of Service', 'it-l10n-ithemes-exchange' )
		);

		parent::__construct( $args );
	}

	/**
	 * This echos the feature metabox.
	 *
	 *
	 *
	 * @param \WP_Post $post
	 */
	function print_metabox( $post ) {
		$data = it_exchange_get_product_feature( isset( $post->ID ) ? $post->ID : 0, $this->slug );
		?>

		<p><?php echo $this->description; ?></p>

		<div class="itetos-settings">

			<?php wp_editor( $data['terms'], 'itetos-terms', array(
				'teeny'         => true,
				'media_buttons' => false,
				'textarea_rows' => 7
			) ); ?>
		</div>
		<?php
	}

	/**
	 * This saves the value.
	 *
	 *
	 */
	public function save_feature_on_product_save() {

		// Abort if we don't have a product ID
		$product_id = empty( $_POST['ID'] ) ? false : absint( $_POST['ID'] );

		if ( ! $product_id ) {
			return;
		}

		$data['terms'] = sanitize_textarea_field( $_POST['itetos-terms'] );

		it_exchange_update_product_feature( $product_id, $this->slug, $data );
	}

	/**
	 * This updates the feature for a product
	 *
	 *
	 *
	 * @param integer $product_id the product id
	 * @param array   $new_value  the new value
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function save_feature( $product_id, $new_value, $options = array() ) {

		$prev_values = it_exchange_get_product_feature( $product_id, $this->slug );
		$values      = \ITUtility::merge_defaults( $new_value, $prev_values );

		return update_post_meta( $product_id, '_it_exchange_itetos_product_terms', $values );
	}

	/**
	 * Return the product's features
	 *
	 *
	 *
	 * @param mixed   $existing   the values passed in by the WP Filter API. Ignored here.
	 * @param integer $product_id the WordPress post ID
	 * @param array   $options
	 *
	 * @return string product feature
	 */
	public function get_feature( $existing, $product_id, $options = array() ) {
		$defaults = array(
			'terms' => ''
		);

		$values   = get_post_meta( $product_id, '_it_exchange_itetos_product_terms', true ) ?: array();
		$raw_meta = \ITUtility::merge_defaults( $values, $defaults );

		if ( ! isset( $options['field'] ) ) { // if we aren't looking for a particular field
			return $raw_meta;
		}

		$field = $options['field'];

		if ( isset( $raw_meta[ $field ] ) ) { // if the field exists with that name just return it
			return $raw_meta[ $field ];
		} else if ( strpos( $field, "." ) !== false ) { // if the field name was passed using array dot notation
			$pieces  = explode( '.', $field );
			$context = $raw_meta;
			foreach ( $pieces as $piece ) {
				if ( ! is_array( $context ) || ! array_key_exists( $piece, $context ) ) {
					// error occurred
					return null;
				}
				$context = &$context[ $piece ];
			}

			return $context;
		} else {
			return null; // we didn't find the data specified
		}
	}

	/**
	 * Does the product have the feature?
	 *
	 *
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	public function product_has_feature( $result, $product_id, $options = array() ) {
		return trim( it_exchange_get_product_feature( $product_id, $this->slug, array( 'field' => 'terms' ) ) ) != '';
	}

	/**
	 * Does the product support this feature?
	 *
	 * This is different than if it has the feature, a product can
	 * support a feature but might not have the feature set.
	 *
	 *
	 *
	 * @param mixed   $result Not used by core
	 * @param integer $product_id
	 * @param array   $options
	 *
	 * @return boolean
	 */
	function product_supports_feature( $result, $product_id, $options = array() ) {
		$product_type = it_exchange_get_product_type( $product_id );

		return it_exchange_product_type_supports_feature( $product_type, $this->slug );
	}
}
