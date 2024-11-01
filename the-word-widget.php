<?php
/**
Plugin Name: The Word Widget
Plugin URI: https://bible2.net/download/the-word-widget-for-wordpress/
Description: Shows two Bible sayings per day: "The Word" by project Bible 2.0, available in over 10 languages, got remotely for each day
Version: 0.9
Requires at least: 4.6
Requires PHP: 5.3
Author: Helmut Steeb
Author URI: http://jsteeb.de
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
==============================================================================
Copyright 2014-2022  Helmut Steeb (email: bible2.net/contact/)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!class_exists('b2_TheWordWidget')) {

  require_once(dirname(__FILE__)."/includes/log.php");
  require_once(dirname(__FILE__)."/includes/server-access.php");
  require_once(dirname(__FILE__)."/includes/local.php");

  /**
   * If WP_DEBUG and WP_DEBUG_LOG are true, writes info to the PHP log using error_log() via log.php.
   */
  class b2_TheWordWidget extends WP_Widget {

    const DOMAIN = "https://bible2.net"; # not re-used - PHP 5.5.3: no expression in constant :-(
    const LISTURL= "https://bible2.net/service/TheWord/twd11?format=atom";
    const WORDURL= "https://bible2.net/service/today/inline/"; # used with trailingslashit


    function __construct()
    {
      $Options = array(
          'description' => __("shows two selected Bible sayings for each day: The Word by project Bible 2.0, available in over 10 languages", "thewordwidget")
      );
      parent::__construct(
       'thewordwidget' # base ID
       , __('The Word', 'thewordwidget') # name displayed on the configuration page
      , $Options # passed to wp_register_sidebar_widget(), description: shown on the configuration page
      );
      add_action('plugins_loaded', array(&$this, 'translation'), 2);
    }
  

    function form($instance)
    {
      b2_Log::startCollecting(); # must call [stopCollecting]
      $Defaults = array('bible' => 'EnglishStandardVersion');
      $instance = wp_parse_args((array) $instance, $Defaults);
      $currentBible = $instance["bible"];

      $TwdFileList = Local::getCachedTwdFileList();
      if (!$TwdFileList) {
        $TwdFileList = ServerAccess::getTwdFileListFromUrl(self::LISTURL);
        if (!$TwdFileList) {
          $Infos = b2_Log::stopCollecting(); # [stopCollecting]
          $Infos[] = sprintf(__("Retrieving Bible list failed from '%s'.", 'thewordwidget'), self::LISTURL);
          $this->reportErrors($Infos);
          return;
        }

        # sort $TwdFileList for output in form
        usort($TwdFileList, array(&$this, "_cmpBibleName"));
  
        # store for use in next call
        Local::setCachedTwdFileList($TwdFileList);
      }
      b2_Log::stopCollecting(); # [stopCollecting]
      # $TwdFileList truthy

      # create HTML

      self::emithtml($this, $TwdFileList, $currentBible, self::DOMAIN);
    }

    /**
     * Emits HTML for the admin form to select a Bible edition.
     *
     * 2021-07-17 HS: for WP 5.8, moved here from former includes/admin-form.php
     * which seems no more found, obviously since WP 5.8 shows it in a new Legacy Widget block.
     * cf. <https://make.wordpress.org/core/2021/06/29/block-based-widgets-editor-in-wordpress-5-8/>
     *
     * @param $Widget the WP_Widget to show
     * @param $TwdFileList array of array of "bible" etc.
     * @param $currentBible which element of $TwdFileList to select
     * @param $domain info to show
     */
    function emithtml($Widget, $TwdFileList, $currentBible, $domain)
    {
      echo "<p>"
        . "<label for='" . $Widget->get_field_id("bible") . "'>" . __("Bible:", 'thewordwidget') . "</label>\n"
        . "<select name='" . $Widget->get_field_name("bible") . "' id='" . $Widget->get_field_id("bible") . "'>\n"
        ;

      foreach ($TwdFileList as $TwdFile) {
        $selected = selected($TwdFile["bible"], $currentBible, false);
        printf("  <option value='%1\$s'%2\$s data-url='" . $TwdFile["url"]. "'>%3\$s (%4\$s)</option>\n"
          , esc_attr($TwdFile["bible"])
          , $selected
          , esc_attr($TwdFile["bibleName"])
          , esc_attr($TwdFile["lang"]
          ));
      }
      printf("</select>\n"
        . "</p>"
        . "<p>"
        . __("The widget will get The Word for the selected Bible and the current day remotely from %s.",
             'thewordwidget')
        . "</p>"
        , $domain)
        ;
    }

    function reportErrors($Infos)
    {
      global $wp_version;
      global $required_php_version;

      $Infos[] = "<b>Environment:</b>";
      $Infos[] = "allow_url_fopen=" . ini_get('allow_url_fopen');
      $Infos[] = "php_version()=" . phpversion();
      $Infos[] = "wp_version=" . $wp_version;
      $Infos[] = "required_php_version for WordPress=" . $required_php_version;
      $s = implode("<br/>", $Infos);
      $s = str_replace("OK",     "<span style='color:green;'>OK</span>", $s);
      $s = str_replace("failed", "<span style='color:red;'>failed</span>", $s);
      printf("<p>$s</p>");
    }

    function update($new_instance, $old_instance)
    {
      $instance = $old_instance;

      $bible = $instance['bible'] = preg_replace("[^A-Za-z0-9_]", "", $new_instance['bible']);
      if (!$bible) {
        b2_Log::debug("the-word-widget.php::update: invalid Bible name $bible");
        return false;
      }

      return $instance;
    }

    function widget($args, $instance)
    {
      $bible = isset($instance["bible"]) ? $instance["bible"] : "";

      $date = date("Y-m-d"); # use one date consistently
      $theWord = Local::getCachedTheWord($date, $bible);
      if (!$theWord) {
        $theWord = ServerAccess::getTheWordFromUrl(trailingslashit(self::WORDURL) . $bible);
        if (!$theWord) {
          # on error, avoid output
          return;
        }
        Local::setCachedTheWord($theWord, $date, $bible);
      }
      # $theWord truthy

      # display The Word
      echo $args['before_widget'];
      echo $theWord;
      echo $args['after_widget'];
    }
    
    # --- Private ---
  
    private static function _cmpBibleName($a, $b)
    { 
      return strcmp($a["bibleName"], $b["bibleName"]);
    }

    private function translation()
    {
      load_plugin_textdomain('the-word-widget', false, 'the-word-widget/languages');
    }

  }
  
  add_action('widgets_init',
             function() {
               return register_widget("b2_TheWordWidget");
             });
}
?>
