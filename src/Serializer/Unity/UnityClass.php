<?php

namespace FOPG\Component\UtilsBundle\Serializer\Unity;

class UnityClass extends AbstractUnityCommon
{
  /** @var Array<int, UnityProperty> */
  private array $_properties = [];
  /** @var Array<int, UnityAssociationEnd> */
  private array $_associations = [];

  public function __construct(string $name) {
    parent::__construct($name);
  }

  public function addAssociation(UnityAssociationEnd $assocation): self {
    $this->_associations[]=$association;
    $assocation->setClass($this);
    return $this;
  }

  public function addProperty(UnityProperty $property): self {
    $this->_properties[]=$property;
    $property->addClass($this);
    return $this;
  }
}
