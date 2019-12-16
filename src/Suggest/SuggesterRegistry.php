<?php

namespace Sirian\SuggestBundle\Suggest;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SuggesterRegistry
{
    protected $container;
    protected $suggesters = [];
    protected $suggesterServices = [];
    protected $serviceAliases = [];
    protected $suggesterAlias = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addService(string $name, string $serviceName)
    {
        $this->suggesterServices[$name] = $serviceName;
    }

    public function setServiceAlias(string $serviceName, string $alias)
    {
        $this->serviceAliases[$serviceName] = $alias;
    }

    public function has(string $name): bool
    {
        return isset($this->suggesters[$name]) || isset($this->suggesterServices[$name]);
    }

    public function get(string $name): SuggesterInterface
    {
        if (isset($this->suggesters[$name])) {
            return $this->suggesters[$name];
        }

        if (!isset($this->suggesterServices[$name])) {
            throw new \InvalidArgumentException(sprintf('Suggester "%s" not registered', $name));
        }
        $serviceName = $this->suggesterServices[$name];

        $suggester = $this->container->get($serviceName);

        if (isset($this->serviceAliases[$serviceName])) {
            $this->suggesterAlias[spl_object_id($suggester)] = $this->serviceAliases[$serviceName];
        }

        $this->suggesters[$name] = $suggester;
        return $this->suggesters[$name];
    }

    public function getAlias(string $name)
    {
        $suggester = $this->get($name);
        $hash = spl_object_id($suggester);
        return $this->suggesterAlias[$hash] ?? $name;
    }
}
