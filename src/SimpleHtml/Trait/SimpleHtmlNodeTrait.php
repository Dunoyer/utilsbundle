<?php

namespace FOPG\Component\UtilsBundle\SimpleHtml\Trait;

use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtmlBase;

trait SimpleHtmlNodeTrait
{
  /**
   * @return ?string
   */
  public function getText(): ?string
  {
      return $this->getNode()->nodeValue;
  }

  /**
   * @return string
   */
  public function __toString()
  {
      return $this->getHtml();
  }
}
