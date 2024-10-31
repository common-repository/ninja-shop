<?php
/**
 * Personal Data (Address) Eraser.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

use ITE_Location as Location;
use ITE_Saved_Address_Table as Address_Table;

class AddressEraser implements Eraser {

  /**
   * Export personal eraser.
   *
   * @param string $email_address The "key" for all eraser.
   * @param int $page Current "page" in the step processor.
   *
   * @return array [ (array) data, (bool) done ]
   */
  public function erase( $email_address, $page = 1 ) {

    if( $user = get_user_by( 'email', $email_address ) ){
      $updated = self::anonymize_customer_addresses( $user->ID );
    } else {
      $updated = self::anonymize_guest_addresses( $email_address );
    }

    if( $updated ){
      $messages = [
        __( 'Some address information was retained for tax reporting purposes.', 'ninja-shop' ),
      ];
    } else {
      $messages = [];
    }

    return [
      'items_removed' => $updated,
      'items_retained' => $updated,
      'messages' => $messages,
      'done' => true,
    ];
  }

  /**
   * Anonymize addresses by customer ID.
   *
   * @param int $customer_id The ID of the customer related to the address.
   *
   * @return int|bool The number of rows updated, or false on error.
   */
  protected static function anonymize_customer_addresses( $customer_id ){
    return self::anonymize_addresses( 'customer', $customer_id, '%d' );
  }

  /**
   * Anonymize addresses by email address, ie Guest Customer.
   *
   * @param string $email_address The email address related to the address.
   *
   * @return int|bool The number of rows updated, or false on error.
   */
  protected static function anonymize_guest_addresses( $email_address ){
    return self::anonymize_addresses( 'email', $email_address, '%s' );
  }

  /**
   * Anonymize transactions for a specific user.
   *
   * @param string $where_key Table column key, ie customer_id or customer_email.
   * @param string|int $where_value Table column value, ie $id or $email.
   * @param string $where_format The wpdb value format, ie '%d', '%f', '%s'.
   *
   * @return int|bool The number of rows updated, or false on error.
   */
  protected static function anonymize_addresses( $where_key, $where_value, $where_format = '%s' ){
    global $wpdb;
    $table = new Address_Table();
    $table_name = $table->get_table_name( $wpdb );

    $anonymized_data = [
      'customer' => 0,
      'label' => '',
      'company-name' => '',
      'first-name' => '',
      'last-name' => '',
      'address1' => '',
      'address2' => '',
      'email' => '',
      'phone' => '',
    ];
    $anonymized_format = [ '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', ];

    $where = [ $where_key => $where_value ];

    return $wpdb->update( $table_name, $anonymized_data, $where, $anonymized_format, $where_format );
  }

  /**
   * Register the personal data eraser.
   *
   * @param array $erasers [ slug => [ name, callback ] ]
   *
   * @return array $erasers
   */
  public static function register( $erasers ) {

    $erasers[ 'ninja-shop-address-eraser' ] = [
      'eraser_friendly_name' => __( 'Ninja Shop Address Eraser', 'ninja-shop' ),
      'callback' => [ self::class, 'erase' ],
    ];

    return $erasers;
  }
}
