<?php

namespace Sirian\SuggestBundle\Suggest;

use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntitySuggester extends DoctrineSuggester
{
    protected function getLoader()
    {
        $options = $this->options;
        return new ORMQueryBuilderLoader($this->createQueryBuilder(), $options['manager'], $options['class']);
    }

    protected function getSuggestLoader(SuggestQuery $query)
    {
        $options = $this->options;
        return new ORMQueryBuilderLoader($this->createSuggestQueryBuilder($query), $options['manager'], $options['class']);
    }

    /**
     * @return QueryBuilder
     */
    protected function createQueryBuilder()
    {
        return $this->getManager()->getRepository($this->options['class'])->createQueryBuilder('e');
    }

    protected function createSuggestQueryBuilder(SuggestQuery $query)
    {
        $qb = $this->createQueryBuilder();
        $alias = $qb->getRootAlias();
        $offset = max(0, $this->options['limit'] * ($query->page - 1));


        $or = $qb->expr()->orX();
        foreach ($this->options['search'] as $field => $searchType) {

            $suffix = in_array($searchType, [DoctrineSuggester::SEARCH_PREFIX, DoctrineSuggester::SEARCH_MIDDLE]) ? '%' : '';
            $prefix = in_array($searchType, [DoctrineSuggester::SEARCH_SUFFIX, DoctrineSuggester::SEARCH_MIDDLE]) ? '%' : '';
            $or->add($alias . '.' . $field . ' LIKE :suggest_' . $field);
            $qb->setParameter('suggest_' . $field, $prefix . $query->searchTerm . $suffix);
        }

        $qb
            ->andWhere($or)
            ->setFirstResult($offset)
            ->setMaxResults($this->options['limit'] + 1)
        ;

        return $qb;
    }
}
