<?php
namespace FOPG\Component\UtilsBundle\Env;

/**
 * Classe utilisée pour la récupération des variables d'environnement
 *
 */
class Env
{
  /**
   * La donnée est t'elle une tableau
   *
   * @param string $param Valeur à analyser
   * @return bool
   */
  private static function isArray(string $param): bool {
    return preg_match("/^[ ]*[{](.*)[}][ ]*$/", $param);
  }

  /**
   * Conversion de la donnée textuelle en donnée typée
   *
   * @param string $param Paramètre à convertir
   * @return mixed Paramètre typé
   */
  private static function cast(string $param): mixed {
    $val = trim($param);
    if(in_array($val,['true','false']))
      $val = ($val === 'true');
    elseif(preg_match("/^\d$/",$val))
      $val = (int)$val;
    else
      $val = preg_replace("/(^'|'$)/","", $val);
    return $val;
  }

  /**
   * Récupération d'un tableau de donnée d'un paramètre d'environnement
   *
   * @param string $param Paramètre à convertir en tableau
   * @return array Tableau résultant
   */
  private static function getArray(string $param): array {
    $output = [];
    if(true === self::isArray($param)) {
      preg_match_all("/[ ]*[']?(?<key>[^'{,]+)[']?[ ]*[:][ ]*(?<value>[']?[^},]+[']?)[ ]*/i", $param, $matches);
      $count = count($matches['key']);
      for($i=0;$i<$count;$i++) {
        $val = self::cast($matches['value'][$i]);
        $output[$matches['key'][$i]]=$val;
      }
    }
    return $output;
  }

  final public static function set(string $param, mixed $value): void {
    $_ENV[$param] = $value;
  }
  /**
   * Récupération de la variable d'environnement courante
   *
   * @param string $param
   * @return mixed
   */
  final public static function get(string $param): mixed {
    $block = $_ENV[$param] ?? null;
    if(null === $block)
      return null;
    return self::isArray($block) ? self::getArray($block) : self::cast($block);
  }
}
