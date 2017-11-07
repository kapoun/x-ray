<?php

namespace Xataface;

class Configuration {

  private $conf;

  /**
   * Creates a configuration which can be passed to a Xataface application.
   * 
   * @param array $array An array to create configuration from.
   */
  public function __construct(array $array = []) {
    $this->conf = $array;
  }

  /**
   * Converts the object to a configuration array.
   */
  public function toArray(): array {
    return $this->conf;
  }

  /**
   * Creates a configuration using values loaded from an INI file.
   */
  public static function fromIni(string $path): Configuration {
    $array = \parse_ini_file($path);
    $conf  = new Configuration($array);
    return $conf;
  }

}
