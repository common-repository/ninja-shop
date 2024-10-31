<?php

namespace NinjaShop\Telemetry\Settings;

class General_Settings_Form {

  public static function add_hooks() {

    /** @NOTE Priority 9: Before "Dangerous Settings", ie plugin reset checkbox. */
    add_action( 'ninja_shop_general_settings_table_bottom', [ self::class, 'append_table_row' ], 9 );
  }

  public static function append_table_row( $form ) {
    ?>
    <tr valign="top">
        <th scope="row"><strong><?php _e( 'Service Improvement', 'ninja-shop' ); ?></strong></th>
        <td></td>
    </tr>
    <tr valign="top">
        <th scope="row"><label><?php _e( 'Diagnostic Reporting', 'ninja-shop' ) ?></label>
        </th>
        <td>
          <?php $form->set_option( 'telemetry-opt-out', General_Settings::is_opted_out() ); ?>
          <?php $form->add_check_box( 'telemetry-opt-out' ); ?>
          <label for="telemetry-opt-out"><?php _e( 'Disable diagnostic reporting.', 'ninja-shop' ) ?></label><br/>
        </td>
    </tr>
    <?php
  }
}
