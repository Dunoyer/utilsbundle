<?php

namespace FOPG\Component\UtilsBundle\Test;

class TestMessageError extends TestMessage
{
  const RENDER_MESSAGE = "[err. {{code}}] {{message}}";

  private mixed $_code = null;

  public function getCode(): ?string {
    return $this->_code ? (string)$this->_code : null;
  }

  public function __construct(string $message, mixed $code) {
    $this->_code = $code;
    parent::__construct($message);
  }

  public function __toString(): string {
    return str_replace(["{{code}}","{{message}}"],[$this->getCode(), $this->getMessage()], self::RENDER_MESSAGE);
  }
}
