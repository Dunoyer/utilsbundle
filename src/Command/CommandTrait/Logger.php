<?php

namespace FOPG\Component\UtilsBundle\Command\CommandTrait;

use FOPG\Component\UtilsBundle\Contracts\Command\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

trait Logger {

  public static $DEFAULT_PROGRESS_INTERVAL = 100;
  private ?SymfonyStyle $_io=null;
  private ?int $_mode=null;
  /** @var int */
  private $_progressIterations=0;
  /** @var int */
  private $_progressCurrent=0;

  public function progressStart(int $iterations): self
  {
      $this->_progressCurrent   = 0;
      $this->_progressIterations= $iterations;
      $this->_io->progressStart(self::$DEFAULT_PROGRESS_INTERVAL);

      return $this;
  }

  public function progressAdvance(int $iterations): self
  {
      $step = (int)($this->_progressIterations/self::$DEFAULT_PROGRESS_INTERVAL);

      $stepBefore = (int)($this->_progressCurrent/$step);
      $this->_progressCurrent+= $iterations;
      $stepAfter = (int)($this->_progressCurrent/$step);
      if($stepBefore < $stepAfter){
        $this->_io->progressAdvance($stepAfter-$stepBefore);
      }
      return $this;
  }

  public function progressFinish(): self
  {
      $step = (int)($this->_progressIterations/self::$DEFAULT_PROGRESS_INTERVAL);
      $stepBefore = (int)($step ? $this->_progressCurrent/$step : 0);
      $stepEnd    = self::$DEFAULT_PROGRESS_INTERVAL;
      $this->_io->progressAdvance($stepEnd-$stepBefore);
      return $this;
  }

  public function initLogger(InputInterface $input, OutputInterface $output): void
  {
    $this->setIO(new SymfonyStyle($input, $output));
    /**
     * Debug management
     *
     */
    $this->_mode = ($input->getOption('debug') === "true") ? LoggerInterface::DEBUG_MODE : LoggerInterface::PRODUCTION_MODE;
  }

  public function setIO(SymfonyStyle $io): self
  {
    $this->_io = $io;
    return $this;
  }

  public function getIO(): SymfonyStyle
  {
      return $this->_io;
  }

  public function logError(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_ERROR);
  }

  public function logSuccess(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_SUCCESS);
  }

  public function logTitle(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_TITLE);
  }

  public function logSection(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_SECTION);
  }

  public function logText(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_TEXT);
  }

  public function logTable(array $data, int $tare=0): void
  {
    $level = (int)(($tare-1)/3);
    $prefix = $tare ? str_repeat("  │",$level)."  ├" : "";
    foreach($data as $text => $subDepth) {
      $newPrefix = ($subDepth) ? $prefix.str_repeat("─",3) : $prefix;
      $this->getIO()->text($newPrefix.$text);
    }
  }

  public function logNote(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_NOTE);
  }

  public function logDebug(String $message): void
  {
      $this->log($message, LoggerInterface::LOG_DEBUG);
  }

  public function getMode(): int
  {
      return $this->_mode;
  }

  public function log(String $message, $type = LoggerInterface::LOG_DEBUG): void
  {
      switch($type) {
          case LoggerInterface::LOG_ERROR:
            $this->getIO()->caution($message);
            break;
          case LoggerInterface::LOG_TEXT:
            $this->getIO()->text($message);
            break;
          case LoggerInterface::LOG_NOTE:
            $this->getIO()->note($message);
            break;
          case LoggerInterface::LOG_SUCCESS:
            $this->getIO()->success($message);
            break;
          case LoggerInterface::LOG_TITLE:
            $this->getIO()->title($message);
            break;
          case LoggerInterface::LOG_SECTION:
            $this->getIO()->section($message);
            break;
          case LoggerInterface::LOG_DEBUG:
            if($this->getMode() === self::DEBUG_MODE)
            {
                $this->getIO()->text('[debug]:'.$message);
            }
      }
  }
}
