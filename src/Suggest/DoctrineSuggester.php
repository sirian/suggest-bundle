<?php

namespace Sirian\SuggestBundle\Suggest;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class DoctrineSuggester implements SuggesterInterface
{
    const SEARCH_MIDDLE = 'middle';
    const SEARCH_PREFIX = 'prefix';
    const SEARCH_SUFFIX = 'suffix';

    /**
     * @var Options
     */
    protected $options;
    protected $registry;
    protected $propertyAccessor;

    public function __construct(ManagerRegistry $registry, $options)
    {
        $this->registry = $registry;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->options = $this->prepareOptions($options);
    }

    public function reverseTransform(array $ids): array
    {
        return $this->getLoader()->getEntitiesByIds($this->options['id_property'], $ids);
    }

    public function transform($objects): array
    {
        $result = [];
        foreach ($objects as $object) {
            $result[] = $this->transformObject($object);
        }
        return $result;
    }

    public function suggest(SuggestQuery $query): Result
    {
        $entities = $this->getSuggestLoader($query)->getEntities();
        $hasMore = count($entities) > $this->options['limit'];
        $entities = array_slice($entities, 0, $this->getLimit());

        return new Result($this->transform($entities), $hasMore);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'id_property' => 'id',
                'manager' => null,
                'limit' => 20,
                'search' => [],
                'property' => 'name',
            ]);

        $resolver->setNormalizer('search', function(Options $options, $value) {
            if (empty($value) && !empty($options['property'])) {
                $value = [
                    $options['property'] => self::SEARCH_MIDDLE,
                ];
            }
            return $value;
        });

        $resolver->setRequired(['class']);


        $resolver->setNormalizer('manager', function($options, $manager) {
            return $this->selectManager($options, $manager);
        });
    }

    protected function transformObject(object $object): Item
    {
        $item = new Item();
        $item->id = $this->propertyAccessor->getValue($object, $this->options['id_property']);
        $item->text = $this->propertyAccessor->getValue($object, $this->options['property']);

        return $item;
    }

    protected function selectManager(Options $options, ?ObjectManager $manager = null)
    {
        if (null !== $manager) {
            return $this->registry->getManager($manager);
        }

        $manager = $this->registry->getManagerForClass($options['class']);

        if (null === $manager) {
            throw new \RuntimeException(sprintf(
                'Class "%s" seems not to be a managed Doctrine entity.' .
                'Did you forget to map it?',
                $options['class']
            ));
        }

        return $manager;
    }

    protected function prepareOptions(array $options): array
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        return $resolver->resolve($options);
    }

    protected function getManager(): ObjectManager
    {
        return $this->options['manager'];
    }

    protected function getLimit(): int
    {
        return $this->options['limit'];
    }

    abstract protected function getLoader(): EntityLoaderInterface;

    abstract protected function getSuggestLoader(SuggestQuery $query): EntityLoaderInterface;
}
