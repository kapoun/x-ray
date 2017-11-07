<?php

namespace Xataface;

class Config {

  public  $conf;
  
  private $defaultLimit = 30;
  private $defaultTable;

    /**
   * Creates a configuration which can be passed to a Xataface application.
   * 
   * @param array $array An array to create configuration from.
   */
  public function __construct(array $array = []) {
    $this->conf = $array;
  }
  
  public function getDefaultLimit() {
    return $this->defaultLimit;
  }

  public function getDefaultTable() {
    return $this->defaultTable;
  }

  public function setDefaultLimit($defaultLimit) {
    $this->defaultLimit = $defaultLimit;
    return $this;
  }

  public function setDefaultTable($defaultTable) {
    $this->defaultTable = $defaultTable;
    return $this;
  }

  /**
   * Converts the object to a configuration array.
   */
  public function toArray(): array {
    $array = [
      'default_limit' => $this->defaultLimit
    ];
    return \array_merge($this->conf, $array);
  }

  /**
   * Creates a configuration using values loaded from an INI file.
   */
  public static function fromIni(string $path): Config {
    $array = \parse_ini_file($path, true);
    $conf  = new Config($array);
    
    if (@$array['default_limit'])
      $conf->defaultLimit = $array['default_limit'];
    if (@$array['default_table'])
      $conf->defaultTable = $array['default_table'];
    
    return $conf;
  }

}
