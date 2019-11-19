<?php

namespace Sirian\SuggestBundle\DependencyInjection;

use Sirian\SuggestBundle\Suggest\DoctrineSuggester;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('sirian_suggest');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // BC layer for symfony/config 4.1 and older
            $rootNode = $treeBuilder->root('sirian_suggest');
        }

        $this->addConfiguration($rootNode, 'odm');
        $this->addConfiguration($rootNode, 'orm');

        $rootNode
            ->children()
                ->arrayNode('form_options')
                    ->prototype('variable')
                    ->end()
                ->end()



        ;

        return $treeBuilder;
    }

    public function addConfiguration(ArrayNodeDefinition $rootNode, $name)
    {
        $searchTypes = [DoctrineSuggester::SEARCH_PREFIX, DoctrineSuggester::SEARCH_MIDDLE, DoctrineSuggester::SEARCH_SUFFIX];
        $rootNode
            ->children()
                ->arrayNode($name)
                ->useAttributeAsKey('id')
                ->prototype('array')
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                        ->scalarNode('id_property')->defaultValue('id')->end()
                        ->scalarNode('property')->defaultValue('name')->end()
                        ->arrayNode('search')
                            ->prototype('scalar')
                            ->treatNullLike('middle')
                                ->validate()
                                    ->ifNotInArray($searchTypes)
                                    ->thenInvalid('Available search types: ' . implode(',', $searchTypes))
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
