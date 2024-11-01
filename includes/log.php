<?php

class b2_Log
{
  /** 
   * If non null: array that collects the info parameter of debug() calls
   *
   * @var string[]|null
   */
  private static $Infos = null;

  /**
   * @param $info string to debug
   * If WP_DEBUG and WP_DEBUG_LOG are true, writes $info to the PHP log using error_log.
   * - in wp-config.php, add define('WP_DEBUG', true); define('WP_DEBUG_LOG', true);
   * - see the result in wp-content/debug.log
   * If startCollecting() has been called, also collects the string for retrieval by stopCollecting(),
   *   as long as stopCollecting() has not been called.
   */
  static function debug($info)
  {
    if (true === WP_DEBUG) {
      error_log("b2:" . $info);
    }
    if (self::$Infos !== null) {
      self::$Infos[] = $info;
    }
  }


  /**
   * Enables collection of infos, resets all existing infos.
   */
  static function startCollecting()
  {
    self::$Infos = array();
  }

  /**
   * Disables collection of infos, returns the array collected.
   *
   * @return string[]|null
   */
  static function stopCollecting()
  {
    $Infos = self::$Infos;
    self::$Infos = null;
    return $Infos;
  }

}
