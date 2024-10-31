<?php
/**
 * Creates the post type for Coupons
 *
 * @package IT_Exchange
 * 
*/

/**
 * Registers the it_exchange_coupon post type
 *
 *
*/
class IT_Exchange_Coupon_Post_Type {

	public $post_type;

	/**
	 * Class Constructor
	 *
	 *
	*/
	function __construct() {
		$this->init();
		add_action( 'save_post', array( $this, 'save_coupon' ) );
		add_filter( 'disable_months_dropdown', array( $this, 'disable_months_dropdown' ), 10, 2 );
		add_filter( 'default_hidden_columns', array( $this, 'hide_customer_column' ), 10, 2 );
		add_filter( 'view_mode_post_types', array( $this, 'remove_view_modes' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	function IT_Exchange_Coupon_Post_Type() {

		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	function init() {
		$this->post_type = 'it_exchange_coupon';
		$labels    = array(
			'name'               => __( 'Coupons', 'it-l10n-ithemes-exchange' ),
			'singular_name'      => __( 'Coupon', 'it-l10n-ithemes-exchange' ),
			'edit_item'          => __( 'Edit Coupon', 'it-l10n-ithemes-exchange' ),
			'search_items'       => __( 'Search Coupons', 'it-l10n-ithemes-exchange' ),
			'not_found'          => __( 'No coupons found.', 'it-l10n-ithemes-exchange' ),
			'not_found_in_trash' => __( 'No coupons found in Trash.', 'it-l10n-ithemes-exchange' )
		);

		// We're not going to add this to the menu. Individual add-ons will need to do that.
		$this->options = array(
			'labels'               => $labels,
			'description'          => __( 'An Ninja Shop Post Type for storing all Coupons in the system', 'it-l10n-ithemes-exchange' ),
			'public'               => false,
			'show_ui'              => true,
			'show_in_nav_menus'    => false,
			'show_in_menu'         => false,
			'show_in_admin_bar'    => false,
			'hierarchical'         => false,
			'register_meta_box_cb' => array( $this, 'meta_box_callback' ),
			'supports'             => array( // We'll register them all but the core add-on for basic coupons isn't using WP add/edit screen.
				'title',
				'editor',
				'author',
				'thumbnail',
				'excerpt',
				'trackbacks',
				'custom-fields',
				'comments',
				'revisions',
				'post-formats',
			),
			'map_meta_cap'         => true,
			'capability_type'      => IT_Exchange_Capabilities::COUPON,
		);

		add_action( 'init', array( $this, 'register_the_post_type' ) );
	}

	/**
	 * Actually registers the post type
	 *
	 *
	 * @return void
	*/
	function register_the_post_type() {
		register_post_type( $this->post_type, $this->options );
	}

	/**
	 * Callback hook for coupon post type admin views
	 *
	 *
	 * @uses it_exchange_get_enabled_add_ons()
	 * @return void
	*/
	function meta_box_callback( $post ) {
		$coupon = it_exchange_get_coupon( $post );

		// Do action for any product type
		do_action( 'ninja_shop_coupon_metabox_callback', $coupon );
	}

	/**
	 * Provides specific hooks for when Ninja Shop coupons are saved.
	 *
	 * This method is hooked to save_post. It provides hooks for add-on developers
	 * that will only be called when the post being saved is an Ninja Shop coupon.
	 * It provides the following 4 hooks:
	 * - it_exchange_save_coupon_unvalidated // Runs every time an Ninja Shop coupon is saved.
	 * - it_exchange_save_coupon             // Runs every time an Ninja Shop coupon is saved if not an autosave and if user has permission to save post
	 *
	 *
	 * @return void
	*/
	function save_coupon( $post ) {

		// Exit if not it_exchange_prod post_type
		if ( ! 'it_exchange_coupon' == get_post_type( $post ) )
			return;

		// These hooks fire off any time a it_exchange_coupon post is saved w/o validations
		do_action( 'ninja_shop_save_coupon_unvalidated', $post );

		// Fire off actions with validations that most instances need to use.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		if ( ! current_user_can( 'edit_post', $post ) )
			return;

		// This is called any time save_post hook
		do_action( 'ninja_shop_save_coupon', $post );
	}

	/**
	 * Disable the months dropdown for coupons.
	 *
	 *
	 *
	 * @param bool $disabled
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function disable_months_dropdown( $disabled, $post_type ) {

		if  ( $post_type === $this->post_type ) {
			$disabled = true;
		}

		return $disabled;
	}

	/**
	 * Hide the coupon customer column by default.
	 *
	 *
	 *
	 * @param array     $hidden
	 * @param WP_Screen $screen
	 *
	 * @return array
	 */
	public function hide_customer_column( $hidden, $screen ) {

		if ( $screen->id == 'edit-it_exchange_coupon' ) {
			$hidden[] = 'it_exchange_coupon_customer';
		}

		return $hidden;
	}

	/**
	 * Remove the 'list' and 'excerpt' view mode from the screen options.
	 *
	 *
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function remove_view_modes( $post_types ) {
		unset( $post_types[ $this->post_type ] );

		return $post_types;
	}
}
$IT_Exchange_Coupon_Post_Type = new IT_Exchange_Coupon_Post_Type();
