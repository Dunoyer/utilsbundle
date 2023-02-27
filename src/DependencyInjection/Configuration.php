<?php
namespace FOPG\Component\UtilsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface {

    public function getConfigTreeBuilder()
    {
        return new TreeBuilder('fopg_utils');
    }

}
