<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Serializer\PdfSerializer;
use FOPG\Component\UtilsBundle\Serializer\XmlSerializer;
use FOPG\Component\UtilsBundle\ShellCommand\ShellCommand;
use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Test\TestGiven;

class PdfSerializerTest extends TestCase
{
    const SECTION_HEADER = '[Serializer:PdfSerializer]';

    public function testRetrieveData(): void {

      $this->section(self::SECTION_HEADER.' Extraction des données d\'un PDF A3');

      /** @var string $directory Répertoire de stockage des PDF A3 */
      $directory = __DIR__.'/docs/serializer/pdf_serializer';

      $this
        ->given(
          description: "Contrôle de la récupération des informations du PDF",
          directory: $directory
        )
        ->when(
          description: "Je récupére le seul PDF d'un répertoire",
          callback: function(string $directory, ?array &$files=[], ?PdfSerializer &$pdf=null) {
            $files = PdfSerializer::getFiles($directory);
            if(count($files) > 0)
              $pdf = new PdfSerializer($files[0]);
          }
        )
        ->then(
          description: "Le nombre de fichier doit être égal à 1",
          callback: function(?array $files) {
            return count($files);
          },
          result: 1,
          onFail: function(?array $files, TestGiven $whoami) {
            /** @var int $count */
            $count = is_array($files) ? count($files) : 0;
            $whoami->addError("$count fichier(s) trouvé(s), 1 attendu",100);
          }
        )
        ->andThen(
          description: "Je dois retrouver un fichier XML associé au PDF",
          callback: function(PdfSerializer $pdf) {
            $associatedFiles = $pdf->getAssociatedFiles();
            return ( count($associatedFiles) && XmlSerializer::isSerializable($associatedFiles[0]) );
          },
          result: true
        )
      ;
    }
}
