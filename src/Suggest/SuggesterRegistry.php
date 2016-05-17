<?php

namespace Sirian\SuggestBundle\Suggest;

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

    public function addService($name, $serviceName)
    {
        $this->suggesterServices[$name] = $serviceName;
    }

    public function setServiceAlias($serviceName, $alias)
    {
        $this->serviceAliases[$serviceName] = $alias;
    }

    public function has($name)
    {
        return isset($this->suggesters[$name]) || isset($this->suggesterServices[$name]);
    }

    /**
     * @param $name
     * @return SuggesterInterface
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        if (isset($this->suggesters[$name])) {
            return $this->suggesters[$name];
        }

        if (isset($this->suggesterServices[$name])) {
            $serviceName = $this->suggesterServices[$name];

            $suggester = $this->container->get($serviceName);
            if (isset($this->serviceAliases[$serviceName])) {
                $this->suggesterAlias[spl_object_hash($suggester)] = $this->serviceAliases[$serviceName];
            }
            $this->suggesters[$name] = $suggester;
            return $this->suggesters[$name];
        }

        throw new \InvalidArgumentException(sprintf('Suggester "%s" not registered', $name));
    }

    public function getAlias($name)
    {
        $suggester = $this->get($name);
        $hash = spl_object_hash($suggester);
        dump($this->suggesterAlias);
        return isset($this->suggesterAlias[$hash]) ? $this->suggesterAlias[$hash] : $name;
    }
}
