<?php

namespace NinjaShop\Telemetry\Admin;

trait Dismissable {

  public function maybe_dismiss_notice() {
    if( ! isset( $_REQUEST[ $this->get_dismiss_flag() ] ) ) return;
    $this->dismiss_notice();
    $this->redirect_after_dismiss();
  }

  public function get_dismiss_flag() {
    return "ninja_shop_dismiss_{$this->slug}_nag";
  }

  public function dismiss_notice() {
    update_option( "ninja-shop-hide-{$this->slug}-nag", true );
  }

  public function redirect_after_dismiss() {
    wp_safe_redirect( remove_query_arg( $this->get_dismiss_flag() ) );
    die();
  }

  public function is_notice_dismissed() {
    return (boolean) get_option( "ninja-shop-hide-{$this->slug}-nag" );
  }
}
