<?php

namespace FOPG\Component\UtilsBundle\Collection;

use FOPG\Component\UtilsBundle\Contracts\CollectionInterface;
use FOPG\Component\UtilsBundle\Exception\InvalidArgumentException;

class Collection implements CollectionInterface, \Iterator {

  private array $_values = [];
  protected array $_keys = [];
  private $_callback = null;
  private $_cmpAlgorithm = null;
  private int $_current = 0;

  /**
   * Constructeur
   *
   * @param array $array Tableau servant de source de donnée
   * @param ?Callable $callback Fonction d'extraction de l'index
   * @param ?Callable $cmpAlgorithm Méthode de comparaison sur les index
   */
  public function __construct(array $array=[], ?Callable $callback=null, ?Callable $cmpAlgorithm=null) {

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

  public function current(): mixed {
    return $this->get($this->_current);
  }

  public function next(): mixed {
    if(false === $this->valid())
      return null;
    $current = $this->get($this->_current);
    $this->_current++;
    return $current;
  }

  public function key(): mixed {
    return $this->_current;
  }

  public function valid(): bool {
    return ($this->_current < count($this->_keys));
  }

  public function rewind(): void {
    $this->_current = 0;
  }
  /**
   * Suppression d'un élément du tableau
   *
   * @param mixed $index Valeur d'index à rechercher pour suppression
   * @return bool
   */
  public function remove(mixed $index): bool {
    /** @var int|false $keyIndex */
    $keyIndex = array_search($index, $this->_keys);
    if(false === $keyIndex)
      return false;
    /** @var int $size */
    $size = count($this->_keys);
    /** @var mixed $tmp */
    $tmp = $this->_keys[$keyIndex];
    unset($this->_values[$tmp]);
    /** @var int $i */
    for($i = $keyIndex; $i<$size;$i++)
      $this->_keys[$i]=$this->_keys[$i+1];
    unset($this->_keys[$size-1]);
    return true;
  }

  /**
   * Ajout d'un élément au tableau
   *
   * @param mixed $item
   * @param mixed $index
   * @param bool $includeSort Option qui impose que la valeur ajoutée respecte le tri du tableau
   * @return self
   */
  public function add(mixed $item, mixed $index=null, bool $includeSort = false): CollectionInterface {
    $callback = $this->_callback;
    $realIndex = $callback($index, $item);
    $this->_values[$realIndex] = $item;
    $this->_keys[count($this->_keys)]=$realIndex;
    if(true === $includeSort) {
      $last = count($this->_keys)-1;
      $this->insertionSort($last);
    }
    return $this;
  }

  public function getCmpAlgorithm(): Callable {
    return $this->_cmpAlgorithm;
  }

  public function getKeys(): array {
    return $this->_keys;
  }

  /**
   * Récupération des valeurs triés
   *
   * @return array
   */
  public function getValues(): array {
    $tab=[];
    for($i=0;$i<$this->count();$i++) {
      $tab[]=$this->get($i);
    }
    return $tab;
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
  public function get(int $index=null): mixed {
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
   * Compléxité en temps : O(n lg n)
   * Compléxité en espace: O(1)
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
   * Validation que l'ensemble des clés en tant qu'entier strict
   *
   * Compléxité en temps : O(n)
   *
   * @throw InvalidArgumentException
   */
  private function _assertIntOnlyInKeys(): void {
    foreach($this->_keys as $key)
      if(!is_int($key))
        throw new InvalidArgumentException('key '.$key.' is forbidden (only int accepted)');
  }

  /**
   * Tri par dénombrement
   *
   * Les valeurs triés doivent être des entiers
   *
   * Compléxité en temps : O(n)
   * Compléxité en espace: O(n)
   *
   * @return self
   */
  public function countingSort(): self {
    $this->_assertIntOnlyInKeys();
    /** @var int $ln */
    $ln = count($this->_keys);
    /** @var ?int $min */
    $min = null;
    /** @var ?int $max */
    $max = null;
    $isReverse = false;
    $this->findMinMax(min: $min, max: $max);
    if($min>$max) {
      $isReverse = true;
      $tmp = $min;
      $min = $max;
      $max = $tmp;
    }
    /** @var array $b */
    $b = [];
    for($i=$min; $i<=$max;$i++)
      $b[$i]=0;

    for($i=0;$i<$ln;$i++)
      $b[$this->_keys[$i]]++;

    for($i=$min+1;$i<=$max;$i++)
      $b[$i]+=$b[$i-1];
    /** @var array<int,int> $c */
    $c=[];
    for($i=$ln-1;$i>=0;$i--) {
      $key = $this->_keys[$i];
      $val = $b[$key];
      $c[$val-1]=$key;
      $b[$key]--;
    }

    for($i=0;$i<$ln;$i++) {
      $j = (false === $isReverse) ? $i : ($ln - $i -1);
      $this->_keys[$j] = $c[$i];
    }

    return $this;
  }

  /**
   * Tri de sélection des extrêmums
   *
   * La notion de minimum et maximum est fonction de la méthode de comparaison.
   * Il peut ainsi survenir de façon contre intuitive qu'un maximum puisse être inférieur au
   * maximum au sens logique
   *
   * @param $min Valeur minimale trouvée
   * @param $max Valeur maximale trouvée
   * @return self
   */
  public function findMinMax(mixed &$min, mixed &$max): self {
    /** @var int $mid */
    $mid = (int)(count($this->_keys)/2);
    /** @var bool $isEven */
    $isEven = (count($this->_keys)%2 === 0);
    /** @var mixed $min */
    $min = $this->_keys[0];
    /** @var Callable $cmpAlgorithm */
    $cmpAlgorithm = $this->_cmpAlgorithm;
    /** @var mixed $max */
    $max = $min;
    /** @var int $inc */
    $inc = -1;
    if(true === $isEven) {
      $inc = 0;
      $max = $this->_keys[1];
      if(true === $cmpAlgorithm($min,$max)) {
        $tmp = $min;
        $min = $max;
        $max = $tmp;
      }
    }
    else {
      $mid+=1;
    }

    for($i=2;$i<=$mid;$i++) {
      $indexA=2*($i-1);
      $indexA+=$inc;
      $indexB=$indexA+1;
      $lmin = $this->_keys[$indexA];
      $lmax = $this->_keys[$indexB];

      if(true === $cmpAlgorithm($lmin, $lmax)) {
        $tmp = $lmin;
        $lmin = $lmax;
        $lmax = $tmp;
      }
      if(true === $cmpAlgorithm($min, $lmin)) {
        $tmp = $lmin;
        $min = $lmin;
        $lmin = $tmp;
      }
      if(true === $cmpAlgorithm($lmax, $max)) {
        $tmp = $lmax;
        $max = $lmax;
        $lmax = $tmp;
      }
    }
    return $this;
  }

  /**
   * Triage par tri rapide
   *
   * @complexity O(n lg n)
   */
  public function quickSort(): self {
    $first = 0;
    $last = count($this->_keys)-1;
    $this->_makeSubQuickSort($first, $last);
    return $this;
  }

  private function _makeSubQuickSort(int $p, int $q): void {
    if($p>$q)
      return;
    $r = $this->_findSeparatorOfQuickSort($p,$q);
    $this->_makeSubQuickSort($p,$r-1);
    $this->_makeSubQuickSort($r+1,$q);
  }

  private function _findSeparatorOfQuickSort(int $p, int $q): int {
    /** optimisation pour garantir un tableau équilibré */
    $rand = rand($p,$q);
    $tmp = $this->_keys[$q];
    $this->_keys[$q]=$this->_keys[$rand];
    $this->_keys[$rand]=$tmp;

    /** séparation des min/max */
    $max = $this->_keys[$q];
    $cmpAlgorithm = $this->_cmpAlgorithm;
    $i = $p-1;
    for($j=$p;$j<$q;$j++) {
      $valJ = $this->_keys[$j];
      if(true === $cmpAlgorithm($valJ,$max)) {
        $i++;
        $this->_keys[$j] = $this->_keys[$i];
        $this->_keys[$i] = $valJ;
      }
    }
    $this->_keys[$q]=$this->_keys[$i+1];
    $this->_keys[$i+1]=$max;
    return $i+1;
  }

  /**
   * Processus d'initialisation du tri par tas
   *
   * @return self
   */
  protected function _initHeapSort(): self {
    $len = count($this->_keys);
    $size = $len-1;

    for($i=(int)($size/2);$i>=0;$i--) {
      $parent = (int)(($i-1)/2);
      $h = $i;
      $cmpAlgorithm = $this->_cmpAlgorithm;
      $this->_makeSubHeapSort($h, $size);
    }
    return $this;
  }

  /**
   * tri par tas
   *
   * Compléxité : O(n lg n)
   */
  public function heapSort(): self {
    $len = count($this->_keys);
    $size = $len-1;

    $this->_initHeapSort();

    for($i=$size;$i>0;$i--) {
      $tmp = $this->_keys[$i];
      $this->_keys[$i] = $this->_keys[0];
      $this->_keys[0] = $tmp;
      $this->_makeSubHeapSort(0, $i-1);
    }
    $this->_keys = array_reverse($this->_keys);

    return $this;
  }

  protected function riseLastElementInHeapSort(): void {
    $last = count($this->_keys)-1;
    $this->_makeRiseHeapSort($last);
  }

  protected function _makeRiseHeapSort(int $i): void {
    $parent = self::parent($i);
    if(null === $parent)
      return;
    /** @var Callable $cmpAlgorithm */
    $cmpAlgorithm = $this->_cmpAlgorithm;
    if(false === $cmpAlgorithm($this->_keys[$parent], $this->_keys[$i])) {
      $tmp = $this->_keys[$i];
      $this->_keys[$i] = $this->_keys[$parent];
      $this->_keys[$parent] = $tmp;
    }
    $this->_makeRiseHeapSort($parent);
  }

  protected static function parent(int $i): ?int { return ($i>0) ? (int)(($i-1)/2) : null; }
  protected static function left(int $i): int { return (2*$i)+1; }
  protected static function right(int $i): int { return self::left($i)+1; }
  /**
   * Méthode de déplacement d'une valeur i
   *
   * @param int $i
   * @param int $size
   */
  protected function _makeSubHeapSort(int $i, int $size): void {
    /** @var int $left */
    $left = self::left($i);
    /** @var int $right */
    $right= self::right($i);
    /** @var Callable $cmpAlgorithm */
    $cmpAlgorithm = $this->_cmpAlgorithm;
    $max = $i;

    if(($left <= $size) && (true === $cmpAlgorithm($this->_keys[$left], $this->_keys[$max])))
      $max = $left;

    if(($right <= $size) && (true === $cmpAlgorithm($this->_keys[$right], $this->_keys[$max])))
      $max = $right;
    if($max !== $i) {
      $tmp = $this->_keys[$max];
      $this->_keys[$max] = $this->_keys[$i];
      $this->_keys[$i] = $tmp;

      $this->_makeSubHeapSort($max, $size);
    }
  }

  /**
   * Tri par insertion
   *
   * Compléxité : O(n^2)
   *
   * @param int $first
   */
  public function insertionSort(int $first=1): self {
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
