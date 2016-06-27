<?php

namespace Sirian\SuggestBundle\Suggest;

interface SuggesterInterface
{
    /**
     * @param SuggestQuery $query
     * @return Result
     */
    public function suggest(SuggestQuery $query);

    /**
     * @param array|\Traversable $objects
     * @return Item[]
     */
    public function transform($objects);

    /**
     * @param array $ids
     * @return mixed
     */
    public function reverseTransform(array $ids);
}
