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

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if ($config['odm']) {
            $loader->load('odm.yml');
            $this->registerDoctrineSuggesters($container, $config['odm'], 'sirian_suggest.document_suggester');
        }

        if ($config['orm']) {
            $loader->load('orm.yml');
            $this->registerDoctrineSuggesters($container, $config['orm'], 'sirian_suggest.entity_suggester');
        }

        $this->registerCustomSuggesters($container, $config['custom']);
    }

    protected function registerDoctrineSuggesters(ContainerBuilder $container, $suggesterConfigs, $parentService)
    {
        $registry = $container->getDefinition('sirian_suggest.registry');

        foreach ($suggesterConfigs as $id => $config) {
            $definition = new DefinitionDecorator($parentService);
            $definition
                ->replaceArgument(1, $config)
            ;

            $suggesterId = 'sirian_suggest.odm.' . $id;

            $registry->addMethodCall('addService', [$id, $suggesterId]);

            $container->setDefinition($suggesterId, $definition);

            $this->registerFormType($container, $suggesterId, $id, $config['form_options']);
        }
    }

    protected function registerFormType(ContainerBuilder $container, $suggesterId, $suggesterName, $defaultOptions)
    {
        $name = 'suggest_' . $suggesterName;

        $formType = new DefinitionDecorator('sirian_suggest.suggest_form_type');
        $formType
            ->replaceArgument(0, new Reference($suggesterId))
            ->replaceArgument(1, $suggesterName)
            ->replaceArgument(2, $name)
            ->replaceArgument(3, $defaultOptions)
            ->addTag('form.type', ['alias' => $name])
        ;

        $container->setDefinition('form.type.' . $name, $formType);
    }

    private function registerCustomSuggesters(ContainerBuilder $container, $config)
    {
        $registry = $container->getDefinition('sirian_suggest.registry');
        foreach ($config as $id => $params) {
            $registry->addMethodCall('addService', [$id, $params['suggester']]);
            $this->registerFormType($container, $params['suggester'], $id, $params['form_options']);
        }
    }
}
