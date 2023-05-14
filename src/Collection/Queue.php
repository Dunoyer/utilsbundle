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

  /**
   * Fonction de suppression de l'élément majoritaire avec remontée du prochain majoritaire
   *
   * @return void
   */
  private function dropMaxAndHeapSort(): void {
    $len = $this->count();
    $size = $len-1;
    $this->permute_keys(0,$size);
    $this->remove_key_by_index($size);
    $this->_makeSubHeapSort(0, $size-1);

  }

  /**
   * Ajout d'un élément dans la file de priorité
   *
   * @param mixed $item
   * @param mixed $index
   * @param bool $includeSort
   * @return CollectionInterface
   */
  public function add(mixed $item, mixed $index=null, bool $includeSort = false): CollectionInterface {
    parent::add(item: $item, index: $index, includeSort: false);
    parent::riseLastElementInHeapSort();
    return $this;
  }

  /**
   * Récupération du premier élément dans la file de priorité
   *
   * La récupération entraîne la sortie de cet élément de la file de priorité
   * @param ?int $index ignoré (présent pour la compatibilité avec la classe parente)
   * @return mixed
   */
  public function get(?int $index=null): mixed {
    $index = $this->get_key(0) ?? null;
    if(null === $index)
      return null;
    $obj = parent::get(0);
    $this->dropMaxAndHeapSort();
    return $obj;
  }
}
