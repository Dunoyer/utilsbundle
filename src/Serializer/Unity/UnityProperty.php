<?php

namespace FOPG\Component\UtilsBundle\Serializer\Unity;

class UnityProperty extends AbstractUnityCommon
{
  private ?string $_type = null;
  private mixed $_value = null;
  private ?UnityClass $_class = null;
  private array $_options = [];
  private bool $_nullable = false;
  public function __construct(string $name) {
    parent::__construct($name);
  }

  public function setNullable(bool $nullable): self {
    $this->_nullable = $nullable;
    return $this;
  }

  public function isNullable(): bool {
    return $this->_nullable;
  }

  public function getOptions(): array {
    return $this->_options;
  }

  public function addOption(string $key, mixed $value): self {
    $this->_options[$key] = $value;
  }

  public function setType(string $type): self {
    $this->_type = $type;
    return $this;
  }

  public function getType(): ?string {
    return $this->_type;
  }

  public function setValue(mixed $value): self {
    $this->_value = $value;
    return $this;
  }

  public function getValue(): mixed {
    return $this->_value;
  }

  public function setClass(UnityClass $class): self {
    $this->_class = $class;
    return $this;
  }

  public function getClass(): ?UnityClass {
    return $this->_class;
  }
}
