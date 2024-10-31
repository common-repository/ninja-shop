<?php

namespace NinjaShop\Telemetry\Cron;

class Schedule {

  protected $slug;
  protected $display_name;
  protected $interval;

  /**
   * @param string $slug
   * @param Interval $interval
   */
  public function __construct( $slug, Interval $interval ) {
    $this->slug = $slug;
    $this->interval = $interval;
  }

  public function get_slug() {
    return $this->slug;
  }

  /**
   * @param string $display_name
   */
  public function set_display_name( $display_name ) {
    $this->display_name = $display_name;
  }

  public function setup() {
    add_filter( 'cron_schedules', [ $this, 'register_schedule' ] );
  }

  /**
   * @param array $schedules
   */
  public function register_schedule( $schedules ) {
      $schedules[ $this->slug ] = array(
          'display' => $this->display_name ?: $this->slug,
          'interval' => $this->interval->get_in_seconds(),
      );
      return $schedules;
  }
}
