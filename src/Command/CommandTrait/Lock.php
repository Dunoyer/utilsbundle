<?php

namespace FOPG\Component\UtilsBundle\Command\CommandTrait;

use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;

trait Lock {
  /** @var String **/
  private $_procId;
  public function initLock(InputInterface $input) {
    $this->_procId = uniqid("PROC");
    /**
     * Unlock management
     *
     */
    $unlock = $input->getOption('unlock');
    if($unlock == 'true')
        $this->unlock();
    $this->lock();
  }

  /**
   * Get lock filename
   *
   * @return String
   */
  private function _getLockFilename():String {
      $filesystem = new Filesystem();
      $directory  = sys_get_temp_dir().'/locks';
      try {
           $filesystem->mkdir($directory);
      }
      catch(IOExceptionInterface $exception) {
      }
      $filename   = $this->_getEncodedFilename($this->getName()).'.lock';
      $aFilename  = "$directory/$filename";
      return $aFilename;
  }

  private static function _getEncodedFilename(String $filename):String
  {
      $ref = preg_replace("/[^a-z0-9]/i",'-',$filename);
      return $ref;
  }

  protected function lock()
  {
      $aFilename  = $this->_getLockFilename();

      if(file_exists($aFilename)) {
          $this->logError('task is locked');
          die();
      }
      else {
          file_put_contents($aFilename,'this file serve to lock the current task. To reactivate this task, just delete this file');
      }
  }

  protected function unlock()
  {
      $aFilename  = $this->_getLockFilename();
      if(file_exists($aFilename))
        unlink($aFilename);
  }
}
