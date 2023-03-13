<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Serializer\XmlSerializer;
use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Test\TestGiven;
use FOPG\Component\UtilsBundle\Serializer\Response\JsonResponse;

class XmlSerializerTest extends TestCase
{
    const SECTION_HEADER = '[Serializer:XmlSerializer]';

    public function testRetrieveData(): void {

      $this->section(self::SECTION_HEADER.' Extraction des données d\'un XML');

      /** @var string $directory Répertoire de stockage des XML */
      $directory = __DIR__.'/docs/serializer/xml_serializer';
      /** @var array $arrayRepresentation */
      $arrayRepresentation = [
        "tag" => "article", "attributes" => [],
        "children" => [
          [
            "tag" => "source", "attributes" => [ "id" => "1" ],
            "children" => [
              [ "tag" => "nom", "attributes" => [], "children" => [], "text" => "X" ],
              [ "tag" => "prenom", "attributes" => [], "children" => [],"text" => "Y" ]
            ],
            "text" => ""
          ],
          [
            "tag" => "body", "attributes" => [],
            "children" => [
              [ "tag" => "h1", "attributes" => [], "children" => [], "text" => "Test" ]
            ],
            "text" => ""
          ]
        ],
        "text" => ""
      ];
      /** @var string $jsonRepresentation */
      $jsonRepresentation = json_encode($arrayRepresentation);

      $this
        ->given(
          description: "Contrôle de la récupération des informations du XML",
          directory: $directory
        )
        ->when(
          description: "Je récupére le seul XML d'un répertoire",
          callback: function(string $directory, ?array &$files=[], ?XmlSerializer &$xml=null) {
            $files = XmlSerializer::getFiles($directory);
            if(count($files) > 0)
              $xml = new XmlSerializer($files[0]);
          }
        )
        ->then(
          description: "Je peux retrouver l'information contenu dans le XML",
          callback: function(?XmlSerializer $xml) {
            $data = $xml->render();
            return ($data instanceof JsonResponse) ? $data->getContent() : null;
          },
          result: $jsonRepresentation
        )
      ;
    }
}
