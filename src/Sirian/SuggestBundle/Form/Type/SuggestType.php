<?php

namespace Sirian\SuggestBundle\Form\Type;

use Sirian\SuggestBundle\Form\DataTransformer\SuggestTransformer;
use Sirian\SuggestBundle\Suggest\Item;
use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SuggestType extends AbstractType
{
    private $suggester;
    private $suggesterName;
    private $name;

    public function __construct(SuggesterInterface $suggester, $suggesterName, $name)
    {
        $this->suggester = $suggester;
        $this->name = $name;
        $this->suggesterName = $suggesterName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new SuggestTransformer($this->suggester, $options['multiple']));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $pos = array_search($this->name, $view->vars['block_prefixes']);
        array_splice($view->vars['block_prefixes'], $pos, 0, 'suggest');

        $ids = [];

        if ($options['multiple']) {
            $ids = array_map(function (Item $item) {
                return $item->id;
            }, $view->vars['value']);
        } elseif ($view->vars['value']) {
            $ids = [$view->vars['value']->id];
        }

        $view->vars = array_merge([
            'ids' => $ids,
            'multiple' => $options['multiple'],
            'suggester_name' => $this->suggesterName
        ], $view->vars);
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'compound' => false,
            'multiple' => false
        ]);
    }

    public function getName()
    {
        return $this->name;
    }
}
