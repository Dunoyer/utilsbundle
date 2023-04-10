<?php

namespace FOPG\Component\UtilsBundle\Collection;

class Collection {

  private array $_values = [];
  private array $_keys = [];
  private $_callback = null;
  private $_cmpAlgorithm = null;

  /**
   * Constructeur
   *
   * @param array $array Tableau servant de source de donnée
   * @param ?Callable $callback Fonction d'extraction de l'index
   * @param ?Callable $cmpAlgorithm Méthode de comparaison sur les index
   */
  public function __construct(array $array, ?Callable $callback=null, ?Callable $cmpAlgorithm=null) {

    if(null === $callback)
      $callback = function($index, $item) { return $index; };
    $this->_callback = $callback;

    if(null === $cmpAlgorithm)
      $cmpAlgorithm = function($a,$b): bool { return ($a > $b); };
    $this->_cmpAlgorithm = $cmpAlgorithm;

    foreach($array as $index => $item) {
      /** @var mixed $realIndex */
      $realIndex = $callback($index, $item);
      $this->_values[$realIndex] = $item;
      $this->_keys[]=$realIndex;
    }
  }

  public function getKeys(): array {
    return $this->_keys;
  }

  public function __toString(): string {
    /** @var string $keys */
    $keys = implode(",",$this->_keys);
    return "<".$keys.">";
  }

  /**
   * Fonction de réarrangement: le dernier élément prend à la place du premier élément par décalage vers la droite.
   *
   * Un contrôle est assuré via l'algorithme de comparaison pour garantir le respect de la cohérence de tri
   *
   * @param mixed $keyOrigin
   * @param mixed $keyTarget
   * @return bool
   */
  public function insertLastToLeft(mixed $keyOrigin, mixed $keyTarget): bool {
    /** @var int|bool $origin */
    $origin = array_search($keyOrigin, $this->_keys);
    /** @var int|bool $target */
    $target = array_search($keyTarget, $this->_keys);
    if(false !== $origin && false !== $target) {
      for($i=$target-1;$i>=$origin;$i--)
        $this->_keys[$i+1] = $this->_keys[$i];
      $this->_keys[$origin]=$keyTarget;
      return true;
    }
    return false;
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
    $last = count($this->_keys)-1;
    for($i=0;$i<=$last;$i++) {
      $rand = rand($i,$last);
      $tmp = $this->_keys[$i];
      $this->_keys[$i] = $this->_keys[$rand];
      $this->_keys[$rand] = $tmp;
    }
    return $this;
  }

  /**
   * Récupération de l'instance positionné en index-ème position
   *
   * Il y'a renvoi de null si l'index n'est pas reconnu
   * @param int $index
   * @return mixed
   */
  public function get(int $index): mixed {
    /** @var mixed $realIndex */
    $realIndex = $this->_keys[$index] ?? null;
    return (null !== $realIndex) ? $this->_values[$realIndex] : null;
  }

  /**
   * Récupération du nombre d'éléments dans la collection
   *
   * @return int
   */
  public function count(): int {
    return count($this->_keys);
  }

  /**
   * Triage par fusion
   *
   * Compléxité : O(n lg n)
   *
   * @return self
   */
  public function mergeSort(): self {
    $first = 0;
    $last = count($this->_keys)-1;
    $this->_makeSubMergeSort($first, $last);
    return $this;
  }

  /**
   * Tri par insertion
   *
   * Compléxité : O(n^2)
   */
  public function insertionSort(): self {
    $first = 1;
    $last = count($this->_keys);
    $cmpAlgorithm = $this->_cmpAlgorithm;

    for($i=$first;$i<$last;$i++) {
      $current = $this->_keys[$i];
      $j=$i;
      while($j>0 && (false === $cmpAlgorithm($this->_keys[$j-1], $current))) {
        $this->_keys[$j] = $this->_keys[$j-1];
        $j--;
      }
      $this->_keys[$j]=$current;
    }

    return $this;
  }

  private function _makeSubMergeSort(int $i, int $j): void {

    $cmpAlgorithm = $this->_cmpAlgorithm;

    if($i === $j)
      return;

    $h = (int)(($i+$j)/2);

    $this->_makeSubMergeSort($i,$h);
    $this->_makeSubMergeSort($h+1,$j);

    $left = [];
    for($w=$i;$w<=$h;$w++)
      $left[]=$this->_keys[$w];

    $right = [];
    for($w=$h+1;$w<=$j;$w++)
      $right[]=$this->_keys[$w];

    $current = $i;
    $w=0;
    $z=0;

    do {
      /** @var ?int $indL */
      $indL = $left[$w] ?? null;
      /** @var ?int $indR */
      $indR = $right[$z] ?? null;

      if(null === $indL) {
        do {
          $this->_keys[$current]=$indR;
          $z++;
          $current++;
        }
        while(null !== ($indR = $right[$z] ?? null));
        return;
      }
      if(null === $indR) {
        do {
          $this->_keys[$current]=$indL;
          $w++;
          $current++;
        }
        while(null !== ($indL = $left[$w] ?? null));
        return;
      }

      if(true === $cmpAlgorithm($indL, $indR)) {
        $this->_keys[$current]=$indL;
        $w++;
      }
      else {
        $this->_keys[$current]=$indR;
        $z++;
      }
      $current++;
    }
    while(true);
  }
}
