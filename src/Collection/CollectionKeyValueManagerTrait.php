<?php

namespace FOPG\Component\UtilsBundle\Collection;

trait CollectionKeyValueManagerTrait {

    protected array $_newKeys = [];
    protected array $_values = [];

    /**
     * Ajout d'un élément dans la collection
     *
     * @param mixed $key Clé
     * @param mixed $item Information associée
     * @return string Renvoi de l'identifiant unique généré
     */
    public function append(mixed $key, mixed $item): void {
      $tmp = new CollectionKey($key);
      $this->_newKeys[$this->count()]=$tmp;
      $uniqid = $tmp->getUniqid();
      $this->_values[$uniqid]=$item;
    }
    public function reverse_keys(): void {
      $this->_newKeys = array_reverse($this->_newKeys);
    }

    /**
     * Récupération d'une valeur de la collection à partir de son index
     *
     * @param int $index
     * @return mixed
     */
    public function get_value_by_index(int $index): mixed {
      /** @var ?CollectionKey $key */
      $key = $this->get_key($index) ?? null;
      return (null !== $key) ? $this->_values[$key->getUniqid()] : null;
    }

    public function get_keys(): array {
      $output = [];
      array_walk($this->_newKeys, function(mixed $item)use(&$output) {
        $output[]=$item->getKey();
      });
      return $output;
    }

    public function set_key(int $index, CollectionKey $key): bool {
      if($index >= $this->count_keys())
        return false;

      $this->_newKeys[$index]=$key;
      return true;
    }

    public function get_key(int $index): ?CollectionKey {
      return $this->_newKeys[$index] ?? null;
    }

    public function get_uniqid_by_index(int $index): ?string {
      return !empty($this->_newKeys[$index]) ? $this->_newKeys[$index]->getUniqid() : null;
    }

    public function get_key_by_index(int $index): mixed {
      return !empty($this->_newKeys[$index]) ? $this->_newKeys[$index]->getKey() : null;
    }

    public function get_index_by_key(mixed $key): ?int {
      $targetIndex = null;
      array_walk($this->_newKeys, function(CollectionKey $newKey, int $index)use($key,&$targetIndex) {
        if($newKey->getKey() === $key)
          $targetIndex = $index;
      });
      return $targetIndex;
    }

    public function permute_keys(int $internalIndexA, int $internalIndexB): void {
      $tmp = $this->_newKeys[$internalIndexA];
      $this->_newKeys[$internalIndexA] = $this->_newKeys[$internalIndexB];
      $this->_newKeys[$internalIndexB] = $tmp;
    }

    public function remove_key_by_index(int $internalIndex): bool {
      return $this->remove_key(index: null, internalIndex: $internalIndex);
    }

    /**
     * Suppression d'une clé
     *
     * Si la position de représentation est renseignée, elle prend l'ascendant sur la clé de recherche
     *
     * @param mixed $index
     * @param ?int $internalIndex Position dans le tableau de représentation
     * @return bool
     */
    public function remove_key(mixed $index, ?int $internalIndex=null): bool {

      /** @var int|null $keyIndex */
      $keyIndex = $internalIndex ?? $this->get_index_by_key($index);

      if(null === $keyIndex)
        return false;

      /** @var int $size */
      $size = $this->count();
      /** @var CollectionKey $tmp */
      $tmp = $this->get_key($keyIndex);
      unset($this->_values[$tmp->getUniqid()]);
      /** @var int $i */
      for($i = $keyIndex; $i<$size;$i++) {
        $tmp = $this->get_key($i+1);
        if(null !== $tmp)
          $this->set_key($i, $tmp);
      }
      unset($this->_newKeys[$size-1]);
      return true;
    }

    public function count_keys(): int {
      return count($this->_newKeys);
    }
}
