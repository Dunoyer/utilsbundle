<?php

namespace FOPG\Component\UtilsBundle\Collection;

trait CollectionKeyManagerTrait {
    protected array $_keys = [];
    protected array $_newKeys = [];

    /**
     * Ajout d'une clé dans la collection
     *
     * @param mixed $index Clé
     * @return string Renvoi de l'identifiant unique généré
     */
    public function append_key(mixed $index): string {
      $this->_keys[$this->count()]=$index;
      $tmp = new CollectionKey($index);
      $this->_newKeys[$this->count()]=$tmp;
      return $tmp->getUniqid();
    }

    public function reverse_keys(): void {
      $this->_keys = array_reverse($this->_keys);
      $this->_newKeys = array_reverse($this->_newKeys);
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

      $this->_keys[$index]=$key->getKey();
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

    public function permute_keys(int $internalIndexA, int $internalIndexB): void {
      $tmp = $this->_keys[$internalIndexA];
      $this->_keys[$internalIndexA] = $this->_keys[$internalIndexB];
      $this->_keys[$internalIndexB] = $tmp;

      $tmp = $this->_newKeys[$internalIndexA];
      $this->_newKeys[$internalIndexA] = $this->_newKeys[$internalIndexB];
      $this->_newKeys[$internalIndexB] = $tmp;
    }

    public function remove_key(mixed $index): bool {

      /** @var int|false $keyIndex */
      $keyIndex = array_search($index, $this->_keys);
      if(false === $keyIndex)
        return false;
      /** @var int $size */
      $size = $this->count();
      /** @var mixed $tmp */
      $tmp = $this->_keys[$keyIndex];
      unset($this->_values[$tmp]);
      //$tmp = $this->_newKeys[$keyIndex]->getUniqid();
      //unset($this->_newValues[$tmp]);

      /** @var int $i */
      for($i = $keyIndex; $i<$size;$i++) {
        $this->_newKeys[$i]=$this->_newKeys[$i+1];
        $this->_keys[$i]=$this->_keys[$i+1];
      }
      unset($this->_keys[$size-1]);
      unset($this->_newKeys[$size-1]);
      return true;
    }

    public function count_keys(): int {
      return count($this->_newKeys);
    }
}
