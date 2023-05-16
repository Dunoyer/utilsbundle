<?php

namespace FOPG\Component\UtilsBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Command\CommandTrait\Lock;
use FOPG\Component\UtilsBundle\Command\CommandTrait\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command {

  use Logger;
  use Lock;

  /** @var EntityManagerInterface */
  private $_container;

  public function __construct(ContainerInterface $container) {
    $this->_container = $container;
    parent::__construct();
  }

  public function getContainer(): ContainerInterface
  {
    return $this->_container;
  }

  public function getEm(): EntityManagerInterface
  {
    return $this->_container->get('doctrine.orm.entity_manager');
  }

  /**
   * This optional method is the first one executed for a command after configure()
   * and is useful to initialize properties based on the input arguments and options.
   */
  protected function initialize(InputInterface $input, OutputInterface $output): void
  {
      $this->initLogger($input, $output);
  }

  protected function configure()
  {
      $this
      ->addOption('unlock', 'u', InputOption::VALUE_OPTIONAL, 'force to unlock the current task', false)
      ->addOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'is in debug mode ? This option add debug log in the crontab log', false);
  }

  protected function execute(InputInterface $input,OutputInterface $output): int
  {
      $this->initLock($input);
      return 1;
  }
}
