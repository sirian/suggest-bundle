<?php

namespace Sirian\SuggestBundle\Controller;

use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Sirian\SuggestBundle\Suggest\SuggestQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SuggestController extends Controller
{
    public function suggestAction(Request $request, $name)
    {
        $suggester = $this->getSuggester($name);

        $query = new SuggestQuery();

        $query->searchTerm = $request->get('q');
        $query->page = $request->get('page', 1);
        $query->extra = $request->get('extra');

        $result = $suggester->suggest($query);

        return new JsonResponse($result->toArray());
    }

    public function initAction(Request $request, $name)
    {
        $suggester = $this->getSuggester($name);

        $ids = $request->query->get('ids', '');
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }

        $objects = $suggester->reverseTransform($ids);

        return new JsonResponse($suggester->transform($objects));
    }

    /**
     * @param $name
     * @return SuggesterInterface
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getSuggester($name)
    {
        $registry = $this->get('sirian_suggest.registry');
        if (!$registry->has($name)) {
            throw new NotFoundHttpException();
        }
        return $registry->get($name);
    }
}
