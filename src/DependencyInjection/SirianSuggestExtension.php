<?php

namespace Sirian\SuggestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SirianSuggestExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->registerFormTheme($container);

        if ($config['odm']) {
            $loader->load('odm.yml');
            $this->registerDoctrineSuggesters($container, $config['odm'], 'sirian_suggest.document_suggester');
        }

        if ($config['orm']) {
            $loader->load('orm.yml');
            $this->registerDoctrineSuggesters($container, $config['orm'], 'sirian_suggest.entity_suggester');
        }

        $container->getDefinition('sirian_suggest.suggest_form_type')->replaceArgument(1, $config['form_options']);
    }

    protected function registerFormTheme(ContainerBuilder $container)
    {
        $resources = $container->getParameter('twig.form.resources');
        $container->setParameter('twig.form.resources', array_merge(['SirianSuggestBundle:Form:suggest.html.twig'], $resources));
    }

    protected function registerDoctrineSuggesters(ContainerBuilder $container, $suggesterConfigs, $parentService)
    {
        $registry = $container->getDefinition('sirian_suggest.registry');

        foreach ($suggesterConfigs as $id => $config) {
            $definition = new DefinitionDecorator($parentService);
            $definition
                ->replaceArgument(1, [
                    'class' => $config['class'],
                    'id_property' => $config['id_property'],
                    'property' => $config['property'],
                    'search' => $config['search']
                ])
            ;

            $suggesterId = 'sirian_suggest.odm.' . $id;

            $registry->addMethodCall('addService', [$id, $suggesterId]);

            $container->setDefinition($suggesterId, $definition);
        }
    }
}
