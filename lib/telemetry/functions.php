<?php

function ninja_shop_telemetry_update_configuration() {

  if( ! NinjaShop\Telemetry\Settings::is_opted_in() ) return;

  global $wpdb;

  $metrics_bag = new NinjaShop\Telemetry\API\Metrics_Bag([
    'php_version' => PHP_VERSION,
    'mysql_version' => $wpdb->db_version(),
    'ninja_shop_plugin_version' => \IT_Exchange::VERSION,
  ]);

  $parameter_bag = new NinjaShop\Telemetry\API\Parameter_Bag([
    'site_url' => site_url(),
    'metrics' => $metrics_bag
  ]);

  $api_url = 'http://api.getninjashop.com/wp-json/ninja-shop/v1/telemetry/metrics';

  $dispatcher = new NinjaShop\Telemetry\API\Request( $api_url, $parameter_bag );
  $response = $dispatcher->dispatch();
}
