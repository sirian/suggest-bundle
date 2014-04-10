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
        if (null === $value) {
            return $this->multiple ? [] : null;
        }

        if ($this->multiple && !(is_array($value) || $value instanceof \Traversable)) {
            throw new TransformationFailedException();
        }

        if (!$this->multiple) {
            $value = [$value];
        }

        $result = $this->suggester->transform($value);

        return $this->multiple ? $result : ($result ? $result[0] : null);
    }

    public function reverseTransform($id)
    {
        if (null === $id) {
            return $this->multiple ? [] : null;
        }

        $id = array_values(array_filter(explode(',', $id)));

        if ($id) {
            $result = $this->suggester->reverseTransform($id);
        } else {
            $result = [];
        }

        return $this->multiple ? $result : ($result ? $result[0] : null);
    }
}
