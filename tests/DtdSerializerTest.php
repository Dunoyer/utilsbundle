<?php

namespace FOPG\Component\UtilsBundle\Tests;

use FOPG\Component\UtilsBundle\Env\Env;
use FOPG\Component\UtilsBundle\Filesystem\File;
use FOPG\Component\UtilsBundle\Serializer\DtdSerializer;
use FOPG\Component\UtilsBundle\ShellCommand\ShellCommand;
use FOPG\Component\UtilsBundle\Test\TestCase;
use FOPG\Component\UtilsBundle\Test\TestGiven;

class DtdSerializerTest extends TestCase
{
    const SECTION_HEADER = '[Serializer:DtdSerializer]';

    public function testRetrieveData(): void {

      $this->section(self::SECTION_HEADER.' Extraction des données d\'une DTD');

      /** @var string $directory Répertoire de stockage des PDF A3 */
      $directory = __DIR__.'/docs/serializer/dtd_serializer';
      /** @var ?bool $ignoreWIP */
      $ignoreWIP = Env::get("TEST__IGNORE_WIP") ? (bool)Env::get("TEST__IGNORE_WIP") : false;

      $this
        ->given(
          description: "Contrôle de la récupération des informations de la DTD",
          directory: $directory,
          ignoreWIP: $ignoreWIP
        )
        ->when(
          description: "Je récupére la seule DTD d'un répertoire",
          callback: function(string $directory, ?array &$files=[], ?DtdSerializer &$dtd=null) {
            $files = DtdSerializer::getFiles($directory);
            if(count($files) > 0)
              $dtd = new DtdSerializer($files[0]);
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
          description: "@todo",
          callback: function(DtdSerializer $dtd, bool $ignoreWIP) {
            die;
            return $ignoreWIP;
          },
          result: true
        )
      ;
    }
}
