<?php
/**
 * Contains personal data eraser class.
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

/**
 * Interface Personal Data Eraser.
 */
interface Eraser {

  /**
   * Export personal eraser.
   *
   * @param string $email_address The "key" for all eraser.
   * @param int $page Current "page" in the step processor.
   *
   * @return array [ (array) data, (bool) done ]
   */
  public function erase( $email_address, $page = 1 );

  /**
   * Register the personal data eraser.
   *
   * @param array $erasers [ slug => [ name, callback ] ]
   *
   * @return array $erasers
   */
  public static function register( $erasers );
}
