<?php

namespace FOPG\Component\UtilsBundle\Contracts;

interface CollectionInterface {
  public function add(mixed $item, mixed $index=null, bool $includeSort = false): CollectionInterface;
  public function count(): int;
  public function getCmpAlgorithm(): Callable;
  public function getKeys(): array;
  public function getValues(): array;
  public function remove(mixed $index): bool;
}
