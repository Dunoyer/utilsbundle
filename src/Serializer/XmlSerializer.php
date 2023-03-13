<?php

namespace FOPG\Component\UtilsBundle\Serializer;

use FOPG\Component\UtilsBundle\Contracts\Response\ResponseInterface;
use FOPG\Component\UtilsBundle\Serializer\Response\JsonResponse;
use FOPG\Component\UtilsBundle\SimpleHtml\SimpleHtml;

class XmlSerializer extends AbstractSerializer
{
  public static function getExtensions(): array {
    return ["xml"];
  }

  public function render(): ResponseInterface {
    /** @var ?string $file */
    $file = $this->getFilename();
    /** @var ?string $content */
    $content = file_get_contents($file);
    if(!empty($content)) {
      /** @var SimpleHtmlDom $dom */
      $dom = SimpleHtml::str_get_xml($content)->getContainer();
      /** @var array $tab */
      $tab = $dom->toArray();
      return new JsonResponse($tab);
    }
  }
}
