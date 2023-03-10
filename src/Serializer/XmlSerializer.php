<?php

namespace FOPG\Component\UtilsBundle\Serializer;

class XmlSerializer extends AbstractSerializer
{
  public static function getExtensions(): array {
    return ["xml"];
  }
}
