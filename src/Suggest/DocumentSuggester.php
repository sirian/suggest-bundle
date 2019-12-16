<?php

namespace Sirian\SuggestBundle\Suggest;

use Doctrine\Bundle\MongoDBBundle\Form\ChoiceList\MongoDBQueryBuilderLoader;
use Doctrine\ODM\MongoDB\Query\Builder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use MongoDB\BSON\Regex;

class DocumentSuggester extends DoctrineSuggester
{
    protected function getLoader(): EntityLoaderInterface
    {
        $options = $this->options;
        return new MongoDBQueryBuilderLoader($this->createQueryBuilder(), $options['manager'], $options['class']);
    }

    protected function getSuggestLoader(SuggestQuery $query): EntityLoaderInterface
    {
        $options = $this->options;
        return new MongoDBQueryBuilderLoader($this->createSuggestQueryBuilder($query), $options['manager'], $options['class']);
    }

    protected function createQueryBuilder(): Builder
    {
        return $this->getManager()->getRepository($this->options['class'])->createQueryBuilder();
    }

    protected function createSuggestQueryBuilder(SuggestQuery $query): Builder
    {
        $qb = $this->createQueryBuilder();
        $offset = max(0, $this->options['limit'] * ($query->page - 1));


        $or = $qb->expr();
        foreach ($this->options['search'] as $field => $searchType) {
            $prefix = $searchType === DocumentSuggester::SEARCH_PREFIX ? '^' : '';
            $suffix = $searchType === DocumentSuggester::SEARCH_SUFFIX ? '$' : '';

            $searchTerm = $prefix . preg_quote($query->searchTerm) . $suffix;
            $flags = 'i';
            if (class_exists(Regex::class)) {
                $regex = new Regex($searchTerm, $flags);
            } else {
                $regex = new \MongoRegex('/' . $searchTerm . '/' . $flags);
            }

            $or->addOr($qb->expr()->field($field)->equals($regex));
        }


        $qb
            ->addAnd($or)
            ->skip($offset)
            ->limit($this->options['limit'] + 1)
            ->sort($this->options['property'], 1)
        ;

        return $qb;
    }
}

