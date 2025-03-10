<?php

/**
 * Exchange main class.
 *
 * @package IT_Exchange
 *
 */
class IT_Exchange {

	const VERSION = '1.1.11';

	const MIN_WP = '4.4.0';
	const MIN_PHP = '5.3.0';

	const NAME = 'Ninja Shop';
	const SLUG = 'ithemes-exchange';

	/** @var string */
	public static $dir;

	/** @var string */
	public static $url;

	/** @var string */
	public static $base;

	/**
	 * Setup the plugin
	 *
	 * Class Constructor. Sets up the environment and then loads admin or enqueues active bar.
	 *
	 *
	 */
	public function __construct() {

		$this->_version    = self::VERSION;
		$this->_wp_minimum = self::MIN_WP;

		// Setup Plugin
		$this->set_plugin_locations();
		$this->set_textdomain();

		// Load supporting libraries
		require( self::$dir . 'vendor/autoload.php' );
		require( self::$dir . 'lib/load.php' );
		require( self::$dir . 'api/load.php' );
		require( self::$dir . 'core-addons/load.php' );

		// Set version
		$GLOBALS['it_exchange']['version'] = self::VERSION;

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) || defined( 'WP_TESTS_TABLE_PREFIX' ) ) {
			$versions        = get_option( 'it-exchange-versions', false );

			if ( ! $versions ) {
				// if this is a new install, mark all our upgrades as completed
				require_once plugin_dir_path( __FILE__ ) . 'lib/upgrades/load.php';

				$upgrader = it_exchange_make_upgrader();

				foreach ( $upgrader->get_upgrades() as $upgrade ) {
					$upgrader->complete( $upgrade );
				}
			}

			$current_version = empty( $versions['current'] ) ? false : $versions['current'];

			if ( self::VERSION !== $current_version ) {
				$versions = array(
					'current'  => self::VERSION,
					'previous' => $current_version,
				);

				update_option( 'it-exchange-versions', $versions );
				do_action( 'ninja_shop_version_updated', $versions );
			}
		}

		do_action( 'ninja_shop_loaded' );
		add_action( 'ninja_shop_libraries_loaded', array( $this, 'addons_init' ) );
		// Remove the Ninja Shop ad from Ninja Forms.
		add_filter( 'ninja-forms-dashboard-promotions', array( $this, 'remove_nf_promotion' ) );
	}

	/**
	 * Deprecated PHP 4 style constructor.
	 *
	 * @deprecated
	 */
	public function IT_Exchange() {
		self::__construct();

		_deprecated_constructor( __CLASS__, '1.24.0' );
	}

	/**
	 * Defines where the plugin lives on the server
	 *
	 *
	 */
	public function set_plugin_locations() {

		self::$dir = plugin_dir_path( __FILE__ );
		self::$url = plugins_url( '', __FILE__ );
		self::$base = plugin_basename( __FILE__ );
	}

	/**
	 * Returns IT Exchange Plugin Path
	 *
	 *
	 *
	 * @return string
	 */
	public function get_plugin_path() {
		return self::$dir;
	}

	/**
	 * Loads the translation data for WordPress
	 *
	 *
	 */
	public function set_textdomain() {
		$plugin_name = dirname( $this->_plugin_base );
		$locale      = apply_filters( 'plugin_locale', get_locale(), $plugin_name );
		$dir         = trailingslashit( WP_LANG_DIR . '/plugins/' . $plugin_name );

		load_textdomain( 'it-l10n-ithemes-exchange', $dir . 'it-l10n-ithemes-exchange-' . $locale . '.mo' );
		load_plugin_textdomain( 'it-l10n-ithemes-exchange', false, $plugin_name . '/lang/' );
	}

	/**
	 * Includes files for enabled add-ons
	 *
	 *
	 */
	public function addons_init() {
		// Add action for third party addons to register addons with.
		do_action( 'ninja_shop_register_addons' );

		// Init all previously enabled addons
		if ( $enabled_addons = it_exchange_get_enabled_addons() ) {
			foreach ( (array) $enabled_addons as $slug => $params ) {
				if ( ! empty( $params['file'] ) && is_file( $params['file'] ) ) {
					include( $params['file'] );
				} else {
					it_exchange_disable_addon( $slug );
					if ( is_admin() ) {
						wp_safe_redirect( 'admin.php?page=it-exchange-addons&message=addon-auto-disabled-' . $slug );
						die();
					}
				}
			}
		}

		// Get addons
		$registered = it_exchange_get_addons();

		// Auto enable all 3rd party addons
		foreach ( $registered as $slug => $params ) {
			if ( ! it_exchange_is_core_addon( $slug ) && ! isset( $enabled_addons[ $slug ] ) && ! empty( $params['options']['auto-enable'] ) ) {
				it_exchange_enable_addon( $slug );
			}
		}
		do_action( 'ninja_shop_enabled_addons_loaded' );
	}

	/**
	 * @inheritDoc
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case '_version':
				return self::VERSION;
			case '_slug':
				return self::SLUG;
			case '_name':
				return self::NAME;
			case '_plugin_url':
				return self::$url;
			case '_plugin_path':
				return self::$dir;
			case '_plugin_base':
				return self::$base;
			case '_wp_minimum':
				return self::MIN_WP;
			case '_series':
				return '';
		}

		return null;
	}

	/**
	 * Unsets the ninja-shop promotion ad from Ninja Forms.
	 *
	 * @since  1.2
	 * @param  array  $promotions Associative array of Ninja Forms promotions.
	 * @return array  Modified array.
	 */
	public function remove_nf_promotion( $promotions )
	{
		unset( $promotions[ 'ninja-shop' ] );
		return $promotions;
	}
}

