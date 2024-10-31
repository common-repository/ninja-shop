<?php
/**
 * Personal Data (Address) Eraser.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

use ITE_Transactions_Table as Transactions_Table;

class TransactionEraser implements Eraser {

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
      $updated = self::anonymize_customer_transactions( $user->ID );
    } else {
      $updated = self::anonymize_guest_transactions( $email_address );
    }

    return [
      'items_removed' => $updated,
      'items_retained' => false,
      'messages' => [],
      'done' => true,
    ];
  }

  /**
   * Anonymize transactions by customer ID.
   *
   * @param int $customer_id The ID of the customer related to the transaction.
   *
   * @return int|bool The number of rows updated, or false on error.
   */
  protected static function anonymize_customer_transactions( $customer_id ){
    return self::anonymize_transactions( 'customer_id', $customer_id, '%d' );
  }

  /**
   * Anonymize transactions by email address, ie Guest Customer.
   *
   * @param string $email_address The email address related to the transaction.
   *
   * @return int|bool The number of rows updated, or false on error.
   */
  protected static function anonymize_guest_transactions( $email_address ){
    return self::anonymize_transactions( 'customer_email', $email_address, '%s' );
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
  protected static function anonymize_transactions( $where_key, $where_value, $where_format = '%s' ){
    global $wpdb;
    $table = new Transactions_Table();
    $table_name = $table->get_table_name( $wpdb );

    $anonymized_data = [ 'customer_id' => 0, 'customer_email' => '' ];
    $anonymized_format = [ '%d', '%s' ];

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

    $erasers[ 'ninja-shop-transaction-eraser' ] = [
      'eraser_friendly_name' => __( 'Ninja Shop Transaction Eraser', 'ninja-shop' ),
      'callback' => [ self::class, 'erase' ],
    ];

    return $erasers;
  }
}
