<?php

namespace Xataface;

class ModuleWrapper {

  public static function wrap($name, $path): Module {
    return new class($name, $path) implements Module {
      
      private $name, $path;
      
      function __construct($name, $path) {
        $this->name = $name;
        $this->path = $path;
      }
      
      function getName(): string {
        return $this->name;
      }
      
      function getPath(): string {
        return $this->path;
      }
      
    };
  }

}
