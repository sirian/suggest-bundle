<?php

namespace Sirian\SuggestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class FormConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $suggester = $container->getDefinition('sirian_suggest.registry');

        foreach ($container->findTaggedServiceIds('sirian_suggester') as $id => $attributes) {
            foreach ($attributes as $attr) {
                if (!isset($attr['alias'])) {
                    throw new \InvalidArgumentException(sprintf('Suggester "%s" must specify "alias" attribute', $id));
                }

                $suggester->addMethodCall('addService', [$attr['alias'], $id]);
            }
        }

        $this->registerFormTheme($container);
    }

    private function registerFormTheme(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');

        $resources[] = 'SirianSuggestBundle:Form:suggest.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
