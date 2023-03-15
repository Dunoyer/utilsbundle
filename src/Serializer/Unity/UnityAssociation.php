<?php

namespace FOPG\Component\UtilsBundle\Serializer\Unity;

class UnityAssociation extends AbstractUnityCommon
{

  private ?UnityAssociationEnd $_associationLft = null;
  private ?UnityAssociationEnd $_associationRgt = null;

  public function __construct(string $name) {
    parent::__construct($name);
  }

  public function setAssociationLft(AssociationEnd $lft): self {
    $this->_associationLft=$lft;
    $lft->setAssociation($this);
    return $this;
  }

  public function getAssociationLft(): ?UnityAssociationEnd {
    return $this->_associationLft;
  }

  public function setAssociationRgt(AssociationEnd $rgt): self {
    $this->_associationRgt=$rgt;
    $rgt->setAssociation($this);
    return $this;
  }

  public function getAssociationRgt(): ?UnityAssociationEnd {
    return $this->_associationRgt;
  }
}
