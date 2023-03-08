<?php

namespace FOPG\Component\UtilsBundle\Test;

use FOPG\Component\UtilsBundle\Contracts\TestMessageInterface;

class TestMessage implements TestMessageInterface
{
  private ?string $_message = null;

  public function getMessage(): ?string {
    return $this->_message;
  }

  public function __construct(string $message) {
    $this->_message = $message;
  }

  public function __toString(): string {
    return $this->getMessage();
  }
}
