<?php

namespace NinjaShop\Telemetry\Cron;

class Scheduled_Event {

  protected $event;
  protected $schedule;

  public function __construct( Schedule $schedule, Event $event ) {
    $this->schedule = $schedule;
    $this->event = $event;
  }

  public function setup() {
    add_action( 'wp', [ $this, 'schedule_event' ] );
  }

  public function schedule_event() {
    if ( $this->is_scheduled() ) return;
    wp_schedule_event( current_time( 'timestamp' ), $this->schedule->get_slug(), $this->event->get_slug() );
  }

  /**
   * @return string|bool Timestamp, the time the scheduled event will next occur (unix timestamp). False, if the event isn't scheduled.
   */
  public function get_next_scheduled() {
    return wp_next_scheduled( $this->event->get_slug() );
  }

  /**
   * @return bool
   */
  public function is_scheduled() {
    return (bool) $this->get_next_scheduled();
  }
}
