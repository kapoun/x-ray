<?php

namespace Xataface;

/**
*  A sample class
*
*  Use this section to define what this class is doing, the PHPDocumentator will use this
*  to automatically generate an API documentation using this information.
*
*  @author yourname
*/
class Application {
  private function __construct() {}
  
  /**
   * Initializes a Xataface application.
   *
   * @param string  $sitePath    The path to your site's access point.
   * @param string  $xatafaceUrl The URL to the Xataface directory.
   * @param boolean $debug       Whether to start the application in debug mode.
   *
   * @return Dataface_Application The Xataface application object.
   */
  public static function init($sitePath, $debug = false) {
    require_once dirname(__DIR__) . '/xataface/dataface-public-api.php';
    $conf = $debug ? ['debug' => true] : null;
    $app = df_init($sitePath, '/vendor/shannah/xataface/xataface', $conf);
    return $app;
  }
}