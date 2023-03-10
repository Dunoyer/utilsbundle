<?php

namespace FOPG\Component\UtilsBundle\Serializer;

use FOPG\Component\UtilsBundle\Contracts\SerializerInterface;
use FOPG\Component\UtilsBundle\Exception\InvalidDirectoryException;
use FOPG\Component\UtilsBundle\Exception\InvalidFilenameException;

abstract class AbstractSerializer implements SerializerInterface
{
    private ?string $_filename=null;

    public function __construct(?string $filename=null) {
      if(!empty($filename) && !file_exists($filename))
        throw new InvalidFilenameException($filename);
      $this->_filename = $filename;
    }

    /**
     * Récupération du fichier courant
     *
     * @return ?string
     */
    public function getFilename(): string {
      return $this->_filename;
    }
    /**
     * Génération du chemin absolue à partir d'un répertoire et d'un nom de fichier
     *
     * @param string $directory Répertoire
     * @param string $file Fichier
     * @return string
     */
    private static function getAbsoluteFile(string $directory, string $file): string {
      return preg_replace("/\/+$/","",$directory).'/'.$file;
    }

    /**
     * Le fichier est-il éligible à la sérialisation ?
     *
     * @param string $filename
     * @return bool
     */
    public static function isSerializable(string $filename): bool {
      $whoami = new (get_called_class())();
      /** @var array $extensions */
      $extensions = $whoami->getExtensions();
      /** @var string $regexp */
      $regexp = "(".implode("|", $extensions).")$";
      return ( (!in_array($filename, [".", ".."])) && (bool)preg_match("/$regexp/i", $filename) );
    }

    /**
     * Récupération de fichier
     *
     * @param string $directory Répertoire où sont stocké les fichiers
     * @return array Tableau des fichiers trouvés
     * @throws InvalidDirectoryException Le répertoire passé en paramètre est introuvable
     */
    public static function getFiles(string $directory): array {
      /** @var array $oFiles Fichiers trouvés dans le répertoire cible */
      $oFiles = [];
      if(!file_exists($directory))
        throw new InvalidDirectoryException($directory);

      /** @var string[] $files */
      $files = scandir($directory);
      /** @var string[] $output */
      $output = [];
      foreach ($files as $file) {
        /** @var string $aFile */
        $aFile = self::getAbsoluteFile($directory,$file);
        if(true === self::isSerializable($aFile))
          $oFiles[] = $aFile;
      }
      return $oFiles;
    }
}
