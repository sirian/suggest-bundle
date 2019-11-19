<?php

namespace Sirian\SuggestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SirianSuggestExtension extends Extension
{
    const VERSION_4_COMPATIBLE_DEFINITION = 'Symfony\Component\DependencyInjection\ChildDefinition';
    const LEGACY_COMPATIBLE_DEFINITION = 'Symfony\Component\DependencyInjection\DefinitionDecorator';

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
        $container->setParameter('twig.form.resources', array_merge(['@SirianSuggest/Form/suggest.html.twig'], $resources));
    }

    protected function registerDoctrineSuggesters(ContainerBuilder $container, $suggesterConfigs, $parentService)
    {
        $registry = $container->getDefinition('sirian_suggest.registry');

        if (class_exists(self::VERSION_4_COMPATIBLE_DEFINITION)) {
            $definition = self::VERSION_4_COMPATIBLE_DEFINITION;
        } else {
            $definition = self::LEGACY_COMPATIBLE_DEFINITION;
        }

        foreach ($suggesterConfigs as $id => $config) {
            $definition = new $definition($parentService);
            $definition
                ->replaceArgument(1, [
                    'class' => $config['class'],
                    'id_property' => $config['id_property'],
                    'property' => $config['property'],
                    'search' => $config['search']
                ])
            ;
            $definition->setPublic(true);
            $suggesterId = 'sirian_suggest.odm.' . $id;
            $registry->addMethodCall('addService', [$id, $suggesterId]);
            $container->setDefinition($suggesterId, $definition);
        }
    }
}
