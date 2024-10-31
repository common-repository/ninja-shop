<?php

namespace NinjaShop\Telemetry\Cron;

class Interval {

  protected $interval_in_seconds;

  /**
   * @param int $interval_in_seconds
   */
  public function __construct( $interval_in_seconds ) {
    $this->interval_in_seconds = $interval_in_seconds;
  }

  public function get_in_seconds() {
    return $this->interval_in_seconds;
  }

  /**
   * @param int $days
   */
  public static function from_days( $days ) {
    return new self( $days * 24 * 60 * 60 );
  }
}
