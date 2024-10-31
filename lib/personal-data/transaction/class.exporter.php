<?php
/**
 * Personal Data Exporter.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

class TransactionExporter implements Exporter {

  /**
   * Export personal data.
   *
   * @param string $email_address The "key" for all exports.
   * @param int $page Current "page" in the step processor.
   *
   * @return array [ (array) data, (bool) done ]
   */
  public function export( $email_address, $page = 1 ) {

    $page = (int) $page;
    $per_page = 25;

    $export_items = [];

    if( $user = get_user_by( 'email', $email_address ) ){
      $customer_id = $user->ID;
    } else {
      $customer_id = $email_address;
    }

    $transactions = it_exchange_get_customer_transactions( $customer_id, compact( 'page', 'per_page' ) );

    foreach( $transactions as $transaction ){

      $item_id = "transaction-{$transaction->ID}";

      $group_id = 'ninja-shop-transactions';

      $group_label = __( 'Transactions', 'ninja-shop' );

      $data = [];

      // Order Number
      $order_number = it_exchange_get_transaction_order_number( $transaction->ID );
      $data[] = [
        'name' => __( 'Order Number' ),
        'value' => $order_number
      ];

      // Total
      $total = it_exchange_get_transaction_total( $transaction );
      $data[] = [
        'name' => __( 'Total' ),
        'value' => $total
      ];

      // Description
      $description = it_exchange_get_transaction_description( $transaction );
      $data[] = [
        'name' => __( 'Description' ),
        'value' => $description
      ];

      // Billing Address (Formatted)
      $billing_address = it_exchange_get_transaction_billing_address( $transaction );
      $billing_address = it_exchange_get_formatted_billing_address( $billing_address );
      $data[] = [
        'name' => __( 'Billing Address' ),
        'value' => $billing_address
      ];

      // Shipping Address (Formatted)
      $shipping_address = it_exchange_get_transaction_shipping_address( $transaction );
      $shipping_address = it_exchange_get_formatted_shipping_address( $shipping_address );
      $data[] = [
        'name' => __( 'Shipping Address' ),
        'value' => $shipping_address
      ];

      $export_items[] = [
        'group_id' => $group_id,
        'group_label' => $group_label,
        'item_id' => $item_id,
        'data' => $data,
      ];
    }

    $done = count( $transactions ) < $per_page;

    return [
      'data' => $export_items,
      'done' => $done,
    ];
  }

  /**
   * Register the personal data exporter.
   *
   * @param array $exporters [ slug => [ name, callback ] ]
   *
   * @return array $exporters
   */
  public static function register( $exporters ) {

    $exporters[ 'ninja-shop-exporter' ] = [
      'exporter_friendly_name' => __( 'Ninja Shop', 'ninja-shop' ),
      'callback' => [ self::class, 'export' ],
    ];

    return $exporters;
  }

}