/**
 * Display a notice if the minimum WordPress version is not met.
 *
 *
 */
function it_exchange_minimum_wp_notice() {
	$required    = IT_Exchange::MIN_WP;
	$running     = $GLOBALS['wp_version'];
	$upgrade_url = admin_url( 'update-core.php' );

	echo '<div class="notice notice-error"><p>';
	printf(
		__( 'Ninja Shop requires version %s of WordPress or greater. You are running version %s.', 'it-l10n-ithemes-exchange' ),
		$required,
		$running
	);
	echo ' ' . sprintf( __( '%sUpgrade Now%s.', 'it-l10n-ithemes-exchange' ), "<a href=\"$upgrade_url\">", '</a>' );
	echo '</p></div>';
}

if ( version_compare( IT_Exchange::MIN_WP, $GLOBALS['wp_version'], '>' ) ) {
	add_action( 'admin_notices', 'it_exchange_minimum_wp_notice' );

	return;
}

/**
 * Loads Exchange after plugins have been enabled
 *
 *
 *
 * @return void
 */
function load_it_exchange() {
	$GLOBALS['IT_Exchange'] = new IT_Exchange();
}

add_action( 'plugins_loaded', 'load_it_exchange', 0 );

/**
 * Deactivate migrated plugins into core.
 *
 *
 */
function it_exchange_deactivate_migrated_plugins() {

	require_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( 'terms-of-service-for-ithemes-exchange/load.php' ) ) {
		deactivate_plugins( 'terms-of-service-for-ithemes-exchange/load.php' );

		it_exchange_enable_addon( 'terms-of-service' );
	}
}

add_action( 'ninja_shop_version_updated', 'it_exchange_deactivate_migrated_plugins' );

/**
 * Redirect users to the IT Exchange Setup page upon activation.
 *
 *
 *
 * @return void
 */
function it_exchange_register_activation_hook() {

	$do_activation = get_option( '_it-exchange-register-activation-hook', false );

	if ( ! $do_activation ) {
		return;
	}

	add_option( '_it-exchange-flush-rewrites', true );

	if ( ! is_network_admin() ) {
		delete_option( '_it-exchange-register-activation-hook' );
		wp_safe_redirect( 'admin.php?page=it-exchange-setup' );
	}
}

add_action( 'admin_init', 'it_exchange_register_activation_hook' );

/**
 * Install Tables.
 *
 *
 */
function it_exchange_install_tables_on_activate() {
	$do_activation = get_option( '_it-exchange-register-activation-hook', false );

	if ( ! $do_activation ) {
		return;
	}

	foreach ( it_exchange_get_tables() as $table ) {
		\IronBound\DB\Manager::maybe_install_table( $table );
	}
}

add_action( 'init', 'it_exchange_install_tables_on_activate', -10 );

/**
 * Install tables on updating Exchange.
 *
 *
 */
function it_exchange_install_tables_on_update() {

	foreach ( it_exchange_get_tables() as $table ) {
		\IronBound\DB\Manager::maybe_install_table( $table );
	}
}

add_action( 'ninja_shop_version_updated', 'it_exchange_install_tables_on_update' );
