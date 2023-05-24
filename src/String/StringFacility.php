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

  public static function toDate(string $model): ?\DateTime {
    if(
      preg_match("/^(?<year>\d{4})[\/-](?<month>\d{2})[\/-](?<day>\d{2})$/", trim($model), $matches)
      ||
      preg_match("/^(?<day>\d{2})\/(?<month>\d{2})\/(?<year>\d{4})$/", trim($model), $matches)
    )
      return new \DateTime($matches['year'].'-'.$matches['month'].'-'.$matches['day']);
    return null;
  }

  public static function toInt(string $model): ?int {
    return (preg_match("/^\d+$/", trim($model))) ? (int)$model : null;
  }

  public static function isText(string $model): bool {
    return (mb_strlen(trim($model))>255);
  }
}
