<?php

namespace FOPG\Component\UtilsBundle\Filesystem;

use FOPG\Component\UtilsBundle\Exception\InvalidDirectoryException;

class Directory extends AbstractFso
{
  const STRATEGY_NONE = "NONE";
  const STRATEGY_YMD = "YYYY-MM-DD";

  private ?string $_strategy = null;

  /**
   * Construction du répertoire à partir d'un nom de fichier
   *
   */
  public function __construct(string $directory, string $strategy = self::STRATEGY_YMD, ?\DateTime $localDate=null) {
    $directory = preg_replace("/\/$/","", $directory);
    if(file_exists($directory) && is_dir($directory))
      $this->_directory = $directory;
    else
      throw new InvalidDirectoryException($directory);
    $this->_strategy = $strategy;
    $this->generatePath($localDate);
  }

  private function generatePath(?\DateTime $localDate=null): self {
    $localDate = (null === $localDate) ? new \DateTime() : $localDate;
    switch($this->_strategy) {
      case self::STRATEGY_YMD:
        $year = $localDate->format('Y');
        $this->_directory = $this->_directory.'/'.$year;
        @mkdir($this->_directory);

        $month = $localDate->format('m');
        $this->_directory = $this->_directory.'/'.$month;
        @mkdir($this->_directory);

        $day = $localDate->format('d');
        $this->_directory = $this->_directory.'/'.$day;
        @mkdir($this->_directory);

        if(!file_exists($this->_directory) || !is_dir($this->_directory))
          throw new InvalidDirectoryException($this->_directory);

        break;
      case self::STRATEGY_NONE:
      default:
    }
    return $this;
  }

  public function __toString(): string {
    return $this->_directory;
  }
}
