<?php

namespace FOPG\Component\UtilsBundle\Collection;

use FOPG\Component\UtilsBundle\Contracts\CollectionInterface;
use FOPG\Component\UtilsBundle\Exception\InvalidArgumentException;

class Collection implements CollectionInterface, \Iterator {

  use CollectionKeyManagerTrait;

  private array $_values = [];
  protected array $_newValues = [];

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
      $uniqid = $this->append_key($realIndex);
      $this->_values[$realIndex] = $item;
      $this->_newValues[$uniqid]=$item;
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
    return ($this->_current < $this->count());
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
    return $this->remove_key($index);
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
    $uniqid=$this->append_key($realIndex);
    $this->_values[$realIndex] = $item;
    $this->_newValues[$uniqid] = $item;
    if(true === $includeSort) {
      $last = $this->count()-1;
      $this->insertionSort($last);
    }
    return $this;
  }

  public function getCmpAlgorithm(): Callable {
    return $this->_cmpAlgorithm;
  }

  public function getKeys(): array {
    return $this->get_keys();
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
    $origin = $this->search_in_keys($keyOrigin);

    /** @var int|bool $target */
    $target = $this->search_in_keys($keyTarget);
    /** @var ?CollectionKey $memTarget */
    $memTarget = $this->_newKeys[$target];

    if(false !== $origin && false !== $target) {
      for($i=$target-1;$i>=$origin;$i--)
        $this->_keys[$i+1] = $this->_keys[$i];
      $this->_keys[$origin]=$keyTarget;
      for($i=$target-1;$i>=$origin;$i--)
        $this->_newKeys[$i+1] = $this->_newKeys[$i];
      $this->_newKeys[$origin]=$memTarget;
      return true;
    }
    return false;
  }

  /**
   * Récupération de l'index pour une clé donnée
   *
   * @param mixed $key
   * @return int|bool
   */
  private function search_in_keys(mixed $key): mixed {
    foreach($this->_newKeys as $index => $cKey)
      if($key === $cKey->getKey())
        return $index;
    return false;
  }

  /**
   * @param mixed $keyOrigin
   * @param mixed $keyTarget
   * @return bool
   */
  public function permute(mixed $keyOrigin, mixed $keyTarget): bool {
    /** @var int|bool $origin */
    $origin = $this->search_in_keys($keyOrigin);
    /** @var int|bool $target */
    $target = $this->search_in_keys($keyTarget);
    if(false !== $origin && false !== $target) {
      $tmp = $this->_newKeys[$target];
      $this->_keys[$origin] = $keyTarget;
      $this->_newKeys[$target] = $this->_newKeys[$origin];
      $this->_keys[$target] = $keyOrigin;
      $this->_newKeys[$origin] = $tmp;
      return true;
    }
    return false;
  }

  /**
   * Application d'un arrangement aléatoire uniforme
   *
   */
  public function shuffle(): self {
    $last = $this->count()-1;
    for($i=0;$i<=$last;$i++) {
      $rand = rand($i,$last);
      $this->permute_keys($i,$rand);
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
    return $this->count_keys();
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
    $last = $this->count()-1;
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
    foreach($this->_newKeys as $ckey)
      if(!is_int($ckey->getKey()))
        throw new InvalidArgumentException('key '.$ckey->getKey().' is forbidden (only int accepted)');
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
    $ln = $this->count();
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
      $b[$this->get_key_by_index($i)]++;

    for($i=$min+1;$i<=$max;$i++)
      $b[$i]+=$b[$i-1];
    /** @var array<int,int> $c */
    $c=[];
    for($i=$ln-1;$i>=0;$i--) {
      $key = $this->get_key_by_index($i);
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
    $mid = (int)($this->count()/2);
    /** @var bool $isEven */
    $isEven = ($this->count()%2 === 0);
    /** @var mixed $min */
    $min = $this->_newKeys[0]->getKey();
    /** @var Callable $cmpAlgorithm */
    $cmpAlgorithm = $this->_cmpAlgorithm;
    /** @var mixed $max */
    $max = $min;
    /** @var int $inc */
    $inc = -1;
    if(true === $isEven) {
      $inc = 0;
      $max = $this->_newKeys[1]->getKey();
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
      $lmin = $this->_newKeys[$indexA]->getKey();
      $lmax = $this->_newKeys[$indexB]->getKey();

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
    $last = $this->count()-1;
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

    $this->permute_keys($q, $rand);

    /** séparation des min/max */
    /** @var mixed $max */
    $max = $this->get_key_by_index($q);
    /** @var CollectionKey $obj */
    $obj = $this->get_key($q);
    $cmpAlgorithm = $this->_cmpAlgorithm;
    $i = $p-1;
    for($j=$p;$j<$q;$j++) {
      $valJ = $this->get_key_by_index($j);
      if(true === $cmpAlgorithm($valJ,$max)) {
        $i++;
        $this->permute_keys($j,$i);
      }
    }
    $obj2 = $this->get_key($i+1);
    $this->set_key($q, $obj2);
    $this->set_key($i+1, $obj);
    return $i+1;
  }

  /**
   * Processus d'initialisation du tri par tas
   *
   * @return self
   */
  protected function _initHeapSort(): self {
    $len = $this->count();
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
    $len = $this->count();
    $size = $len-1;

    $this->_initHeapSort();

    for($i=$size;$i>0;$i--) {
      $this->permute_keys($i,0);
      $this->_makeSubHeapSort(0, $i-1);
    }

    $this->reverse_keys();

    return $this;
  }

  protected function riseLastElementInHeapSort(): void {
    $last = $this->count()-1;
    $this->_makeRiseHeapSort($last);
  }

  protected function _makeRiseHeapSort(int $i): void {
    $parent = self::parent($i);
    if(null === $parent)
      return;
    /** @var Callable $cmpAlgorithm */
    $cmpAlgorithm = $this->_cmpAlgorithm;
    /** @var mixed $keyParent */
    $keyParent = $this->get_key_by_index($parent);
    /** @var mixed $keyI */
    $keyI = $this->get_key_by_index($i);
    if(false === $cmpAlgorithm($keyParent, $keyI))
      $this->permute_keys($i, $parent);

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
      $this->permute_keys($max, $i);
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
    $last = $this->count();
    $cmpAlgorithm = $this->_cmpAlgorithm;

    for($i=$first;$i<$last;$i++) {
      $current2 = $this->_newKeys[$i];
      $j=$i;
      while($j>0 && (false === $cmpAlgorithm($this->get_key_by_index($j-1), $current2->getKey()))) {
        $tmp = $this->set_key($j, $this->get_key($j-1));
        $j--;
      }
      $this->set_key($j, $current2);
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
