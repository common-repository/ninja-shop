<?php
/**
 * Contains personal data exporter class.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

/**
 * Interface Persoanl Data Exporter.
 */
interface Exporter {

  /**
   * Export personal data.
   *
   * @param string $email_address The "key" for all exports.
   * @param int $page Current "page" in the step processor.
   *
   * @return array [ (array) data, (bool) done ]
   */
  public function export( $email_address, $page = 1 );

  /**
   * Register the personal data exporter.
   *
   * @param array $exporters [ slug => [ name, callback ] ]
   *
   * @return array $exporters
   */
  public static function register( $exporters );
}
