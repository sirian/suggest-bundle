<?php

namespace Sirian\SuggestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CustomSuggestersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition('sirian_suggest.registry');

        $taggedServices = $container->findTaggedServiceIds('sirian_suggest.suggester');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $registry->addMethodCall('addService', [$id, $attributes['alias']]);
            }
        }
    }
}
