<?php

namespace NinjaShop\Telemetry\Admin;

trait Restorable {

  public function maybe_restore_notice() {
    if( ! isset( $_REQUEST[ $this->get_restore_flag() ] ) ) return;
    $this->restore_notice();
    $this->redirect_after_restore();
  }

  public function get_restore_flag() {
    return "ninja_shop_restore_{$this->slug}_nag";
  }

  public function redirect_after_restore() {
    wp_safe_redirect( remove_query_arg( $this->get_restore_flag() ) );
    die();
  }

  public function restore_notice() {
    delete_option( "ninja-shop-hide-{$this->slug}-nag" );
  }
}
