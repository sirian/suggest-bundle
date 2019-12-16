<?php

namespace Sirian\SuggestBundle\Controller;

use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Sirian\SuggestBundle\Suggest\SuggesterRegistry;
use Sirian\SuggestBundle\Suggest\SuggestQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SuggestController
{
    protected $registry;

    public function __construct(SuggesterRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function suggest(Request $request, $name): JsonResponse
    {
        $suggester = $this->getSuggester($name);

        $query = new SuggestQuery();

        $query->searchTerm = $request->get('q');
        $query->page = $request->get('page', 1);
        $query->extra = $request->get('extra');

        $result = $suggester->suggest($query);

        return new JsonResponse($result->toArray());
    }

    public function init(Request $request, $name): JsonResponse
    {
        $suggester = $this->getSuggester($name);

        $ids = $request->query->get('ids', '');
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $objects = $suggester->reverseTransform($ids);

        return new JsonResponse($suggester->transform($objects));
    }

    protected function getSuggester($name): SuggesterInterface
    {
        if (!$this->registry->has($name)) {
            throw new NotFoundHttpException();
        }
        return $this->registry->get($name);
    }
}
