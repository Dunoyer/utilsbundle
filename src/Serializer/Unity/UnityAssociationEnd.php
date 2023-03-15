<?php

namespace FOPG\Component\UtilsBundle\Serializer\Unity;

class UnityAssociationEnd extends AbstractUnityCommon
{
  const ZERO_TO_ONE = '0..1';
  const ONE_TO_ONE  = '1';
  const ONE_TO_MANY = '1..*';
  const MANY_TO_ONE = '*..1';

  private ?UnityAssociation $_association = null;
  private ?unityClass $_class = null;
  private ?string $_multiplicity = null;

  public function __construct(string $name) {
    parent::__construct($name);
  }

  public function setAssociation(UnityAssociation $association): self {
    $this->_association = $assocation;
    return $this;
  }

  public function getAssociation(): ?UnityAssociation {
    return $this->_association;
  }

  public function setClass(UnityClass $class): self {
    $this->_class = $class;
    return $this;
  }

  public function getClass(): ?UnityClass {
    return $this->_class;
  }

  public function setMultiplicity(string $multiplicity): self {
    $this->_multiplicity = $multiplicity;
    return $this;
  }

  public function getMultiplicity(): ?string {
    return $this->_multiplicity;
  }
}
