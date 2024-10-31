<?php

namespace NinjaShop\Telemetry\Settings;

class General_Settings {

  public static function add_hooks() {
    add_filter( 'ninja_shop_storage_get_defaults_exchange_settings_general', [ self::class, 'register_defaults' ] );
  }

  public static function is_opted_in() {
    if( self::is_opted_out() ) return false;
    if( self::is_opted_in_automatically() ) return true;
    if( self::is_opted_in_explicitly() ) return true;
    return false;
  }

  public static function is_opted_in_explicitly() {
    if( self::is_opted_out() ) return false;
	$settings = it_exchange_get_option( 'settings_general' );

	if ( ! array_key_exists( 'telemetry-opt-in', $settings ) ) {
		$settings[ 'telemetry-opt-in' ] = null;
	}
	$opted_in = $settings[ 'telemetry-opt-in' ];

    return ( $opted_in );
  }

  public static function is_opted_in_automatically() {
    return apply_filters( 'ninja_shop_telemetry_is_opted_in', false );
  }

  public static function is_opted_out() {
	$settings = it_exchange_get_option( 'settings_general' );

	if ( ! array_key_exists( 'telemetry-opt-out', $settings ) ) {
		$settings[ 'telemetry-opt-out' ] = null;
	}
	$opted_out = $settings[ 'telemetry-opt-out' ];

    return $opted_out;
  }

  public static function register_defaults( $values ) {
    $values[ 'telemetry-opt-in' ] = null;
    $values[ 'telemetry-opt-out' ] = null;
    return $values;
  }
}
