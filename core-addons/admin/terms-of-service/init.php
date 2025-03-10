<?php
/**
 * Load the main plugin functions, hooks, and settings.
 *
 * @author iThemes
 * 
 */

namespace ITETOS;

/**
 * Class Plugin
 * @package ITETOS
 */
class Plugin {

	/**
	 * Plugin Version
	 */
	const VERSION = '1.0.0';

	/**
	 * Translation SLUG
	 */
	const SLUG = 'it-l10n-ithemes-exchange';

	/**
	 * Exchange add-on slug.
	 */
	const ADD_ON = 'terms-of-service';

	/**
	 * @var string
	 */
	static $dir;

	/**
	 * @var string
	 */
	static $url;

	/**
	 * Constructor.
	 */
	public function __construct() {
		self::$dir = plugin_dir_path( __FILE__ );
		self::$url = plugin_dir_url( __FILE__ );

		/*
		TODO: Not sure why these files weren't autoloaded.
		To get TOS working in the short-term, these files have been included manually.
		 */
		require( self::$dir . 'lib/Settings.php' );
		require( self::$dir . 'lib/Hooks.php' );
		require( self::$dir . 'lib/Product/Feature/Base.php' );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts_and_styles' ), 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts_and_styles' ), 5 );
	}

	/**
	 * Run the upgrade routine if necessary.
	 *
	 * @deprecated 2.0.0
	 */
	public static function upgrade() {
		// No-op
	}

	/**
	 * The activation hook.
	 */
	public function activate() {
		do_action( 'itetos_activate' );
	}

	/**
	 * The deactivation hook.
	 */
	public function deactivate() {

	}

	/**
	 * Register admin scripts.
	 *
	 *
	 */
	public function scripts_and_styles() {

		wp_register_script( 'itetos-checkout', self::$url . 'assets/js/checkout.js', array( 'jquery' ), self::VERSION );
		wp_register_script( 'itetos-sw', self::$url . 'assets/js/super-widget.js', array( 'jquery' ), self::VERSION );

		wp_register_style( 'itetos-checkout', self::$url . 'assets/css/checkout.css', array(), self::VERSION );
		wp_register_style( 'itetos-sw', self::$url . 'assets/css/super-widget.css', array(), self::VERSION );
	}
}

new Plugin();

if ( ! class_exists( 'ITETOS\Settings' ) ) {
	return;
}

use ITETOS\Product\Feature\Base;

Settings::init();

new Hooks();
new Base();
