<?php

namespace NinjaShop\Telemetry;

include_once 'functions.php';
include_once 'api/load.php';
include_once 'cron/load.php';
include_once 'admin/load.php';
include_once 'settings/load.php';
include_once 'class.settings.php';
include_once 'class.optin-notice.php';

$interval = Cron\Interval::from_days( 1 );

$schedule = new Cron\Schedule( 'ninja-shop-telemetry', $interval );
$schedule->set_display_name( __( 'Ninja Shop Telemetry Reporting', 'ninja-shop' ) );
$schedule->setup();

$event = new Cron\Event( 'ninja-shop-telemetry-update-configuration', function( $event ) {
  ninja_shop_telemetry_update_configuration();
} );
$event->setup();

$scheduled_event = new Cron\Scheduled_Event( $schedule, $event );
$scheduled_event->setup();

$optin_notice = new Optin_Notice( 'telemetry-optin' );
$optin_notice->setup();

// Nested hook to combine condition and timing requirements.
add_action( 'ninja_shop_version_updated', function(){
  add_action( 'ninja_shop_libraries_loaded', function() { // We need access to other libraries.
    ninja_shop_telemetry_update_configuration();
  } );
} );
