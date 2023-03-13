<?php

namespace FOPG\Component\UtilsBundle\Filesystem;

use FOPG\Component\UtilsBundle\Exception\InvalidFilenameException;

class File extends AbstractFso
{
  private ?string $_filename = null;
  private ?string $_extension = null;

  public function __construct(string $filename) {
    if(preg_match(self::REGEXP_DIRECTORY, $filename, $matches)) {
      $this->_extension = $matches['extension'];
      $this->_filename  = $matches['filename'];
    }
    else
      throw new InvalidFilenameException($filename);
  }

  public function getBasename(): string {
    return $this->_filename.'.'.$this->_extension;
  }

  /**
   * Fonction de copie de document
   *
   * @param string $filename Fichier source à copier
   * @param string $newFilename Fichier destination
   * @param bool $force Option d'écrasement si la destination existe
   * @return bool L'action de copie s'est t'elle bien opérée ?
   */
  public static function copy(string $filename, string $newFilename, bool $force=false): bool {

    if(!file_exists($filename))
      return false;

    if(file_exists($newFilename) && (true===$force))
      @unlink($newFilename);

    if(file_exists($newFilename))
      return false;
  
    @copy($filename, $newFilename);
    return file_exists($newFilename);
  }
}
