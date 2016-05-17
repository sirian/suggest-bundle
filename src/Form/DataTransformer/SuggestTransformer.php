<?php

namespace Sirian\SuggestBundle\Form\DataTransformer;

use Sirian\SuggestBundle\Suggest\Item;
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
            return null;
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
            $result[] = $this->transform($item);
        }

        if ($this->multiple) {
            return $result;
        }

        if (!$result) {
            return null;
        }

        return $result[0];
    }

    protected function transformItem(Item $item)
    {
        return [
            'id' => $item->id,
            'text' => $item->text,
            'extra' => $item->extra
        ];
    }

    public function reverseTransform($id)
    {
        if (!$id) {
            return $this->multiple ? [] : null;
        }
        if ($this->multiple) {
            if (!is_array($id)) {
                $id = preg_split('/\s*,\s*/', $id, null, PREG_SPLIT_NO_EMPTY);
            }
        } else {
            $id = [$id];
        }

        if ($id) {
            $result = $this->suggester->reverseTransform($id);
        } else {
            $result = [];
        }
        
        if ($this->multiple) {
            return $result;
        }
        
        

        return $result ? $result[0] : null;
    }
}
