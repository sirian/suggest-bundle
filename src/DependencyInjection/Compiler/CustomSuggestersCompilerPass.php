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
            $registry->addMethodCall('addService', [$container->getDefinition($id)->getClass(), $id]);

            foreach ($tags as $attributes) {
                if (!isset($attributes['alias'])) {
                    continue;
                }

                $registry->addMethodCall('setServiceAlias', [$id, $attributes['alias']]);
                $registry->addMethodCall('addService', [$attributes['alias'], $id]);
            }
        }
    }
}
