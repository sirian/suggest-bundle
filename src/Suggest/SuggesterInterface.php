<?php

namespace Sirian\SuggestBundle\Suggest;

interface SuggesterInterface
{
    public function suggest(SuggestQuery $query): Result;

    /**
     * @param iterable $objects
     * @return Item[]
     */
    public function transform(iterable $objects): array;

    public function reverseTransform(array $ids): array;
}
