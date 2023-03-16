<?php

namespace FOPG\Component\UtilsBundle\String;

class StringFacility
{
  /**
   * Fonction de formatage en chaîne de type snakeCase
   *
   * @param string $model
   * @return string
   */
  public static function toSnakeCase(string $model): string {
      $tmp = preg_replace("/([A-Z]+)/","_$1", $model);
      $tmp = preg_replace("/^[_]/","",mb_strtolower($tmp));
      return $tmp;
  }

  public static function toCamelCase(string $model): string {

    $tmp = explode("_", $model);
    foreach($tmp as $index => $_)
      if($index > 0)
        $tmp[$index] = ucfirst($_);
    return implode("", $tmp);
  }
}
