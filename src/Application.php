<?php

namespace Xataface;

require_once dirname(__DIR__).'/xataface/public-api.php';
require_once dirname(__DIR__).'/xataface/PEAR.php';

/**
 *  A class representing the whole Xataface application.
 */
class Application {

  private $app;
  private $config;

  /**
   * Initializes a Xataface application.
   *
   * @return Dataface_Application The Xataface application object.
   */
  public function __construct(Config $config) {
    define('DATAFACE_SITE_HREF', '');
    define('DATAFACE_SITE_PATH', '.');
    define('DATAFACE_SITE_URL', '');
    define('DATAFACE_URL', 'vendor/shannah/xataface/xataface');
    require_once dirname(__DIR__).'/xataface/config.inc.php';
    if (@$_GET['-action'] == 'js')
      require_once dirname(__DIR__).'/xataface/js.php';
    if (@$_GET['-action'] == 'css')
      require_once dirname(__DIR__).'/xataface/css.php';

    $this->config  = $config;
  }

//  /**
//   * Adds a new configuration and combines it with the current one (if any). The
//   * values present in the current configuration are either overwritten (if
//   * there is a corresponding value in the new configuration) or left untouched
//   * (if there is not). Values not yet present in the current configuration are
//   * simply added.
//   */
//  public function addConfiguration(Config $configuration): Application {
//    $array      = $configuration->toArray();
//    $this->config = \array_merge($this->config, $array);
//    return $this;
//  }

  /**
   * Adds an optional module to the application.
   */
  public function addModule(Module $module): Application {
    $name = $module->getName();
    $path = $module->getPath();
    $this->config->conf['_modules']["modules_{$name}"] = $path;
    return $this;
  }
  
  public function getConfig(): Config {
    return $this->config;
  }

  /**
   * Runs and displays the Xataface application.
   */
  public function run() {
    require_once dirname(__DIR__).'/xataface/Dataface/Application.php';
    $conf = $this->config->toArray();
    $this->app = \Dataface_Application::getInstance($conf);
    $this->app->display();
  }

  /**
   * A static version of the constructor. The function is the same.
   */
  public static function create(Config $config): Application {
    return new Application($config);
  }

}
