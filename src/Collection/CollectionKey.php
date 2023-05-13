<?php

namespace FOPG\Component\UtilsBundle\Collection;

class CollectionKey {
  private string $_uniqid;
  private mixed $_key;

  public function __construct(mixed $key) {
    $this->_key = $key;
    $this->_uniqid = uniqid();
  }

  public function getUniqid(): string {
    return $this->_uniqid;
  }

  public function getKey(): mixed {
    return $this->_key;
  }
}
