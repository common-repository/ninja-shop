<?php
/**
 * Personal Data Policy Suggested Text.
 *
 * @link https://developer.wordpress.org/plugins/privacy/suggesting-text-for-the-site-privacy-policy/
 *
 * 
 * @license GPLv2
 */

namespace NinjaShop\PersonalData;

class Policy {

  /**
   * Returns the suggested text.
   */
  public static function get_text() {
    return include( 'views/policy.php' );
  }

  /**
   * Register the privacy policy content.
   */
  public static function register() {
    if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
      return;
    }
    wp_add_privacy_policy_content( 'Ninja Shop', self::get_text() );
  }
}
