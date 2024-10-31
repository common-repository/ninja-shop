<?php
/**
 * Personal Data Eraser.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

use ITE_Location as Location;
use IT_Exchange_Customer as Customer;

class Eraser {

  /**
   * Export personal eraser.
   *
   * @param string $email_address The "key" for all eraser.
   * @param int $page Current "page" in the step processor.
   *
   * @return array [ (array) data, (bool) done ]
   */
  public function erase( $email_address, $page = 1 ) {

    $items_removed = false;
    $items_retained = false;

    if( $user = get_user_by( 'email', $email_address ) ){
      $customer_id = $user->ID;
    } else {
      $customer_id = $email_address;
    }

    $customer = it_exchange_get_customer( $customer_id );

    /*
     * Anonymize Customer Transactions
     */
    $this->anonymize_customer_transactions( $customer );

    /*
     * Anonymize Customer Addresses
     */
    $this->anonymize_customer_addresses( $customer );

    $done = true;

    if( $items_retained ){
      $messages = [
        __( 'Some address information was retained for tax reporting purposes.', 'ninja-shop' ),
      ];
    } else {
      $messages = [];
    }

    return [
      'items_removed' => $items_removed,
      'items_retained' => false,
      'messages' => $messages,
      'done' => $done,
    ];
  }

  public static function anonymize_customer_transactions( $customer ) {

  }

  public static function anonymize_customer_addresses( Customer $customer ) {
    $billing_address = $customer->get_billing_address();
    if( $billing_address ){
      self::anonymize_address( $billing_address );
      $items_removed = true;
      $items_retained = true;
    }

    $shipping_address = $customer->get_shipping_address();
    if( $shipping_address ){
      self::anonymize_address( $shipping_address );
      $items_removed = true;
      $items_retained = true;
    }
  }

  public static function anonymize_address( Location $address ){
    $address[ 'company-name' ] = '';
    $address[ 'first-name' ] = '';
    $address[ 'last-name' ] = '';
    $address[ 'address1' ] = '';
    $address[ 'address2' ] = '';
    $address[ 'email' ] = '';
    $address[ 'phone' ] = '';
    return $address->save();
  }

  /**
   * Register the personal data eraser.
   *
   * @param array $erasers [ slug => [ name, callback ] ]
   *
   * @return array $erasers
   */
  public static function register( $erasers ) {

    $erasers[ 'ninja-shop-eraser' ] = [
      'eraser_friendly_name' => __( 'Ninja Shop', 'ninja-shop' ),
      'callback' => [ self::class, 'erase' ],
    ];

    return $erasers;
  }
}
