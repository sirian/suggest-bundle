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
     * @param array $items
     * @return Item[]
     */
    public function transform(array $items);

    /**
     * @param array $ids
     * @return mixed
     */
    public function reverseTransform(array $ids);
}
