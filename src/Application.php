<?php

namespace Xataface;

require_once dirname(__DIR__) . '/xataface/public-api.php';
require_once dirname(__DIR__) . '/xataface/init.php';
require_once dirname(__DIR__) . '/xataface/PEAR.php';

/**
 *  A class representing the whole Xataface application.
 */
class Application {
  private $app;
  
  /**
   * Initializes a Xataface application.
   *
   * @param string  $sitePath    The path to your site's access point.
   * @param boolean $debug       Whether to start the application in debug mode.
   *
   * @return Dataface_Application The Xataface application object.
   */
  public function __construct($sitePath, $debug = false) {
    init($sitePath, '/vendor/shannah/xataface/xataface');
    require_once dirname(__DIR__) . '/xataface/Dataface/Application.php';

    $conf = $debug ? ['debug' => true] : null;
    $this->app = \Dataface_Application::getInstance($conf);
  }
  
  /**
   * Displays the Xataface application.
   */
  public function display() {
    $this->app->display();
  }
}