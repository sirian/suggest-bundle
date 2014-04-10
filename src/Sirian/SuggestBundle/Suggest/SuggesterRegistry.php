<?php

namespace Sirian\SuggestBundle\Suggest;

use Symfony\Component\DependencyInjection\ContainerInterface;

class SuggesterRegistry
{
    protected $container;
    protected $suggesters = [];
    protected $suggesterServices = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addSuggester($name, SuggesterInterface $suggester)
    {
        $this->suggesters[$name] = $suggester;
    }

    public function addService($name, $serviceName)
    {
        $this->suggesterServices[$name] = $serviceName;
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
            $this->suggesters[$name] = $this->container->get($this->suggesterServices[$name]);
            return $this->suggesters[$name];
        }

        throw new \InvalidArgumentException(sprintf('Suggester "%s" not registered', $name));
    }
}
