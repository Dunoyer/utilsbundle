<?php

namespace FOPG\Component\UtilsBundle\Collection;

use \Ds\Map as DsMap;

class Collection {

  private ?DsMap $_map = null;
  private array $_keys = [];

  public function __construct(array $array, Callable $callback) {
    $this->_map = new DsMap();
    foreach($array as $index => $item) {
      /** @var mixed $realIndex */
      $realIndex = $callback($index, $item);
      $this->_map->put($realIndex, $item);
      if(!in_array($realIndex, $this->_keys))
        $this->_keys[]=$realIndex;
    }
  }

  public function getMap(): ?DsMap {
    return $this->_map;
  }

  public function getKeys(): array {
    return $this->_keys;
  }
  public function __toString(): string {
    /** @var \Ds\Set $keys */
    $keys = implode(",",$this->_keys);
    return "<".$keys.">";
  }

  /**
   * @param mixed $keyOrigin
   * @param mixed $keyTarget
   * @return bool
   */
  public function permute(mixed $keyOrigin, mixed $keyTarget): bool {
    /** @var int|bool $origin */
    $origin = array_search($keyOrigin, $this->_keys);
    /** @var int|bool $target */
    $target = array_search($keyTarget, $this->_keys);
    if(false !== $origin && false !== $target) {
      $this->_keys[$origin] = $keyTarget;
      $this->_keys[$target] = $keyOrigin;
      return true;
    }
    return false;
  }

  /**
   * Application d'un arrangement aléatoire uniforme
   *
   */
  public function shuffle(): self {
    $len = count($this->_keys);
    for($i=0;$i<=$len-1;$i++) {
      $rand = rand($i,$len);
      $this->permute($i, $rand);
    }
    return $this;
  }
}
