<?php

namespace NinjaShop\Telemetry;

class Optin_Notice extends Admin\Notice {

  public function setup() {
    parent::setup();
    add_action( 'admin_init', [ $this, 'maybe_enable' ] );
  }

  public function should_display_notice() {
    $opted_in = Settings::is_opted_in();
    $opted_out = Settings::is_opted_out();
    return ! $opted_in && ! $opted_out && ! $this->is_notice_dismissed();
  }

  public function display_notice() {
    $format = __( 'Help to improve Ninja Shop with diagnostic reporting. %sEnable%s', 'ninja-shop' );
    $enable_url = add_query_arg( $this->get_enable_flag(), 1 );
    $message = sprintf( $format, '<a class="btn" href="' . $enable_url . '">', '</a>' );
    $dismiss_url = add_query_arg( array( $this->get_dismiss_flag() => true ) );
    include( 'views/optin-admin-notice.html.php' );
  }

  public function get_enable_flag() {
    return "ninja_shop_enable_{$this->slug}";
  }

  public function maybe_enable() {
    if( ! isset( $_REQUEST[ $this->get_enable_flag() ] ) ) return;
    $this->enable_telemetry();
    $this->redirect_after_enable_telemetry();
  }

  public function enable_telemetry() {
    $settings = it_exchange_get_option( 'settings_general' );
    $settings[ 'telemetry-opt-in' ] = 1;
    it_exchange_save_option( 'settings_general', $settings );
  }

  public function redirect_after_enable_telemetry() {
    wp_safe_redirect( remove_query_arg( $this->get_enable_flag() ) );
    die();
  }
}
