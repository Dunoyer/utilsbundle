<?php

namespace FOPG\Component\UtilsBundle\Contracts;

use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;

interface SerializerInterface
{
  public static function getExtensions(): array;
  public function render(): ResponseInterface;
}
