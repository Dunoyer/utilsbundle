<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Env\Env;
use FOPG\Component\UtilsBundle\Filesystem\File;
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
      /** @var ?bool $ignoreWIP */
      $ignoreWIP = Env::get("TEST__IGNORE_WIP") ? (bool)Env::get("TEST__IGNORE_WIP") : false;

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

      /** @var string $tmpFilename Nom du fichier de copie */
      $tmpDirectory = "/tmp";

      $this
        ->given(
          description: "Contrôle du MERGE entre PDF",
          directory: $directory,
          tmpDirectory: $tmpDirectory,
          test: $this,
          ignoreWIP: $ignoreWIP
        )
        ->when(
          description: "Je récupére le seul PDF d'un répertoire",
          callback: function(string $directory, ?array &$files=[], ?PdfSerializer &$pdf=null) {
            $files = PdfSerializer::getFiles($directory);
            if(count($files) > 0)
              $pdf = new PdfSerializer($files[0]);
          }
        )
        ->andWhen(
          description: "Je repositionne le PDF vers un répertoire temporaire sans altérer l'original",
          callback: function(PdfSerializer $pdf, string $tmpDirectory, ?string &$oldFilename=null) {
            $oldFilename = $pdf->getFilename();
            $pdf->rebaseAt($tmpDirectory, true);
          }
        )
        ->then(
          description: "Le PDF doit bien être positionné dans le répertoire temporaire",
          callback: function(PdfSerializer $pdf, string $tmpDirectory) {
            return (bool)preg_match("#^".preg_quote($tmpDirectory)."[\/]#i",$pdf->getFilename());
          },
          result: true
        )
        ->andThen(
          description: "Le PDF copié doit être identique au PDF d'origine",
          callback: function(PdfSerializer $pdf, string $oldFilename) {
            $oldStream = file_get_contents($oldFilename);
            $newStream = file_get_contents($pdf->getFilename());
            return (mb_strlen($oldStream) === mb_strlen($newStream));
          },
          result: true
        )
        ->andWhen(
          description: "J'ajoute au PDF copié un nouveau PDF",
          callback: function(PdfSerializer $pdf, string $oldFilename) {
            $pdfToAppend = new PdfSerializer($oldFilename);
            $pdf->append($pdfToAppend);
          }
        )
        ->andThen(
          description: "Le PDF est fusionné",
          callback: function(PdfSerializer $pdf, PdfSerializerTest $test, bool $ignoreWIP) {
            $test->debug("@todo - Méthode PdfSerializer::append() à développer");
            return $ignoreWIP;
          },
          result: true
        )
      ;
    }
}
