<?php

namespace Xataface;

class ModuleWrapper {

  public static function wrap(string $name, string $path): Module {
    return new class($name, $path) implements Module {
      
      private $name, $path;
      
      function __construct(string $name, string $path) {
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
