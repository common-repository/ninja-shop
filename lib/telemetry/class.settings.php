<?php

namespace NinjaShop\Telemetry;

/**
 * A facade for accessing Telemetry Settings;
 */
class Settings {

  public static function is_opted_in() {
    return Settings\General_Settings::is_opted_in();
  }

  public static function is_opted_out() {
    return Settings\General_Settings::is_opted_out();
  }
}
