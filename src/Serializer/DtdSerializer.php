<?php

namespace FOPG\Component\UtilsBundle\Serializer;

use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;
use FOPG\Component\UtilsBundle\Serializer\Dtd\DtdClass;
use FOPG\Component\UtilsBundle\Serializer\Response\JsonResponse;

class DtdSerializer extends AbstractSerializer
{
  public static function getExtensions(): array {
    return ["dtd"];
  }

  public function render(): ResponseInterface {

    /**
     * @todo
     */
  return new JsonResponse(['todo']);

  }
}
