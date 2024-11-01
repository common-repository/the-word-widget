<?php

require_once(dirname(__FILE__)."/log.php");

class ServerAccess
{
  /**
   * @return array of array("lang", "bible", "bibleName", "url", "year"), i.e. info about TwdFile, or false,
   */
  static function getTwdFileListFromUrl($url)
  {
    b2_Log::debug("Getting .twd file list from '$url'...");

    # 2014-12-22 HSteeb:
    # simplexml_load_file fails if allow_url_fopen=off
    # --> fall back to WP mechanism (which uses streams)
    # No idea whether the WP mechanism will work on all server configurations
    # --> keeping two ways may be better than one...
    $TwdList = false;
    $Xml = self::_getXmlUsingSimpleXml($url);

    if (!$Xml) {
      $Xml = self::_getXmlUsingWP($url);
    }
    if ($Xml) {
      $TwdList = self::_parseTwdList($Xml);
    }
    b2_Log::debug("Getting .twd file list " . ($TwdList ? "OK" : "failed"));
    return $TwdList;
  }

  private static function _getXmlUsingSimpleXml($url)
  {
    b2_Log::debug("  ... trying simplexml_load_file");

    if (!function_exists("simplexml_load_file")) {
      b2_Log::debug("  ... function simplexml_load_file does not exist, skipping (you may need to install php5.6-xml or php7.0-xml...).");
      return null;
    }
    $originalUseErrors = libxml_use_internal_errors(true);
    $Xml = @simplexml_load_file($url);
    if ($Xml === false) {
      b2_Log::debug("  ... failed to simplexml_load_file '$url', XML errors:");
      self::_reportXmlErrors();
    }
    else {
      b2_Log::debug("  ... simplexml_load_file OK.");
    }
    libxml_use_internal_errors($originalUseErrors); # restore original value
    return $Xml;
  }

  private static function _getXmlUsingWP($url)
  {
    b2_Log::debug("  ... trying wp_remote_get");

    $Response = wp_remote_get($url);
    if (!is_array($Response)) {
      b2_Log::debug("  ... failed to wp_remote_get for '$url': " . ($Response ? $Response->get_error_message() : "") . ".");
      return false;
    }
    b2_Log::debug("  ... wp_remote_get OK (got non-error Response).");

    $rc = wp_remote_retrieve_response_code($Response);
    if (200 != $rc) {
      b2_Log::debug("  ... failed to wp_remote_get for '$url': HTTP $rc.");
      return false;
    }
    b2_Log::debug("  ... wp_remote_get OK (got HTTP $rc).");

    $body = wp_remote_retrieve_body($Response);
    if (!$body) {
      b2_Log::debug("  ... failed to wp_remote_retrieve_body from data retrieved by wp_remote_get.");
      return false;
    }
    b2_Log::debug("  ... wp_remote_retrieve_body OK (non-empty string).");

    if (!function_exists("simplexml_load_string")) {
      b2_Log::debug("  ... function simplexml_load_string does not exist, cannot use it for body of data retrieved by wp_remote_get.");
      return false;
    }

    $originalUseErrors = libxml_use_internal_errors(true);
    $Xml = @simplexml_load_string($body);
    if ($Xml === false) {
      b2_Log::debug("  ... failed to simplexml_load_string from body of data retrieved by wp_remote_get: " . htmlspecialchars(substr($body, 0, 100)) . "...");
      self::_reportXmlErrors();
    }
    else {
      b2_Log::debug("  ... simplexml_load_string OK");
    }
    libxml_use_internal_errors($originalUseErrors); # restore original value
    return $Xml;
  }

  private static function _parseTwdList($Xml)
  {
    b2_Log::debug("Parsing .twd file list...");
    $currentYear = date("Y");
    $TwdFileList = array();

    # Convention for storing .twd file info in Atom format:
    # https://bible2.net/download/online-retrieval-of-twd-1-1-files/
    $langCodePattern       = "[A-Za-z0-9-]+";
    $bibleShortnamePattern = "[A-Za-z0-9]+";
    $yearPattern           = "\d{4}";
    foreach ($Xml->entry as $entry) {
      if ($entry->category["term"] == "file"
          # e.g. <id>https://bible2.net/service/TheWord/twd11/de_HoffnungFuerAlle_2014</id>
          # - language (ISO language code)
          # - Bible short name (A-Za-z0-9)
          # - year
          && preg_match("@/($langCodePattern)_($bibleShortnamePattern)_($yearPattern)$@", $entry->id, $ID)
          # e.g. <title>2014 de Hoffnung für Alle</title>
          # - year
          # - language (ISO language code)
          # - Bible long name (Unicode)
          && preg_match("@^$yearPattern\s+$langCodePattern\s+(.*)@", $entry->title, $Title)
          && $ID[3] == $currentYear
        ) {

        # find <link> with rel="alternate"
        $url = "";
        foreach ($entry->link as $link) {
          if ($link["rel"] == "alternate") {
            # Need (string) cast!
            # Without cast, $url is a SimpleXMLElement which gives an error when used in set_transient:
            # PHP Fatal error:  Uncaught exception 'Exception' with message 'Serialization of 'SimpleXMLElement' is not allowed'
            $url = (string) $link["href"];
          }
        }
        if (!$url) {
          b2_Log::debug("  ... missing url in '" . htmlspecialchars($entry->title) . "', skipping this file entry.");
        }
        else {
          $TwdFileList[$ID[2]] = array (
              "lang" => $ID[1]
            , "bible" => $ID[2]
            , "bibleName" => $Title[1]
            , "url" => $url
            , "year" => $currentYear
            );
        }
      }
    }
    $count = count($TwdFileList);
    b2_Log::debug("  ... " . (0 == $count ? "failed" : "OK") . " ($count file entries for the current year $currentYear)");
    return $TwdFileList;
  }

  private static function _reportXmlErrors()
  {
    $i = 0;
    foreach (libxml_get_errors() as $error) {
      if (++$i == 6) { # emit 5 XML errors
        b2_Log::debug("(skipping further XML errors)");
        break;
      }
      b2_Log::debug($error->message);
    }
  }


  /**
   * @param string $url ready to use url including Bible
   * @return string $theWord as HTML to display
   */
  static function getTheWordFromUrl($url)
  {
    b2_Log::debug("Getting The Word from '$url'...");

    $Response = wp_remote_get($url);
    $rc = wp_remote_retrieve_response_code($Response);
    if (200 != $rc) {
      b2_Log::debug("  ... failed to get '$url': HTTP $rc");
      return false;
    }

    $body = wp_remote_retrieve_body($Response);
    if (!$body) {
      b2_Log::debug("  ... failed to retrieve body from response.");
      return false;
    }
    b2_Log::debug(" ... OK.");
    return $body;
  }
}

# Local Variables:
# coding: utf-8
# End:
