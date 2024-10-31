<?php

class Ninja_Shop_Basic_US_Sales_Taxes_Admin {

  /** @var string */
  protected $page_slug;

  /** @var string */
  protected $plugin_slug;

  public function __construct() {
    $this->page_slug = 'basic-us-sales-taxes';
    $this->plugin_slug = 'ninja-shop-' . $this->page_slug;
  }

  public function setup() {
    if( ! $this->is_settings_page() ) return;
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
    add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
  }

  public function enqueue_admin_styles() {
	   wp_enqueue_style( $this->plugin_slug . '-admin-styles', plugins_url( 'styles/admin.css', __FILE__ ), array() );
  }

  public function enqueue_admin_scripts() {
    wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ) );
  }

  /**
   * @return bool
   */
  public function is_settings_page() {
  	return isset( $_GET['add-on-settings'] ) && $this->page_slug == $_GET['add-on-settings'];
  }
}
