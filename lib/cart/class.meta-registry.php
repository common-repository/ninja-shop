<?php
/**
 * Cart Meta registry.
 *
 * 
 * @license GPLv2
 */

/**
 * Class ITE_Cart_Meta_Registry
 */
class ITE_Cart_Meta_Registry {

	/**
	 * @var ITE_Cart_Meta[]
	 */
	private static $meta = array();

	/**
	 * Register metadata.
	 *
	 *
	 *
	 * @param ITE_Cart_Meta $meta
	 */
	public static function register( ITE_Cart_Meta $meta ) {
		static::$meta[ $meta->get_key() ] = $meta;
	}

	/**
	 * Get meta key.
	 *
	 *
	 *
	 * @param string $key
	 *
	 * @return ITE_Cart_Meta|null
	 */
	public static function get( $key ) {
		return isset( static::$meta[ $key ] ) ? static::$meta[ $key ] : null;
	}

	/**
	 * Remove the meta entry.
	 *
	 *
	 *
	 * @param string $key
	 */
	public static function remove( $key ) {
		unset( static::$meta[ $key ] );
	}

	/**
	 * Get all meta values from the registry.
	 *
	 *
	 *
	 * @return ITE_Cart_Meta[]
	 */
	public static function all() {
		return array_values( static::$meta );
	}

	/**
	 * Get all meta that is viewable in REST.
	 *
	 *
	 *
	 * @return ITE_Cart_Meta[]
	 */
	public static function shows_in_rest() {
		$r = array();

		foreach ( static::all() as $entry ) {
			if ( $entry->show_in_rest() ) {
				$r[] = $entry;
			}
		}

		return $r;
	}

	/**
	 * Get all meta that is editable in REST.
	 *
	 *
	 *
	 * @return ITE_Cart_Meta[]
	 */
	public static function editable_in_rest() {
		$editable = array();

		foreach ( static::all() as $entry ) {
			if ( $entry->editable_in_rest() ) {
				$editable[] = $entry;
			}
		}

		return $editable;
	}
}
