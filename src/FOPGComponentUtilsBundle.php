<?php
namespace FOPG\Component\UtilsBundle;

use Doctrine\Bundle\MigrationsBundle\DependencyInjection\CompilerPass\ConfigureDependencyFactoryPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FOPG\Component\UtilsBundle\DependencyInjection\FOPGUtilsBundleExtension;

class FOPGComponentUtilsBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
      return new FOPGUtilsBundleExtension();
    }
}
