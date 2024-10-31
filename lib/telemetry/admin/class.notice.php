<?php

namespace NinjaShop\Telemetry\Admin;

abstract class Notice {

  use Restorable,
      Dismissable;

  /**
   * @param string $slug
   */
  public function __construct( $slug ) {
    $this->slug = $slug;
  }

  public function setup() {
    add_action( 'admin_init', [ $this, 'maybe_restore_notice' ] );
    add_action( 'admin_init', [ $this, 'maybe_dismiss_notice' ] );
    add_action( 'admin_notices', [ $this, 'maybe_display_notice' ] );
  }

  public function maybe_display_notice() {
    if( ! $this->should_display_notice() ) return;
    $this->display_notice();
  }

  public function should_display_notice() {
    return true;
  }

  public abstract function display_notice();
}
