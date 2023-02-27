<?php
namespace FOPG\Component\UtilsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class FOPGUtilsBundleExtension extends Extension
{
    /**
     * Extension alias given when we execute command "config:dump-reference" from application who consum Crawler
     */
    public function getAlias(): string
    {
      return 'fopg_utils';
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.yaml');
    }
}
