<?php

namespace NinjaShop\Telemetry\Cron;

class Event {

  protected $slug;

  /**
   * @param string $slug
   * @param callable $callback
   */
  public function __construct( $slug, callable $callback ) {
    $this->slug = $slug;
    $this->callback = $callback;
  }

  public function get_slug() {
    return $this->slug;
  }

  public function setup() {
    add_action( $this->slug, [ $this, 'run' ] );
  }

  public function run() {
    call_user_func_array( $this->callback, [ $this ] );
  }
}
