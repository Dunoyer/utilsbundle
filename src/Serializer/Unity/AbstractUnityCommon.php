<?php

namespace FOPG\Component\UtilsBundle\Serializer\Unity;

abstract class AbstractUnityCommon
{
  private ?string $_name = null;

  public function getName(): ?string {
    return $this->_name;
  }

  public function __construct(string $name) {
    $this->_name = $name;
  }
}
