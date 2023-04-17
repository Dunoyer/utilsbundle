<?php

namespace FOPG\Component\UtilsBundle\Collection;

use FOPG\Component\UtilsBundle\Contracts\CollectionInterface;

class Queue extends Collection {

  /**
   * Constructeur
   *
   * @param array $array Tableau servant de source de donnée
   * @param ?Callable $callback Fonction d'extraction de l'index
   * @param ?Callable $cmpAlgorithm Méthode de comparaison sur les index
   */
  public function __construct(array $array=[], ?Callable $callback=null, ?Callable $cmpAlgorithm=null) {
    parent::__construct(array: $array,callback: $callback, cmpAlgorithm: $cmpAlgorithm);
    $this->_initHeapSort();
  }

  private function dropMaxAndHeapSort(): void {
    $len = count($this->_keys);
    $size = $len-1;
    $tmp = $this->_keys[$size];
    $this->_keys[$size] = $this->_keys[0];
    $this->_keys[0] = $tmp;
    $this->remove($this->_keys[$size]);
    $this->_makeSubHeapSort(0, $size-1);

  }

  public function add(mixed $item, mixed $index=null, bool $includeSort = false): CollectionInterface {
    dump('@todo');die;
    parent::add(item: $item, index: $index, includeSort: false);
    $this->permuteLastToOneAndHeapSort();
    return $this;
  }

  public function get(?int $index=null): mixed {
    $index = $this->_keys[0];
    $obj = parent::get(0);
    $this->dropMaxAndHeapSort();
    return $obj;
  }
}
