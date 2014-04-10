<?php

namespace Sirian\SuggestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class FormConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');

        $resources[] = 'SirianSuggestBundle:Form:suggest.html.twig';

        $container->setParameter('twig.form.resources', $resources);
    }
}
