<?php

namespace Sirian\SuggestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sirian_suggest');
        $rootNode
            ->children()
                ->arrayNode('odm')
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('id_property')->defaultValue('id')->end()
                        ->scalarNode('property')->isRequired()->end()
                        ->arrayNode('search')
                            ->prototype('scalar')
                            ->treatNullLike('middle')
                            ->validate()
                            ->ifNotInArray(['prefix', 'suffix', 'middle'])
                            ->thenInvalid('Available search types: "prefix", "suffix", "middle"')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()

        ;


        return $treeBuilder;
    }
}
