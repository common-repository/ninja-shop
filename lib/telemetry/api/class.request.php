<?php

namespace NinjaShop\Telemetry\API;

/**
 * A wrapper for wp_remote_post, formatted for api.getninjashop.com
 */
class Request {

  protected $api_url;
  protected $parameter_bag;

  public function __construct( $api_url, \ITE_Parameter_Bag $parameter_bag ) {
    $this->api_url = $api_url;
    $this->parameter_bag = $parameter_bag;
  }

  public function dispatch() {
    return $response = wp_remote_post( $this->api_url, $this->get_args() );
  }

  protected function get_args() {
    return [
      'body' => $this->parameter_bag->get_params(),
    ];
  }
}
