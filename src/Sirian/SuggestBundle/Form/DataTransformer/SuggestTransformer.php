<?php

namespace Sirian\SuggestBundle\Form\DataTransformer;

use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SuggestTransformer implements DataTransformerInterface
{
    private $suggester;
    private $multiple;

    public function __construct(SuggesterInterface $suggester, $multiple = false)
    {
        $this->suggester = $suggester;
        $this->multiple = $multiple;
    }

    public function transform($value)
    {
        if (!$value) {
            return $this->multiple ? [] : null;
        }

        if ($this->multiple && !(is_array($value) || $value instanceof \Traversable)) {
            throw new TransformationFailedException();
        }

        if (!$this->multiple) {
            $value = [$value];
        }

        $items = $this->suggester->transform($value);

        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'id' => $item->id,
                'text' => $item->text,
                'extra' => $item->extra
            ];
        }

        return $this->multiple ? $result : ($result ? $result[0] : null);
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return $this->multiple ? [] : null;
        }
        if (!is_array($id)) {
            $id = [$id];
        }
        if ($id) {
            $result = $this->suggester->reverseTransform($id);
        } else {
            $result = [];
        }

        return $this->multiple ? $result : ($result ? $result[0] : null);
    }
}
