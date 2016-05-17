<?php

namespace Sirian\SuggestBundle\Suggest;

class Result
{
    /**
     * @var Item[]
     */
    protected $items;

    protected $hasMore;

    public function __construct(array $items, $hasMore = false)
    {
        $this->items = $items;
        $this->hasMore = $hasMore;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    public function hasMore()
    {
        return $this->hasMore;
    }

    public function setHasMore($hasMore)
    {
        $this->hasMore = !!$hasMore;
        return $this;
    }

    public function toArray()
    {
        return [
            'items' => $this->items,
            'hasMore' => $this->hasMore
        ];
    }
}
